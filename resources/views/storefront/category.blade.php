@extends('layouts.app')

@section('title', $category->label . ' — Wassili')

@section('content')
    <div class="mb-6">
        <a href="{{ route('storefront.index') }}" class="text-sm text-gray-500 hover:text-brand-600">&larr; {{ __('Back to all categories') }}</a>
    </div>

    <header class="mb-4">
        <h1 class="text-2xl font-bold">{{ $category->icon ?: '🏷️' }} {{ $category->label }}</h1>
        <p class="text-gray-500">{{ $products->count() }} {{ __('products') }}</p>
    </header>

    @php
        $vendors = $products->pluck('vendor')->filter()->unique('id')->sortBy('name');
        $makeSearch = fn ($p) => \Illuminate\Support\Str::lower(trim(
            $p->name.' '.$p->name_ar.' '
            .optional($p->vendor)->name.' '.optional($p->vendor)->name_ar
        ));
    @endphp

    <div x-data="{ q: '', vendor: '' }">
        <div class="sticky top-16 z-30 mb-8 flex flex-col gap-3 sm:flex-row">
            <div class="relative flex-1">
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
            <select
                x-model="vendor"
                class="w-full rounded-2xl border border-gray-200 bg-white py-3 px-4 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:w-56 dark:border-gray-700 dark:bg-gray-800"
            >
                <option value="">{{ __('All vendors') }}</option>
                @foreach ($vendors as $v)
                    <option value="{{ $v->id }}">{{ $v->label }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($products as $product)
                @php
                    $searchText = $makeSearch($product);
                    $vendorId = (string) $product->vendor_id;
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

                <div
                    x-show="(!q || {{ Js::from($searchText) }}.includes(q.toLowerCase().trim())) && (!vendor || vendor === {{ Js::from($vendorId) }})"
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

        <div
            x-show="(q || vendor) && !{{ Js::from($products->map($makeSearch)->values()->all()) }}.some(s => s.includes(q.toLowerCase().trim()))"
            x-cloak
            class="py-16 text-center text-gray-400"
        >
            <p class="text-4xl">🔎</p>
            <p class="mt-2">{{ __('No products match your search.') }}</p>
        </div>
    </div>
@endsection
