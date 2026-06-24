<?php

namespace App\Http\Requests\Admin\Sorting;

class UpdateSortingRequest extends StoreSortingRequest
{
    protected function allowedDepletedBatchId(): ?int
    {
        return (int) $this->route('sorting')?->raw_material_stock_id ?: null;
    }
}
