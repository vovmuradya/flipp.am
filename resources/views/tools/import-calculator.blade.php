@extends('layouts.app')

@section('content')
<div class="container py-6" x-data="importCalculator()">
    <h1 class="text-3xl font-bold mb-4">Калькулятор растаможки</h1>
    <p class="text-gray-600 mb-6">Расчёт через официальный API SRC.am.</p>

    <div class="bg-white shadow rounded-lg p-4 md:p-6 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <label class="block">
                <span class="text-sm font-semibold">Стоимость (value)</span>
                <input type="number" min="0" step="100" x-model.number="form.value" class="mt-1 w-full border rounded px-3 py-2" placeholder="Напр. 12000">
                <span class="text-xs text-red-600" x-text="errors.value"></span>
            </label>

            <label class="block">
                <span class="text-sm font-semibold">Дата выпуска (productionDate)</span>
                <input type="date" x-model="form.productionDate" class="mt-1 w-full border rounded px-3 py-2">
                <span class="text-xs text-red-600" x-text="errors.productionDate"></span>
            </label>

            <label class="block">
                <span class="text-sm font-semibold">Объём двигателя, см³ (volume)</span>
                <input type="number" min="0" step="50" x-model.number="form.volume" class="mt-1 w-full border rounded px-3 py-2" placeholder="Напр. 1998">
                <span class="text-xs text-red-600" x-text="errors.volume"></span>
            </label>

            <label class="block">
                <span class="text-sm font-semibold">Мощность, л.с. (ICEpower)</span>
                <input type="number" min="0" step="1" x-model.number="form.ICEpower" class="mt-1 w-full border rounded px-3 py-2" placeholder="Напр. 150">
                <span class="text-xs text-red-600" x-text="errors.ICEpower"></span>
            </label>

            <label class="block">
                <span class="text-sm font-semibold">Тип двигателя (selectedEngineType)</span>
                <select x-model.number="form.selectedEngineType" class="mt-1 w-full border rounded px-3 py-2">
                    <option value="1">1 — бензин</option>
                    <option value="2">2 — дизель</option>
                    <option value="3">3 — электрический</option>
                </select>
                <span class="text-xs text-red-600" x-text="errors.selectedEngineType"></span>
            </label>

            <label class="block">
                <span class="text-sm font-semibold">Offroad</span>
                <select x-model.number="form.offRoad" class="mt-1 w-full border rounded px-3 py-2">
                    <option value="0">0 — нет</option>
                    <option value="1">1 — offroad</option>
                </select>
                <span class="text-xs text-red-600" x-text="errors.offRoad"></span>
            </label>

            <label class="block">
                <span class="text-sm font-semibold">Тип лица (isLegal)</span>
                <select x-model.number="form.isLegal" class="mt-1 w-full border rounded px-3 py-2">
                    <option value="0">0 — физлицо</option>
                    <option value="1">1 — юрлицо</option>
                </select>
                <span class="text-xs text-red-600" x-text="errors.isLegal"></span>
            </label>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" class="btn btn-brand-gradient" @click="calculate" :disabled="loading">
                <span x-show="!loading">Рассчитать</span>
                <span x-show="loading">Загрузка...</span>
            </button>
            <span class="text-sm text-red-600" x-text="globalError"></span>
        </div>

        <template x-if="result">
            <div class="bg-slate-50 border rounded p-3">
                <h3 class="font-semibold mb-2">Результат</h3>
                <pre class="text-sm overflow-auto" x-text="JSON.stringify(result, null, 2)"></pre>
            </div>
        </template>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function importCalculator() {
        return {
            loading: false,
            result: null,
            globalError: '',
            errors: {},
            form: {
                value: '',
                productionDate: '',
                volume: '',
                ICEpower: '',
                selectedEngineType: 1,
                offRoad: 0,
                isLegal: 0,
            },
            validate() {
                const errs = {};
                if (!this.form.value || this.form.value <= 0) errs.value = 'Укажите стоимость';
                if (!this.form.productionDate) errs.productionDate = 'Укажите дату выпуска';
                if (!this.form.volume || this.form.volume <= 0) errs.volume = 'Укажите объём двигателя';
                if (this.form.ICEpower === '' || this.form.ICEpower < 0) errs.ICEpower = 'Укажите мощность';
                if (![1,2,3].includes(Number(this.form.selectedEngineType))) errs.selectedEngineType = 'Тип двигателя: 1,2,3';
                return errs;
            },
            async calculate() {
                this.globalError = '';
                this.errors = {};
                this.result = null;

                const errs = this.validate();
                if (Object.keys(errs).length) {
                    this.errors = errs;
                    this.globalError = 'Исправьте ошибки в форме';
                    return;
                }

                this.loading = true;
                try {
                    const payload = {...this.form};
                    // Привести дату к ISO, если указана как YYYY-MM-DD
                    if (payload.productionDate && !payload.productionDate.includes('T')) {
                        payload.productionDate = `${payload.productionDate}T00:00:00.000Z`;
                    }
                    const res = await fetch('/api/import-calculator', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                        body: JSON.stringify(payload),
                    });
                    const data = await res.json();
                    if (!res.ok || data.ok === false) {
                        this.globalError = data?.error || 'Не удалось рассчитать';
                        if (data?.errors) this.errors = data.errors;
                        this.result = data?.data || data;
                        return;
                    }
                    this.result = data.data;
                } catch (e) {
                    this.globalError = 'Серверная ошибка';
                } finally {
                    this.loading = false;
                }
            }
        }
    }
</script>
@endpush
