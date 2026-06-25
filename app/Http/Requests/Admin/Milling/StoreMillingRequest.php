<?php

namespace App\Http\Requests\Admin\Milling;

use App\Models\ProductCatalog;
use App\Models\Roasting;
use App\Models\Sorting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreMillingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $items = $this->input('items', []);
        $total = array_sum(array_column($items, 'quantity'));
        $loss  = max((float) $this->input('loss', 0), 0);
        $this->merge([
            'total_mixed_quantity' => $total,
            'output_flour'         => max($total - $loss, 0),
        ]);
    }

    public function rules(): array
    {
        $catalogNames = ProductCatalog::active()->production()->pluck('name')->toArray();

        return [
            'date'                              => ['required', 'date'],
            'batch_number'                      => ['required', 'string', 'max:255'],
            'loss'                              => ['required', 'numeric', 'min:0'],
            'output_flour'                      => ['required', 'numeric', 'min:0'],
            'employee_id'                       => ['required', 'exists:employees,id'],
            'items'                             => ['required', 'array', 'min:1'],
            'items.*.type'                      => ['required', 'string', ...(count($catalogNames) ? ['in:' . implode(',', $catalogNames)] : [])],
            'items.*.source'                    => ['required', 'in:roasting,sorting'],
            'items.*.stock_id'                  => ['required', 'integer', 'min:1'],
            'items.*.quantity'                  => ['required', 'numeric', 'min:0.01'],
            'items.*.overflow'                  => ['sometimes', 'array'],
            'items.*.overflow.*.stock_id'       => ['required_with:items.*.overflow', 'integer', 'min:1'],
            'items.*.overflow.*.quantity'       => ['required_with:items.*.overflow', 'numeric', 'min:0.01'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $items = $this->input('items', []);
            $total = array_sum(array_column($items, 'quantity'));

            if ((float) $this->input('loss', 0) > $total) {
                $validator->errors()->add('loss', 'Loss cannot exceed total mixed quantity.');
            }

            foreach ($items as $i => $item) {
                $source   = $item['source']   ?? '';
                $qty      = (float) ($item['quantity'] ?? 0);
                $overflow = $item['overflow'] ?? [];

                // Build the full allocation list: primary + overflow
                $allocations = [['stock_id' => $item['stock_id'] ?? null, 'quantity' => $qty]];
                foreach ($overflow as $ov) {
                    // overflow quantities are already split by JS; primary qty = total - sum(overflow)
                    $allocations[] = ['stock_id' => $ov['stock_id'] ?? null, 'quantity' => (float) ($ov['quantity'] ?? 0)];
                }

                // When overflow present, primary qty is the remainder after overflow draws
                if (!empty($overflow)) {
                    $overflowQty = array_sum(array_column($overflow, 'quantity'));
                    $allocations[0]['quantity'] = max(round($qty - $overflowQty, 4), 0);
                }

                foreach ($allocations as $j => $alloc) {
                    $stockId = $alloc['stock_id'];
                    $aQty    = (float) $alloc['quantity'];
                    if (!$stockId || $aQty <= 0) continue;

                    if ($source === 'roasting') {
                        $batch = Roasting::find($stockId);
                    } else {
                        $batch = Sorting::find($stockId);
                    }
                    $avail = $batch ? (float) $batch->quantity_out : 0;

                    if (!$batch) {
                        $validator->errors()->add("items.{$i}.stock_id", 'Selected batch not found.');
                    } elseif ($aQty > $avail) {
                        $validator->errors()->add("items.{$i}.quantity",
                            "Exceeds available in batch. Max: " . number_format($avail, 2) . " kg.");
                    }
                }
            }
        });
    }
}
