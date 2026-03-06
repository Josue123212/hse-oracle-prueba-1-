<x-filament-widgets::widget>
    <div class="mb-4 flex items-center gap-x-3">
        <h2 class="text-lg font-bold tracking-tight text-gray-950 dark:text-white sm:text-xl">
            {{ $this->getHeading() }}
        </h2>
    </div>

    {{-- Contenedor con scroll horizontal --}}
    <div 
        class="horizontal-activities-widget-wrapper relative"
        style="overflow-x: auto; padding-bottom: 0.5rem;"
    >
        {{ $this->table }}
    </div>

    <style>
        /* 
         * Forzamos que el Grid de Filament se comporte como un Flex Row 
         * para permitir el scroll horizontal de las cards.
         */
        .horizontal-activities-widget-wrapper .fi-ta-content-grid {
            display: flex !important;
            flex-wrap: nowrap !important;
            gap: 1rem; /* Espaciado entre cards */
        }
        
        /* Definimos el ancho fijo de las cards para que no se encojan */
        .horizontal-activities-widget-wrapper .fi-ta-content-grid > * {
            flex: 0 0 320px; /* Ancho fijo de la card */
            min-width: 320px;
            max-width: 320px;
        }

        /* Ocultar la paginación si no es necesaria o ajustarla */
        .horizontal-activities-widget-wrapper .fi-ta-footer-pagination {
            display: none;
        }
    </style>
</x-filament-widgets::widget>
