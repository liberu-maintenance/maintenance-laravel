<?php

namespace App\Observers;

use App\Models\WorkOrder;

class WorkOrderObserver
{
    /**
     * Handle the WorkOrder "updating" event.
     */
    public function updating(WorkOrder $workOrder): void
    {
        // Auto-set started_at when status changes to in_progress
        if ($workOrder->isDirty('status')) {
            if ($workOrder->status === 'in_progress' && !$workOrder->started_at) {
                $workOrder->started_at = now();
            }
            
            // Auto-set completed_at when status changes to completed
            if ($workOrder->status === 'completed' && !$workOrder->completed_at) {
                $workOrder->completed_at = now();
            }
        }
    }

    /**
     * Handle the WorkOrder "created" event.
     */
    public function created(WorkOrder $workOrder): void
    {
        // Set submitted_at if not already set
        if (!$workOrder->submitted_at) {
            $workOrder->update(['submitted_at' => now()]);
        }
    }

    /**
     * Handle the WorkOrder "deleted" event.
     */
    public function deleted(WorkOrder $workOrder): void
    {
        //
    }

    /**
     * Handle the WorkOrder "restored" event.
     */
    public function restored(WorkOrder $workOrder): void
    {
        //
    }

    /**
     * Handle the WorkOrder "force deleted" event.
     */
    public function forceDeleted(WorkOrder $workOrder): void
    {
        //
    }
}
