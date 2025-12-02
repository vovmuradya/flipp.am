@php
    $currentSort = request('sort', 'latest');
    $sortOptions = [
        'latest' => __('Сначала новые'),
        'oldest' => __('Сначала старые'),
        'price_desc' => __('Цена: сначала дорогие'),
        'price_asc' => __('Цена: сначала дешевые'),
    ];

    $baseQuery = request()->except('sort');
    $preserveInputs = [];
    foreach ($baseQuery as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $item) {
                $preserveInputs[] = ['name' => $key . '[]', 'value' => $item];
            }
        } else {
            $preserveInputs[] = ['name' => $key, 'value' => $value];
        }
    }

    $sortUrl = fn(string $value) => request()->fullUrlWithQuery(array_merge($baseQuery, ['sort' => $value]));
@endphp

<form method="GET" class="d-flex flex-wrap align-items-center gap-2">
    @foreach($preserveInputs as $input)
        <input type="hidden" name="{{ $input['name'] }}" value="{{ $input['value'] }}">
    @endforeach
    <label for="sort" class="text-muted small mb-0">{{ __('Сортировка') }}</label>
    <select name="sort" id="sort" class="form-select form-select-sm" onchange="this.form.submit()">
        @foreach($sortOptions as $value => $label)
            <option value="{{ $value }}" @selected($currentSort === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <div class="btn-group btn-group-sm" role="group" aria-label="{{ __('Сортировать по дате') }}">
        <a href="{{ $sortUrl('latest') }}" class="btn {{ $currentSort === 'latest' ? 'btn-dark' : 'btn-outline-secondary' }}">
            {{ __('Сначала новые') }}
        </a>
        <a href="{{ $sortUrl('oldest') }}" class="btn {{ $currentSort === 'oldest' ? 'btn-dark' : 'btn-outline-secondary' }}">
            {{ __('Сначала старые') }}
        </a>
    </div>
</form>
