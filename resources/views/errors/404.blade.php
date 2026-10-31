{{-- Unmatched URLs never reach SetSiteLocale, so read the language from the path. --}}
@php(app()->setLocale(request()->is('mr', 'mr/*') ? 'mr' : 'en'))
@include('errors.site-error', ['error' => 'not_found'])
