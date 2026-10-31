@php(app()->setLocale(request()->is('mr', 'mr/*') ? 'mr' : 'en'))
@include('errors.site-error', ['error' => 'too_many'])
