<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-4">
            @if(count($insights) > 0)
                @foreach($insights as $insight)
                    <div class="rounded-lg border p-4 @if($insight['type'] === 'critical') border-red-300 bg-red-50 @elseif($insight['type'] === 'warning') border-yellow-300 bg-yellow-50 @else border-blue-300 bg-blue-50 @endif">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                @if($insight['type'] === 'critical')
                                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                @elseif($insight['type'] === 'warning')
                                    <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                @else
                                    <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                @endif
                            </div>
                            <div class="ml-3 flex-1">
                                <h3 class="text-sm font-medium @if($insight['type'] === 'critical') text-red-800 @elseif($insight['type'] === 'warning') text-yellow-800 @else text-blue-800 @endif">
                                    {{ $insight['category'] }}
                                </h3>
                                <div class="mt-2 text-sm @if($insight['type'] === 'critical') text-red-700 @elseif($insight['type'] === 'warning') text-yellow-700 @else text-blue-700 @endif">
                                    <p>{{ $insight['message'] }}</p>
                                </div>
                                <div class="mt-3">
                                    <div class="text-sm @if($insight['type'] === 'critical') text-red-900 @elseif($insight['type'] === 'warning') text-yellow-900 @else text-blue-900 @endif">
                                        <strong>Recommendation:</strong> {{ $insight['recommendation'] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="rounded-lg border border-green-300 bg-green-50 p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800">All Systems Optimal</h3>
                            <div class="mt-2 text-sm text-green-700">
                                <p>No critical issues detected. All metrics are within acceptable ranges.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="rounded-lg border border-gray-200 bg-white p-4">
                    <dt class="text-sm font-medium text-gray-500">Mean Time To Repair (MTTR)</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($mttr, 2) }} hrs</dd>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4">
                    <dt class="text-sm font-medium text-gray-500">Total Maintenance Cost</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">${{ number_format($costAnalysis['total_cost'], 2) }}</dd>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
