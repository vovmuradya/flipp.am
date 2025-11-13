@php
    $fieldName = $fieldName ?? 'region_id';
    $fieldId = $fieldId ?? $fieldName;
    $selectedRegion = $selectedRegion ?? null;
    $isRequired = $required ?? true;
    $regionsCollection = $regions instanceof \Illuminate\Support\Collection ? $regions : collect($regions);
    $regionsByParent = $regionsCollection->groupBy('parent_id');
    $country = $regionsCollection->firstWhere('type', 'country');
    $capitalCities = $country ? ($regionsByParent->get($country->id) ?? collect())->where('type', 'city')->sortBy('localized_name') : collect();
    $districts = $regionsCollection->where('type', 'district')->sortBy('localized_name');
@endphp

<div class="{{ $wrapperClass ?? 'mb-4' }}">
    <label class="form-label">
        {{ $label ?? __('Регион') }}
        @if($isRequired)
            <span class="text-danger">*</span>
        @endif
    </label>
    <select
        name="{{ $fieldName }}"
        id="{{ $fieldId }}"
        class="form-select"
        @if($isRequired) required @endif
    >
        <option value="">{{ __('Выберите регион') }}</option>

        @if($capitalCities->isNotEmpty())
            <optgroup label="{{ __('Столица') }}">
                @foreach($capitalCities as $capital)
                    <option value="{{ $capital->id }}" @selected($selectedRegion == $capital->id)>
                        {{ $capital->localized_name }}
                    </option>
                @endforeach
            </optgroup>
        @endif

        @foreach($districts as $district)
            @php
                $cities = ($regionsByParent->get($district->id) ?? collect())
                    ->where('type', 'city')
                    ->sortBy('localized_name');
            @endphp
            <optgroup label="{{ $district->localized_name }}">
                @forelse($cities as $city)
                    <option value="{{ $city->id }}" @selected($selectedRegion == $city->id)>
                        {{ $city->localized_name }}
                    </option>
                @empty
                    <option value="{{ $district->id }}" @selected($selectedRegion == $district->id)>
                        {{ $district->localized_name }}
                    </option>
                @endforelse
            </optgroup>
        @endforeach
    </select>

    @error($fieldName)
        <small class="text-danger mt-1 d-block">{{ $message }}</small>
    @enderror
</div>
