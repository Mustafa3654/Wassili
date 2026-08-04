@php
    $locale = app()->getLocale();
    $isRtl  = in_array($locale, config('wassili.rtl_locales', ['ar']));
@endphp
<!DOCTYPE html>
<html
    lang="{{ $locale }}"
    dir="{{ $isRtl ? 'rtl' : 'ltr' }}"
    x-data="{ dark: (localStorage.getItem('wassili_theme') || 'light') === 'dark' }"
    x-init="$watch('dark', v => localStorage.setItem('wassili_theme', v ? 'dark' : 'light'))"
    :class="{ 'dark': dark }"
>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1B6B4C">
    <title>@yield('title', 'Wassili — وصّلي')</title>

    {{-- Apply saved theme before paint to avoid a flash of the wrong mode. --}}
    <script>
        if ((localStorage.getItem('wassili_theme') || 'light') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>

    {{-- Runtime config consumed by resources/js/cart.js --}}
    <script>
        window.WASSILI = {
            callCenter: @json(\App\Support\Settings::callCenterNumber()),
            baseFee: @json(\App\Support\Settings::baseDeliveryFee()),
            multiVendorFee: @json(\App\Support\Settings::multiVendorFee()),
            locale: @json($locale),
            currency: {
                lbpRate: @json(\App\Support\Settings::lbpRate()),
                usdSymbol: @json(config('wassili.currency.usd_symbol')),
                lbpLabel: @json(app()->getLocale() === 'ar' ? 'ل.ل' : 'LL'),
            },
            csrf: document.querySelector('meta[name=csrf-token]').content,
            storeUrl: @json(route('orders.store')),
            trackBase: @json(url('/track')),
            t: {
                added: @json(__('Added to cart')),
                closed: @json(__('This store is currently closed')),
                empty_cart: @json(__('Your cart is empty')),
                name_required: @json(__('Please enter your name')),
                phone_required: @json(__('Please enter your phone number')),
                address_required: @json(__('Please enter your address')),
                send_failed: @json(__('Could not send the order. Please try again.')),
            },
        };
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">

    {{-- ============================= NAVBAR ============================= --}}
    <nav class="sticky top-0 z-40 border-b border-paper-edge/70 bg-paper/90 backdrop-blur-md dark:border-white/10 dark:bg-zaatar-900/90">
        <div class="mx-auto flex max-w-5xl items-center gap-3 px-4 py-3">

            <a href="{{ route('storefront.index') }}" class="flex items-center gap-2 font-display text-lg font-extrabold text-zaatar-600 dark:text-zaatar-200">
                <span class="grid h-9 w-9 place-items-center rounded-2xl bg-zaatar-600 text-base text-white shadow-card">🛵</span>
                <span class="leading-none">
                    <span class="block">وصّلي</span>
                    <span class="block text-[10px] font-medium uppercase tracking-[.18em] text-ink-faint">Wassili</span>
                </span>
            </a>

            <div class="ms-auto flex items-center gap-1.5">
                @php $other = $locale === 'ar' ? 'en' : 'ar'; @endphp
                <a href="{{ request()->fullUrlWithQuery(['lang' => $other]) }}"
                   class="rounded-xl px-3 py-2 text-sm font-semibold text-ink-soft transition hover:bg-paper-sunk dark:text-paper/80 dark:hover:bg-white/10">
                    {{ $locale === 'ar' ? 'EN' : 'ع' }}
                </a>

                <button @click="dark = !dark" type="button"
                    class="grid h-10 w-10 place-items-center rounded-xl text-ink-soft transition hover:bg-paper-sunk dark:text-paper/80 dark:hover:bg-white/10"
                    :aria-label="dark ? '{{ __('Switch to light mode') }}' : '{{ __('Switch to dark mode') }}'">
                    <span x-show="!dark">🌙</span>
                    <span x-show="dark" x-cloak>☀️</span>
                </button>

            </div>
        </div>
    </nav>

    {{-- Extra bottom room only while the floating cart is on screen. --}}
    <main class="mx-auto max-w-5xl px-4 pt-5" :class="$store.cart.count > 0 ? 'pb-28' : 'pb-12'">
        @yield('content')
    </main>

    {{-- ================= FLOATING CART BUTTON ================= --}}
    {{-- Sits in the thumb zone, and `end-4` mirrors with the writing direction:
         bottom-right in English, bottom-left in Arabic. --}}
    <button @click="$dispatch('open-cart')" type="button"
            x-show="$store.cart.count > 0" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-3 scale-90"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90"
            aria-label="{{ __('View cart') }}"
            style="bottom: calc(1.25rem + env(safe-area-inset-bottom));"
            class="group fixed end-4 z-40 grid h-16 w-16 place-items-center rounded-full bg-zaatar-600 text-white shadow-lift ring-4 ring-paper transition hover:bg-zaatar-700 active:scale-90 dark:ring-zaatar-900">
        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="9" cy="20" r="1.6" fill="currentColor" stroke="none"/>
            <circle cx="18" cy="20" r="1.6" fill="currentColor" stroke="none"/>
            <path d="M2.5 3.5h2.2l2.4 11.2a1.6 1.6 0 0 0 1.6 1.3h8.9a1.6 1.6 0 0 0 1.6-1.2l1.8-7.3H6"/>
        </svg>

        {{-- Count badge --}}
        <span x-show="$store.cart.count > 0" x-cloak
              x-text="$store.cart.count"
              x-transition.scale
              class="absolute -top-1 -end-1 grid h-6 min-w-6 place-items-center rounded-full bg-tangerine-500 px-1.5 text-xs font-extrabold text-white ring-2 ring-paper dark:ring-zaatar-900"></span>

        {{-- Running total, revealed beside the icon once something is in the cart --}}
        <span x-show="$store.cart.count > 0" x-cloak
              x-text="$store.cart.money($store.cart.subtotal)"
              class="pointer-events-none absolute end-full me-2 hidden whitespace-nowrap rounded-full bg-ink/90 px-3 py-1.5 text-xs font-bold text-paper opacity-0 shadow-card transition group-hover:opacity-100 sm:block dark:bg-paper/90 dark:text-ink"></span>
    </button>

    {{-- ============================= CART DRAWER ============================= --}}
    @include('partials.cart-drawer')

    {{-- ============================= TOASTS ============================= --}}
    <div x-data="{ show: false, message: '' }"
         @wassili-toast.window="message = $event.detail.message; show = true; setTimeout(() => show = false, 2200)"
         x-show="show" x-cloak
         class="pointer-events-none fixed inset-x-0 bottom-24 z-50 mx-auto w-fit max-w-[90vw] rounded-full bg-ink px-5 py-2.5 text-sm font-semibold text-paper shadow-lift sm:bottom-8 dark:bg-paper dark:text-ink"
         x-transition>
        <span x-text="message"></span>
    </div>
</body>
</html>
