@extends('layouts.app')

@section('title', 'Wassili — وصّلي')

@section('content')
    <header class="mb-8">
        <h1 class="text-2xl font-bold">{{ __('Order anything, delivered') }}</h1>
        <p class="text-gray-500">{{ __('Pick a store type below, choose your store, then add what you want.') }}</p>
    </header>

    <div x-data="{ q: '' }">

        {{-- ===================== SEARCH BAR ===================== --}}
        <div class="sticky top-16 z-30 mb-8">
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-4 text-gray-400">🔍</span>
                <input
                    x-model="q"
                    type="search"
                    placeholder="{{ __('Search for stores or products…') }}"
                    class="w-full rounded-2xl border border-gray-200 bg-white py-3 ps-11 pe-10 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800"
                >
                <button
                    x-show="q" x-cloak @click="q = ''"
                    type="button"
                    class="absolute inset-y-0 end-0 flex items-center pe-4 text-gray-400 hover:text-gray-600"
                    aria-label="{{ __('Clear') }}">&times;</button>
            </div>
        </div>

        {{-- ===================== STORE TYPES ===================== --}}
        <section class="mb-10">
            <h2 class="mb-4 text-xl font-bold">{{ __('Store types') }}</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                {{-- Restaurants --}}
                <a href="#restaurants" x-show="!q || {{ Js::from(__('restaurants')) }}.includes(q.toLowerCase().trim()) || {{ Js::from('مطاعم') }}.includes(q.toLowerCase().trim())"
                   class="group flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-brand-500 hover:shadow-md dark:border-gray-700 dark:bg-gray-800/50">
                    <span class="text-4xl">🍽️</span>
                    <span>
                        <span class="block text-lg font-bold">{{ __('Restaurants') }} <span class="text-sm font-normal text-gray-400">({{ $vendors->count() }})</span></span>
                        <span class="block text-sm text-gray-500">{{ __('Pick a restaurant and order your meal') }}</span>
                    </span>
                    <span class="ms-auto text-gray-300 group-hover:text-brand-500">→</span>
                </a>

                {{-- Supermarket --}}
                @php $super = $categories->firstWhere('slug', 'supermarket'); @endphp
                <a href="{{ $super ? route('storefront.category', $super) : '#' }}" x-show="!q || {{ Js::from(__('supermarket')) }}.includes(q.toLowerCase().trim()) || {{ Js::from('سوبر ماركت') }}.includes(q.toLowerCase().trim())"
                   class="group flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-brand-500 hover:shadow-md dark:border-gray-700 dark:bg-gray-800/50">
                    <span class="text-4xl">🛒</span>
                    <span>
                        <span class="block text-lg font-bold">{{ __('Supermarket') }}</span>
                        <span class="block text-sm text-gray-500">{{ __('Everything from the universal catalog') }}</span>
                    </span>
                    <span class="ms-auto text-gray-300 group-hover:text-brand-500">→</span>
                </a>

                {{-- Pharmacy (placeholder) --}}
                <div class="flex items-center gap-4 rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-5 opacity-60 dark:border-gray-700 dark:bg-gray-800/30"
                     x-show="!q || {{ Js::from(__('pharmacy')) }}.includes(q.toLowerCase().trim()) || {{ Js::from('صيدلية') }}.includes(q.toLowerCase().trim())">
                    <span class="text-4xl">💊</span>
                    <span>
                        <span class="block text-lg font-bold">{{ __('Pharmacies') }}</span>
                        <span class="block text-sm text-gray-500">{{ __('Coming soon') }}</span>
                    </span>
                </div>
            </div>
        </section>

        {{-- ===================== RESTAURANTS ===================== --}}
        <section id="restaurants" class="mb-10">
            @php
                $vendorSearch = $vendors->map(fn ($v) => \Illuminate\Support\Str::lower(trim(
                    $v->name.' '.$v->name_ar
                )))->values()->all();
            @endphp
            <h2 class="mb-4 flex items-center justify-between text-xl font-bold">
                <span>🍽️ {{ __('Restaurants') }}</span>
                <span class="text-sm font-normal text-gray-400">{{ $vendors->count() }} {{ __('stores') }}</span>
            </h2>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
                 x-show="!q || {{ Js::from($vendorSearch) }}.some(s => s.includes(q.toLowerCase().trim()))">
                @foreach ($vendors as $vendor)
                    @php
                        $vSearch = \Illuminate\Support\Str::lower(trim($vendor->name.' '.$vendor->name_ar));
                        $isOpen = $vendor->is_open;
                    @endphp
                    <a href="{{ route('storefront.vendor', $vendor) }}"
                       x-show="!q || {{ Js::from($vSearch) }}.includes(q.toLowerCase().trim())"
                       class="group flex items-center gap-4 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm transition hover:border-brand-500 hover:shadow-md dark:border-gray-800 dark:bg-gray-800/50">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gray-100 text-2xl dark:bg-gray-700">
                            @if ($vendor->logo)
                                <img src="{{ asset('storage/'.$vendor->logo) }}" alt="" class="h-full w-full object-cover">
                            @else
                                🍽️
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold group-hover:text-brand-600">{{ $vendor->label }}</p>
                            <p class="text-xs text-gray-500">{{ $vendor->products_count }} {{ __('items') }}</p>
                        </div>
                        @if ($isOpen)
                            <span class="shrink-0 rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700 dark:bg-green-900/40 dark:text-green-400">{{ __('Open') }}</span>
                        @else
                            <span class="shrink-0 rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-600 dark:bg-red-900/40 dark:text-red-400">{{ __('Closed') }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>

        {{-- ===================== MORE CATEGORIES ===================== --}}
        @php
            $otherCategories = $categories->where('slug', '!=', 'supermarket');
            $catSearch = $otherCategories->map(fn ($c) => \Illuminate\Support\Str::lower(trim(
                $c->name.' '.$c->name_ar
            )))->values()->all();
        @endphp
        <section class="mb-10" x-show="!q || {{ Js::from($catSearch) }}.some(s => s.includes(q.toLowerCase().trim()))">
            <h2 class="mb-4 text-xl font-bold">🏷️ {{ __('Browse all categories') }}</h2>
            <div class="flex flex-wrap gap-2">
                @foreach ($otherCategories as $category)
                    @php
                        $cSearch = \Illuminate\Support\Str::lower(trim($category->name.' '.$category->name_ar));
                    @endphp
                    <a href="{{ route('storefront.category', $category) }}"
                       x-show="!q || {{ Js::from($cSearch) }}.includes(q.toLowerCase().trim())"
                       class="rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-medium shadow-sm transition hover:border-brand-500 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800/50">
                        {{ $category->icon ?: '🏷️' }} {{ $category->label }}
                        <span class="text-xs text-gray-400">({{ $category->products_count }})</span>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- ===================== NO RESULTS ===================== --}}
        <div x-show="q && !{{ Js::from(array_merge($vendorSearch, $catSearch)) }}.some(s => s.includes(q.toLowerCase().trim()))"
             x-cloak
             class="py-16 text-center text-gray-400">
            <p class="text-4xl">🔎</p>
            <p class="mt-2">{{ __('No stores match your search.') }}</p>
            <p class="mt-1 text-sm">
                <a :href="'{{ $super ? route('storefront.category', $super) : '#' }}?q=' + encodeURIComponent(q)"
                   class="font-medium text-brand-600 hover:underline">
                    {{ __('Search all products for') }} “<span x-text="q"></span>”
                </a>
            </p>
        </div>
    </div>
@endsection
