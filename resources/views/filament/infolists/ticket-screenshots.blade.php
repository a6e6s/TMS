<div>
    @if (empty($shots))
        <p style="color:#6b7280;font-size:0.875rem">{{ __('filament/resources/tickets.no_screenshots') }}</p>
    @else
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1rem">
            @foreach ($shots as $shot)
                <figure style="margin:0;border:1px solid #d1d5db;border-radius:0.5rem;overflow:hidden;background:#fff">
                    <a href="{{ $shot['url'] }}" target="_blank" rel="noopener" title="{{ __('filament/resources/tickets.open_full_size') }}">
                        <img src="{{ $shot['url'] }}" alt="{{ $shot['label'] }}" loading="lazy"
                            style="display:block;width:100%;height:200px;object-fit:contain;background:#f9fafb">
                    </a>
                    <figcaption style="display:flex;justify-content:space-between;align-items:center;padding:0.5rem 0.75rem;font-size:0.875rem">
                        <span style="font-weight:600;color:#374151">{{ $shot['label'] }}</span>
                        <a href="{{ $shot['url'] }}" target="_blank" rel="noopener" style="color:#2563eb">
                            {{ __('filament/resources/tickets.open_full_size') }}
                        </a>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    @endif
</div>
