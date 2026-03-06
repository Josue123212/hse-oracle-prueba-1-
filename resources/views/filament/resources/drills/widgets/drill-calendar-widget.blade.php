<x-filament::widget>
    <x-filament::section>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold tracking-tight text-gray-950 dark:text-white">
                Próximos Simulacros Programados
            </h2>
            
            <div class="flex items-center space-x-4">
                <button wire:click="previousMonth" type="button" class="p-1 hover:bg-gray-100 rounded-full dark:hover:bg-gray-800 transition-colors">
                    <x-heroicon-m-chevron-left class="w-5 h-5 text-gray-500" />
                </button>
                
                <span class="text-sm font-medium text-gray-900 dark:text-gray-100 w-32 text-center">
                    {{ $this->calendarData['monthName'] }} {{ $this->calendarData['year'] }}
                </span>
                
                <button wire:click="nextMonth" type="button" class="p-1 hover:bg-gray-100 rounded-full dark:hover:bg-gray-800 transition-colors">
                    <x-heroicon-m-chevron-right class="w-5 h-5 text-gray-500" />
                </button>
            </div>
        </div>

        <div class="w-full overflow-x-auto">
            <div class="min-w-[600px]">
                <!-- Calendar Header -->
                <div class="grid grid-cols-7 border-b border-gray-200 dark:border-gray-700 mb-2" style="grid-template-columns: repeat(7, minmax(0, 1fr));">
                    @foreach(['LUN', 'MAR', 'MIE', 'JUE', 'VIE', 'SAB', 'DOM'] as $day)
                        <div class="py-2 text-center text-xs font-semibold text-gray-400 tracking-wider">
                            {{ $day }}
                        </div>
                    @endforeach
                </div>

                <!-- Calendar Grid -->
                <div class="grid grid-cols-7 gap-1 auto-rows-fr" style="grid-template-columns: repeat(7, minmax(0, 1fr));">
                    {{-- Empty cells for previous month days --}}
                    @for ($i = 0; $i < $this->calendarData['firstDayOffset']; $i++)
                        <div class="h-24 p-1 border border-transparent"></div>
                    @endfor

                {{-- Days of current month --}}
                @for ($day = 1; $day <= $this->calendarData['daysInMonth']; $day++)
                    <div class="relative h-24 p-2 border border-gray-100 dark:border-gray-800 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                        <span class="absolute top-2 left-2 text-sm font-medium text-gray-700 dark:text-gray-300 {{ $day == now()->day && $this->currentMonth == now()->month && $this->currentYear == now()->year ? 'bg-primary-500 text-white w-6 h-6 rounded-full flex items-center justify-center -ml-1 -mt-1' : '' }}">
                            {{ $day }}
                        </span>
                        
                        <div class="mt-6 space-y-1 overflow-y-auto max-h-[calc(100%-1.5rem)] custom-scrollbar">
                            @if(isset($this->events[$day]))
                                @foreach($this->events[$day] as $event)
                                    <div 
                                        class="px-2 py-1 text-xs rounded-md truncate cursor-pointer transition-transform hover:scale-105"
                                        style="background-color: {{ match($event['status']->value ?? '') {
                                            'programado' => '#dbeafe', // blue-100
                                            'en_proceso' => '#e0f2fe', // sky-100
                                            'ejecutado' => '#dcfce7', // green-100
                                            'no_cumplio' => '#fee2e2', // red-100
                                            default => '#f3f4f6' // gray-100
                                        } }}; color: {{ match($event['status']->value ?? '') {
                                            'programado' => '#1e40af', // blue-800
                                            'en_proceso' => '#0369a1', // sky-700
                                            'ejecutado' => '#166534', // green-800
                                            'no_cumplio' => '#991b1b', // red-800
                                            default => '#374151' // gray-700
                                        } }};"
                                        title="{{ $event['title'] }} - {{ ucfirst(str_replace('_', ' ', $event['status']->value ?? '')) }}"
                                    >
                                        {{ $event['title'] }}
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </div>
    </x-filament::section>
</x-filament::widget>
