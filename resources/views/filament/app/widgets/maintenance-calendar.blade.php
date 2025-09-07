<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ static::$heading }}
        </x-slot>

        <div class="space-y-4">
            <!-- Summary Stats -->
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-3 border border-blue-200">
                    <div class="text-2xl font-bold text-blue-700">{{ $this->getViewData()['totalUpcoming'] }}</div>
                    <div class="text-sm text-blue-600">Upcoming (14 days)</div>
                </div>
                <div class="bg-gradient-to-r from-red-50 to-red-100 rounded-lg p-3 border border-red-200">
                    <div class="text-2xl font-bold text-red-700">{{ $this->getViewData()['overdueCount'] }}</div>
                    <div class="text-sm text-red-600">Overdue Items</div>
                </div>
            </div>

            <!-- Calendar Grid -->
            <div class="space-y-2">
                <h4 class="font-semibold text-gray-900 text-sm uppercase tracking-wide">Next 14 Days</h4>
                <div class="space-y-1 max-h-64 overflow-y-auto">
                    @foreach($this->getViewData()['calendarData'] as $day)
                        <div class="flex items-start space-x-3 p-2 rounded-lg hover:bg-gray-50 transition-colors
                            {{ $day['is_today'] ? 'bg-blue-50 border border-blue-200' : '' }}
                            {{ $day['is_weekend'] ? 'bg-gray-50' : '' }}">

                            <!-- Date Column -->
                            <div class="flex-shrink-0 text-center min-w-[60px]">
                                <div class="text-xs text-gray-500 uppercase">
                                    {{ $day['date']->format('D') }}
                                </div>
                                <div class="text-lg font-semibold 
                                    {{ $day['is_today'] ? 'text-blue-700' : 'text-gray-900' }}">
                                    {{ $day['date']->format('j') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $day['date']->format('M') }}
                                </div>
                            </div>

                            <!-- Maintenance Items -->
                            <div class="flex-1 min-w-0">
                                @if($day['maintenance_items']->count() > 0)
                                    <div class="space-y-1">
                                        @foreach($day['maintenance_items'] as $item)
                                            <div class="flex items-center space-x-2">
                                                <div class="w-2 h-2 rounded-full 
                                                    {{ $item->next_due_date->isPast() ? 'bg-red-500' : 
                                                       ($item->next_due_date->isToday() ? 'bg-orange-500' : 'bg-green-500') }}">
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="text-sm font-medium text-gray-900 truncate">
                                                        {{ $item->equipment->name ?? 'Unknown Equipment' }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 truncate">
                                                        {{ $item->maintenance_type }} - {{ $item->frequency }}
                                                    </div>
                                                </div>
                                                <div class="text-xs text-gray-400">
                                                    {{ $item->next_due_date->format('g:i A') }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-sm text-gray-400 italic">
                                        No maintenance scheduled
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Pending Work Orders -->
            @if($this->getViewData()['pendingWorkOrders']->count() > 0)
                <div class="border-t pt-4">
                    <h4 class="font-semibold text-gray-900 text-sm uppercase tracking-wide mb-2">
                        Pending Work Orders
                    </h4>
                    <div class="space-y-2">
                        @foreach($this->getViewData()['pendingWorkOrders'] as $workOrder)
                            <div class="flex items-center space-x-3 p-2 bg-yellow-50 rounded-lg border border-yellow-200">
                                <div class="w-2 h-2 rounded-full 
                                    {{ $workOrder->priority === 'urgent' ? 'bg-red-500' : 
                                       ($workOrder->priority === 'high' ? 'bg-orange-500' : 'bg-yellow-500') }}">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium text-gray-900 truncate">
                                        {{ $workOrder->title }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ ucfirst($workOrder->priority) }} priority • {{ $workOrder->location }}
                                    </div>
                                </div>
                                <div class="text-xs text-gray-400">
                                    {{ $workOrder->submitted_at->diffForHumans() }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>