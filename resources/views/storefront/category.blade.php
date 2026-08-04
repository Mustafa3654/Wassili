@extends('layouts.app')

@section('title', $category->label . ' — Wassili')

@section('content')
    <div class="mb-6">
        <a href="{{ route('storefront.index') }}" class="text-sm text-gray-500 hover:text-brand-600">&larr; {{ __('Back to all stores') }}</a>
    </div>

    <header class="mb-4">
        <h1 class="text-2xl font-bold">{{ $category->icon ?: '🏷️' }} {{ $category->label }}</h1>
        <p class="text-gray-500">{{ $products->total() }} {{ __('products') }}</p>
    </header>

    <form method="GET" action="{{ route('storefront.category', $category) }}" class="sticky top-16 z-30 mb-8 flex flex-col gap-3 sm:flex-row">
        <div class="relative flex-1">
            <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-4 text-gray-400">🔍</span>
            <input
                name="q"
                type="search"
                value="{{ request('q') }}"
                placeholder="{{ __('Search in this category…') }}"
                class="w-full rounded-2xl border border-gray-200 bg-white py-3 ps-11 pe-10 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800"
            >
        </div>
        @if ($vendors->isNotEmpty())
            <select name="vendor" class="w-full rounded-2xl border border-gray-200 bg-white py-3 px-4 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:w-56 dark:border-gray-700 dark:bg-gray-800">
                <option value="">{{ __('All vendors') }}</option>
                @foreach ($vendors as $v)
                    <option value="{{ $v->id }}" @selected(request('vendor') == $v->id)>{{ $v->label }}</option>
                @endforeach
            </select>
        @endif
        <button type="submit" class="rounded-2xl bg-brand-600 px-5 py-3 text-sm font-medium text-white hover:bg-brand-700">
            {{ __('Search') }}
        </button>
    </form>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
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

            <div class="flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-800/50 {{ $orderable ? '' : 'opacity-60' }}">
                @if ($product->image)
                    <img src="{{ asset('storage/'.$product->image) }}" alt="" class="h-32 w-full object-cover">
                @else
                    <div class="flex h-32 w-full items-center justify-center bg-gray-100 text-3xl dark:bg-gray-700">🛍️</div>
                @endif

                <div class="flex flex-1 flex-col p-3">
                    <p class="font-semibold">{{ $product->label }}</p>

                    @if ($vendor)
                        <a href="{{ route('storefront.vendor', $vendor) }}" class="text-xs text-gray-500 hover:text-brand-600">{{ $vendor->label }}</a>
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
        @empty
            <div class="col-span-full py-16 text-center text-gray-400">
                <p class="text-4xl">🔎</p>
                <p class="mt-2">{{ __('No products match your search.') }}</p>
            </div>
        @endforelse
    </div>

    @if ($products->hasPages())
        <div class="mt-8 flex justify-center">
            {{ $products->links() }}
        </div>
    @endif
@endsection
