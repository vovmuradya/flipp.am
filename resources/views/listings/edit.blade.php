<!-- ...existing code... -->
<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h2 class="text-2xl font-bold mb-6">Редактирование объявления</h2>

                    <form method="POST" action="{{ route('listings.update', $listing) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <div class="mt-4">
                            <x-input-label for="title" value="Заголовок" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $listing->title)" required />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="category_id" value="Категория" />
                            <select name="category_id" id="category_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                @foreach($categories as $category)
                                    @php
                                        $raw = $category->name ?? '';
                                        $label = '';

                                        if (isset($category->localized_name) && $category->localized_name) {
                                            $label = (string) $category->localized_name;
                                        } elseif (is_string($raw)) {
                                            $decoded = json_decode($raw, true);
                                            if (is_array($decoded) && count($decoded)) {
                                                $label = $decoded[app()->getLocale()] ?? $decoded['ru'] ?? $decoded['en'] ?? array_values($decoded)[0];
                                            } else {
                                                $label = $raw;
                                            }
                                        } elseif ($raw instanceof \Illuminate\Support\Collection) {
                                            $arr = $raw->toArray();
                                            $label = $arr[app()->getLocale()] ?? $arr['ru'] ?? $arr['en'] ?? (array_values($arr)[0] ?? '');
                                        } elseif (is_array($raw)) {
                                            $label = $raw[app()->getLocale()] ?? $raw['ru'] ?? $raw['en'] ?? (array_values($raw)[0] ?? '');
                                        } elseif (is_object($raw)) {
                                            $arr = (array) $raw;
                                            $label = $arr[app()->getLocale()] ?? $arr['ru'] ?? $arr['en'] ?? (array_values($arr)[0] ?? '');
                                        } else {
                                            $label = (string) $raw;
                                        }
                                        $label = $label ?? '';
                                    @endphp
                                    <option value="{{ $category->id }}" @selected(old('category_id', $listing->category_id) == $category->id)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mt-4">
                            <x-input-label for="region_id" value="Регион" />
                            <select name="region_id" id="region_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                @foreach($regions as $region)
                                    @php
                                        $rraw = $region->name ?? '';
                                        if (is_string($rraw)) {
                                            $rdec = json_decode($rraw, true);
                                            $rlabel = is_array($rdec) ? ($rdec[app()->getLocale()] ?? $rdec['ru'] ?? $rdec['en'] ?? array_values($rdec)[0]) : $rraw;
                                        } elseif (is_array($rraw)) {
                                            $rlabel = $rraw[app()->getLocale()] ?? $rraw['ru'] ?? $rraw['en'] ?? (array_values($rraw)[0] ?? '');
                                        } else {
                                            $rlabel = (string) $rraw;
                                        }
                                    @endphp
                                    <option value="{{ $region->id }}" @selected(old('region_id', $listing->region_id) == $region->id)>{{ $rlabel }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mt-4">
                            <x-input-label for="price" value="Цена (USD)" />
                            <x-text-input id="price" class="block mt-1 w-full" type="number" name="price" :value="old('price', $listing->price)" required />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="description" value="Описание" />
                            <textarea name="description" id="description" rows="6" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('description', $listing->description) }}</textarea>
                        </div>

                        {{-- Текущие изображения (миниатюры) --}}
                        @php $media = $listing->getMedia('images'); @endphp
                        @if($media->isNotEmpty())
                            <div class="mt-4">
                                <x-input-label value="Текущие изображения" />
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach($media as $m)
                                        <div class="w-1/3 sm:w-1/4 md:w-1/6 border rounded overflow-hidden">
                                            <a href="{{ $m->getUrl() }}" target="_blank">
                                                <img src="{{ $m->getUrl() }}" alt="image" class="w-full h-24 object-cover" />
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="mt-4">
                            <x-input-label for="images" value="Изображения (при загрузке новых, старые будут заменены)" />
                            <input id="images" name="images[]" type="file" multiple class="block w-full text-sm text-slate-500 mt-1 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100" />
                        </div>

                        @php $vd = $listing->vehicleDetail ?? null; @endphp
                        @if($vd)
                            <div class="mt-6 bg-gray-50 p-4 rounded">
                                <h3 class="font-medium">Характеристики автомобиля</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                                    <div>
                                        <x-input-label for="vehicle_make" value="Марка" />
                                        <x-text-input id="vehicle_make" type="text" name="vehicle[make]" :value="old('vehicle.make', $vd->make)" class="mt-1 w-full" />
                                    </div>
                                    <div>
                                        <x-input-label for="vehicle_model" value="Модель" />
                                        <x-text-input id="vehicle_model" type="text" name="vehicle[model]" :value="old('vehicle.model', $vd->model)" class="mt-1 w-full" />
                                    </div>
                                    <div>
                                        <x-input-label for="vehicle_year" value="Год выпуска" />
                                        <x-text-input id="vehicle_year" type="number" name="vehicle[year]" :value="old('vehicle.year', $vd->year)" class="mt-1 w-full" />
                                    </div>
                                    <div>
                                        <x-input-label for="vehicle_mileage" value="Пробег (км)" />
                                        <x-text-input id="vehicle_mileage" type="number" name="vehicle[mileage]" :value="old('vehicle.mileage', $vd->mileage)" class="mt-1 w-full" />
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                Сохранить изменения
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
<!-- ...existing code... -->
