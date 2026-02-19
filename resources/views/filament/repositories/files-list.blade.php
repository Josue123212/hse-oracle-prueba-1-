<div class="space-y-2">
    @forelse($items as $item)
        <a href="{{ $item['url'] }}" target="_blank" class="text-primary-600 hover:underline">
            {{ $item['name'] }}
        </a>
    @empty
        <p>No hay archivos en este repositorio.</p>
    @endforelse
</div>

