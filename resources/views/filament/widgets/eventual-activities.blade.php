<x-filament-widgets::widget>
    <div class="mb-4">
        <h2 class="text-lg font-bold tracking-tight text-gray-950 dark:text-white sm:text-xl">
            Actividades Eventuales
        </h2>
    </div>
    <x-filament::section
        class="fi-wi-stats-overview-stat-chart-container relative overflow-hidden"
    >
        <div 
            class="fi-wi-stats-overview-stat-chart-container relative overflow-hidden"
            style="max-height: 350px; overflow-y: auto; overflow-x: auto;"
        >
            {{ $this->table }}
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
