<x-filament-panels::page>
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
                    eventDidMount: function(info) {
                        // Optional: Add tooltip or custom styling
                    }
                });
                calendar.render();
            }
        }"
        x-init="initCalendar()"
        wire:ignore
    >
        <div id="calendar" class="h-screen bg-white rounded-lg shadow p-4 text-black"></div>
    </div>

    <!-- Load FullCalendar from CDN -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/locales/es.global.min.js'></script>
    
    <style>
        /* Custom styles for calendar if needed */
        .fc-event {
            cursor: pointer;
        }
        .fc-toolbar-title {
            font-size: 1.25rem !important;
        }
        .fc-button {
            background-color: #3B82F6 !important;
            border-color: #3B82F6 !important;
        }
    </style>
</x-filament-panels::page>
