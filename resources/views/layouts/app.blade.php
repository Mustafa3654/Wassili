@php
    $locale = app()->getLocale();
    $isRtl  = in_array($locale, config('reva.rtl_locales', ['ar']));
@endphp
<!DOCTYPE html>
<html
    lang="{{ $locale }}"
    dir="{{ $isRtl ? 'rtl' : 'ltr' }}"
    x-data="{ dark: (localStorage.getItem('reva_theme') || 'light') === 'dark' }"
    x-init="$watch('dark', v => localStorage.setItem('reva_theme', v ? 'dark' : 'light'))"
    :class="{ 'dark': dark }"
>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('app_name'))</title>

    {{-- Apply saved theme before paint to avoid a flash of the wrong mode. --}}
    <script>
        if ((localStorage.getItem('reva_theme') || 'light') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>

    {{-- Runtime config consumed by resources/js/cart.js --}}
    <script>
        window.REVA = {
            callCenter: @json(\App\Support\Settings::callCenterNumber()),
            baseFee: @json(\App\Support\Settings::baseDeliveryFee()),
            multiVendorFee: @json(\App\Support\Settings::multiVendorFee()),
            locale: @json($locale),
            currency: {
                lbpRate: @json(\App\Support\Settings::lbpRate()),
                usdSymbol: @json(config('reva.currency.usd_symbol')),
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
<body>
    {{-- ============================= NAVBAR ============================= --}}
    <nav class="sticky top-0 z-40 border-b border-gray-200 bg-white/80 backdrop-blur dark:border-gray-800 dark:bg-gray-900/80">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
            <a href="{{ route('storefront.index') }}" class="flex items-center gap-2 text-xl font-bold text-brand-600 dark:text-brand-500">
                <span>🛵</span>
                <span>{{ app()->getLocale() === 'ar' ? 'ريڤا' : 'Reva' }}</span>
            </a>

            <div class="flex items-center gap-2">
                {{-- Locale switcher --}}
                @php $other = $locale === 'ar' ? 'en' : 'ar'; @endphp
                <a href="{{ request()->fullUrlWithQuery(['lang' => $other]) }}"
                   class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm font-medium hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800">
                    {{ $locale === 'ar' ? 'English' : 'العربية' }}
                </a>

                {{-- Dark / light toggle --}}
                <button @click="dark = !dark" type="button"
                    class="rounded-lg border border-gray-200 p-2 hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800"
                    :aria-label="dark ? 'Light mode' : 'Dark mode'">
                    <span x-show="!dark">🌙</span>
                    <span x-show="dark" x-cloak>☀️</span>
                </button>

                {{-- Cart button --}}
                <button @click="$dispatch('open-cart')" type="button"
                    class="relative rounded-lg bg-brand-600 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                    🛒 {{ __('Cart') }}
                    <span x-show="$store.cart.count > 0"
                          x-text="$store.cart.count"
                          class="absolute -top-2 -end-2 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-xs text-white"></span>
                </button>
            </div>
        </div>
    </nav>

    <main class="mx-auto max-w-6xl px-4 py-6">
        @yield('content')
    </main>

    {{-- ============================= CART DRAWER ============================= --}}
    @include('partials.cart-drawer')

    {{-- ============================= TOASTS ============================= --}}
    <div x-data="{ show: false, message: '' }"
         @reva-toast.window="message = $event.detail.message; show = true; setTimeout(() => show = false, 2000)"
         x-show="show" x-cloak
         class="fixed bottom-5 inset-x-0 z-50 mx-auto w-fit rounded-full bg-gray-900 px-5 py-2 text-sm text-white shadow-lg dark:bg-white dark:text-gray-900"
         x-transition>
        <span x-text="message"></span>
    </div>
</body>
</html>
