<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Equipment;
use App\Models\MaintenanceSchedule;
use App\Models\WorkOrder;
use App\Models\Task;
use Carbon\Carbon;

class IntuitiveMaintenanceTracker extends Component
{
    use WithPagination;

    public $activeTab = 'overview';
    public $searchTerm = '';
    public $selectedEquipment = null;
    public $selectedPriority = 'all';
    public $selectedStatus = 'all';
    public $viewMode = 'cards';
    public $showQuickActions = true;
    public $autoRefresh = true;

    // Quick action properties
    public $showQuickMaintenanceModal = false;
    public $quickMaintenanceEquipment = null;
    public $quickMaintenanceType = 'inspection';
    public $quickMaintenanceNotes = '';

    protected $queryString = [
        'activeTab' => ['except' => 'overview'],
        'searchTerm' => ['except' => ''],
        'selectedEquipment' => ['except' => null],
        'selectedPriority' => ['except' => 'all'],
        'selectedStatus' => ['except' => 'all'],
        'viewMode' => ['except' => 'cards'],
    ];

    protected $listeners = [
        'refreshData' => 'loadData',
        'maintenanceCompleted' => 'handleMaintenanceCompleted',
        'workOrderCreated' => 'handleWorkOrderCreated',
    ];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        // This method will be called to refresh data
        $this->emit('dataRefreshed');
    }

    public function getOverviewStatsProperty()
    {
        return [
            'critical_alerts' => MaintenanceSchedule::overdue()->where('priority', 'critical')->count(),
            'due_today' => MaintenanceSchedule::active()->whereDate('next_due_date', today())->count(),
            'due_this_week' => MaintenanceSchedule::dueSoon(7)->count(),
            'in_progress' => WorkOrder::where('status', 'in_progress')->count(),
            'completed_today' => MaintenanceSchedule::whereDate('last_completed_date', today())->count(),
            'equipment_health' => $this->calculateEquipmentHealth(),
        ];
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
            ->when($this->selectedPriority !== 'all', function ($q) {
                $q->where('priority', $this->selectedPriority);
            })
            ->when($this->selectedStatus !== 'all', function ($q) {
                if ($this->selectedStatus === 'overdue') {
                    $q->overdue();
                } elseif ($this->selectedStatus === 'due_soon') {
                    $q->dueSoon(7);
                } else {
                    $q->where('status', $this->selectedStatus);
                }
            });

        // Sort by urgency
        $query->orderByRaw("
            CASE 
                WHEN next_due_date < NOW() AND priority = 'critical' THEN 1
                WHEN next_due_date < NOW() AND priority = 'high' THEN 2
                WHEN next_due_date < NOW() THEN 3
                WHEN next_due_date < DATE_ADD(NOW(), INTERVAL 1 DAY) THEN 4
                WHEN next_due_date < DATE_ADD(NOW(), INTERVAL 7 DAY) THEN 5
                ELSE 6
            END
        ");

        return $query->paginate(12);
    }

    public function getEquipmentListProperty()
    {
        return Equipment::active()->orderBy('name')->get();
    }

    public function getCriticalAlertsProperty()
    {
        return MaintenanceSchedule::overdue()
            ->where('priority', 'critical')
            ->with(['equipment', 'assignedUser'])
            ->limit(5)
            ->get();
    }

    public function getUpcomingMaintenanceProperty()
    {
        return MaintenanceSchedule::dueSoon(7)
            ->with(['equipment', 'assignedUser'])
            ->orderBy('next_due_date')
            ->limit(10)
            ->get();
    }

    public function getActiveWorkOrdersProperty()
    {
        return WorkOrder::whereIn('status', ['approved', 'in_progress'])
            ->with(['equipment', 'reviewer'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    private function calculateEquipmentHealth()
    {
        $totalEquipment = Equipment::active()->count();
        if ($totalEquipment === 0) return 100;

        $criticalEquipment = Equipment::critical()->count();
        $overdueMaintenanceCount = MaintenanceSchedule::overdue()->distinct('equipment_id')->count();

        $healthScore = 100 - (($criticalEquipment + $overdueMaintenanceCount) / $totalEquipment * 100);
        return max(0, min(100, round($healthScore)));
    }

    public function quickCompleteMaintenanceTask($scheduleId)
    {
        $schedule = MaintenanceSchedule::find($scheduleId);
        if ($schedule && ($schedule->assignedUser && $schedule->assignedUser->id === auth()->id() || auth()->user()->hasRole('admin'))) {
            $schedule->markCompleted();
            $this->emit('maintenanceCompleted', $schedule->name);
            session()->flash('success', "✅ Maintenance '{$schedule->name}' completed successfully!");
            $this->loadData();
        }
    }

    public function quickCreateWorkOrder($scheduleId)
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
            session()->flash('success', "🔧 Work order created for '{$schedule->name}'!");
            $this->loadData();
        }
    }

    public function postponeMaintenanceTask($scheduleId, $days = 7)
    {
        $schedule = MaintenanceSchedule::find($scheduleId);
        if ($schedule && auth()->user()->hasRole('admin')) {
            $schedule->update([
                'next_due_date' => $schedule->next_due_date->addDays($days)
            ]);
            session()->flash('success', "📅 Maintenance '{$schedule->name}' postponed by {$days} days");
            $this->loadData();
        }
    }

    public function openQuickMaintenanceModal($equipmentId = null)
    {
        $this->quickMaintenanceEquipment = $equipmentId;
        $this->quickMaintenanceType = 'inspection';
        $this->quickMaintenanceNotes = '';
        $this->showQuickMaintenanceModal = true;
    }

    public function submitQuickMaintenance()
    {
        $this->validate([
            'quickMaintenanceEquipment' => 'required|exists:equipment,id',
            'quickMaintenanceType' => 'required|in:inspection,repair,cleaning,calibration',
            'quickMaintenanceNotes' => 'required|min:10',
        ]);

        $equipment = Equipment::find($this->quickMaintenanceEquipment);

        // Create a quick maintenance schedule
        $schedule = MaintenanceSchedule::create([
            'name' => ucfirst($this->quickMaintenanceType) . " - {$equipment->name}",
            'description' => $this->quickMaintenanceNotes,
            'equipment_id' => $this->quickMaintenanceEquipment,
            'frequency_type' => 'one_time',
            'frequency_value' => 1,
            'priority' => 'medium',
            'status' => 'active',
            'next_due_date' => now(),
            'assigned_user_id' => auth()->id(),
        ]);

        // Mark as completed immediately
        $schedule->markCompleted();

        $this->showQuickMaintenanceModal = false;
        session()->flash('success', "⚡ Quick {$this->quickMaintenanceType} completed for {$equipment->name}!");
        $this->loadData();
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function toggleViewMode()
    {
        $this->viewMode = $this->viewMode === 'cards' ? 'list' : 'cards';
    }

    public function toggleAutoRefresh()
    {
        $this->autoRefresh = !$this->autoRefresh;
    }

    public function handleMaintenanceCompleted($name)
    {
        $this->loadData();
    }

    public function handleWorkOrderCreated($id)
    {
        $this->loadData();
    }

    public function getUrgencyLevel($schedule)
    {
        $dueDate = $schedule->next_due_date;
        $now = now();

        if ($dueDate < $now) {
            return $schedule->priority === 'critical' ? 'critical-overdue' : 'overdue';
        } elseif ($dueDate < $now->addDay()) {
            return 'due-today';
        } elseif ($dueDate < $now->addDays(3)) {
            return 'due-soon';
        } else {
            return 'normal';
        }
    }

    public function getUrgencyColor($urgencyLevel)
    {
        return match($urgencyLevel) {
            'critical-overdue' => 'bg-red-600 text-white',
            'overdue' => 'bg-red-500 text-white',
            'due-today' => 'bg-yellow-500 text-white',
            'due-soon' => 'bg-orange-400 text-white',
            default => 'bg-green-500 text-white'
        };
    }

    public function render()
    {
        return view('livewire.intuitive-maintenance-tracker', [
            'overviewStats' => $this->overviewStats,
            'maintenanceItems' => $this->maintenanceItems,
            'equipmentList' => $this->equipmentList,
            'criticalAlerts' => $this->criticalAlerts,
            'upcomingMaintenance' => $this->upcomingMaintenance,
            'activeWorkOrders' => $this->activeWorkOrders,
        ]);
    }
}