<?php

namespace App\Observers;

use App\Models\Equipment;

class EquipmentObserver
{
    /**
     * Handle the Equipment "updated" event.
     */
    public function updated(Equipment $equipment): void
    {
        // When equipment status changes from under_maintenance to active,
        // ensure there are no active work orders
        if ($equipment->isDirty('status') && $equipment->status === 'active') {
            $hasActiveWorkOrders = $equipment->workOrders()
                ->whereNotIn('status', ['completed', 'rejected'])
                ->exists();
            
            if ($hasActiveWorkOrders) {
                // Log a warning but don't prevent the update
                \Log::warning('Equipment marked as active but has active work orders', [
                    'equipment_id' => $equipment->id,
                    'equipment_name' => $equipment->name,
                ]);
            }
        }
    }

    /**
     * Handle the Equipment "updating" event.
     */
    public function updating(Equipment $equipment): void
    {
        // Automatically set equipment to under_maintenance if it has active work orders
        // and status is being changed to active
        if ($equipment->isDirty('status') && $equipment->status === 'active') {
            $activeWorkOrders = $equipment->workOrders()
                ->whereIn('status', ['pending', 'approved', 'in_progress'])
                ->count();
            
            if ($activeWorkOrders > 0) {
                // Keep equipment under maintenance if there are active work orders
                $equipment->status = 'under_maintenance';
            }
        }
    }
}
