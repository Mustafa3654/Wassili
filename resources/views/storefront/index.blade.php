@extends('layouts.app')

@section('title', 'Wassili — وصّلي')

@section('content')
@php
    $vendorIndex = $vendors->map(fn ($v) => [
        'name'    => $v->label,
        'sub'     => optional($v->category)->label,
        'count'   => $v->products_count,
        'open'    => (bool) $v->is_open,
        'opensAt' => $v->opens_at,
        'icon'    => optional($v->category)->icon ?: '🏪',
        'url'     => route('storefront.vendor', $v),
        'q'       => \Illuminate\Support\Str::lower(trim($v->name.' '.$v->name_ar.' '.optional($v->category)->name)),
    ])->values();
@endphp

<div x-data="storefront()" class="space-y-10">

    {{-- ==================== HERO: the ask ==================== --}}
    <header class="animate-rise">
        <p class="mb-1 text-sm font-semibold text-tangerine-500">
            {{ __('Delivered by scooter, ordered on WhatsApp') }}
        </p>
        <h1 class="text-3xl font-extrabold leading-tight sm:text-4xl">
            {{ __('What do you need today?') }}
        </h1>
        <p class="mt-1.5 text-ink-faint dark:text-paper/60">
            {{ __('Search once — we look inside every store for you.') }}
        </p>

        {{-- Signature: one field that searches products AND stores --}}
        <div class="sticky top-[68px] z-30 -mx-1 mt-5 px-1 py-2">
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 start-0 grid w-14 place-items-center text-lg">🔍</span>
                <input
                    x-model="q"
                    x-ref="search"
                    type="search"
                    inputmode="search"
                    enterkeyhint="search"
                    autocomplete="off"
                    placeholder="{{ __('Try “burger”, “water”, or a store name…') }}"
                    aria-label="{{ __('Search products and stores') }}"
                    class="w-full appearance-none rounded-3xl border-2 border-paper-edge bg-white py-4 ps-14 pe-12 text-base font-medium shadow-card transition placeholder:text-ink-faint/70 focus:border-zaatar-500 focus:ring-0 [&::-webkit-search-cancel-button]:hidden dark:border-white/10 dark:bg-white/5"
                >
                <button x-show="q" x-cloak @click="q = ''; $refs.search.focus()" type="button"
                        aria-label="{{ __('Clear search') }}"
                        class="absolute inset-y-0 end-0 grid w-12 place-items-center text-2xl text-ink-faint transition hover:text-ink">&times;</button>
            </div>
        </div>
    </header>

    {{-- ==================== INSTANT RESULTS ==================== --}}
    <section x-show="q" x-cloak class="space-y-6">

        {{-- Products: add straight to the cart, no drilling into stores --}}
        <div x-show="products.length">
            <h2 class="mb-3 flex items-baseline gap-2 text-lg font-bold">
                {{ __('Items') }}
                <span class="text-sm font-medium text-ink-faint" x-text="`(${products.length})`"></span>
            </h2>
            <ul class="space-y-2">
                <template x-for="p in products.slice(0, 8)" :key="p.id">
                    <li class="flex items-center gap-3 rounded-2xl border border-paper-edge bg-white p-3 shadow-card dark:border-white/10 dark:bg-white/5">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-paper-sunk text-xl dark:bg-white/10" x-text="p.icon"></span>
                        {{-- Name gets the full row width; price sits beneath it so
                             long dual-currency figures never squeeze the title. --}}
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold leading-snug" x-text="name(p)"></p>
                            <p class="truncate text-xs text-ink-faint">
                                <span x-text="vendorName(p) || '{{ __('Universal catalog') }}'"></span>
                                <template x-if="!p.is_open">
                                    <span class="font-semibold text-tangerine-600"> · {{ __('Closed') }}</span>
                                </template>
                            </p>
                            <p class="mt-0.5 truncate text-sm font-bold text-zaatar-600 dark:text-zaatar-200"
                               x-text="$store.cart.money(p.price)"></p>
                        </div>
                        <button
                            @click="$store.cart.add(toCartItem(p))"
                            :disabled="!p.is_open"
                            :aria-label="'{{ __('Add') }} ' + name(p)"
                            class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-tangerine-500 text-xl font-bold text-white transition hover:bg-tangerine-600 active:scale-90 disabled:cursor-not-allowed disabled:bg-paper-edge disabled:text-ink-faint dark:disabled:bg-white/10">
                            +
                        </button>
                    </li>
                </template>
            </ul>
        </div>

        {{-- Stores --}}
        <div x-show="stores.length">
            <h2 class="mb-3 text-lg font-bold">{{ __('Stores') }}</h2>
            <ul class="space-y-2">
                <template x-for="(v, i) in stores.slice(0, 6)" :key="i">
                    <li>
                        <a :href="v.url" class="flex items-center gap-3 rounded-2xl border border-paper-edge bg-white p-3 shadow-card transition hover:border-zaatar-400 dark:border-white/10 dark:bg-white/5">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-paper-sunk text-xl dark:bg-white/10" x-text="v.icon"></span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold" x-text="v.name"></p>
                                <p class="truncate text-xs text-ink-faint" x-text="v.sub"></p>
                            </div>
                            <template x-if="v.open">
                                <span class="shrink-0 rounded-full bg-zaatar-50 px-2.5 py-1 text-xs font-bold text-zaatar-600 dark:bg-zaatar-500/20 dark:text-zaatar-200">{{ __('Open') }}</span>
                            </template>
                            <template x-if="!v.open">
                                <span class="shrink-0 rounded-full bg-paper-sunk px-2.5 py-1 text-xs font-semibold text-ink-faint dark:bg-white/10" x-text="v.opensAt ? '{{ __('Opens') }} ' + v.opensAt : '{{ __('Closed') }}'"></span>
                            </template>
                        </a>
                    </li>
                </template>
            </ul>
        </div>

        {{-- Nothing found: the custom request is the way out --}}
        <div x-show="!products.length && !stores.length" x-cloak
             class="rounded-3xl border-2 border-dashed border-paper-edge p-8 text-center dark:border-white/10">
            <p class="text-3xl">🧾</p>
            <p class="mt-2 font-bold">{{ __('We don\'t list that yet.') }}</p>
            <p class="mt-1 text-sm text-ink-faint">{{ __('Add it as a custom request and the driver will find it for you.') }}</p>
            <button @click="$store.cart.addCustom(q, ''); q = ''"
                    class="mt-4 rounded-2xl bg-tangerine-500 px-5 py-3 font-display font-bold text-white shadow-card transition hover:bg-tangerine-600 active:scale-95">
                {{ __('Request') }} “<span x-text="q"></span>”
            </button>
        </div>
    </section>

    {{-- ==================== BROWSE (hidden while searching) ==================== --}}
    <div x-show="!q" class="space-y-10">

        {{-- ---------- Open now ---------- --}}
        @if ($openNow->isNotEmpty())
            <section>
                <div class="mb-3 flex items-baseline justify-between gap-3">
                    <h2 class="text-lg font-bold">
                        <span class="inline-grid h-2 w-2 -translate-y-px place-items-center rounded-full bg-zaatar-500 ring-4 ring-zaatar-500/20"></span>
                        {{ __('Open now') }}
                    </h2>
                    <span class="text-sm text-ink-faint">{{ trans_choice('{1}:count store|[2,*]:count stores', $openNow->count(), ['count' => $openNow->count()]) }}</span>
                </div>

                <div class="rail -mx-4 px-4">
                    @foreach ($openNow as $vendor)
                        <a href="{{ route('storefront.vendor', $vendor) }}"
                           class="w-[172px] rounded-3xl border border-paper-edge bg-white p-4 shadow-card transition hover:-translate-y-0.5 hover:border-zaatar-400 hover:shadow-lift dark:border-white/10 dark:bg-white/5">
                            <span class="grid h-12 w-12 place-items-center rounded-2xl bg-zaatar-50 text-2xl dark:bg-zaatar-500/20">
                                {{ optional($vendor->category)->icon ?: '🏪' }}
                            </span>
                            <p class="mt-3 truncate font-bold">{{ $vendor->label }}</p>
                            <p class="truncate text-xs text-ink-faint">{{ optional($vendor->category)->label }}</p>
                            <p class="mt-2 text-xs font-semibold text-zaatar-600 dark:text-zaatar-200">
                                {{ trans_choice('{1}:count item|[2,*]:count items', $vendor->products_count, ['count' => $vendor->products_count]) }}
                            </p>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ---------- Shop by category (100% from the admin) ---------- --}}
        <section>
            <h2 class="mb-3 text-lg font-bold">{{ __('Shop by category') }}</h2>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                @foreach ($sections as $section)
                    @php
                        $cat  = $section->category;
                        $href = $section->vendors->isNotEmpty()
                            ? '#cat-'.$cat->id
                            : route('storefront.category', $cat);
                    @endphp
                    <a href="{{ $href }}"
                       class="group relative overflow-hidden rounded-3xl border border-paper-edge bg-white p-4 shadow-card transition hover:-translate-y-0.5 hover:border-zaatar-400 hover:shadow-lift dark:border-white/10 dark:bg-white/5">
                        <span class="text-3xl">{{ $cat->icon ?: '🏷️' }}</span>
                        <p class="mt-2 font-bold leading-tight">{{ $cat->label }}</p>
                        <p class="mt-0.5 text-xs text-ink-faint">
                            @if ($section->vendors->isNotEmpty())
                                {{ trans_choice('{1}:count store|[2,*]:count stores', $section->vendors->count(), ['count' => $section->vendors->count()]) }}
                            @else
                                {{ trans_choice('{1}:count item|[2,*]:count items', $section->products, ['count' => $section->products]) }}
                            @endif
                        </p>
                        @if ($section->open > 0)
                            <span class="mt-2 inline-block rounded-full bg-zaatar-50 px-2 py-0.5 text-[11px] font-bold text-zaatar-600 dark:bg-zaatar-500/20 dark:text-zaatar-200">
                                {{ __(':count open', ['count' => $section->open]) }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>

        {{-- ---------- Every store, under its real category ---------- --}}
        @foreach ($sections as $section)
            @continue($section->vendors->isEmpty())
            <section id="cat-{{ $section->category->id }}" class="scroll-mt-32">
                <div class="mb-3 flex items-baseline justify-between gap-3">
                    <h2 class="text-lg font-bold">
                        {{ $section->category->icon }} {{ $section->category->label }}
                    </h2>
                    <span class="text-sm text-ink-faint">
                        {{ trans_choice('{1}:count store|[2,*]:count stores', $section->vendors->count(), ['count' => $section->vendors->count()]) }}
                    </span>
                </div>

                <ul class="space-y-2">
                    @foreach ($section->vendors as $vendor)
                        <li>
                            <a href="{{ route('storefront.vendor', $vendor) }}"
                               class="flex items-center gap-3 rounded-2xl border border-paper-edge bg-white p-3 shadow-card transition hover:border-zaatar-400 hover:shadow-lift dark:border-white/10 dark:bg-white/5">
                                <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-paper-sunk text-xl dark:bg-white/10">
                                    @if ($vendor->logo)
                                        <img src="{{ asset('storage/'.$vendor->logo) }}" alt="" class="h-full w-full rounded-2xl object-cover">
                                    @else
                                        {{ optional($vendor->category)->icon ?: '🏪' }}
                                    @endif
                                </span>

                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-bold">{{ $vendor->label }}</p>
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
            </section>
        @endforeach

        {{-- ---------- Can't find it? ---------- --}}
        <section class="rounded-3xl bg-zaatar-600 p-6 text-center text-white shadow-lift dark:bg-zaatar-700">
            <p class="text-3xl">🧾</p>
            <h2 class="mt-2 text-xl font-extrabold">{{ __('Need something not listed?') }}</h2>
            <p class="mx-auto mt-1 max-w-sm text-sm text-white/80">
                {{ __('Write it in your own words. The driver buys it and brings it with the rest of your order.') }}
            </p>
            <button @click="$dispatch('open-cart')"
                    class="mt-4 rounded-2xl bg-white px-5 py-3 font-display font-bold text-zaatar-700 shadow-card transition hover:bg-paper active:scale-95">
                {{ __('Add a custom request') }}
            </button>
        </section>
    </div>
</div>

<script>
    function storefront() {
        return {
            q: '',
            productIndex: @json($productIndex),
            vendorIndex: @json($vendorIndex),

            get term() {
                return this.q.toLowerCase().trim();
            },
            get products() {
                if (!this.term) return [];
                return this.productIndex.filter(p => p.q.includes(this.term));
            },
            get stores() {
                if (!this.term) return [];
                return this.vendorIndex.filter(v => v.q.includes(this.term));
            },
            name(p) {
                return window.WASSILI.locale === 'ar' ? p.name_ar : p.name;
            },
            vendorName(p) {
                return window.WASSILI.locale === 'ar' ? p.vendor_ar : p.vendor;
            },
            /** Map a search hit onto the shape the Alpine cart store expects. */
            toCartItem(p) {
                return {
                    id: p.id,
                    name: p.name,
                    name_ar: p.name_ar,
                    price: p.price,
                    vendor_id: p.vendor_id,
                    vendor: p.vendor,
                    is_open: p.is_open,
                };
            },
        };
    }
</script>
@endsection
