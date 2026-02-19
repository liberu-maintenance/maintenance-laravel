<div class="space-y-6" role="main" aria-label="Maintenance Dashboard">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
        <div class="bg-white overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200 rounded-lg border border-gray-100">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-50 p-3 rounded-lg">
                        <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-600 truncate">Total Equipment</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ $stats['total_equipment'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200 rounded-lg border border-red-100">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-50 p-3 rounded-lg">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-600 truncate">Overdue Maintenance</dt>
                            <dd class="text-2xl font-semibold text-red-600">{{ $stats['overdue_maintenance'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200 rounded-lg border border-yellow-100">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-50 p-3 rounded-lg">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h2m0 0h2m-2 0v4a2 2 0 002 2h2a2 2 0 002-2v-4m0 0V9a2 2 0 00-2-2H9z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-600 truncate">Pending Work Orders</dt>
                            <dd class="text-2xl font-semibold text-yellow-700">{{ $stats['pending_work_orders'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200 rounded-lg border border-green-100">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-50 p-3 rounded-lg">
                        <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-600 truncate">Completed This Month</dt>
                            <dd class="text-2xl font-semibold text-green-600">{{ $stats['completed_this_month'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overdue Maintenance -->
    <div class="bg-white shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden sm:rounded-lg border border-gray-200">
        <div class="px-4 py-5 sm:px-6 bg-gradient-to-r from-red-50 to-white border-b border-red-100">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg leading-6 font-semibold text-gray-900 flex items-center">
                        <svg class="h-5 w-5 text-red-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        Overdue Maintenance
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-600">Maintenance tasks that require immediate attention</p>
                </div>
                @if($overdueMaintenance->count() > 0)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                        {{ $overdueMaintenance->count() }} {{ Str::plural('item', $overdueMaintenance->count()) }}
                    </span>
                @endif
            </div>
        </div>
        @if($overdueMaintenance->count() > 0)
        <ul role="list" class="divide-y divide-gray-200">
            @foreach($overdueMaintenance as $maintenance)
            <li class="px-4 py-4 sm:px-6 hover:bg-gray-50 transition-colors duration-150">
                <div class="flex items-center justify-between">
                    <div class="flex items-center min-w-0 flex-1">
                        <div class="flex-shrink-0 h-12 w-12">
                            <div class="h-12 w-12 rounded-full bg-red-100 flex items-center justify-center ring-2 ring-red-200">
                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 min-w-0 flex-1">
                            <div class="text-sm font-semibold text-gray-900 truncate">{{ $maintenance->name }}</div>
                            <div class="text-sm text-gray-600 mt-1 flex items-center">
                                <svg class="h-4 w-4 mr-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z" />
                                </svg>
                                {{ $maintenance->equipment->name ?? 'N/A' }}
                            </div>
                            <div class="text-sm text-red-700 font-medium mt-1 flex items-center">
                                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Due: {{ $maintenance->next_due_date->format('M d, Y') }} ({{ $maintenance->next_due_date->diffForHumans() }})
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3 ml-4">
                        @if($maintenance->assignedUser)
                            <span class="text-sm text-gray-600 hidden sm:inline-flex items-center" title="Assigned to {{ $maintenance->assignedUser->name }}">
                                <svg class="h-4 w-4 mr-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ $maintenance->assignedUser->name }}
                            </span>
                        @endif
                        <button 
                            wire:click="markMaintenanceCompleted({{ $maintenance->id }})" 
                            class="bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-150 shadow-sm"
                            aria-label="Mark {{ $maintenance->name }} as complete">
                            <span class="hidden sm:inline">Mark Complete</span>
                            <span class="sm:hidden">Complete</span>
                        </button>
                    </div>
                </div>
            </li>
            @endforeach
        </ul>
        @else
        <div class="px-4 py-12 text-center">
            <svg class="mx-auto h-16 w-16 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">All caught up!</h3>
            <p class="mt-2 text-sm text-gray-600">There are no overdue maintenance tasks at this time.</p>
        </div>
        @endif
    </div>

    <!-- Pending Work Orders -->
    <div class="bg-white shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden sm:rounded-lg border border-gray-200">
        <div class="px-4 py-5 sm:px-6 bg-gradient-to-r from-yellow-50 to-white border-b border-yellow-100">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg leading-6 font-semibold text-gray-900 flex items-center">
                        <svg class="h-5 w-5 text-yellow-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h2m0 0h2m-2 0v4a2 2 0 002 2h2a2 2 0 002-2v-4m0 0V9a2 2 0 00-2-2H9z" />
                        </svg>
                        Pending Work Orders
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-600">Work orders awaiting review and approval</p>
                </div>
                @if($pendingWorkOrders->count() > 0)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                        {{ $pendingWorkOrders->count() }} pending
                    </span>
                @endif
            </div>
        </div>
        @if($pendingWorkOrders->count() > 0)
        <ul role="list" class="divide-y divide-gray-200">
            @foreach($pendingWorkOrders as $workOrder)
            <li class="px-4 py-4 sm:px-6 hover:bg-gray-50 transition-colors duration-150">
                <div class="flex items-center justify-between">
                    <div class="flex items-center min-w-0 flex-1">
                        <div class="flex-shrink-0 h-12 w-12">
                            <div class="h-12 w-12 rounded-full bg-yellow-100 flex items-center justify-center ring-2 ring-yellow-200">
                                <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h2m0 0h2m-2 0v4a2 2 0 002 2h2a2 2 0 002-2v-4m0 0V9a2 2 0 00-2-2H9z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 min-w-0 flex-1">
                            <div class="text-sm font-semibold text-gray-900 truncate">{{ $workOrder->title }}</div>
                            <div class="text-sm text-gray-600 mt-1 truncate">{{ Str::limit($workOrder->description, 60) }}</div>
                            <div class="flex items-center mt-1 space-x-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($workOrder->priority === 'critical') bg-red-100 text-red-800
                                    @elseif($workOrder->priority === 'high') bg-orange-100 text-orange-800
                                    @elseif($workOrder->priority === 'medium') bg-yellow-100 text-yellow-800
                                    @else bg-blue-100 text-blue-800
                                    @endif">
                                    {{ ucfirst($workOrder->priority) }} Priority
                                </span>
                                @if($workOrder->equipment)
                                    <span class="text-xs text-gray-500 flex items-center">
                                        <svg class="h-3 w-3 mr-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z" />
                                        </svg>
                                        {{ $workOrder->equipment->name }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 ml-4">
                        <button 
                            wire:click="approveWorkOrder({{ $workOrder->id }})" 
                            class="bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-150 shadow-sm"
                            aria-label="Approve work order {{ $workOrder->title }}">
                            Approve
                        </button>
                    </div>
                </div>
            </li>
            @endforeach
        </ul>
        @else
        <div class="px-4 py-12 text-center">
            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h2m0 0h2m-2 0v4a2 2 0 002 2h2a2 2 0 002-2v-4m0 0V9a2 2 0 00-2-2H9z" />
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">No pending work orders</h3>
            <p class="mt-2 text-sm text-gray-600">All work orders have been processed or none have been submitted yet.</p>
        </div>
        @endif
    </div>

    <!-- Loading Indicator -->
    <div wire:loading class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50" role="status" aria-live="assertive">
        <div class="bg-white rounded-lg p-6 shadow-xl flex items-center space-x-4">
            <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-900 font-medium">Loading...</span>
        </div>
    </div>
</div>