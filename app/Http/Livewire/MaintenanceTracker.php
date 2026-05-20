<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Equipment;
use App\Models\MaintenanceSchedule;
use App\Models\WorkOrder;
use Carbon\Carbon;

class MaintenanceTracker extends Component
{
    use WithPagination;

    public $selectedEquipment = null;
    public $selectedStatus = 'all';
    public $selectedPriority = 'all';
    public $searchTerm = '';
    public $viewMode = 'grid'; // grid or list
    public $dateRange = 'all';

    protected $queryString = [
        'selectedEquipment' => ['except' => null],
        'selectedStatus' => ['except' => 'all'],
        'selectedPriority' => ['except' => 'all'],
        'searchTerm' => ['except' => ''],
        'viewMode' => ['except' => 'grid'],
    ];

    public function mount()
    {
        $this->resetPage();
    }

    public function updatingSearchTerm()
    {
        $this->resetPage();
    }

    public function updatingSelectedEquipment()
    {
        $this->resetPage();
    }

    public function updatingSelectedStatus()
    {
        $this->resetPage();
    }

    public function updatingSelectedPriority()
    {
        $this->resetPage();
    }

    public function getMaintenanceItemsProperty()
    {
        $query = MaintenanceSchedule::with(['equipment', 'assignedUser'])
            ->when($this->searchTerm, function ($q) {
                $q->where('name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhereHas('equipment', function ($eq) {
                      $eq->where('name', 'like', '%' . $this->searchTerm . '%');
                  });
            })
            ->when($this->selectedEquipment, function ($q) {
                $q->where('equipment_id', $this->selectedEquipment);
            })
            ->when($this->selectedStatus !== 'all', function ($q) {
                if ($this->selectedStatus === 'overdue') {
                    $q->overdue();
                } elseif ($this->selectedStatus === 'due_soon') {
                    $q->dueSoon(7);
                } else {
                    $q->where('status', $this->selectedStatus);
                }
            })
            ->when($this->selectedPriority !== 'all', function ($q) {
                $q->where('priority', $this->selectedPriority);
            })
            ->when($this->dateRange !== 'all', function ($q) {
                switch ($this->dateRange) {
                    case 'today':
                        $q->whereDate('next_due_date', today());
                        break;
                    case 'week':
                        $q->whereBetween('next_due_date', [now(), now()->addWeek()]);
                        break;
                    case 'month':
                        $q->whereBetween('next_due_date', [now(), now()->addMonth()]);
                        break;
                }
            })
            ->orderBy('next_due_date');

        return $query->paginate(12);
    }

    public function getEquipmentListProperty()
    {
        return Equipment::active()->orderBy('name')->get();
    }

    public function getStatsProperty()
    {
        return [
            'total_scheduled' => MaintenanceSchedule::active()->count(),
            'overdue' => MaintenanceSchedule::overdue()->count(),
            'due_today' => MaintenanceSchedule::active()
                ->whereDate('next_due_date', today())->count(),
            'due_this_week' => MaintenanceSchedule::dueSoon(7)->count(),
            'completed_this_month' => MaintenanceSchedule::active()
                ->whereMonth('last_completed_date', now()->month)->count(),
        ];
    }

    public function markCompleted($scheduleId)
    {
        $schedule = MaintenanceSchedule::find($scheduleId);
        if ($schedule && $schedule->assignedUser && $schedule->assignedUser->id === auth()->id()) {
            $schedule->markCompleted();
            $this->emit('maintenanceCompleted', $schedule->name);
            session()->flash('success', "Maintenance '{$schedule->name}' marked as completed!");
        }
    }

    public function createWorkOrder($scheduleId)
    {
        $schedule = MaintenanceSchedule::find($scheduleId);
        if ($schedule) {
            $workOrder = WorkOrder::create([
                'title' => "Maintenance: {$schedule->name}",
                'description' => $schedule->description ?? "Scheduled maintenance for {$schedule->equipment->name}",
                'priority' => $schedule->priority,
                'status' => 'approved',
                'equipment_id' => $schedule->equipment_id,
                'maintenance_schedule_id' => $schedule->id,
                'submitted_at' => now(),
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            $this->emit('workOrderCreated', $workOrder->id);
            session()->flash('success', "Work order created for '{$schedule->name}'!");
        }
    }

    public function toggleViewMode()
    {
        $this->viewMode = $this->viewMode === 'grid' ? 'list' : 'grid';
    }

    public function render()
    {
        return view('livewire.maintenance-tracker', [
            'maintenanceItems' => $this->maintenanceItems,
            'equipmentList' => $this->equipmentList,
            'stats' => $this->stats,
        ]);
    }
}