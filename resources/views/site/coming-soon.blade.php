@extends('layouts.site')

@section('title', 'Coming soon')
@section('meta_description', 'Bandgar Construction — a new website is on the way.')

@section('content')
    <section class="mx-auto flex w-full max-w-6xl flex-col justify-center px-4 py-20 md:px-8 md:py-28">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-700">Bandgar Construction</p>

        <h1 class="mt-4 max-w-2xl text-3xl font-semibold tracking-tight text-stone-900 sm:text-4xl">
            Our new website is on the way.
        </h1>

        <p class="mt-4 max-w-xl text-base leading-relaxed text-stone-600">
            We are putting together a proper home for the work we do, the projects we have
            delivered and the people we build for. Please check back shortly.
        </p>

        <div class="mt-10">
            <a href="{{ route('admin.login') }}"
               class="inline-flex items-center gap-2 rounded-md bg-stone-900 px-4 py-2 text-sm font-medium text-stone-50 transition hover:bg-stone-800">
                Staff sign in
            </a>
        </div>
    </section>
@endsection
