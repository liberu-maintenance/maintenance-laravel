<x-filament-widgets::widget>
    <div class="space-y-6">
        <!-- Critical Alerts Section -->
        @if(count($this->getViewData()['alerts']) > 0)
            <div class="bg-gradient-to-r from-red-50 to-orange-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center mb-3">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-red-600 mr-2" />
                    <h3 class="font-semibold text-red-800">Critical Alerts</h3>
                </div>
                <div class="space-y-2">
                    @foreach($this->getViewData()['alerts'] as $alert)
                        <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-red-100">
                            <div class="flex items-center space-x-3">
                                <div class="w-2 h-2 rounded-full 
                                    {{ $alert['type'] === 'critical' ? 'bg-red-500 animate-pulse' : 'bg-orange-500' }}">
                                </div>
                                <span class="text-sm text-gray-700">{{ $alert['message'] }}</span>
                            </div>
                            <a href="{{ $alert['url'] }}" 
                               class="text-xs bg-red-100 hover:bg-red-200 text-red-700 px-3 py-1 rounded-full transition-colors">
                                {{ $alert['action'] }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Critical Metrics Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg p-4 border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Urgent Work Orders</p>
                        <p class="text-2xl font-bold 
                            {{ $this->getViewData()['critical']['urgent_work_orders'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ $this->getViewData()['critical']['urgent_work_orders'] }}
                        </p>
                    </div>
                    <div class="p-2 rounded-lg 
                        {{ $this->getViewData()['critical']['urgent_work_orders'] > 0 ? 'bg-red-100' : 'bg-green-100' }}">
                        <x-heroicon-o-exclamation-triangle class="w-6 h-6 
                            {{ $this->getViewData()['critical']['urgent_work_orders'] > 0 ? 'text-red-600' : 'text-green-600' }}" />
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg p-4 border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Overdue Maintenance</p>
                        <p class="text-2xl font-bold 
                            {{ $this->getViewData()['critical']['overdue_maintenance'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ $this->getViewData()['critical']['overdue_maintenance'] }}
                        </p>
                    </div>
                    <div class="p-2 rounded-lg 
                        {{ $this->getViewData()['critical']['overdue_maintenance'] > 0 ? 'bg-red-100' : 'bg-green-100' }}">
                        <x-heroicon-o-calendar-days class="w-6 h-6 
                            {{ $this->getViewData()['critical']['overdue_maintenance'] > 0 ? 'text-red-600' : 'text-green-600' }}" />
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg p-4 border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Equipment Down</p>
                        <p class="text-2xl font-bold 
                            {{ $this->getViewData()['critical']['equipment_down'] > 3 ? 'text-orange-600' : 'text-green-600' }}">
                            {{ $this->getViewData()['critical']['equipment_down'] }}
                        </p>
                    </div>
                    <div class="p-2 rounded-lg 
                        {{ $this->getViewData()['critical']['equipment_down'] > 3 ? 'bg-orange-100' : 'bg-green-100' }}">
                        <x-heroicon-o-wrench-screwdriver class="w-6 h-6 
                            {{ $this->getViewData()['critical']['equipment_down'] > 3 ? 'text-orange-600' : 'text-green-600' }}" />
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg p-4 border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Pending Approvals</p>
                        <p class="text-2xl font-bold 
                            {{ $this->getViewData()['critical']['pending_approvals'] > 5 ? 'text-yellow-600' : 'text-green-600' }}">
                            {{ $this->getViewData()['critical']['pending_approvals'] }}
                        </p>
                    </div>
                    <div class="p-2 rounded-lg 
                        {{ $this->getViewData()['critical']['pending_approvals'] > 5 ? 'bg-yellow-100' : 'bg-green-100' }}">
                        <x-heroicon-o-clock class="w-6 h-6 
                            {{ $this->getViewData()['critical']['pending_approvals'] > 5 ? 'text-yellow-600' : 'text-green-600' }}" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
            <h3 class="font-semibold text-gray-900 mb-4">Performance Metrics</h3>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600 mb-1">
                        {{ $this->getViewData()['performance']['completion_rate'] }}%
                    </div>
                    <div class="text-sm text-gray-600">Completion Rate</div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                        <div class="bg-blue-600 h-2 rounded-full transition-all duration-500" 
                             style="width: {{ $this->getViewData()['performance']['completion_rate'] }}%"></div>
                    </div>
                </div>

                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600 mb-1">
                        {{ $this->getViewData()['performance']['avg_response_time'] }}h
                    </div>
                    <div class="text-sm text-gray-600">Avg Response Time</div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                        <div class="bg-green-600 h-2 rounded-full transition-all duration-500" 
                             style="width: {{ min(100, max(0, 100 - ($this->getViewData()['performance']['avg_response_time'] * 4))) }}%"></div>
                    </div>
                </div>

                <div class="text-center">
                    <div class="text-3xl font-bold text-purple-600 mb-1">
                        {{ $this->getViewData()['performance']['equipment_uptime'] }}%
                    </div>
                    <div class="text-sm text-gray-600">Equipment Uptime</div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                        <div class="bg-purple-600 h-2 rounded-full transition-all duration-500" 
                             style="width: {{ $this->getViewData()['performance']['equipment_uptime'] }}%"></div>
                    </div>
                </div>

                <div class="text-center">
                    <div class="text-3xl font-bold text-teal-600 mb-1">
                        {{ $this->getViewData()['performance']['maintenance_compliance'] }}%
                    </div>
                    <div class="text-sm text-gray-600">Maintenance Compliance</div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                        <div class="bg-teal-600 h-2 rounded-full transition-all duration-500" 
                             style="width: {{ $this->getViewData()['performance']['maintenance_compliance'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>