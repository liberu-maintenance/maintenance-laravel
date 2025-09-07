<div class="space-y-6">
    <!-- Header with Stats -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Maintenance Tracking</h2>
            <div class="flex items-center space-x-4">
                <button wire:click="toggleViewMode" 
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    @if($viewMode === 'grid')
                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                        List View
                    @else
                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        Grid View
                    @endif
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-blue-600">{{ $stats['total_scheduled'] }}</div>
                <div class="text-sm text-blue-800">Total Scheduled</div>
            </div>
            <div class="bg-red-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-red-600">{{ $stats['overdue'] }}</div>
                <div class="text-sm text-red-800">Overdue</div>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-yellow-600">{{ $stats['due_today'] }}</div>
                <div class="text-sm text-yellow-800">Due Today</div>
            </div>
            <div class="bg-orange-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-orange-600">{{ $stats['due_this_week'] }}</div>
                <div class="text-sm text-orange-800">Due This Week</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-green-600">{{ $stats['completed_this_month'] }}</div>
                <div class="text-sm text-green-800">Completed This Month</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input wire:model.debounce.300ms="searchTerm" 
                       type="text" 
                       placeholder="Search maintenance..."
                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Equipment</label>
                <select wire:model="selectedEquipment" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Equipment</option>
                    @foreach($equipmentList as $equipment)
                        <option value="{{ $equipment->id }}">{{ $equipment->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select wire:model="selectedStatus" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="all">All Status</option>
                    <option value="overdue">Overdue</option>
                    <option value="due_soon">Due Soon</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="completed">Completed</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                <select wire:model="selectedPriority" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="all">All Priorities</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                <select wire:model="dateRange" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="all">All Dates</option>
                    <option value="today">Due Today</option>
                    <option value="week">Due This Week</option>
                    <option value="month">Due This Month</option>
                </select>
            </div>

            <div class="flex items-end">
                <button wire:click="$refresh" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
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
    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        {{ $maintenanceItems->links() }}
    </div>

    @if (session()->has('success'))
        <div class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
            {{ session('success') }}
        </div>
    @endif
</div>

<script>
    document.addEventListener('livewire:load', function () {
        Livewire.on('maintenanceCompleted', function (name) {
            // You can add custom JavaScript here for animations or notifications
            console.log('Maintenance completed:', name);
        });

        Livewire.on('workOrderCreated', function (id) {
            console.log('Work order created:', id);
        });
    });
</script>