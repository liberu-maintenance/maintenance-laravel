<div class="space-y-6" role="main" aria-label="Maintenance Tracking">
    <!-- Header with Stats -->
    <div class="bg-white shadow-sm hover:shadow-md transition-shadow duration-200 rounded-lg p-6 border border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 space-y-4 sm:space-y-0">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Maintenance Tracking</h2>
                <p class="mt-1 text-sm text-gray-600">Monitor and manage all maintenance schedules</p>
            </div>
            <div class="flex items-center space-x-2 sm:space-x-4">
                <button 
                    wire:click="toggleViewMode" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150"
                    aria-label="Toggle between grid and list view">
                    @if($viewMode === 'grid')
                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                        List View
                    @else
                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        Grid View
                    @endif
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3 md:gap-4">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-lg border border-blue-200 hover:shadow-md transition-shadow duration-200">
                <div class="text-2xl font-bold text-blue-700">{{ $stats['total_scheduled'] }}</div>
                <div class="text-sm font-medium text-blue-800">Total Scheduled</div>
            </div>
            <div class="bg-gradient-to-br from-red-50 to-red-100 p-4 rounded-lg border border-red-200 hover:shadow-md transition-shadow duration-200">
                <div class="text-2xl font-bold text-red-700">{{ $stats['overdue'] }}</div>
                <div class="text-sm font-medium text-red-800">Overdue</div>
            </div>
            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 p-4 rounded-lg border border-yellow-200 hover:shadow-md transition-shadow duration-200">
                <div class="text-2xl font-bold text-yellow-700">{{ $stats['due_today'] }}</div>
                <div class="text-sm font-medium text-yellow-800">Due Today</div>
            </div>
            <div class="bg-gradient-to-br from-orange-50 to-orange-100 p-4 rounded-lg border border-orange-200 hover:shadow-md transition-shadow duration-200">
                <div class="text-2xl font-bold text-orange-700">{{ $stats['due_this_week'] }}</div>
                <div class="text-sm font-medium text-orange-800">Due This Week</div>
            </div>
            <div class="bg-gradient-to-br from-green-50 to-green-100 p-4 rounded-lg border border-green-200 hover:shadow-md transition-shadow duration-200 col-span-2 sm:col-span-1">
                <div class="text-2xl font-bold text-green-700">{{ $stats['completed_this_month'] }}</div>
                <div class="text-sm font-medium text-green-800">Completed This Month</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Filter Maintenance Items</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div>
                <label for="search-term" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input 
                    id="search-term"
                    wire:model.debounce.300ms="searchTerm" 
                    type="text" 
                    placeholder="Search maintenance..."
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                    aria-label="Search maintenance items">
            </div>

            <div>
                <label for="equipment-filter" class="block text-sm font-medium text-gray-700 mb-1">Equipment</label>
                <select 
                    id="equipment-filter"
                    wire:model="selectedEquipment" 
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                    aria-label="Filter by equipment">
                    <option value="">All Equipment</option>
                    @foreach($equipmentList as $equipment)
                        <option value="{{ $equipment->id }}">{{ $equipment->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select 
                    id="status-filter"
                    wire:model="selectedStatus" 
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                    aria-label="Filter by status">
                    <option value="all">All Status</option>
                    <option value="overdue">Overdue</option>
                    <option value="due_soon">Due Soon</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="completed">Completed</option>
                </select>
            </div>

            <div>
                <label for="priority-filter" class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                <select 
                    id="priority-filter"
                    wire:model="selectedPriority" 
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                    aria-label="Filter by priority">
                    <option value="all">All Priorities</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                </select>
            </div>

            <div>
                <label for="date-range-filter" class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                <select 
                    id="date-range-filter"
                    wire:model="dateRange" 
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                    aria-label="Filter by due date">
                    <option value="all">All Dates</option>
                    <option value="today">Due Today</option>
                    <option value="week">Due This Week</option>
                    <option value="month">Due This Month</option>
                </select>
            </div>

            <div class="flex items-end">
                <button 
                    wire:click="$refresh" 
                    class="w-full bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-150 shadow-sm"
                    aria-label="Refresh maintenance list">
                    <svg class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Maintenance Items -->
    @if($viewMode === 'grid')
        <!-- Grid View -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($maintenanceItems as $item)
                <div class="bg-white shadow rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-200">
                    <!-- Status Indicator -->
                    <div class="h-2 
                        @if($item->next_due_date < now()) bg-red-500
                        @elseif($item->next_due_date < now()->addDays(7)) bg-yellow-500
                        @else bg-green-500
                        @endif">
                    </div>

                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 truncate">{{ $item->name }}</h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($item->priority === 'critical') bg-red-100 text-red-800
                                @elseif($item->priority === 'high') bg-orange-100 text-orange-800
                                @elseif($item->priority === 'medium') bg-yellow-100 text-yellow-800
                                @else bg-green-100 text-green-800
                                @endif">
                                {{ ucfirst($item->priority) }}
                            </span>
                        </div>

                        <div class="space-y-2 mb-4">
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z" />
                                </svg>
                                {{ $item->equipment->name ?? 'No Equipment' }}
                            </div>

                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Due: {{ $item->next_due_date->format('M d, Y') }}
                                @if($item->next_due_date < now())
                                    <span class="ml-2 text-red-600 font-medium">({{ $item->next_due_date->diffForHumans() }})</span>
                                @endif
                            </div>

                            @if($item->assignedUser)
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    {{ $item->assignedUser->name }}
                                </div>
                            @endif
                        </div>

                        <div class="flex space-x-2">
                            @if($item->assignedUser && $item->assignedUser->id === auth()->id())
                                <button wire:click="markCompleted({{ $item->id }})" 
                                        class="flex-1 bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm font-medium">
                                    Complete
                                </button>
                            @endif

                            <button wire:click="createWorkOrder({{ $item->id }})" 
                                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm font-medium">
                                Create Work Order
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h2m0 0h2m-2 0v4a2 2 0 002 2h2a2 2 0 002-2v-4m0 0V9a2 2 0 00-2-2H9z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No maintenance items found</h3>
                    <p class="mt-1 text-sm text-gray-500">Try adjusting your filters or search terms.</p>
                </div>
            @endforelse
        </div>
    @else
        <!-- List View -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200">
                @forelse($maintenanceItems as $item)
                    <li class="px-6 py-4 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <!-- Status Indicator -->
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 rounded-full flex items-center justify-center
                                        @if($item->next_due_date < now()) bg-red-100
                                        @elseif($item->next_due_date < now()->addDays(7)) bg-yellow-100
                                        @else bg-green-100
                                        @endif">
                                        <div class="h-3 w-3 rounded-full
                                            @if($item->next_due_date < now()) bg-red-500
                                            @elseif($item->next_due_date < now()->addDays(7)) bg-yellow-500
                                            @else bg-green-500
                                            @endif">
                                        </div>
                                    </div>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center space-x-3">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $item->name }}</p>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($item->priority === 'critical') bg-red-100 text-red-800
                                            @elseif($item->priority === 'high') bg-orange-100 text-orange-800
                                            @elseif($item->priority === 'medium') bg-yellow-100 text-yellow-800
                                            @else bg-green-100 text-green-800
                                            @endif">
                                            {{ ucfirst($item->priority) }}
                                        </span>
                                    </div>
                                    <div class="flex items-center space-x-4 mt-1">
                                        <p class="text-sm text-gray-500">{{ $item->equipment->name ?? 'No Equipment' }}</p>
                                        <p class="text-sm text-gray-500">Due: {{ $item->next_due_date->format('M d, Y') }}</p>
                                        @if($item->assignedUser)
                                            <p class="text-sm text-gray-500">Assigned: {{ $item->assignedUser->name }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center space-x-2">
                                @if($item->assignedUser && $item->assignedUser->id === auth()->id())
                                    <button wire:click="markCompleted({{ $item->id }})" 
                                            class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">
                                        Complete
                                    </button>
                                @endif

                                <button wire:click="createWorkOrder({{ $item->id }})" 
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                                    Work Order
                                </button>
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="px-6 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h2m0 0h2m-2 0v4a2 2 0 002 2h2a2 2 0 002-2v-4m0 0V9a2 2 0 00-2-2H9z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No maintenance items found</h3>
                        <p class="mt-1 text-sm text-gray-500">Try adjusting your filters or search terms.</p>
                    </li>
                @endforelse
            </ul>
        </div>
    @endif

    <!-- Pagination -->
    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6 rounded-b-lg shadow-sm">
        {{ $maintenanceItems->links() }}
    </div>

    <!-- Loading Indicator -->
    <div wire:loading class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50" role="status" aria-live="polite">
        <div class="bg-white rounded-lg p-6 shadow-xl flex items-center space-x-4">
            <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-900 font-medium">Loading maintenance items...</span>
        </div>
    </div>

    <!-- Success Notification -->
    @if (session()->has('success'))
        <div 
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 5000)"
            class="fixed bottom-4 right-4 bg-green-600 text-white px-6 py-4 rounded-lg shadow-2xl z-50 flex items-center space-x-3 max-w-md animate-slide-in"
            role="alert"
            aria-live="assertive">
            <svg class="h-6 w-6 text-white flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
            <button @click="show = false" class="ml-4 text-white hover:text-gray-200 focus:outline-none" aria-label="Close notification">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif
</div>

<script>
    document.addEventListener('livewire:load', function () {
        Livewire.on('maintenanceCompleted', function (name) {
            // Show success animation or notification
            console.log('Maintenance completed:', name);
        });

        Livewire.on('workOrderCreated', function (id) {
            console.log('Work order created:', id);
        });
    });
</script>