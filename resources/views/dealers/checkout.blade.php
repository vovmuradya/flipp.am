<x-app-layout>
    <section class="brand-section">
        <div class="brand-container">
            <div class="brand-section__header">
                <h2 class="brand-section__title">{{ __('dealer.pay_title') }}</h2>
                <p class="brand-section__subtitle">{{ __('dealer.become') }}</p>
            </div>

            <div class="brand-surface py-4">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-6">
                        <h3 class="h5 mb-3">{{ __('dealer.pay_title') }}</h3>
                        <p class="text-muted mb-4">{{ __('dealer.pay_text') }}</p>
                        <ul class="list-unstyled text-muted small">
                            <li>• {{ __('dealer.pay_feature1') }}</li>
                            <li>• {{ __('dealer.pay_feature2') }}</li>
                            <li>• {{ __('dealer.pay_feature3') }}</li>
                        </ul>
                    </div>
                    <div class="col-lg-6">
                        <form action="{{ route('dealer.payment.checkout') }}" method="POST" class="p-4 bg-light rounded-3 shadow-sm">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">{{ __('dealer.company') }}</label>
                                <input type="text" name="company_name" class="form-control" value="{{ old('company_name', auth()->user()->dealerProfile->company_name ?? auth()->user()->name) }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __('dealer.email') }}</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', auth()->user()->email) }}" required>
                            </div>
                            <button type="submit" class="btn btn-brand-gradient w-100">
                                {{ __('dealer.pay_button') }}
                            </button>
                            @if ($errors->any())
                                <div class="alert alert-danger mt-3 mb-0">
                                    @foreach($errors->all() as $err)
                                        <div>{{ $err }}</div>
                                    @endforeach
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
