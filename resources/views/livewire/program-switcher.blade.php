<div class="px-4 py-2 border-b border-gray-200 dark:border-gray-800">
    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider">
        Filtrar por Programa
    </div>
    <select 
        wire:model.live="selectedProgramId" 
        class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
    >
        <option value="">Vista General (Todos)</option>
        @foreach($programs as $id => $name)
            <option value="{{ $id }}">{{ $name }}</option>
        @endforeach
    </select>
</div>
