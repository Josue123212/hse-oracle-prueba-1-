<x-filament-widgets::widget>
    <div class="mb-4 flex items-center gap-x-3">
        <h2 class="text-lg font-bold tracking-tight text-gray-950 dark:text-white sm:text-xl">
            Ejecuciones Vencidas (Urgente)
        </h2>
    </div>
    <x-filament::section
        class="fi-wi-stats-overview-stat-chart-container relative overflow-hidden"
    >
        <div 
            class="overdue-activities-table-wrapper relative"
            style="max-height: 350px; overflow: auto;"
        >
            {{ $this->table }}
        </div>
        <style>
            .overdue-activities-table-wrapper .fi-ta-content,
            .overdue-activities-table-wrapper .fi-ta-content-ctn {
                overflow: visible !important;
            }
        </style>
    </x-filament::section>
</x-filament-widgets::widget>