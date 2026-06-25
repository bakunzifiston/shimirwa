<?php

namespace App\Models\Concerns;

trait ManagesPipelineBatch
{
    /**
     * Computed usable output: quantity_in - loss.
     */
    public function quantityOut(): float
    {
        return max((float) $this->quantity_in - (float) ($this->loss ?? 0), 0);
    }

    /**
     * Balance still available for roasting, milling, or other downstream use.
     */
    public function remainingUsable(): float
    {
        return (float) ($this->quantity_remaining ?? $this->quantityOut());
    }

    /**
     * On create: keep quantity_in as gross input; initialize remaining from quantity_out.
     */
    protected function initializePipelineBatchBalances(): void
    {
        $loss = (float) ($this->loss ?? 0);
        $gross = (float) $this->quantity_in;

        if ($loss > $gross) {
            throw new \InvalidArgumentException('Loss cannot exceed quantity in.');
        }

        $this->quantity_remaining = max($gross - $loss, 0);
    }

    protected function refreshPipelineBatchRemaining(): void
    {
        $originalGross = (float) $this->getOriginal('quantity_in');
        $originalLoss = (float) $this->getOriginal('loss');
        $originalRemaining = (float) $this->getOriginal('quantity_remaining');

        $consumed = max($originalGross - $originalLoss - $originalRemaining, 0);

        $newGross = (float) $this->quantity_in;
        $newLoss = (float) ($this->loss ?? 0);

        if ($newLoss > $newGross) {
            throw new \InvalidArgumentException('Loss cannot exceed quantity in.');
        }

        $this->quantity_remaining = max($newGross - $newLoss - $consumed, 0);
    }
}
