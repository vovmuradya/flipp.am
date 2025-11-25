<div class="dealer-logos-grid">
    @foreach($dealers as $dealer)
        <a href="{{ route('dealers.show', $dealer->slug) }}" class="dealer-logo-card">
            @if($dealer->logo)
                <img src="{{ $dealer->logo }}" alt="{{ $dealer->company_name }}" loading="lazy">
            @else
                <div class="dealer-logo-placeholder">{{ $dealer->company_name }}</div>
            @endif
        </a>
    @endforeach
</div>
