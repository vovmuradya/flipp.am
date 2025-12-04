@extends('layouts.app')

@section('content')
<div class="container py-5" x-data="iaaCalculator()">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h4 mb-3">{{ __('Կալկուլյատոր մաքսի և մատակարարման') }}</h1>
                    <p class="text-muted mb-4">{{ __('Վերցնում ենք данные Copart և считаем расходы через iaa.am') }}</p>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Copart հղում') }}</label>
                        <input type="url" class="form-control" x-model="form.copart_link" placeholder="https://www.copart.com/lot/12345678">
                        <div class="form-text">{{ __('По ссылке подставим год/объём/топливо, если сможем распарсить.') }}</div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Цена, $') }}</label>
                            <input type="number" min="0" class="form-control" x-model.number="form.price">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Локация аукциона (ID)') }}</label>
                            <input type="number" min="1" class="form-control" x-model.number="form.auction_location_id">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Тип кузова') }}</label>
                            <select class="form-select" x-model="form.auto_type">
                                <option value="sedan">Sedan</option>
                                <option value="big_suv">Big SUV</option>
                                <option value="truck">Truck</option>
                                <option value="motorcycle">Motorcycle</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Тип топлива (motor)') }}</label>
                            <select class="form-select" x-model.number="form.motor">
                                <option :value="1">Дизель/Электро/Гибрид</option>
                                <option :value="0">Бензин/Другое</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Возраст авто (лет)') }}</label>
                            <input type="number" min="0" class="form-control" x-model.number="form.year">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Год выпуска') }}</label>
                            <input type="number" min="1900" class="form-control" x-model.number="form.year2">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Объём двигателя, см³') }}</label>
                            <input type="number" min="0" class="form-control" x-model.number="form.engine_volume">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Стоимость доставки (auction_location)') }}</label>
                            <input type="number" min="0" class="form-control" x-model.number="form.auction_location">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Комиссия аукциона (auction_fee)') }}</label>
                            <input type="number" min="0" step="0.01" class="form-control" x-model.number="form.auction_fee">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="insurance" x-model="form.insurance">
                                <label class="form-check-label" for="insurance">
                                    {{ __('Страховка') }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 mt-4">
                        <button class="btn btn-brand-gradient" @click="submit" :disabled="loading">
                            <span x-show="!loading">{{ __('Հաշվել') }}</span>
                            <span x-show="loading">{{ __('Հաշվում ենք…') }}</span>
                        </button>
                        <span class="text-danger" x-text="error"></span>
                    </div>

                    <div class="mt-4" x-show="result">
                        <h6 class="mb-2">{{ __('Результат') }}</h6>
                        <pre class="bg-light p-3 rounded border" style="white-space: pre-wrap;" x-text="JSON.stringify(result, null, 2)"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function iaaCalculator() {
    return {
        loading: false,
        error: '',
        result: null,
        form: {
            copart_link: '',
            price: '',
            auction_location_id: 4,
            auction: 'copart',
            motor: 1,
            auto_type: 'sedan',
            year: 3,
            year2: new Date().getFullYear() - 3,
            auction_fee: 0,
            engine_volume: 2000,
            insurance: true,
            auction_location: 2475,
        },
        async submit() {
            this.loading = true;
            this.error = '';
            this.result = null;
            try {
                const fd = new FormData();
                Object.entries(this.form).forEach(([k, v]) => fd.append(k, v));
                const resp = await fetch('{{ url('/api/copart-calculator/calculate') }}', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: fd,
                });
                const data = await resp.json().catch(() => ({}));
                if (!resp.ok || data.error) {
                    const statusText = data.status ? ` (status: ${data.status})` : '';
                    const bodyText = data.body ? `\n${data.body}` : '';
                    const detailText = data.detail ? `\n${data.detail}` : '';
                    this.error = (data.error || '{{ __('Ошибка расчета') }}') + statusText + bodyText + detailText;
                    return;
                }
                this.result = data;
            } catch (e) {
                this.error = '{{ __('Ошибка расчета. Попробуйте позже.') }}';
            } finally {
                this.loading = false;
            }
        },
    };
}
</script>
@endpush
