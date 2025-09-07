<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Equipment;
use App\Models\MaintenanceSchedule;
use App\Models\WorkOrder;
use App\Models\Task;

class MaintenanceDashboard extends Component
{
    public $overdueMaintenance = [];
    public $dueSoonMaintenance = [];
    public $pendingWorkOrders = [];
    public $criticalEquipment = [];
    public $stats = [];

    public function mount()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        // Load overdue maintenance
        $this->overdueMaintenance = MaintenanceSchedule::overdue()
            ->with(['equipment', 'assignedUser'])
            ->limit(5)
            ->get();

        // Load maintenance due soon
        $this->dueSoonMaintenance = MaintenanceSchedule::dueSoon(7)
            ->with(['equipment', 'assignedUser'])
            ->limit(5)
            ->get();

        // Load pending work orders
        $this->pendingWorkOrders = WorkOrder::pending()
            ->with(['equipment', 'reviewer'])
            ->limit(5)
            ->get();

        // Load critical equipment
        $this->criticalEquipment = Equipment::critical()
            ->active()
            ->limit(5)
            ->get();

        // Calculate stats
        $this->stats = [
            'total_equipment' => Equipment::active()->count(),
            'overdue_maintenance' => MaintenanceSchedule::overdue()->count(),
            'pending_work_orders' => WorkOrder::pending()->count(),
            'completed_this_month' => WorkOrder::completed()
                ->whereMonth('updated_at', now()->month)
                ->count(),
        ];
    }

    public function markMaintenanceCompleted($scheduleId)
    {
        $schedule = MaintenanceSchedule::find($scheduleId);
        if ($schedule) {
            $schedule->markCompleted();
            $this->loadDashboardData();
            $this->emit('maintenanceCompleted', $schedule->name);
        }
    }

    public function approveWorkOrder($workOrderId)
    {
        $workOrder = WorkOrder::find($workOrderId);
        if ($workOrder) {
            $workOrder->update([
                'status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
            $this->loadDashboardData();
            $this->emit('workOrderApproved', $workOrder->title);
        }
    }

    public function render()
    {
        return view('livewire.maintenance-dashboard');
    }
}