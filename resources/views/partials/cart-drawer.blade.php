{{-- Slide-over multi-vendor cart + inline checkout. RTL/LTR aware via `end/start`. --}}
<div x-data="{ open: false }"
     @open-cart.window="open = true"
     x-show="open" x-cloak
     class="fixed inset-0 z-50"
     style="display:none">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/40" @click="open = false" x-transition.opacity></div>

    {{-- Panel (anchored to the inline-end edge => right in LTR, left in RTL) --}}
    <div class="absolute inset-y-0 end-0 flex w-full max-w-md flex-col bg-white shadow-xl dark:bg-gray-900"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="translate-x-full rtl:-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-data="checkout()">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-gray-200 p-4 dark:border-gray-800">
            <h2 class="text-lg font-bold">{{ __('Your Cart') }}</h2>
            <button @click="open = false" class="text-2xl leading-none text-gray-400 hover:text-gray-600">&times;</button>
        </div>

        {{-- Items --}}
        <div class="flex-1 space-y-3 overflow-y-auto p-4">
            <template x-if="$store.cart.items.length === 0">
                <p class="py-10 text-center text-gray-400">{{ __('Your cart is empty') }}</p>
            </template>

            <template x-for="item in $store.cart.items" :key="item.key">
                <div class="flex items-start gap-3 rounded-xl border border-gray-100 p-3 dark:border-gray-800">
                    <div class="flex-1">
                        <p class="font-semibold" x-text="$store.cart.displayName(item)"></p>
                        <p x-show="item.vendor" class="text-xs text-gray-500" x-text="item.vendor"></p>
                        <p x-show="item.is_custom" class="text-xs text-amber-600">{{ __('Custom request') }}</p>
                        <p x-show="item.note" class="text-xs text-gray-400" x-text="item.note"></p>
                        <p x-show="item.price > 0" class="mt-1 text-sm text-brand-600"
                           x-text="$store.cart.money(item.price)"></p>
                    </div>

                    {{-- Quantity stepper --}}
                    <div class="flex items-center gap-2">
                        <button @click="$store.cart.decrement(item.key)"
                                class="h-7 w-7 rounded-full bg-gray-100 text-lg leading-none dark:bg-gray-800">−</button>
                        <span class="w-6 text-center" x-text="item.quantity"></span>
                        <button @click="$store.cart.increment(item.key)"
                                class="h-7 w-7 rounded-full bg-gray-100 text-lg leading-none dark:bg-gray-800">+</button>
                    </div>

                    <button @click="$store.cart.remove(item.key)"
                            class="text-gray-300 hover:text-red-500">🗑️</button>
                </div>
            </template>

            {{-- Add a custom / unlisted item --}}
            <div x-data="{ n: '', note: '' }" class="rounded-xl border border-dashed border-gray-300 p-3 dark:border-gray-700">
                <p class="mb-2 text-sm font-semibold">{{ __('Add a custom request') }}</p>
                <input x-model="n" type="text" placeholder="{{ __('Item name') }}"
                       class="mb-2 w-full rounded-lg border-gray-200 bg-transparent text-sm dark:border-gray-700">
                <input x-model="note" type="text" placeholder="{{ __('Note (optional)') }}"
                       class="mb-2 w-full rounded-lg border-gray-200 bg-transparent text-sm dark:border-gray-700">
                <button @click="$store.cart.addCustom(n, note); n=''; note=''"
                        class="w-full rounded-lg bg-gray-900 py-2 text-sm text-white dark:bg-white dark:text-gray-900">
                    {{ __('Add custom item') }}
                </button>
            </div>
        </div>

        {{-- Totals + checkout form --}}
        <div class="border-t border-gray-200 p-4 dark:border-gray-800" x-show="$store.cart.items.length > 0">
            <dl class="mb-3 space-y-1 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">{{ __('Subtotal') }}</dt>
                    <dd x-text="$store.cart.money($store.cart.subtotal)"></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">{{ __('Delivery fee') }}</dt>
                    <dd x-text="$store.cart.money($store.cart.deliveryFee)"></dd>
                </div>
                <div class="flex justify-between text-base font-bold">
                    <dt>{{ __('Total') }}</dt>
                    <dd x-text="$store.cart.money($store.cart.total)"></dd>
                </div>
            </dl>

            <div class="space-y-2">
                <input x-model="form.customer_name" type="text" placeholder="{{ __('Customer name') }}"
                       class="w-full rounded-lg border-gray-200 bg-transparent text-sm dark:border-gray-700">
                <input x-model="form.customer_phone" type="tel" placeholder="{{ __('Phone') }}"
                       class="w-full rounded-lg border-gray-200 bg-transparent text-sm dark:border-gray-700">
                <textarea x-model="form.address" rows="2" placeholder="{{ __('Address') }}"
                          class="w-full rounded-lg border-gray-200 bg-transparent text-sm dark:border-gray-700"></textarea>
                <textarea x-model="form.notes" rows="1" placeholder="{{ __('Notes / GPS location') }}"
                          class="w-full rounded-lg border-gray-200 bg-transparent text-sm dark:border-gray-700"></textarea>
            </div>

            <p x-show="error" x-text="error" x-cloak class="mt-2 text-sm text-red-500"></p>

            <button @click="submit()" :disabled="sending"
                    class="mt-3 flex w-full items-center justify-center gap-2 rounded-xl bg-green-600 py-3 font-semibold text-white hover:bg-green-700 disabled:opacity-60">
                <span x-show="!sending">📲 {{ __('Send Order via WhatsApp') }}</span>
                <span x-show="sending" x-cloak>{{ __('Sending…') }}</span>
            </button>
        </div>
    </div>
</div>
