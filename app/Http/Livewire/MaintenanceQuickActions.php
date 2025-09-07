<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Equipment;
use App\Models\MaintenanceSchedule;
use App\Models\WorkOrder;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Support\Facades\Auth;

class MaintenanceQuickActions extends Component
{
    public $showQuickMaintenanceModal = false;
    public $showQuickTaskModal = false;
    public $showQuickWorkOrderModal = false;

    // Quick Maintenance Properties
    public $quickMaintenanceEquipment = null;
    public $quickMaintenanceType = 'inspection';
    public $quickMaintenanceNotes = '';
    public $quickMaintenancePriority = 'medium';

    // Quick Task Properties
    public $quickTaskTitle = '';
    public $quickTaskDescription = '';
    public $quickTaskPriority = 'medium';
    public $quickTaskEquipment = null;
    public $quickTaskAssignee = null;
    public $quickTaskDueDate = null;

    // Quick Work Order Properties
    public $quickWorkOrderTitle = '';
    public $quickWorkOrderDescription = '';
    public $quickWorkOrderPriority = 'medium';
    public $quickWorkOrderEquipment = null;

    protected $rules = [
        'quickMaintenanceEquipment' => 'required|exists:equipment,id',
        'quickMaintenanceType' => 'required|in:inspection,repair,cleaning,calibration,preventive',
        'quickMaintenanceNotes' => 'required|min:10',
        'quickMaintenancePriority' => 'required|in:low,medium,high,critical',

        'quickTaskTitle' => 'required|string|max:255',
        'quickTaskDescription' => 'required|string|min:10',
        'quickTaskPriority' => 'required|in:low,medium,high,urgent',
        'quickTaskEquipment' => 'nullable|exists:equipment,id',
        'quickTaskAssignee' => 'nullable|exists:users,id',
        'quickTaskDueDate' => 'nullable|date|after:today',

        'quickWorkOrderTitle' => 'required|string|max:255',
        'quickWorkOrderDescription' => 'required|string|min:10',
        'quickWorkOrderPriority' => 'required|in:low,medium,high,urgent',
        'quickWorkOrderEquipment' => 'nullable|exists:equipment,id',
    ];

    public function mount()
    {
        $this->quickTaskDueDate = now()->addDays(7)->format('Y-m-d');
    }

    public function openQuickMaintenanceModal($equipmentId = null)
    {
        $this->quickMaintenanceEquipment = $equipmentId;
        $this->quickMaintenanceType = 'inspection';
        $this->quickMaintenanceNotes = '';
        $this->quickMaintenancePriority = 'medium';
        $this->showQuickMaintenanceModal = true;
    }

    public function openQuickTaskModal($equipmentId = null)
    {
        $this->quickTaskEquipment = $equipmentId;
        $this->quickTaskTitle = '';
        $this->quickTaskDescription = '';
        $this->quickTaskPriority = 'medium';
        $this->quickTaskAssignee = null;
        $this->quickTaskDueDate = now()->addDays(7)->format('Y-m-d');
        $this->showQuickTaskModal = true;
    }

    public function openQuickWorkOrderModal($equipmentId = null)
    {
        $this->quickWorkOrderEquipment = $equipmentId;
        $this->quickWorkOrderTitle = '';
        $this->quickWorkOrderDescription = '';
        $this->quickWorkOrderPriority = 'medium';
        $this->showQuickWorkOrderModal = true;
    }

    public function submitQuickMaintenance()
    {
        $this->validate([
            'quickMaintenanceEquipment' => 'required|exists:equipment,id',
            'quickMaintenanceType' => 'required|in:inspection,repair,cleaning,calibration,preventive',
            'quickMaintenanceNotes' => 'required|min:10',
            'quickMaintenancePriority' => 'required|in:low,medium,high,critical',
        ]);

        $equipment = Equipment::find($this->quickMaintenanceEquipment);

        // Create a quick maintenance schedule
        $schedule = MaintenanceSchedule::create([
            'name' => ucfirst($this->quickMaintenanceType) . " - {$equipment->name}",
            'description' => $this->quickMaintenanceNotes,
            'equipment_id' => $this->quickMaintenanceEquipment,
            'frequency_type' => 'one_time',
            'frequency_value' => 1,
            'priority' => $this->quickMaintenancePriority,
            'status' => 'active',
            'next_due_date' => now(),
            'assigned_user_id' => auth()->id(),
            'company_id' => auth()->user()->currentTeam->id,
        ]);

        // Mark as completed immediately
        $schedule->markCompleted();

        $this->showQuickMaintenanceModal = false;
        $this->resetQuickMaintenanceForm();

        session()->flash('success', "⚡ Quick {$this->quickMaintenanceType} completed for {$equipment->name}!");
        $this->emit('maintenanceCompleted');
    }

    public function submitQuickTask()
    {
        $this->validate([
            'quickTaskTitle' => 'required|string|max:255',
            'quickTaskDescription' => 'required|string|min:10',
            'quickTaskPriority' => 'required|in:low,medium,high,urgent',
            'quickTaskEquipment' => 'nullable|exists:equipment,id',
            'quickTaskAssignee' => 'nullable|exists:users,id',
            'quickTaskDueDate' => 'nullable|date|after:today',
        ]);

        $task = Task::create([
            'title' => $this->quickTaskTitle,
            'description' => $this->quickTaskDescription,
            'priority' => $this->quickTaskPriority,
            'status' => 'pending',
            'equipment_id' => $this->quickTaskEquipment ?: null,
            'assigned_to' => $this->quickTaskAssignee ?: null,
            'created_by' => Auth::id(),
            'company_id' => Auth::user()->currentTeam->id,
            'due_date' => $this->quickTaskDueDate ? \Carbon\Carbon::parse($this->quickTaskDueDate) : now()->addDays(7),
        ]);

        // Send notification if assigned to someone
        if ($this->quickTaskAssignee) {
            $assignee = User::find($this->quickTaskAssignee);
            $assignee->notify(new TaskAssignedNotification($task));
        }

        $this->showQuickTaskModal = false;
        $this->resetQuickTaskForm();

        session()->flash('success', "📋 Task '{$this->quickTaskTitle}' created successfully!");
        $this->emit('taskCreated');
    }

    public function submitQuickWorkOrder()
    {
        $this->validate([
            'quickWorkOrderTitle' => 'required|string|max:255',
            'quickWorkOrderDescription' => 'required|string|min:10',
            'quickWorkOrderPriority' => 'required|in:low,medium,high,urgent',
            'quickWorkOrderEquipment' => 'nullable|exists:equipment,id',
        ]);

        $workOrder = WorkOrder::create([
            'title' => $this->quickWorkOrderTitle,
            'description' => $this->quickWorkOrderDescription,
            'priority' => $this->quickWorkOrderPriority,
            'status' => 'approved',
            'equipment_id' => $this->quickWorkOrderEquipment ?: null,
            'submitted_at' => now(),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'company_id' => auth()->user()->currentTeam->id,
        ]);

        $this->showQuickWorkOrderModal = false;
        $this->resetQuickWorkOrderForm();

        session()->flash('success', "🔧 Work order '{$this->quickWorkOrderTitle}' created successfully!");
        $this->emit('workOrderCreated');
    }

    public function closeModals()
    {
        $this->showQuickMaintenanceModal = false;
        $this->showQuickTaskModal = false;
        $this->showQuickWorkOrderModal = false;
    }

    public function resetQuickMaintenanceForm()
    {
        $this->quickMaintenanceEquipment = null;
        $this->quickMaintenanceType = 'inspection';
        $this->quickMaintenanceNotes = '';
        $this->quickMaintenancePriority = 'medium';
    }

    public function resetQuickTaskForm()
    {
        $this->quickTaskTitle = '';
        $this->quickTaskDescription = '';
        $this->quickTaskPriority = 'medium';
        $this->quickTaskEquipment = null;
        $this->quickTaskAssignee = null;
        $this->quickTaskDueDate = now()->addDays(7)->format('Y-m-d');
    }

    public function resetQuickWorkOrderForm()
    {
        $this->quickWorkOrderTitle = '';
        $this->quickWorkOrderDescription = '';
        $this->quickWorkOrderPriority = 'medium';
        $this->quickWorkOrderEquipment = null;
    }

    public function getEquipmentListProperty()
    {
        return Equipment::where('company_id', Auth::user()->currentTeam->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function getUsersListProperty()
    {
        return User::whereHas('teams', function ($query) {
            $query->where('team_id', Auth::user()->currentTeam->id);
        })->orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.maintenance-quick-actions', [
            'equipmentList' => $this->equipmentList,
            'usersList' => $this->usersList,
        ]);
    }
}