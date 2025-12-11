<?php

namespace App\Observers;

use App\Models\KpiTarget;

class KpiTargetObserver
{
    /**
     * Handle the KpiTarget "creating" event.
     */
    public function creating(KpiTarget $kpiTarget): void
    {
        // Auto-set created_by if not provided
        if (auth()->check()) {
            $kpiTarget->created_by = auth()->id();
        }
    }
}
