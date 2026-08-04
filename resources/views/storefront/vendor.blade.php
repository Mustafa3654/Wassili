@extends('layouts.app')

@section('title', $vendor->label . ' — Wassili')

@section('content')
@php
    $itemCount = $groups->sum(fn ($g) => $g->count());
    $allSearch = $groups->flatMap->values()
        ->map(fn ($p) => \Illuminate\Support\Str::lower(trim($p->name.' '.$p->name_ar)))
        ->values()->all();
    $showPrice = \App\Support\Settings::showPriceOnMainPage();
@endphp

<div x-data="{ q: '' }" class="space-y-6">

    <a href="{{ route('storefront.index') }}"
       class="inline-flex items-center gap-1.5 text-sm font-semibold text-ink-faint transition hover:text-zaatar-600">
        <span class="rtl:rotate-180">&larr;</span> {{ __('All stores') }}
    </a>

    {{-- ==================== STORE HEADER ==================== --}}
    <header class="flex items-center gap-4">
        <span class="grid h-16 w-16 shrink-0 place-items-center overflow-hidden rounded-3xl bg-zaatar-50 text-3xl shadow-card dark:bg-zaatar-500/20">
            @if ($vendor->logo)
                <img src="{{ asset('storage/'.$vendor->logo) }}" alt="" class="h-full w-full object-cover">
            @else
                {{ optional($vendor->category)->icon ?: '🏪' }}
            @endif
        </span>
        <div class="min-w-0 flex-1">
            <h1 class="truncate text-2xl font-extrabold">{{ $vendor->label }}</h1>
            <p class="mt-0.5 flex flex-wrap items-center gap-x-2 text-sm text-ink-faint">
                <span>{{ trans_choice('{1}:count item|[2,*]:count items', $itemCount, ['count' => $itemCount]) }}</span>
                <span aria-hidden="true">·</span>
                @if ($vendor->is_open)
                    <span class="font-bold text-zaatar-600 dark:text-zaatar-200">{{ __('Open now') }}</span>
                @else
                    <span class="font-bold text-tangerine-600">
                        {{ $vendor->opens_at ? __('Opens :time', ['time' => $vendor->opens_at]) : __('Closed') }}
                    </span>
                @endif
            </p>
        </div>
    </header>

    {{-- Closed notice: say what the customer can still do. --}}
    @unless ($vendor->is_open)
        <p class="rounded-2xl bg-tangerine-50 px-4 py-3 text-sm font-medium text-tangerine-600 dark:bg-tangerine-500/15">
            {{ __('This store is closed right now, so its items can\'t be added yet.') }}
        </p>
    @endunless

    {{-- ==================== MENU SEARCH ==================== --}}
    <div class="sticky top-[68px] z-30 -mx-1 px-1 py-2">
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 start-0 grid w-12 place-items-center">🔍</span>
            <input x-model="q" type="search" autocomplete="off"
                   placeholder="{{ __('Search this menu…') }}"
                   aria-label="{{ __('Search this menu…') }}"
                   class="w-full appearance-none rounded-2xl border-2 border-paper-edge bg-white py-3 ps-12 pe-11 text-base shadow-card transition placeholder:text-ink-faint/70 focus:border-zaatar-500 focus:ring-0 [&::-webkit-search-cancel-button]:hidden dark:border-white/10 dark:bg-white/5">
            <button x-show="q" x-cloak @click="q = ''" type="button" aria-label="{{ __('Clear search') }}"
                    class="absolute inset-y-0 end-0 grid w-11 place-items-center text-2xl text-ink-faint hover:text-ink">&times;</button>
        </div>
    </div>

    {{-- ==================== MENU ==================== --}}
    @foreach ($groups as $categoryId => $group)
        @php
            $category = $group->first()->category;
            $groupSearch = $group->map(fn ($p) => \Illuminate\Support\Str::lower(trim($p->name.' '.$p->name_ar)))->values()->all();
        @endphp

        <section x-show="!q || {{ Js::from($groupSearch) }}.some(s => s.includes(q.toLowerCase().trim()))">
            <h2 class="mb-3 flex items-baseline gap-2 text-lg font-bold">
                <span>{{ $category?->icon ?: '🏷️' }}</span>
                <span>{{ $category?->label ?: __('Other') }}</span>
                <span class="text-sm font-medium text-ink-faint">{{ $group->count() }}</span>
            </h2>

            <ul class="space-y-2">
                @foreach ($group as $product)
                    @php
                        $orderable = $product->is_available && $vendor->is_open;
                        $search = \Illuminate\Support\Str::lower(trim($product->name.' '.$product->name_ar));
                        $payload = [
                            'id'        => $product->id,
                            'name'      => $product->name,
                            'name_ar'   => $product->name_ar,
                            'price'     => (float) $product->price,
                            'vendor_id' => $product->vendor_id,
                            'vendor'    => $vendor->name,
                            'is_open'   => $vendor->is_open,
                        ];
                    @endphp

                    <li x-show="!q || {{ Js::from($search) }}.includes(q.toLowerCase().trim())"
                        class="flex items-center gap-3 rounded-2xl border border-paper-edge bg-white p-3 shadow-card transition dark:border-white/10 dark:bg-white/5 {{ $orderable ? '' : 'opacity-60' }}">

                        <span class="grid h-12 w-12 shrink-0 place-items-center overflow-hidden rounded-xl bg-paper-sunk text-xl dark:bg-white/10">
                            @if ($product->image)
                                <img src="{{ asset('storage/'.$product->image) }}" alt="" class="h-full w-full object-cover">
                            @else
                                {{ $category?->icon ?: '🛍️' }}
                            @endif
                        </span>

                        <div class="min-w-0 flex-1">
                            <p class="font-semibold leading-snug">{{ $product->label }}</p>
                            @if ($showPrice)
                                <p class="mt-0.5 text-[13px] font-bold leading-tight text-zaatar-600 sm:text-sm dark:text-zaatar-200">
                                    {{ \App\Support\Money::both((float) $product->price) }}
                                </p>
                            @endif
                        </div>

                        @if ($orderable)
                            {{-- Stepper appears once the item is in the cart, so the
                                 quantity is adjustable without opening the drawer. --}}
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
                                {{ $vendor->is_open ? __('Unavailable') : __('Closed') }}
                            </span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>
    @endforeach

    {{-- ==================== NO RESULTS ==================== --}}
    <div x-show="q && !{{ Js::from($allSearch) }}.some(s => s.includes(q.toLowerCase().trim()))"
         x-cloak
         class="rounded-3xl border-2 border-dashed border-paper-edge p-10 text-center dark:border-white/10">
        <p class="text-3xl">🔎</p>
        <p class="mt-2 font-bold">{{ __('Nothing on this menu matches.') }}</p>
        <p class="mt-1 text-sm text-ink-faint">{{ __('Try another word, or search every store from the home page.') }}</p>
    </div>
</div>
@endsection
