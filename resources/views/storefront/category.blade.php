@extends('layouts.app')

@section('title', $category->label . ' — Wassili')

@section('content')
@php $showPrice = \App\Support\Settings::showPriceOnMainPage(); @endphp

<div class="space-y-6">

    <a href="{{ route('storefront.index') }}"
       class="inline-flex items-center gap-1.5 text-sm font-semibold text-ink-faint transition hover:text-zaatar-600">
        <span class="rtl:rotate-180">&larr;</span> {{ __('All stores') }}
    </a>

    {{-- ==================== HEADER ==================== --}}
    <header class="flex items-center gap-4">
        <span class="grid h-14 w-14 shrink-0 place-items-center rounded-3xl bg-zaatar-50 text-2xl shadow-card dark:bg-zaatar-500/20">
            {{ $category->icon ?: '🏷️' }}
        </span>
        <div class="min-w-0">
            <h1 class="truncate text-2xl font-extrabold">{{ $category->label }}</h1>
            <p class="text-sm text-ink-faint">
                {{ trans_choice('{1}:count item|[2,*]:count items', $products->total(), ['count' => $products->total()]) }}
            </p>
        </div>
    </header>

    {{-- ==================== FILTERS ==================== --}}
    <form method="GET" action="{{ route('storefront.category', $category) }}"
          class="sticky top-[68px] z-30 -mx-1 space-y-2 px-1 py-2">
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 start-0 grid w-12 place-items-center">🔍</span>
            <input name="q" type="search" value="{{ request('q') }}" autocomplete="off"
                   placeholder="{{ __('Search in this category…') }}"
                   aria-label="{{ __('Search in this category…') }}"
                   class="w-full appearance-none rounded-2xl border-2 border-paper-edge bg-white py-3 ps-12 pe-4 text-base shadow-card transition placeholder:text-ink-faint/70 focus:border-zaatar-500 focus:ring-0 [&::-webkit-search-cancel-button]:hidden dark:border-white/10 dark:bg-white/5">
        </div>

        <div class="flex gap-2">
            @if ($vendors->isNotEmpty())
                <select name="vendor" onchange="this.form.submit()"
                        aria-label="{{ __('Filter by store') }}"
                        class="min-w-0 flex-1 rounded-2xl border-2 border-paper-edge bg-white px-4 py-3 text-sm font-medium shadow-card focus:border-zaatar-500 focus:ring-0 dark:border-white/10 dark:bg-white/5">
                    <option value="">{{ __('All stores') }}</option>
                    @foreach ($vendors as $v)
                        <option value="{{ $v->id }}" @selected(request('vendor') == $v->id)>{{ $v->label }}</option>
                    @endforeach
                </select>
            @endif
            <button type="submit"
                    class="shrink-0 rounded-2xl bg-zaatar-600 px-5 py-3 text-sm font-bold text-white shadow-card transition hover:bg-zaatar-700 active:scale-95">
                {{ __('Search') }}
            </button>
        </div>
    </form>

    {{-- ==================== PRODUCTS ==================== --}}
    <ul class="space-y-2">
        @forelse ($products as $product)
            @php
                $vendor = $product->vendor;
                $isOpen = ! $vendor || $vendor->is_open;
                $orderable = $product->is_available && $isOpen;
                $payload = [
                    'id'        => $product->id,
                    'name'      => $product->name,
                    'name_ar'   => $product->name_ar,
                    'price'     => (float) $product->price,
                    'vendor_id' => $product->vendor_id,
                    'vendor'    => $vendor?->name,
                    'is_open'   => $isOpen,
                ];
            @endphp

            <li class="flex items-center gap-3 rounded-2xl border border-paper-edge bg-white p-3 shadow-card dark:border-white/10 dark:bg-white/5 {{ $orderable ? '' : 'opacity-60' }}">
                <span class="grid h-12 w-12 shrink-0 place-items-center overflow-hidden rounded-xl bg-paper-sunk text-xl dark:bg-white/10">
                    @if ($product->image)
                        <img src="{{ asset('storage/'.$product->image) }}" alt="" class="h-full w-full object-cover">
                    @else
                        {{ $category->icon ?: '🛍️' }}
                    @endif
                </span>

                <div class="min-w-0 flex-1">
                    <p class="font-semibold leading-snug">{{ $product->label }}</p>
                    @if ($vendor)
                        <a href="{{ route('storefront.vendor', $vendor) }}"
                           class="truncate text-xs text-ink-faint underline-offset-2 hover:text-zaatar-600 hover:underline">{{ $vendor->label }}</a>
                    @else
                        <p class="truncate text-xs text-ink-faint">{{ __('Universal catalog') }}</p>
                    @endif
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
                        {{ $isOpen ? __('Unavailable') : __('Closed') }}
                    </span>
                @endif
            </li>
        @empty
            <li class="rounded-3xl border-2 border-dashed border-paper-edge p-10 text-center dark:border-white/10">
                <p class="text-3xl">🔎</p>
                <p class="mt-2 font-bold">{{ __('Nothing here matches.') }}</p>
                <p class="mt-1 text-sm text-ink-faint">{{ __('Try a different word, or add it as a custom request.') }}</p>
            </li>
        @endforelse
    </ul>

    @if ($products->hasPages())
        <div class="pt-2">{{ $products->links() }}</div>
    @endif
</div>
@endsection
