<x-filament-panels::page>
    <form wire:submit="generateReport">
        {{ $this->form }}

        <div class="mt-6 flex gap-3">
            <x-filament::button type="submit">
                Generate Report
            </x-filament::button>

            @if($reportData)
                <x-filament::button color="success" wire:click="exportCsv">
                    Export CSV
                </x-filament::button>
            @endif
        </div>
    </form>

    @if($reportData)
        <div class="mt-8 space-y-6">
            <!-- Summary Section -->
            <x-filament::section>
                <x-slot name="heading">
                    Report Summary
                </x-slot>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Mean Time To Repair</dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">{{ number_format($reportData['mttr'], 2) }} hrs</dd>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Cost</dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">${{ number_format($reportData['cost_analysis']['total_cost'], 2) }}</dd>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Parts Cost</dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">${{ number_format($reportData['cost_analysis']['parts_cost'], 2) }}</dd>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Labor Cost</dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">${{ number_format($reportData['cost_analysis']['labor_cost'], 2) }}</dd>
                    </div>
                </div>
            </x-filament::section>

            <!-- Equipment Performance -->
            <x-filament::section>
                <x-slot name="heading">
                    Top Equipment by Cost
                </x-slot>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">Equipment</th>
                                <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">Serial Number</th>
                                <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">Work Orders</th>
                                <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">Uptime %</th>
                                <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">Total Cost</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach(array_slice($reportData['equipment_performance'], 0, 10) as $equipment)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $equipment['equipment_name'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $equipment['serial_number'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $equipment['work_order_count'] }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium
                                            @if($equipment['uptime_percentage'] >= 95) bg-green-100 text-green-700
                                            @elseif($equipment['uptime_percentage'] >= 80) bg-yellow-100 text-yellow-700
                                            @else bg-red-100 text-red-700
                                            @endif">
                                            {{ number_format($equipment['uptime_percentage'], 2) }}%
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">${{ number_format($equipment['total_cost'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>

            <!-- Technician Performance -->
            <x-filament::section>
                <x-slot name="heading">
                    Technician Performance
                </x-slot>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">Technician</th>
                                <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">Assigned</th>
                                <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">Completed</th>
                                <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">Completion Rate</th>
                                <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">Avg Time (hrs)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($reportData['technician_performance'] as $tech)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $tech['technician_name'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $tech['total_assigned'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $tech['completed'] }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium
                                            @if($tech['completion_rate'] >= 80) bg-green-100 text-green-700
                                            @elseif($tech['completion_rate'] >= 60) bg-yellow-100 text-yellow-700
                                            @else bg-red-100 text-red-700
                                            @endif">
                                            {{ number_format($tech['completion_rate'], 2) }}%
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ number_format($tech['average_completion_time_hours'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>

            <!-- Actionable Insights -->
            <x-filament::section>
                <x-slot name="heading">
                    Actionable Insights
                </x-slot>

                <div class="space-y-4">
                    @if(count($reportData['actionable_insights']) > 0)
                        @foreach($reportData['actionable_insights'] as $insight)
                            <div class="rounded-lg border p-4 @if($insight['type'] === 'critical') border-red-300 bg-red-50 dark:border-red-700 dark:bg-red-900/20 @elseif($insight['type'] === 'warning') border-yellow-300 bg-yellow-50 dark:border-yellow-700 dark:bg-yellow-900/20 @else border-blue-300 bg-blue-50 dark:border-blue-700 dark:bg-blue-900/20 @endif">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        @if($insight['type'] === 'critical')
                                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                        @elseif($insight['type'] === 'warning')
                                            <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        @else
                                            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <h3 class="text-sm font-medium @if($insight['type'] === 'critical') text-red-800 dark:text-red-300 @elseif($insight['type'] === 'warning') text-yellow-800 dark:text-yellow-300 @else text-blue-800 dark:text-blue-300 @endif">
                                            {{ $insight['category'] }}
                                        </h3>
                                        <div class="mt-2 text-sm @if($insight['type'] === 'critical') text-red-700 dark:text-red-400 @elseif($insight['type'] === 'warning') text-yellow-700 dark:text-yellow-400 @else text-blue-700 dark:text-blue-400 @endif">
                                            <p>{{ $insight['message'] }}</p>
                                        </div>
                                        <div class="mt-3">
                                            <div class="text-sm @if($insight['type'] === 'critical') text-red-900 dark:text-red-300 @elseif($insight['type'] === 'warning') text-yellow-900 dark:text-yellow-300 @else text-blue-900 dark:text-blue-300 @endif">
                                                <strong>Recommendation:</strong> {{ $insight['recommendation'] }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="rounded-lg border border-green-300 bg-green-50 p-4 dark:border-green-700 dark:bg-green-900/20">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-green-800 dark:text-green-300">All Systems Optimal</h3>
                                    <div class="mt-2 text-sm text-green-700 dark:text-green-400">
                                        <p>No critical issues detected. All metrics are within acceptable ranges.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </x-filament::section>
        </div>
    @endif
</x-filament-panels::page>
