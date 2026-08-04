@extends('layouts.app')

@section('title', $vendor->label . ' — Wassili')

@section('content')
    <div class="mb-6">
        <a href="{{ route('storefront.index') }}" class="text-sm text-gray-500 hover:text-brand-600">&larr; {{ __('Back to all stores') }}</a>
    </div>

    <header class="mb-6 flex items-center gap-4">
        <div class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-2xl bg-gray-100 text-3xl dark:bg-gray-700">
            @if ($vendor->logo)
                <img src="{{ asset('storage/'.$vendor->logo) }}" alt="" class="h-full w-full object-cover">
            @else
                🍽️
            @endif
        </div>
        <div class="flex-1">
            <h1 class="text-2xl font-bold">{{ $vendor->label }}</h1>
            <p class="text-gray-500">
                {{ $groups->sum(fn ($g) => $g->count()) }} {{ __('items') }}
                @if ($vendor->is_open)
                    · <span class="text-green-600 dark:text-green-400">{{ __('Open now') }}</span>
                @else
                    · <span class="text-red-600 dark:text-red-400">{{ __('Closed') }}</span>
                @endif
            </p>
        </div>
    </header>

    @php
        $allSearch = $groups->flatMap->values()->map(fn ($p) => \Illuminate\Support\Str::lower(trim(
            $p->name.' '.$p->name_ar
        )))->values()->all();
    @endphp

    <div x-data="{ q: '' }">
        <div class="sticky top-16 z-30 mb-8">
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-4 text-gray-400">🔍</span>
                <input
                    x-model="q"
                    type="search"
                    placeholder="{{ __('Search this menu…') }}"
                    class="w-full rounded-2xl border border-gray-200 bg-white py-3 ps-11 pe-10 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800"
                >
                <button
                    x-show="q" x-cloak @click="q = ''"
                    type="button"
                    class="absolute inset-y-0 end-0 flex items-center pe-4 text-gray-400 hover:text-gray-600"
                    aria-label="{{ __('Clear') }}">&times;</button>
            </div>
        </div>

        @foreach ($groups as $categoryId => $group)
            @php
                $category = $group->first()->category;
                $groupSearch = $group->map(fn ($p) => \Illuminate\Support\Str::lower(trim($p->name.' '.$p->name_ar)))->values()->all();
            @endphp
            <section class="mb-10"
                     x-show="!q || {{ Js::from($groupSearch) }}.some(s => s.includes(q.toLowerCase().trim()))">
                <h2 class="mb-3 flex items-center gap-2 text-xl font-bold">
                    <span>{{ $category?->icon ?: '🏷️' }}</span>
                    <span>{{ $category?->label ?: __('Other') }}</span>
                    <span class="text-sm font-normal text-gray-400">({{ $group->count() }})</span>
                </h2>

                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($group as $product)
                        @php
                            $isOpen = $vendor->is_open;
                            $orderable = $product->is_available && $isOpen;
                            $search = \Illuminate\Support\Str::lower(trim($product->name.' '.$product->name_ar));
                            $payload = [
                                'id'        => $product->id,
                                'name'      => $product->name,
                                'name_ar'   => $product->name_ar,
                                'price'     => (float) $product->price,
                                'vendor_id' => $product->vendor_id,
                                'vendor'    => $vendor->name,
                                'is_open'   => $isOpen,
                            ];
                        @endphp

                        <div
                            x-show="!q || {{ Js::from($search) }}.includes(q.toLowerCase().trim())"
                            class="flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-800/50 {{ $orderable ? '' : 'opacity-60' }}">
                            @if ($product->image)
                                <img src="{{ asset('storage/'.$product->image) }}" alt="" class="h-32 w-full object-cover">
                            @else
                                <div class="flex h-32 w-full items-center justify-center bg-gray-100 text-3xl dark:bg-gray-700">🍽️</div>
                            @endif

                            <div class="flex flex-1 flex-col p-3">
                                <p class="font-semibold">{{ $product->label }}</p>

                                @if (\App\Support\Settings::showPriceOnMainPage())
                                    <p class="mt-1 text-sm font-medium text-brand-600">{{ \App\Support\Money::both((float) $product->price) }}</p>
                                @endif

                                <div class="mt-auto pt-3">
                                    @if ($orderable)
                                        <button
                                            @click="$store.cart.add({{ Js::from($payload) }})"
                                            class="w-full rounded-lg bg-brand-600 py-2 text-sm font-medium text-white hover:bg-brand-700">
                                            {{ __('Add to cart') }}
                                        </button>
                                    @elseif (! $isOpen)
                                        <span class="block rounded-lg bg-red-100 py-2 text-center text-sm text-red-600 dark:bg-red-900/40">
                                            {{ __('Store closed') }}
                                        </span>
                                    @else
                                        <span class="block rounded-lg bg-gray-100 py-2 text-center text-sm text-gray-500 dark:bg-gray-700">
                                            {{ __('Unavailable') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

        <div
            x-show="q && !{{ Js::from($allSearch) }}.some(s => s.includes(q.toLowerCase().trim()))"
            x-cloak
            class="py-16 text-center text-gray-400"
        >
            <p class="text-4xl">🔎</p>
            <p class="mt-2">{{ __('No products match your search.') }}</p>
        </div>
    </div>
@endsection
