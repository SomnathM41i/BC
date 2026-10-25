@extends('layouts.site')
@use('App\Support\Site\SiteRoutes')

@section('title', __('site.meta.contact.title'))
@section('description', __('site.meta.contact.description'))

@section('content')
    <x-site.page-hero
        :eyebrow="__('site.contact.banner_eyebrow')"
        :title="__('site.contact.banner_title')"
        :lead="__('site.contact.banner_lead')" />

    <section class="site-container py-16 lg:py-20">
        <div class="grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:gap-14">
            {{-- Contact details --}}
            <div>
                <h2 class="font-display text-2xl font-semibold text-brand-dark">{{ __('site.contact.info_title') }}</h2>

                <div class="mt-6 space-y-3">
                    @foreach ($site['phones'] as $phone)
                        <a href="{{ $phone['tel'] }}"
                           class="flex items-center gap-4 rounded-xl border border-ink/10 bg-white p-4 transition hover:border-brand/25 hover:shadow-sm">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-lg bg-sand text-brand">
                                <x-site.icon name="phone" class="h-5 w-5" />
                            </span>
                            <span class="min-w-0">
                                <span class="block text-xs font-semibold uppercase tracking-wider text-ink/45">{{ $phone['name'] }}</span>
                                <span class="block text-base font-semibold text-brand-dark">{{ $phone['display'] }}</span>
                            </span>
                        </a>
                    @endforeach

                    <a href="{{ $site['whatsapp_url'] }}" target="_blank" rel="noopener"
                       class="flex items-center gap-4 rounded-xl border border-ink/10 bg-white p-4 transition hover:border-brand/25 hover:shadow-sm">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-lg bg-sand text-brand">
                            <x-site.icon name="whatsapp" class="h-5 w-5" />
                        </span>
                        <span>
                            <span class="block text-xs font-semibold uppercase tracking-wider text-ink/45">{{ __('site.contact.whatsapp_label') }}</span>
                            <span class="block text-base font-semibold text-brand-dark">{{ $site['phones'][0]['display'] }}</span>
                        </span>
                    </a>

                    <div class="flex items-start gap-4 rounded-xl border border-ink/10 bg-white p-4">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-lg bg-sand text-brand">
                            <x-site.icon name="pin" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-ink/45">{{ __('site.contact.address_label') }}</p>
                            <address class="mt-1 space-y-0.5 text-sm not-italic leading-relaxed text-ink/70">
                                @foreach ($site['address'] as $line)
                                    <span class="block">{{ $line }}</span>
                                @endforeach
                            </address>
                        </div>
                    </div>

                    @if ($site['email'])
                        <a href="mailto:{{ $site['email'] }}"
                           class="flex items-center gap-4 rounded-xl border border-ink/10 bg-white p-4 transition hover:border-brand/25 hover:shadow-sm">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-lg bg-sand text-brand">
                                <x-site.icon name="mail" class="h-5 w-5" />
                            </span>
                            <span>
                                <span class="block text-xs font-semibold uppercase tracking-wider text-ink/45">{{ __('site.contact.email_label') }}</span>
                                <span class="block text-base font-semibold text-brand-dark">{{ $site['email'] }}</span>
                            </span>
                        </a>
                    @endif
                </div>

                <div class="mt-8 overflow-hidden rounded-2xl border border-ink/10 bg-white">
                    <div class="border-b border-ink/10 px-5 py-4">
                        <h2 class="font-display text-lg font-semibold text-brand-dark">{{ __('site.contact.map_title') }}</h2>
                    </div>

                    @if ($site['map_embed_url'])
                        <iframe src="{{ $site['map_embed_url'] }}" title="{{ __('site.contact.map_title') }}"
                                class="h-64 w-full border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    @else
                        <p class="px-5 py-6 text-sm leading-relaxed text-ink/55">{{ __('site.contact.map_note') }}</p>
                    @endif
                </div>
            </div>

            {{-- Enquiry form --}}
            <div id="enquiry-form" class="scroll-mt-28">
                <div class="site-card p-6 sm:p-8">
                    <h2 class="font-display text-2xl font-semibold text-brand-dark">{{ __('site.contact.form_title') }}</h2>
                    <p class="mt-2 text-sm leading-relaxed text-ink/60">{{ __('site.contact.form_lead') }}</p>

                    @if (session('inquiry_sent'))
                        <div class="mt-6 rounded-xl border border-green-200 bg-green-50 p-5" role="status">
                            <div class="flex items-start gap-3">
                                <x-site.icon name="check-circle" class="mt-0.5 h-5 w-5 shrink-0 text-green-700" />
                                <div>
                                    <p class="font-semibold text-green-900">{{ __('site.contact.success_title') }}</p>
                                    <p class="mt-1 text-sm text-green-800/80">{{ __('site.contact.success_text') }}</p>
                                </div>
                            </div>
                        </div>
                    @else
                        @if ($errors->any())
                            <div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
                                {{ __('site.contact.error_text') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ SiteRoutes::url('contact.store') }}" class="mt-6 space-y-5" novalidate>
                            @csrf

                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="name" class="site-label">{{ __('site.contact.form.name') }} *</label>
                                    <input id="name" name="name" type="text" value="{{ old('name') }}" required
                                           autocomplete="name" placeholder="{{ __('site.contact.form.name_placeholder') }}"
                                           @class(['site-input', 'border-red-300 focus:border-red-400 focus:ring-red-100' => $errors->has('name')])>
                                    @error('name')
                                        <p class="mt-1.5 text-xs text-red-700">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="phone" class="site-label">{{ __('site.contact.form.phone') }} *</label>
                                    <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required
                                           inputmode="tel" autocomplete="tel" placeholder="{{ __('site.contact.form.phone_placeholder') }}"
                                           @class(['site-input', 'border-red-300 focus:border-red-400 focus:ring-red-100' => $errors->has('phone')])>
                                    @error('phone')
                                        <p class="mt-1.5 text-xs text-red-700">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="email" class="site-label">
                                        {{ __('site.contact.form.email') }}
                                        <span class="font-normal normal-case tracking-normal text-ink/35">({{ __('site.contact.form.email_optional') }})</span>
                                    </label>
                                    <input id="email" name="email" type="email" value="{{ old('email') }}"
                                           autocomplete="email" placeholder="{{ __('site.contact.form.email_placeholder') }}"
                                           @class(['site-input', 'border-red-300 focus:border-red-400 focus:ring-red-100' => $errors->has('email')])>
                                    @error('email')
                                        <p class="mt-1.5 text-xs text-red-700">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="service" class="site-label">{{ __('site.contact.form.service') }}</label>
                                    <select id="service" name="service"
                                            @class(['site-input', 'border-red-300 focus:border-red-400 focus:ring-red-100' => $errors->has('service')])>
                                        <option value="">{{ __('site.contact.form.service_placeholder') }}</option>
                                        @foreach ($services as $service)
                                            <option value="{{ $service['slug'] }}" @selected(old('service') === $service['slug'])>
                                                {{ $service['name'] }}
                                            </option>
                                        @endforeach
                                        <option value="other" @selected(old('service') === 'other')>
                                            {{ __('site.contact.form.service_other') }}
                                        </option>
                                    </select>
                                    @error('service')
                                        <p class="mt-1.5 text-xs text-red-700">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label for="message" class="site-label">{{ __('site.contact.form.message') }} *</label>
                                <textarea id="message" name="message" rows="5" required
                                          placeholder="{{ __('site.contact.form.message_placeholder') }}"
                                          @class(['site-input resize-y', 'border-red-300 focus:border-red-400 focus:ring-red-100' => $errors->has('message')])>{{ old('message') }}</textarea>
                                @error('message')
                                    <p class="mt-1.5 text-xs text-red-700">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Honeypot --}}
                            <div class="hidden" aria-hidden="true">
                                <label for="company_website">{{ __('site.contact.form.honeypot') }}</label>
                                <input id="company_website" name="company_website" type="text" tabindex="-1" autocomplete="off">
                            </div>

                            <div class="flex flex-col gap-4 border-t border-ink/10 pt-5 sm:flex-row sm:items-center sm:justify-between">
                                <p class="text-xs leading-relaxed text-ink/45">{{ __('site.contact.form.privacy') }}</p>
                                <x-site.button icon="arrow-right" class="shrink-0">
                                    {{ __('site.contact.form.submit') }}
                                </x-site.button>
                            </div>
                        </form>
                    @endif
                </div>

                <div class="mt-5 flex flex-col gap-3 rounded-xl bg-brand-dark p-5 text-white sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-white/70">{{ __('site.actions.call_now') }} — {{ $site['phones'][0]['display'] }}</p>
                    <a href="{{ $site['phones'][0]['tel'] }}"
                       class="inline-flex items-center gap-2 text-sm font-semibold text-gold transition hover:text-white">
                        <x-site.icon name="phone" class="h-4 w-4" />
                        {{ $site['phones'][0]['display'] }}
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
