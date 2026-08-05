@extends('layouts.app')

@section('title', $category->label . ' — Wassili')

@section('content')
@php
    $showPrice = \App\Support\Settings::showPriceOnMainPage();
    $hasStores = $storeList->isNotEmpty();
@endphp

<div class="space-y-5 sm:space-y-6">

    <a href="{{ route('storefront.index') }}"
       class="inline-flex items-center gap-1.5 text-sm font-semibold text-ink-faint transition hover:text-zaatar-600">
        <span class="rtl:rotate-180">&larr;</span> {{ __('Home') }}
    </a>

    {{-- ==================== HEADER ==================== --}}
    <header class="flex items-center gap-4">
        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-zaatar-50 text-xl shadow-card sm:h-14 sm:w-14 sm:rounded-3xl sm:text-2xl dark:bg-zaatar-500/20">
            {{ $category->icon ?: '🏷️' }}
        </span>
        <div class="min-w-0">
            <h1 class="truncate text-xl font-extrabold sm:text-2xl">{{ $category->label }}</h1>
            <p class="text-sm text-ink-faint">
                @if ($hasStores)
                    {{ trans_choice('{1}:count store|[2,*]:count stores', $storeList->count(), ['count' => $storeList->count()]) }}
                @else
                    {{ trans_choice('{1}:count item|[2,*]:count items', $products->total(), ['count' => $products->total()]) }}
                @endif
            </p>
        </div>
    </header>

    {{-- ==================== STORES IN THIS CATEGORY ==================== --}}
    @if ($hasStores)
        <ul class="space-y-2">
            @foreach ($storeList as $vendor)
                <li>
                    <a href="{{ route('storefront.vendor', $vendor) }}"
                       class="flex items-center gap-3 rounded-2xl border border-paper-edge bg-white p-3 shadow-card transition hover:border-zaatar-400 hover:shadow-lift dark:border-white/10 dark:bg-white/5">
                        <span class="grid h-11 w-11 shrink-0 place-items-center overflow-hidden rounded-xl bg-paper-sunk text-lg sm:h-12 sm:w-12 sm:rounded-2xl sm:text-xl dark:bg-white/10">
                            @if ($vendor->logo)
                                <img src="{{ asset('storage/'.$vendor->logo) }}" alt="" class="h-full w-full object-cover">
                            @else
                                {{ optional($vendor->category)->icon ?: $category->icon ?: '🏪' }}
                            @endif
                        </span>

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold sm:text-base">{{ $vendor->label }}</p>
                            <p class="truncate text-xs text-ink-faint">
                                {{ trans_choice('{1}:count item|[2,*]:count items', $vendor->products_count, ['count' => $vendor->products_count]) }}
                            </p>
                        </div>

                        @if ($vendor->is_open)
                            <span class="shrink-0 rounded-full bg-zaatar-50 px-2.5 py-1 text-xs font-bold text-zaatar-600 dark:bg-zaatar-500/20 dark:text-zaatar-200">
                                {{ __('Open') }}
                            </span>
                        @else
                            <span class="shrink-0 rounded-full bg-paper-sunk px-2.5 py-1 text-xs font-semibold text-ink-faint dark:bg-white/10">
                                {{ $vendor->opens_at ? __('Opens :time', ['time' => $vendor->opens_at]) : __('Closed') }}
                            </span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    @endif

    {{-- ============ CATALOG PRODUCTS (categories sold without a store) ============ --}}
    @if ($products->total() > 0)
        @if ($hasStores)
            <h2 class="pt-2 text-base font-bold sm:text-lg">{{ __('Also available') }}</h2>
        @endif

        {{-- Search only matters once there is a real list to filter. --}}
        @if ($products->total() > 8 || request('q'))
            <form method="GET" action="{{ route('storefront.category', $category) }}"
                  class="sticky top-[60px] z-30 -mx-1 px-1 py-2 sm:top-[68px]">
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 start-0 grid w-12 place-items-center">🔍</span>
                    <input name="q" type="search" value="{{ request('q') }}" autocomplete="off"
                           placeholder="{{ __('Search in this category…') }}"
                           aria-label="{{ __('Search in this category…') }}"
                           class="w-full appearance-none rounded-2xl border-2 border-paper-edge bg-white py-3 ps-12 pe-4 text-[15px] shadow-card transition placeholder:text-ink-faint/70 focus:border-zaatar-500 focus:ring-0 sm:text-base [&::-webkit-search-cancel-button]:hidden dark:border-white/10 dark:bg-white/5">
                </div>
            </form>
        @endif

        <ul class="space-y-2">
            @foreach ($products as $product)
                @php
                    $orderable = $product->is_available;
                    $payload = [
                        'id'        => $product->id,
                        'name'      => $product->name,
                        'name_ar'   => $product->name_ar,
                        'price'     => (float) $product->price,
                        'vendor_id' => null,
                        'vendor'    => null,
                        'is_open'   => true,
                    ];
                @endphp

                <li class="flex items-center gap-3 rounded-2xl border border-paper-edge bg-white p-3 shadow-card dark:border-white/10 dark:bg-white/5 {{ $orderable ? '' : 'opacity-60' }}">
                    <span class="grid h-11 w-11 shrink-0 place-items-center overflow-hidden rounded-xl bg-paper-sunk text-lg sm:h-12 sm:w-12 sm:text-xl dark:bg-white/10">
                        @if ($product->image)
                            <img src="{{ asset('storage/'.$product->image) }}" alt="" class="h-full w-full object-cover">
                        @else
                            {{ $category->icon ?: '🛍️' }}
                        @endif
                    </span>

                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold leading-snug sm:text-base">{{ $product->label }}</p>
                        @if ($showPrice)
                            <p class="mt-0.5 text-[13px] font-bold leading-tight text-zaatar-600 sm:text-sm dark:text-zaatar-200">
                                {{ \App\Support\Money::both((float) $product->price) }}
                            </p>
                        @endif
                    </div>

                    @if ($orderable)
                        <div class="shrink-0" x-data="{ id: {{ $product->id }} }">
                            <template x-if="$store.cart.qtyOf(id) === 0">
                                <button @click="$store.cart.add({{ Js::from($payload) }})"
                                        aria-label="{{ __('Add') }} {{ $product->label }}"
                                        class="grid h-11 w-11 place-items-center rounded-xl bg-tangerine-500 text-xl font-bold text-white transition hover:bg-tangerine-600 active:scale-90">+</button>
                            </template>
                            <template x-if="$store.cart.qtyOf(id) > 0">
                                <div class="flex items-center rounded-xl bg-zaatar-600 p-0.5 text-white">
                                    <button @click="$store.cart.stepDown(id)" aria-label="{{ __('Remove one') }}"
                                            class="grid h-10 w-9 place-items-center rounded-lg text-lg font-bold transition hover:bg-white/15 active:scale-90">−</button>
                                    <span class="min-w-5 text-center text-sm font-bold tabular-nums" x-text="$store.cart.qtyOf(id)"></span>
                                    <button @click="$store.cart.stepUp(id)" aria-label="{{ __('Add one') }}"
                                            class="grid h-10 w-9 place-items-center rounded-lg text-lg font-bold transition hover:bg-white/15 active:scale-90">+</button>
                                </div>
                            </template>
                        </div>
                    @else
                        <span class="shrink-0 rounded-xl bg-paper-sunk px-3 py-2 text-xs font-semibold text-ink-faint dark:bg-white/10">
                            {{ __('Unavailable') }}
                        </span>
                    @endif
                </li>
            @endforeach
        </ul>

        @if ($products->hasPages())
            <div class="pt-2">{{ $products->links() }}</div>
        @endif
    @endif

    {{-- ==================== EMPTY ==================== --}}
    @if (! $hasStores && $products->total() === 0)
        <div class="rounded-3xl border-2 border-dashed border-paper-edge p-10 text-center dark:border-white/10">
            <p class="text-3xl">🔎</p>
            <p class="mt-2 font-bold">{{ __('Nothing here matches.') }}</p>
            <p class="mt-1 text-sm text-ink-faint">{{ __('Try a different word, or add it as a custom request.') }}</p>
        </div>
    @endif
</div>
@endsection
