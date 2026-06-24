<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\RawMaterialStock;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidationRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_batch_number_for_same_type_is_rejected(): void
    {
        $employee = $this->createEmployee('ID-VAL-001');
        $supplier = $this->createSupplier();

        RawMaterialStock::create([
            'date' => now()->toDateString(),
            'client_id' => $supplier->id,
            'type' => 'Raw Material',
            'item' => 'Maize',
            'received' => 50,
            'rejected' => 0,
            'batch_number' => 'BATCH-DUP-001',
            'employee_id' => $employee->id,
        ]);

        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.raw-material-stocks.store'), [
            'date' => now()->toDateString(),
            'client_id' => $supplier->id,
            'type' => 'Raw Material',
            'item' => 'Soy',
            'received' => 20,
            'rejected' => 0,
            'batch_number' => 'BATCH-DUP-001',
            'employee_id' => $employee->id,
        ]);

        $response->assertSessionHasErrors('batch_number');
    }

    public function test_duplicate_national_id_is_rejected(): void
    {
        Employee::create([
            'full_name' => 'Existing Employee',
            'national_id' => '1199887766554433',
            'phone_number' => '0780000100',
            'gender' => 'Male',
            'province' => 'Kigali',
            'district' => 'Gasabo',
            'position' => 'Staff',
            'start_date' => now()->toDateString(),
        ]);

        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.employees.store'), [
            'full_name' => 'Another Employee',
            'national_id' => '1199887766554433',
            'phone_number' => '0780000101',
            'gender' => 'Female',
            'province' => 'Kigali',
            'district' => 'Gasabo',
            'position' => 'Staff',
            'start_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('national_id');
    }

    private function createEmployee(string $nationalId): Employee
    {
        return Employee::create([
            'full_name' => 'Validation Employee',
            'national_id' => $nationalId,
            'phone_number' => '0780000099',
            'gender' => 'Male',
            'province' => 'Kigali',
            'district' => 'Gasabo',
            'position' => 'Staff',
            'start_date' => now()->toDateString(),
        ]);
    }

    private function createSupplier(): Client
    {
        return Client::create([
            'full_name' => 'Validation Supplier',
            'client_type' => 'Company',
            'role' => 'supplier',
            'supplier_code' => 'SUP-VAL-'.uniqid(),
            'phone' => '0781000099',
            'address' => 'Kigali',
        ]);
    }
}
