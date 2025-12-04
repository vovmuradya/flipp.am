@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="mb-3">{{ __('Կալկուլյատոր մաքսի և մատակարարման') }}</h1>
    <p class="text-muted">{{ __('Մուտքագրեք տվյալները, կոճակ «Հաշվել» — և մենք կուղարկենք հարցումը արտաքին սերվիսին։') }}</p>

    <form id="calc-form" class="mt-4">
        @csrf
        <div class="mb-3">
            <label for="copart_link" class="form-label">{{ __('Copart հղում (ըստ ցանկության)') }}</label>
            <input type="url" name="copart_link" id="copart_link" class="form-control" placeholder="https://www.copart.com/lot/123456" />
        </div>

        <div class="mb-3">
            <label for="price" class="form-label">{{ __('Ավտոյի գինը ($)') }}</label>
            <input type="number" name="price" id="price" class="form-control" required min="1" placeholder="{{ __('Մուտքագրեք գինը') }}">
        </div>

        <div class="mb-3">
            <label for="auction_type" class="form-label">{{ __('Աճուրդ') }}</label>
            <select name="auction_type" id="auction_type" class="form-select" required>
                <option value="copart">Copart</option>
                <option value="iaai">IAAI</option>
                <option value="other">{{ __('Այլ') }}</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="location" class="form-label">{{ __('Պլատֆորմ (լոքացիա)') }}</label>
            <select name="location" id="location" class="form-select" required>
                <option value="" selected disabled>{{ __('Ընտրեք լոքացիա') }}</option>
                <option value="CA-LOS ANGELES">CA-LOS ANGELES</option>
                <option value="TX-HOUSTON">TX-HOUSTON</option>
                <option value="FL-MIAMI">FL-MIAMI</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="engine_type" class="form-label">{{ __('Շարժիչի տեսակ') }}</label>
            <select name="engine_type" id="engine_type" class="form-select" required>
                <option value="petrol">{{ __('Բենզին') }}</option>
                <option value="diesel">{{ __('Դիզել') }}</option>
                <option value="hybrid">{{ __('Հիբրիդ') }}</option>
                <option value="electric">{{ __('Էլեկտրո') }}</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="age" class="form-label">{{ __('Տարիք (տ.)') }}</label>
            <input type="number" name="age" id="age" class="form-control" required min="0" max="30" placeholder="0">
        </div>

        <div class="mb-3">
            <label for="engine_volume" class="form-label">{{ __('Շարժիչի ծավալ (սմ³)') }}</label>
            <input type="number" name="engine_volume" id="engine_volume" class="form-control" required min="300" placeholder="300">
        </div>

        <div class="mb-3">
            <label for="transport_type" class="form-label">{{ __('Տրանսպորտի տեսակ') }}</label>
            <select name="transport_type" id="transport_type" class="form-select" required>
                <option value="passenger">{{ __('Թեթև ավտոմեքենա') }}</option>
                <option value="truck">{{ __('Բեռնատար') }}</option>
                <option value="motorcycle">{{ __('Մոտոցիկլ') }}</option>
                <option value="other">{{ __('Այլ') }}</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">{{ __('Հաշվել') }}</button>
    </form>

    <div id="loader" class="mt-3" style="display:none;">{{ __('Հաշվում ենք…') }}</div>
    <div id="calc-result" class="mt-4"></div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('calc-form');
    if (!form) return;
    const resultDiv = document.getElementById('calc-result');
    const loaderDiv = document.getElementById('loader');

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        resultDiv.innerHTML = '';
        loaderDiv.style.display = 'block';

        fetch('{{ url('/copart-calculator/calculate') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                'Accept': 'application/json',
            },
            body: new FormData(form)
        })
        .then(async resp => {
            loaderDiv.style.display = 'none';
            let data = {};
            try { data = await resp.clone().json(); } catch (_) {}

            if (resp.status === 422 && data?.errors) {
                const errs = Object.values(data.errors).flat().join('<br>');
                resultDiv.innerHTML = `<div class="alert alert-danger">${errs}</div>`;
                return;
            }
            if (!resp.ok || data?.error) {
                resultDiv.innerHTML = `<div class="alert alert-danger">${data?.error ?? '{{ __('Ошибка расчета. Попробуйте позже.') }}'}</div>`;
                return;
            }

            let html = '<table class="table table-bordered"><tbody>';
            Object.entries(data).forEach(([k, v]) => {
                html += `<tr><th>${k}</th><td>${v}</td></tr>`;
            });
            html += '</tbody></table>';
            resultDiv.innerHTML = html;
        })
        .catch(() => {
            loaderDiv.style.display = 'none';
            resultDiv.innerHTML = '<div class="alert alert-danger">{{ __('Ошибка расчета. Попробуйте позже.') }}</div>';
        });
    });
});
</script>
@endpush
@endsection
