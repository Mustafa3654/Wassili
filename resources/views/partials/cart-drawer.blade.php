{{-- Slide-over multi-vendor cart + inline checkout. RTL/LTR aware via `end/start`. --}}
<div x-data="{ open: false }"
     @open-cart.window="open = true"
     @keydown.escape.window="open = false"
     x-show="open" x-cloak
     class="fixed inset-0 z-50"
     style="display:none">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-ink/50 backdrop-blur-sm" @click="open = false" x-transition.opacity></div>

    {{-- Panel (anchored to the inline-end edge => right in LTR, left in RTL) --}}
    <div class="absolute inset-y-0 end-0 flex w-full max-w-md flex-col bg-paper shadow-lift dark:bg-zaatar-900"
         role="dialog" aria-modal="true"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="translate-x-full rtl:-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full rtl:-translate-x-full"
         x-data="checkout()">

        {{-- ---------------- Header ---------------- --}}
        <div class="flex items-center justify-between border-b border-paper-edge px-4 py-3.5 dark:border-white/10">
            <h2 class="font-display text-lg font-extrabold">{{ __('Your order') }}</h2>
            <button @click="open = false" aria-label="{{ __('Close') }}"
                    class="grid h-10 w-10 place-items-center rounded-xl text-2xl text-ink-faint transition hover:bg-paper-sunk dark:hover:bg-white/10">&times;</button>
        </div>

        {{-- ---------------- Items ---------------- --}}
        <div class="flex-1 space-y-2.5 overflow-y-auto p-4">

            <template x-if="$store.cart.items.length === 0">
                <div class="py-14 text-center">
                    <p class="text-4xl">🛒</p>
                    <p class="mt-3 font-bold">{{ __('Your cart is empty') }}</p>
                    <p class="mt-1 text-sm text-ink-faint">{{ __('Add items from any store — or request something below.') }}</p>
                </div>
            </template>

            <template x-for="item in $store.cart.items" :key="item.key">
                <div class="rounded-2xl border border-paper-edge bg-white p-3 shadow-card dark:border-white/10 dark:bg-white/5">
                    <div class="flex items-start gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold leading-snug" x-text="$store.cart.displayName(item)"></p>
                            <p x-show="item.vendor" class="truncate text-xs text-ink-faint" x-text="item.vendor"></p>
                            <p x-show="item.is_custom" class="text-xs font-semibold text-tangerine-600">{{ __('Custom request') }}</p>
                            <p x-show="item.note" class="truncate text-xs text-ink-faint" x-text="item.note"></p>
                            <p x-show="item.price > 0 && $store.cart.showPrices" class="mt-1 text-sm font-bold text-zaatar-600 dark:text-zaatar-200"
                               x-text="$store.cart.money(item.price * item.quantity)"></p>
                            <p x-show="item.price === 0 && $store.cart.showPrices" class="mt-1 text-xs text-ink-faint">{{ __('Priced when confirmed') }}</p>
                        </div>

                        {{-- Quantity --}}
                        <div class="flex shrink-0 items-center rounded-xl bg-paper-sunk p-0.5 dark:bg-white/10">
                            <button @click="$store.cart.decrement(item.key)" aria-label="{{ __('Remove one') }}"
                                    class="grid h-9 w-9 place-items-center rounded-lg text-lg font-bold transition hover:bg-white dark:hover:bg-white/10">−</button>
                            <span class="min-w-5 text-center text-sm font-bold tabular-nums" x-text="item.quantity"></span>
                            <button @click="$store.cart.increment(item.key)" aria-label="{{ __('Add one') }}"
                                    class="grid h-9 w-9 place-items-center rounded-lg text-lg font-bold transition hover:bg-white dark:hover:bg-white/10">+</button>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Custom request --}}
            <div x-data="{ n: '', note: '' }"
                 class="rounded-2xl border-2 border-dashed border-paper-edge p-3 dark:border-white/10">
                <p class="mb-2 text-sm font-bold">{{ __('Add a custom request') }}</p>
                <input x-model="n" type="text" placeholder="{{ __('What do you need?') }}"
                       class="mb-2 w-full rounded-xl border border-paper-edge bg-white px-3 py-2.5 text-sm placeholder:text-ink-faint/70 focus:border-zaatar-500 focus:ring-0 dark:border-white/10 dark:bg-white/5">
                <input x-model="note" type="text" placeholder="{{ __('Brand, size, note (optional)') }}"
                       class="mb-2 w-full rounded-xl border border-paper-edge bg-white px-3 py-2.5 text-sm placeholder:text-ink-faint/70 focus:border-zaatar-500 focus:ring-0 dark:border-white/10 dark:bg-white/5">
                <button @click="$store.cart.addCustom(n, note); n=''; note=''"
                        :disabled="!n.trim()"
                        class="w-full rounded-xl bg-tangerine-500 py-2.5 text-sm font-bold text-white transition hover:bg-tangerine-600 active:scale-[.98] disabled:bg-paper-edge disabled:text-ink-faint dark:disabled:bg-white/10">
                    {{ __('Add to order') }}
                </button>
            </div>
        </div>

        {{-- ---------------- Totals + checkout ---------------- --}}
        <div class="border-t border-paper-edge bg-white/60 p-4 pb-[calc(1rem+env(safe-area-inset-bottom))] dark:border-white/10 dark:bg-white/5"
             x-show="$store.cart.items.length > 0">

            <dl class="mb-3 space-y-1.5 text-sm" x-show="$store.cart.showPrices">
                <div class="flex justify-between gap-3">
                    <dt class="text-ink-faint">{{ __('Subtotal') }}</dt>
                    <dd class="font-medium" x-text="$store.cart.money($store.cart.subtotal)"></dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-ink-faint">{{ __('Delivery fee') }}</dt>
                    <dd class="font-medium" x-text="$store.cart.money($store.cart.deliveryFee)"></dd>
                </div>
                <div class="flex justify-between gap-3 border-t border-paper-edge pt-2 font-display text-base font-extrabold dark:border-white/10">
                    <dt>{{ __('Total') }}</dt>
                    <dd x-text="$store.cart.money($store.cart.total)"></dd>
                </div>
            </dl>

            <div class="space-y-2">
                <input x-model="form.customer_name" type="text" autocomplete="name"
                       placeholder="{{ __('Your name') }}" aria-label="{{ __('Your name') }}"
                       class="w-full rounded-xl border border-paper-edge bg-white px-3 py-3 text-sm placeholder:text-ink-faint/70 focus:border-zaatar-500 focus:ring-0 dark:border-white/10 dark:bg-white/5">
                <input x-model="form.customer_phone" type="tel" inputmode="tel" autocomplete="tel"
                       placeholder="{{ __('Phone number') }}" aria-label="{{ __('Phone number') }}"
                       class="w-full rounded-xl border border-paper-edge bg-white px-3 py-3 text-sm placeholder:text-ink-faint/70 focus:border-zaatar-500 focus:ring-0 dark:border-white/10 dark:bg-white/5">
                <textarea x-model="form.address" rows="2" autocomplete="street-address"
                          placeholder="{{ __('Address') }}" aria-label="{{ __('Address') }}"
                          class="w-full rounded-xl border border-paper-edge bg-white px-3 py-3 text-sm placeholder:text-ink-faint/70 focus:border-zaatar-500 focus:ring-0 dark:border-white/10 dark:bg-white/5"></textarea>
                <textarea x-model="form.notes" rows="1"
                          placeholder="{{ __('Notes / location link (optional)') }}" aria-label="{{ __('Notes') }}"
                          class="w-full rounded-xl border border-paper-edge bg-white px-3 py-3 text-sm placeholder:text-ink-faint/70 focus:border-zaatar-500 focus:ring-0 dark:border-white/10 dark:bg-white/5"></textarea>
            </div>

            <p x-show="error" x-text="error" x-cloak
               class="mt-2 rounded-xl bg-tangerine-50 px-3 py-2 text-sm font-medium text-tangerine-600 dark:bg-tangerine-500/15"></p>

            <button @click="submit()" :disabled="sending"
                    class="mt-3 flex w-full items-center justify-center gap-2 rounded-2xl bg-zaatar-600 py-4 font-display text-base font-extrabold text-white shadow-card transition hover:bg-zaatar-700 active:scale-[.98] disabled:opacity-60">
                <span x-show="!sending">{{ __('Send order on WhatsApp') }}</span>
                <span x-show="sending" x-cloak>{{ __('Sending…') }}</span>
            </button>
            <p class="mt-2 text-center text-xs text-ink-faint">{{ __('We confirm every order by WhatsApp before delivery.') }}</p>
        </div>
    </div>
</div>
