<x-app-layout>
    <x-slot name="header">
        <div class="brand-container py-6">
            <p class="profile-page__eyebrow">{{ __('profile.cabinet') }}</p>
            <h1 class="profile-page__title">{{ __('profile.settings') }}</h1>
            <p class="profile-page__subtitle">
                {{ __('profile.subtitle') }}
            </p>
        </div>
    </x-slot>

    <section class="brand-section profile-page">
        <div class="brand-container space-y-6">
            @if (session('error'))
                <div class="profile-alert profile-alert--error">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <div>
                        <p class="mb-0">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <div class="profile-page__hero brand-surface">
                <div>
                    <p class="profile-page__hero-label">{{ __('profile.authorized_as') }}</p>
                    <h2 class="profile-page__hero-title">{{ $user->name }}</h2>
                    <p class="profile-page__hero-subtitle">{{ $user->email }}</p>
                </div>
                <div class="profile-pill-group">
                    <span class="profile-pill {{ $user->phone ? 'profile-pill--success' : 'profile-pill--muted' }}">
                        <i class="fa-solid fa-phone"></i>
                        {{ $user->phone ? __('profile.phone_set') : __('profile.phone_not_set') }}
                    </span>
                    <span class="profile-pill profile-pill--muted">
                        <i class="fa-solid fa-circle-user"></i>
                        ID {{ $user->id }}
                    </span>
                </div>
            </div>

            <div class="profile-page__grid">
                <div class="brand-surface profile-card">
                    <div class="profile-card__header">
                        <div>
                            <p class="profile-card__eyebrow">{{ __('profile.main_info') }}</p>
                            <h3 class="profile-card__title">{{ __('profile.contact_data') }}</h3>
                        </div>
                        @if (session('status') === 'profile-updated')
                            <span class="profile-pill profile-pill--success">
                                <i class="fa-solid fa-circle-check"></i>
                                {{ __('profile.saved') }}
                            </span>
                        @endif
                    </div>

                    <form method="post" action="{{ route('profile.update') }}" class="profile-form">
                        @csrf
                        @method('patch')

                        <div class="profile-form__group">
                            <label for="profile_name" class="brand-form-label">{{ __('profile.name') }}</label>
                            <input
                                type="text"
                                id="profile_name"
                                name="name"
                                value="{{ old('name', $user->name) }}"
                                class="brand-form-control"
                                required
                                autocomplete="name"
                            >
                            @error('name')
                                <p class="profile-form__error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="profile-form__group">
                            <label for="profile_email" class="brand-form-label">{{ __('profile.email') }}</label>
                            <input
                                type="email"
                                id="profile_email"
                                name="email"
                                value="{{ old('email', $user->email) }}"
                                class="brand-form-control"
                                required
                                autocomplete="username"
                            >
                            @error('email')
                                <p class="profile-form__error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="profile-form__actions">
                            <button type="submit" class="btn-brand-gradient">
                                {{ __('profile.save_changes') }}
                            </button>
                        </div>
                    </form>

                </div>


                <div class="brand-surface profile-card" data-phone-card>
                    <div class="profile-card__header">
                        <div>
                            <p class="profile-card__eyebrow">{{ __('profile.listing_security') }}</p>
                            <h3 class="profile-card__title">{{ __('profile.phone_title') }}</h3>
                        </div>
                        <span class="profile-pill {{ $user->phone ? 'profile-pill--success' : 'profile-pill--muted' }}">
                            {{ $user->phone ? __('profile.specified') : __('profile.not_specified') }}
                        </span>
                    </div>

                    @if (session('phone_status'))
                        <div class="profile-alert profile-alert--success mb-4">
                            <i class="fa-solid fa-circle-check"></i>
                            <p class="mb-0">{{ session('phone_status') }}</p>
                        </div>
                    @endif

                    <form
                        method="post"
                        action="{{ route('profile.phone.verify') }}"
                        class="profile-form"
                        data-phone-form
                    >
                        @csrf

                        <div class="profile-form__group">
                            <label for="profile_phone" class="brand-form-label">{{ __('profile.phone_title') }}</label>
                            <div class="d-flex align-items-center gap-2">
                                <span class="brand-phone-prefix">+374</span>
                                <input
                                    type="tel"
                                    id="profile_phone"
                                    name="phone"
                                    value="{{ old('phone', $user->phone ? substr($user->phone, 4) : '') }}"
                                    class="brand-form-control flex-grow-1"
                                    placeholder="77 123 456"
                                    maxlength="8"
                                >
                            </div>
                            <p class="profile-form__hint">
                                {{ __('profile.phone_hint') }}
                            </p>
                            @if ($errors->phoneVerification->has('phone'))
                                <p class="profile-form__error">{{ $errors->phoneVerification->first('phone') }}</p>
                            @endif
                        </div>

                        <div class="profile-form__actions">
                            <button type="submit" class="btn-brand-gradient">
                                {{ __('profile.save_phone') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="profile-page__grid">
                <div class="brand-surface profile-card">
                    <div class="profile-card__header">
                        <div>
                            <p class="profile-card__eyebrow">{{ __('profile.dealer_section') }}</p>
                            <h3 class="profile-card__title">{{ __('profile.dealer_title') }}</h3>
                        </div>
                        @if($user->is_dealer)
                            <span class="profile-pill profile-pill--success">
                                <i class="fa-solid fa-circle-check"></i>
                                {{ __('profile.dealer_active') }}
                            </span>
                        @else
                            <span class="profile-pill profile-pill--muted">
                                {{ __('profile.dealer_inactive') }}
                            </span>
                        @endif
                    </div>

                    @if(!$user->is_dealer)
                        <p class="profile-card__text mb-3">{{ __('profile.dealer_text') }}</p>
                        <a href="{{ route('dealer.payment') }}" class="btn btn-brand-gradient">
                            {{ __('profile.become_dealer') }}
                        </a>
                    @else
                        @php $dealerProfile = $user->dealerProfile; @endphp
                        @if($dealerProfile)
                            <a href="{{ route('dealers.show', $dealerProfile->slug) }}" class="btn btn-brand-gradient">
                                {{ __('profile.my_dealer_profile') }}
                            </a>
                        @else
                            <p class="profile-card__text text-muted mb-2">{{ __('profile.dealer_no_profile') }}</p>
                            <a href="{{ route('dealer.payment') }}" class="btn btn-brand-outline">
                                {{ __('profile.create_dealer_profile') }}
                            </a>
                        @endif
                        <a href="{{ route('dealer.settings') }}" class="btn btn-brand-outline mt-2">
                            {{ __('profile.edit_dealer_profile') }}
                        </a>
                    @endif
                </div>
            </div>

            <div class="profile-page__grid profile-page__grid--stacked">
                <div class="brand-surface profile-card">
                    <div class="profile-card__header">
                        <div>
                            <p class="profile-card__eyebrow">{{ __('profile.security') }}</p>
                            <h3 class="profile-card__title">{{ __('profile.password_change') }}</h3>
                        </div>
                        @if (session('status') === 'password-updated')
                            <span class="profile-pill profile-pill--success">
                                <i class="fa-solid fa-circle-check"></i>
                                {{ __('profile.updated') }}
                            </span>
                        @endif
                    </div>

                    <form method="post" action="{{ route('password.update') }}" class="profile-form">
                        @csrf
                        @method('put')

                        <div class="profile-form__group">
                            <label for="current_password" class="brand-form-label">{{ __('profile.current_password') }}</label>
                            <input
                                type="password"
                                id="current_password"
                                name="current_password"
                                class="brand-form-control"
                                autocomplete="current-password"
                            >
                            @if ($errors->updatePassword->has('current_password'))
                                <p class="profile-form__error">{{ $errors->updatePassword->first('current_password') }}</p>
                            @endif
                        </div>

                        <div class="profile-form__grid">
                            <div>
                                <label for="new_password" class="brand-form-label">{{ __('profile.new_password') }}</label>
                                <input
                                    type="password"
                                    id="new_password"
                                    name="password"
                                    class="brand-form-control"
                                    autocomplete="new-password"
                                >
                                @if ($errors->updatePassword->has('password'))
                                    <p class="profile-form__error">{{ $errors->updatePassword->first('password') }}</p>
                                @endif
                            </div>

                            <div>
                                <label for="new_password_confirmation" class="brand-form-label">{{ __('profile.confirm_password') }}</label>
                                <input
                                    type="password"
                                    id="new_password_confirmation"
                                    name="password_confirmation"
                                    class="brand-form-control"
                                    autocomplete="new-password"
                                >
                                @if ($errors->updatePassword->has('password_confirmation'))
                                    <p class="profile-form__error">{{ $errors->updatePassword->first('password_confirmation') }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="profile-form__actions">
                            <button type="submit" class="btn-brand-outline">
                                {{ __('profile.save_password') }}
                            </button>
                        </div>
                    </form>
                </div>

                <div class="brand-surface profile-card profile-card--danger">
                    <div class="profile-card__header">
                        <div>
                            <p class="profile-card__eyebrow">{{ __('profile.danger_zone') }}</p>
                            <h3 class="profile-card__title">{{ __('profile.delete_account') }}</h3>
                        </div>
                    </div>

                    <p class="profile-card__text">
                        {{ __('profile.delete_warning') }}
                    </p>

                    <form method="post" action="{{ route('profile.destroy') }}" class="profile-form">
                        @csrf
                        @method('delete')

                        <div class="profile-form__group">
                            <label for="delete_password" class="brand-form-label">{{ __('profile.confirm_password_label') }}</label>
                            <input
                                type="password"
                                id="delete_password"
                                name="password"
                                class="brand-form-control"
                                autocomplete="current-password"
                            >
                            @if ($errors->userDeletion->has('password'))
                                <p class="profile-form__error">{{ $errors->userDeletion->first('password') }}</p>
                            @endif
                        </div>

                        <div class="profile-form__actions">
                            <button
                                type="submit"
                                class="btn-brand-red btn-brand-full"
                                onclick="return confirm('{{ __('profile.confirm_delete') }}')"
                            >
                                {{ __('profile.delete_button') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
