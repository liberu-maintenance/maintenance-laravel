<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Welcome Section -->
        <div class="bg-gradient-to-r from-teal-500 to-emerald-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2">{{ $this->getHeading() }}</h1>
                    <p class="text-teal-100 text-lg">{{ $this->getSubheading() }}</p>
                </div>
                <div class="hidden md:block">
                    <div class="bg-white/20 backdrop-blur-sm rounded-lg p-4">
                        <div class="text-center">
                            <div class="text-3xl font-bold">{{ now()->format('H:i') }}</div>
                            <div class="text-sm text-teal-100">{{ now()->format('M j, Y') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('filament.app.resources.work-orders.work-orders.create') }}" 
               class="bg-white rounded-lg p-4 shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 hover:scale-105 group">
                <div class="flex items-center space-x-3">
                    <div class="bg-blue-100 p-2 rounded-lg group-hover:bg-blue-200 transition-colors">
                        <x-heroicon-o-plus class="w-6 h-6 text-blue-600" />
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900">New Work Order</div>
                        <div class="text-sm text-gray-500">Create maintenance request</div>
                    </div>
                </div>
            </a>

            <a href="{{ route('filament.app.resources.equipment.equipment.index') }}" 
               class="bg-white rounded-lg p-4 shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 hover:scale-105 group">
                <div class="flex items-center space-x-3">
                    <div class="bg-green-100 p-2 rounded-lg group-hover:bg-green-200 transition-colors">
                        <x-heroicon-o-wrench-screwdriver class="w-6 h-6 text-green-600" />
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900">Equipment</div>
                        <div class="text-sm text-gray-500">Manage assets</div>
                    </div>
                </div>
            </a>

            <a href="{{ route('filament.app.resources.maintenance-schedules.maintenance-schedules.index') }}" 
               class="bg-white rounded-lg p-4 shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 hover:scale-105 group">
                <div class="flex items-center space-x-3">
                    <div class="bg-purple-100 p-2 rounded-lg group-hover:bg-purple-200 transition-colors">
                        <x-heroicon-o-calendar-days class="w-6 h-6 text-purple-600" />
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900">Schedules</div>
                        <div class="text-sm text-gray-500">Plan maintenance</div>
                    </div>
                </div>
            </a>

            <a href="{{ route('filament.app.resources.checklists.checklists.index') }}" 
               class="bg-white rounded-lg p-4 shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 hover:scale-105 group">
                <div class="flex items-center space-x-3">
                    <div class="bg-orange-100 p-2 rounded-lg group-hover:bg-orange-200 transition-colors">
                        <x-heroicon-o-clipboard-document-list class="w-6 h-6 text-orange-600" />
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900">Checklists</div>
                        <div class="text-sm text-gray-500">Inspection forms</div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Widgets Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Stats Row -->
            <div class="lg:col-span-12">
                <x-filament-widgets::widgets 
                    :widgets="[
                        \App\Filament\App\Widgets\MaintenanceStatsWidget::class,
                    ]" 
                    :columns="[
                        'sm' => 1,
                        'md' => 2,
                        'lg' => 4,
                    ]"
                />
            </div>

            <!-- Charts Row -->
            <div class="lg:col-span-4">
                <x-filament-widgets::widgets 
                    :widgets="[
                        \App\Filament\App\Widgets\EquipmentStatusWidget::class,
                    ]" 
                />
            </div>

            <div class="lg:col-span-4">
                <x-filament-widgets::widgets 
                    :widgets="[
                        \App\Filament\App\Widgets\EquipmentHealthWidget::class,
                    ]" 
                />
            </div>

            <div class="lg:col-span-4">
                <x-filament-widgets::widgets 
                    :widgets="[
                        \App\Filament\App\Widgets\WorkOrderTrendsWidget::class,
                    ]" 
                />
            </div>

            <!-- Calendar and Recent Items -->
            <div class="lg:col-span-5">
                <x-filament-widgets::widgets 
                    :widgets="[
                        \App\Filament\App\Widgets\MaintenanceCalendarWidget::class,
                    ]" 
                />
            </div>

            <div class="lg:col-span-7">
                <x-filament-widgets::widgets 
                    :widgets="[
                        \App\Filament\App\Widgets\RecentWorkOrdersWidget::class,
                    ]" 
                />
            </div>

            <!-- Account Widget -->
            <div class="lg:col-span-12">
                <x-filament-widgets::widgets 
                    :widgets="[
                        \Filament\Widgets\AccountWidget::class,
                    ]" 
                />
            </div>
        </div>
    </div>
</x-filament-panels::page>