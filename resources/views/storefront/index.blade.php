@extends('layouts.app')

@section('title', 'Wassili — وصّلي')

@section('content')
    <header class="mb-6">
        <h1 class="text-2xl font-bold">{{ __('Order anything, delivered') }}</h1>
        <p class="text-gray-500">{{ __('Browse stores or add a custom request — we dispatch it to a driver instantly.') }}</p>
    </header>

    {{-- Everything below reacts to the live search query `q`. --}}
    <div x-data="{ q: '' }">

        {{-- ===================== SEARCH BAR ===================== --}}
        <div class="sticky top-16 z-30 mb-8">
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-4 text-gray-400">🔍</span>
                <input
                    x-model="q"
                    type="search"
                    placeholder="{{ __('Search for products or stores…') }}"
                    class="w-full rounded-2xl border border-gray-200 bg-white py-3 ps-11 pe-10 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800"
                >
                <button
                    x-show="q" x-cloak @click="q = ''"
                    type="button"
                    class="absolute inset-y-0 end-0 flex items-center pe-4 text-gray-400 hover:text-gray-600"
                    aria-label="{{ __('Clear') }}">&times;</button>
            </div>
        </div>

        @php
            // Lowercased searchable text per product: product name (EN/AR),
            // vendor name (EN/AR) and category name (EN/AR) — used for instant
            // client-side filtering.
            $searchable = fn ($p) => \Illuminate\Support\Str::lower(trim(
                $p->name.' '.$p->name_ar.' '
                .optional($p->vendor)->name.' '.optional($p->vendor)->name_ar.' '
                .optional($p->category)->name.' '.optional($p->category)->name_ar
            ));
            $allSearch = $categories->flatMap->products->map($searchable)->values()->all();
        @endphp

        @foreach ($categories as $category)
            @php
                $catSearch = $category->products->map($searchable)->values()->all();
                $limited = $category->products->take(5);
            @endphp

            <section class="mb-10"
                     x-show="!q || {{ Js::from($catSearch) }}.some(s => s.includes(q.toLowerCase().trim()))">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="flex items-center gap-2 text-xl font-bold">
                        <span>{{ $category->icon ?: '🏷️' }}</span>
                        <span>{{ $category->label }}</span>
                    </h2>
                    @if ($category->products->count() > 5)
                        <a href="{{ route('storefront.category', $category) }}"
                           class="text-sm font-medium text-brand-600 hover:underline">
                            {{ __('View all') }} &rarr;
                        </a>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($limited as $product)
                        @php
                            $vendor = $product->vendor;              // may be null (universal)
                            $isOpen = ! $vendor || $vendor->is_open;   // universal items always orderable
                            $orderable = $product->is_available && $isOpen;
                            $search = $searchable($product);
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

                        <div
                            x-show="!q || {{ Js::from($search) }}.includes(q.toLowerCase().trim())"
                            class="flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-800/50 {{ $orderable ? '' : 'opacity-60' }}">
                            @if ($product->image)
                                <img src="{{ asset('storage/'.$product->image) }}" alt="" class="h-32 w-full object-cover">
                            @else
                                <div class="flex h-32 w-full items-center justify-center bg-gray-100 text-3xl dark:bg-gray-700">🛍️</div>
                            @endif

                            <div class="flex flex-1 flex-col p-3">
                                <p class="font-semibold">{{ $product->label }}</p>

                                @if ($vendor)
                                    <p class="text-xs text-gray-500">{{ $vendor->label }}</p>
                                @else
                                    <p class="text-xs text-brand-600">{{ __('Universal Catalog') }}</p>
                                @endif

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

        {{-- ===================== NO RESULTS ===================== --}}
        <div x-show="q && !{{ Js::from($allSearch) }}.some(s => s.includes(q.toLowerCase().trim()))"
             x-cloak
             class="py-16 text-center text-gray-400">
            <p class="text-4xl">🔎</p>
            <p class="mt-2">{{ __('No products match your search.') }}</p>
            <p class="mt-1 text-sm">{{ __('Tip: you can still add it as a custom request in your cart.') }}</p>
        </div>
    </div>
@endsection
