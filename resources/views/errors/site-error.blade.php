@extends('layouts.site')
@use('App\Support\Site\SiteRoutes')

@section('title', __('site.errors.'.$error.'.title'))
@section('description', __('site.errors.'.$error.'.text'))
@section('robots', 'noindex, nofollow')

@section('content')
    <x-site.page-hero
        :eyebrow="__('site.errors.eyebrow')"
        :title="__('site.errors.'.$error.'.title')"
        :lead="__('site.errors.'.$error.'.text')" />

    <section class="site-container py-14 sm:py-20">
        <div class="flex flex-wrap gap-3">
            <x-site.button :href="SiteRoutes::url('home')" icon="arrow-right">
                {{ __('site.errors.home') }}
            </x-site.button>
            <x-site.button :href="SiteRoutes::url('contact')" variant="outline">
                {{ __('site.actions.contact_us') }}
            </x-site.button>
        </div>
    </section>
@endsection
