@php
    $loginDirection = app()->isLocale('ar') ? 'rtl' : 'ltr';
@endphp

<div class="tms-login-page" dir="ltr">
    <section class="tms-login-welcome" dir="{{ $loginDirection }}" aria-label="{{ __('filament/common.auth.welcome_heading') }}">
        <div class="tms-login-language">
            @include('filament.partials.language-switcher')
        </div>

        <div class="tms-login-welcome-copy">
            <p class="tms-login-eyebrow">{{ __('filament/common.auth.welcome') }}</p>
            <h2>{{ __('filament/common.auth.welcome_heading') }}</h2>
            <p class="tms-login-description">{{ __('filament/common.auth.welcome_description') }}</p>
        </div>
    </section>

    <section class="tms-login-panel" dir="{{ $loginDirection }}">
        <a href="{{ filament()->getHomeUrl() ?: '/' }}" class="tms-login-brand" dir="ltr" aria-label="{{ __('filament/common.brand') }}">
            <span class="tms-login-brand-mark" aria-hidden="true"></span>
            <span dir="auto">{{ __('filament/common.brand') }}</span>
        </a>

        <div class="tms-login-form-wrap">
            <h1>{{ $this->getHeading() }}</h1>
            <p class="tms-login-form-subheading">
                {{ filled($this->userUndertakingMultiFactorAuthentication) ? $this->getSubheading() : __('filament/common.auth.login_subheading') }}
            </p>

            <div class="tms-login-form">
                {{ $this->content }}
            </div>

            <div class="tms-login-security">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 3 20 6v5c0 5-3.4 8.2-8 10-4.6-1.8-8-5-8-10V6l8-3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                    <path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div>
                    <strong>{{ __('filament/common.auth.security_heading') }}</strong>
                    <span>{{ __('filament/common.auth.security_description') }}</span>
                </div>
            </div>

            <p class="tms-login-copyright">© {{ now()->year }} {{ __('filament/common.brand') }}. {{ __('filament/common.auth.copyright') }}</p>
        </div>
    </section>
</div>
