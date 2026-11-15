<form method="POST" action="{{ route('admin.language') }}" class="tms-language-switcher-form" aria-label="{{ __('filament/common.language.switch_to_arabic') }} / {{ __('filament/common.language.switch_to_english') }}">
    @csrf

    <button type="submit" name="locale" value="en" lang="en" class="tms-language-option {{ app()->isLocale('en') ? 'is-active' : '' }}" aria-pressed="{{ app()->isLocale('en') ? 'true' : 'false' }}">EN</button>
    <button type="submit" name="locale" value="ar" lang="ar" class="tms-language-option {{ app()->isLocale('ar') ? 'is-active' : '' }}" aria-pressed="{{ app()->isLocale('ar') ? 'true' : 'false' }}">عربي</button>
</form>
