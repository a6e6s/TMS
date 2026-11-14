<form method="POST" action="{{ route('admin.language') }}" class="tms-language-switcher" aria-label="{{ __('filament/common.language.switch_to_arabic') }} / {{ __('filament/common.language.switch_to_english') }}" style="display:flex;gap:.25rem;padding:.25rem;">
    @csrf

    <button type="submit" name="locale" value="en" lang="en" aria-pressed="{{ app()->isLocale('en') ? 'true' : 'false' }}" style="padding:.25rem .6rem;border-radius:.375rem;font-size:.875rem;cursor:pointer;border:1px solid {{ app()->isLocale('en') ? '#f59e0b' : 'transparent' }};background:{{ app()->isLocale('en') ? '#fef3c7' : 'transparent' }};">EN</button>
    <button type="submit" name="locale" value="ar" lang="ar" aria-pressed="{{ app()->isLocale('ar') ? 'true' : 'false' }}" style="padding:.25rem .6rem;border-radius:.375rem;font-size:.875rem;cursor:pointer;border:1px solid {{ app()->isLocale('ar') ? '#f59e0b' : 'transparent' }};background:{{ app()->isLocale('ar') ? '#fef3c7' : 'transparent' }};">عربي</button>
</form>
