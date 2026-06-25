<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Emballage;
use App\Models\Employee;
use App\Models\Milling;
use App\Models\RawMaterialStock;
use App\Models\Sale;
use App\Models\Sorting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryPipelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_pipeline_from_reception_to_sale(): void
    {
        $employee = Employee::create([
            'full_name' => 'Pipeline Tester',
            'national_id' => 'ID-PIPE-001',
            'phone_number' => '0780000001',
            'gender' => 'Male',
            'province' => 'Kigali',
            'district' => 'Gasabo',
            'position' => 'Operator',
            'start_date' => now()->toDateString(),
        ]);

        $supplier = Client::create([
            'full_name' => 'Supplier One',
            'client_type' => 'Company',
            'role' => 'supplier',
            'supplier_code' => 'SUP-001',
            'phone' => '0781000001',
            'email' => 'supplier@example.com',
            'address' => 'Kigali',
        ]);

        $buyer = Client::create([
            'full_name' => 'Buyer One',
            'client_type' => 'Individual',
            'role' => 'client',
            'phone' => '0782000001',
            'email' => 'buyer@example.com',
            'address' => 'Kigali',
        ]);

        $raw = RawMaterialStock::create([
            'date' => now()->toDateString(),
            'client_id' => $supplier->id,
            'type' => 'Raw Material',
            'item' => 'Sorghum',
            'received' => 100,
            'rejected' => 0,
            'batch_number' => 'RM-SOR-001',
            'employee_id' => $employee->id,
        ]);
        $this->assertSame(100.0, $raw->fresh()->quantity_in);

        $packagingStock = RawMaterialStock::create([
            'date' => now()->toDateString(),
            'client_id' => $supplier->id,
            'type' => 'Packaging Staff',
            'item' => '1kg',
            'received' => 200,
            'rejected' => 0,
            'batch_number' => 'PKG-1KG-001',
            'employee_id' => $employee->id,
        ]);

        $sorting = Sorting::create([
            'date' => now()->toDateString(),
            'raw_material_stock_id' => $raw->id,
            'quantity_in' => 60,
            'loss' => 0,
            'employee_id' => $employee->id,
        ]);
        $this->assertSame(40.0, $raw->fresh()->quantity_in);
        $this->assertSame(60.0, $sorting->fresh()->quantity_remaining);

        $milling = Milling::create([
            'date' => now()->toDateString(),
            'batch_number' => 'MILL-001',
            'loss' => 5,
            'employee_id' => $employee->id,
            'items' => [
                ['type' => 'sorghum', 'stock_id' => $sorting->id, 'quantity' => 50],
            ],
        ]);
        $this->assertSame(50.0, $milling->fresh()->total_mixed_quantity);
        $this->assertSame(45.0, $milling->fresh()->output_flour);
        $this->assertSame(10.0, $sorting->fresh()->quantity_remaining);

        $emballage = Emballage::create([
            'date' => now()->toDateString(),
            'packaging_batch_id' => 'PKG-BATCH-001',
            'packaging_type' => '1kg',
            'raw_material_stock_id' => $packagingStock->id,
            'milling_id' => $milling->id,
            'item' => 10,
            'quantity' => 10,
            'unit_price' => 0,
            'total_price' => 0,
            'comment' => '',
            'employee_id' => $employee->id,
        ]);
        $this->assertSame(190.0, $packagingStock->fresh()->quantity_in);
        $this->assertSame(35.0, $milling->fresh()->output_flour);
        $this->assertEquals(10, $emballage->fresh()->item);

        $sale = Sale::create([
            'date' => now()->toDateString(),
            'item' => 'Sorghum flour 1kg',
            'client_id' => $buyer->id,
            'employee_id' => $employee->id,
            'returned' => 0,
            'batches' => [
                [
                    'emballage_id' => $emballage->id,
                    'quantity' => 4,
                    'unit_price' => 1500,
                    'line_total' => 6000,
                ],
            ],
        ]);
        $this->assertSame(4, $sale->fresh()->quantity);
        $this->assertSame(6000.0, (float) $sale->fresh()->total_price);
        $this->assertEquals(6, $emballage->fresh()->item);

        $sale->delete();
        $this->assertEquals(10, $emballage->fresh()->item);
    }

    public function test_packaging_cannot_be_edited_or_deleted_when_referenced_by_sale(): void
    {
        [$emballage, $sale] = $this->createPackagingWithSale();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('referenced by sales');
        $emballage->update(['item' => 5]);
    }

    public function test_packaging_delete_blocked_when_referenced_by_sale(): void
    {
        [$emballage] = $this->createPackagingWithSale();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('referenced by sales');
        $emballage->delete();
    }

    public function test_packaging_metadata_can_still_be_updated_when_referenced_by_sale(): void
    {
        [$emballage] = $this->createPackagingWithSale();

        $emballage->update(['comment' => 'Delivered to shop']);

        $this->assertSame('Delivered to shop', $emballage->fresh()->comment);
    }

    /**
     * @return array{0: Emballage, 1: Sale}
     */
    private function createPackagingWithSale(): array
    {
        $employee = Employee::create([
            'full_name' => 'Guard Tester',
            'national_id' => 'ID-GUARD-001',
            'phone_number' => '0780000002',
            'gender' => 'Female',
            'province' => 'Kigali',
            'district' => 'Gasabo',
            'position' => 'Operator',
            'start_date' => now()->toDateString(),
        ]);

        $supplier = Client::create([
            'full_name' => 'Supplier Two',
            'client_type' => 'Company',
            'role' => 'supplier',
            'supplier_code' => 'SUP-002',
            'phone' => '0781000002',
            'address' => 'Kigali',
        ]);

        $buyer = Client::create([
            'full_name' => 'Buyer Two',
            'client_type' => 'Individual',
            'role' => 'client',
            'phone' => '0782000002',
            'address' => 'Kigali',
        ]);

        $packagingStock = RawMaterialStock::create([
            'date' => now()->toDateString(),
            'client_id' => $supplier->id,
            'type' => 'Packaging Staff',
            'item' => '1kg',
            'received' => 50,
            'rejected' => 0,
            'batch_number' => 'PKG-1KG-002',
            'employee_id' => $employee->id,
        ]);

        $milling = Milling::create([
            'date' => now()->toDateString(),
            'batch_number' => 'MILL-002',
            'loss' => 0,
            'employee_id' => $employee->id,
            'items' => [],
        ]);
        $milling->forceFill(['output_flour' => 20, 'total_mixed_quantity' => 20])->save();

        $emballage = Emballage::create([
            'date' => now()->toDateString(),
            'packaging_batch_id' => 'PKG-BATCH-002',
            'packaging_type' => '1kg',
            'raw_material_stock_id' => $packagingStock->id,
            'milling_id' => $milling->id,
            'item' => 8,
            'quantity' => 8,
            'unit_price' => 0,
            'total_price' => 0,
            'comment' => '',
            'employee_id' => $employee->id,
        ]);

        $sale = Sale::create([
            'date' => now()->toDateString(),
            'item' => 'Test flour',
            'client_id' => $buyer->id,
            'employee_id' => $employee->id,
            'batches' => [
                [
                    'emballage_id' => $emballage->id,
                    'quantity' => 2,
                    'unit_price' => 1000,
                    'line_total' => 2000,
                ],
            ],
        ]);

        return [$emballage->fresh(), $sale->fresh()];
    }
}
