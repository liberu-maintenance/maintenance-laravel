<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Equipment</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['total_equipment'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Overdue Maintenance</dt>
                            <dd class="text-lg font-medium text-red-600">{{ $stats['overdue_maintenance'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h2m0 0h2m-2 0v4a2 2 0 002 2h2a2 2 0 002-2v-4m0 0V9a2 2 0 00-2-2H9z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Pending Work Orders</dt>
                            <dd class="text-lg font-medium text-yellow-600">{{ $stats['pending_work_orders'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Completed This Month</dt>
                            <dd class="text-lg font-medium text-green-600">{{ $stats['completed_this_month'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overdue Maintenance -->
    @if($overdueMaintenance->count() > 0)
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Overdue Maintenance</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Maintenance tasks that are past due</p>
        </div>
        <ul class="divide-y divide-gray-200">
            @foreach($overdueMaintenance as $maintenance)
            <li class="px-4 py-4 sm:px-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            <div class="h-10 w-10 rounded-full bg-red-100 flex items-center justify-center">
                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">{{ $maintenance->name }}</div>
                            <div class="text-sm text-gray-500">{{ $maintenance->equipment->name ?? 'N/A' }}</div>
                            <div class="text-sm text-red-600">Due: {{ $maintenance->next_due_date->format('M d, Y') }}</div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        @if($maintenance->assignedUser)
                            <span class="text-sm text-gray-500">{{ $maintenance->assignedUser->name }}</span>
                        @endif
                        <button wire:click="markMaintenanceCompleted({{ $maintenance->id }})" 
                                class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">
                            Mark Complete
                        </button>
                    </div>
                </div>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Pending Work Orders -->
    @if($pendingWorkOrders->count() > 0)
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Pending Work Orders</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Work orders awaiting approval</p>
        </div>
        <ul class="divide-y divide-gray-200">
            @foreach($pendingWorkOrders as $workOrder)
            <li class="px-4 py-4 sm:px-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            <div class="h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center">
                                <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h2m0 0h2m-2 0v4a2 2 0 002 2h2a2 2 0 002-2v-4m0 0V9a2 2 0 00-2-2H9z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">{{ $workOrder->title }}</div>
                            <div class="text-sm text-gray-500">{{ Str::limit($workOrder->description, 50) }}</div>
                            <div class="text-sm text-gray-500">Priority: {{ ucfirst($workOrder->priority) }}</div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button wire:click="approveWorkOrder({{ $workOrder->id }})" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                            Approve
                        </button>
                    </div>
                </div>
            </li>
            @endforeach
        </ul>
    </div>
    @endif
</div>