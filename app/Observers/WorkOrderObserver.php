<?php

namespace App\Observers;

use App\Models\WorkOrder;
use Illuminate\Support\Facades\Cache;

class WorkOrderObserver
{
    /**
     * Clear cache when work order is updated.
     */
    protected function clearCache(): void
    {
        Cache::forget('work_orders.badge_counts');
    }

    /**
     * Handle the WorkOrder "updating" event.
     */
    public function updating(WorkOrder $workOrder): void
    {
        // Auto-set started_at when status changes to in_progress
        if ($workOrder->isDirty('status')) {
            if ($workOrder->status === 'in_progress' && !$workOrder->started_at) {
                $workOrder->started_at = now();
                
                // Update equipment status to under_maintenance when work starts
                if ($workOrder->equipment_id && $workOrder->equipment) {
                    $workOrder->equipment->update(['status' => 'under_maintenance']);
                }
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
        
        $this->clearCache();
    }

    /**
     * Handle the WorkOrder "updated" event.
     */
    public function updated(WorkOrder $workOrder): void
    {
        // Clear cache if status or due_date changed
        if ($workOrder->wasChanged(['status', 'due_date'])) {
            $this->clearCache();
        }
        
        // When a work order is completed or rejected, check if equipment should be set back to active
        if ($workOrder->wasChanged('status') && 
            in_array($workOrder->status, ['completed', 'rejected']) &&
            $workOrder->equipment_id && 
            $workOrder->equipment) {
            
            // Check if there are any other active work orders for this equipment
            $hasOtherActiveWorkOrders = $workOrder->equipment->workOrders()
                ->where('id', '!=', $workOrder->id)
                ->whereIn('status', ['pending', 'approved', 'in_progress'])
                ->exists();
            
            // If no other active work orders, set equipment back to active
            if (!$hasOtherActiveWorkOrders && $workOrder->equipment->status === 'under_maintenance') {
                $workOrder->equipment->update(['status' => 'active']);
            }
        }
    }

    /**
     * Handle the WorkOrder "deleted" event.
     */
    public function deleted(WorkOrder $workOrder): void
    {
        $this->clearCache();
    }

    /**
     * Handle the WorkOrder "restored" event.
     */
    public function restored(WorkOrder $workOrder): void
    {
        $this->clearCache();
    }

    /**
     * Handle the WorkOrder "force deleted" event.
     */
    public function forceDeleted(WorkOrder $workOrder): void
    {
        $this->clearCache();
    }
}
