<x-filament-panels::page>
    <div class="flex flex-col gap-6">

        <!-- Month Summary (Top, Full Width) -->
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-6">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Resumen de Mes</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                <!-- Stats -->
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 flex flex-col items-center justify-center">
                    <span class="block text-xs text-gray-500 uppercase font-medium">Total Actividades</span>
                    <span class="block text-3xl font-bold text-gray-900 dark:text-white mt-1">
                        {{ $this->monthStats['total'] }}
                    </span>
                </div>
                
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 flex flex-col items-center justify-center">
                    <span class="block text-xs text-gray-500 uppercase font-medium">Completadas</span>
                    <span class="block text-3xl font-bold text-green-600 mt-1">
                        {{ $this->monthStats['completed'] }}
                    </span>
                </div>

                <!-- Compliance Card -->
                <div class="bg-blue-600 rounded-xl p-5 text-white relative overflow-hidden shadow-lg shadow-blue-500/30 h-full flex flex-col justify-center">
                    <div class="relative z-10">
                        <div class="flex justify-between items-end mb-2">
                            <span class="block text-xs text-blue-100 uppercase font-medium">Cumplimiento Global</span>
                            <span class="text-3xl font-bold tracking-tight">{{ $this->monthStats['percentage'] }}%</span>
                        </div>
                        <div class="w-full bg-blue-800/50 rounded-full h-2">
                            <div class="bg-white h-2 rounded-full transition-all duration-500 ease-out" style="width: {{ $this->monthStats['percentage'] }}%;"></div>
                        </div>
                    </div>
                    
                    <!-- Decorative Chart Line -->
                    <div class="absolute -bottom-2 -right-2 w-32 h-16 text-blue-500/30">
                            <svg viewBox="0 0 100 40" preserveAspectRatio="none" class="w-full h-full fill-current">
                                <path d="M0,40 L0,30 C20,20 40,35 60,15 S90,5 100,20 L100,40 Z" />
                            </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Calendar Section (Left, 1 col = 50%) -->
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-4">
                <div
                    x-data="{
                        events: @js(json_decode($this->getEvents())),
                        initCalendar() {
                            let calendarEl = document.getElementById('calendar');
                            let calendar = new FullCalendar.Calendar(calendarEl, {
                                initialView: 'dayGridMonth',
                                locale: 'es',
                                headerToolbar: {
                                    left: 'prev,next today',
                                    center: 'title',
                                    right: 'dayGridMonth,timeGridWeek,listWeek'
                                },
                                events: this.events,
                                eventClick: (info) => {
                                    $wire.mountAction('viewEvent', { record_id: info.event.id });
                                },
                                eventContent: function(arg) {
                                    // Custom render for event content
                                    let typeLabel = arg.event.extendedProps.type_label || '';
                                    let title = arg.event.title;
                                    
                                    // Create a custom structure
                                    let content = document.createElement('div');
                                    content.className = 'fc-event-main-frame flex flex-col px-1 overflow-hidden';
                                    content.innerHTML = `
                                        <div class='fc-event-title-container'>
                                            <div class='fc-event-title font-bold text-xs truncate'>${title}</div>
                                        </div>
                                    `;
                                    return { domNodes: [content] };
                                },
                                eventDidMount: function(info) {
                                    // Optional: Add tooltip
                                    info.el.title = info.event.extendedProps.real_name || info.event.title;
                                }
                            });
                            calendar.render();
                        }
                    }"
                    x-init="initCalendar()"
                    wire:ignore
                >
                    <div id="calendar" class="min-h-[600px] text-gray-900 dark:text-white"></div>
                </div>
            </div>

            <!-- Right Section (Right, 1 col = 50%) -->
            <div class="flex flex-col gap-6 h-full">
                
                <!-- Upcoming Events -->
                <div class="flex-1 bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-6">
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Próximos Eventos</h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @forelse($this->upcomingEvents as $event)
                        <div class="flex flex-col p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors border border-transparent hover:border-gray-100 dark:hover:border-gray-700 cursor-pointer h-full"
                             wire:click="mountAction('viewEvent', { record_id: {{ $event->id }} })">
                            
                            <!-- Header with Date & Badge -->
                            <div class="flex justify-between items-start mb-2">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">
                                    @if($event->fecha_programada->isToday())
                                        HOY
                                    @elseif($event->fecha_programada->isTomorrow())
                                        MAÑANA
                                    @else
                                        {{ $event->fecha_programada->format('d/m') }}
                                    @endif
                                </p>
                                @php
                                    $type = $event->activity->tipo ?? 'general';
                                    $colorClass = match($type) {
                                        'inspeccion' => 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
                                        'simulacro' => 'bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400',
                                        'auditoria' => 'bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400',
                                        'capacitacion' => 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400',
                                        'incidente' => 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
                                        default => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                                    };
                                @endphp
                                <div class="w-6 h-6 rounded {{ $colorClass }} flex items-center justify-center">
                                     <!-- Simple SVG Icons Small -->
                                    @if($type == 'inspeccion')
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    @elseif($type == 'simulacro')
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                    @elseif($type == 'auditoria')
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                                    @elseif($type == 'capacitacion')
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 14l9-5-9-5-9 5 9 5z" /><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" /></svg>
                                    @else
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    @endif
                                </div>
                            </div>
                            
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white truncate mb-1" title="{{ $event->activity->nombre }}">
                                {{ $event->activity->nombre }}
                            </h4>
                            <p class="text-xs text-gray-500 truncate">
                                {{ $event->activity->location->nombre ?? 'Sin ubicación' }}
                            </p>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-8">
                            <p class="text-sm text-gray-500">No hay eventos próximos.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    <!-- Load FullCalendar from CDN -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/locales/es.global.min.js'></script>
    
    <style>
        /* Custom styles for calendar */
        .fc-event {
            cursor: pointer;
            border-radius: 4px;
            border: none;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            transition: all 0.2s;
        }
        .fc-event:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .fc-toolbar-title {
            font-size: 1.25rem !important;
            font-weight: 700 !important;
            text-transform: capitalize;
        }
        .fc-button {
            background-color: #fff !important;
            border-color: #e5e7eb !important;
            color: #374151 !important;
            font-weight: 500 !important;
            padding: 0.5rem 1rem !important;
            border-radius: 0.5rem !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
        }
        .fc-button:hover {
            background-color: #f9fafb !important;
            border-color: #d1d5db !important;
        }
        .fc-button-primary:not(:disabled).fc-button-active, 
        .fc-button-primary:not(:disabled):active {
            background-color: #111827 !important;
            border-color: #111827 !important;
            color: #fff !important;
        }
        .fc-day-today {
            background-color: #eff6ff !important;
        }
        .fc-col-header-cell-cushion {
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: 600;
            color: #6b7280;
            padding-top: 1rem !important;
            padding-bottom: 1rem !important;
        }
        .dark .fc-button {
            background-color: #1f2937 !important;
            border-color: #374151 !important;
            color: #e5e7eb !important;
        }
        .dark .fc-button:hover {
            background-color: #374151 !important;
        }
        .dark .fc-button-primary:not(:disabled).fc-button-active {
            background-color: #fff !important;
            color: #000 !important;
        }
        .dark .fc-day-today {
            background-color: #1e293b !important;
        }
        .dark .fc-col-header-cell-cushion {
            color: #9ca3af;
        }
        .fc-daygrid-day-number {
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            padding: 0.5rem !important;
        }
        .dark .fc-daygrid-day-number {
            color: #d1d5db;
        }
    </style>
</x-filament-panels::page>