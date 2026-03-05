<x-filament-widgets::widget>
    <x-filament::section
        class="fi-wi-stats-overview-stat-chart-container relative overflow-hidden"
    >
        <div class="mb-4">
            <h2 class="text-lg font-bold tracking-tight text-gray-950 dark:text-white sm:text-xl">
                {{ $this->getHeadingTitle() }}
            </h2>
        </div>

        <div 
            class="base-activity-widget-table-wrapper relative"
            style="max-height: 400px; overflow: auto;"
        >
            {{ $this->table }}
        </div>
        <style>
            .base-activity-widget-table-wrapper .fi-ta-content,
            .base-activity-widget-table-wrapper .fi-ta-content-ctn {
                overflow: visible !important;
            }
        </style>
    </x-filament::section>
</x-filament-widgets::widget>
