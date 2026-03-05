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
            class="fi-wi-stats-overview-stat-chart-container relative overflow-hidden"
            style="max-height: 400px; overflow-y: auto; overflow-x: hidden;"
        >
            {{ $this->table }}
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
