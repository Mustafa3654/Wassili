/**
 * Wassili (وصّلي) — client-side multi-vendor cart.
 *
 * Everything lives in localStorage until checkout, so there is zero session
 * overhead while the customer browses. Registered as an Alpine.js store named
 * `cart`, plus a `checkout` component that formats the WhatsApp message and
 * mirrors the order to the Laravel backend.
 *
 * Runtime config is injected by the Blade layout on `window.WASSILI`:
 *   { callCenter, baseFee, multiVendorFee, locale, storeUrl, csrf, t: {...} }
 */

const STORAGE_KEY = 'wassili_cart_v1';

export function registerCart(Alpine) {
    Alpine.store('cart', {
        items: [],

        // ---- lifecycle ----
        init() {
            try {
                this.items = JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
            } catch (e) {
                this.items = [];
            }
        },

        save() {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(this.items));
        },

        // ---- mutations ----
        /**
         * Add (or merge) a catalog product.
         * `product` = { id, name, name_ar, price, vendor_id, vendor, is_open }
         */
        add(product) {
            // Guard: never allow items from a closed vendor.
            if (product.vendor_id && product.is_open === false) {
                this.toast(window.WASSILI.t.closed);
                return;
            }

            const key = 'p' + product.id;
            const existing = this.items.find((i) => i.key === key);

            if (existing) {
                existing.quantity++;
            } else {
                this.items.push({
                    key,
                    product_id: product.id,
                    name: product.name,
                    name_ar: product.name_ar || product.name,
                    price: Number(product.price) || 0,
                    quantity: 1,
                    vendor_id: product.vendor_id || null,
                    vendor: product.vendor || null,
                    is_custom: false,
                    note: '',
                });
            }
            this.save();
            this.toast(window.WASSILI.t.added);
        },

        /** Add a free-text custom request for an unlisted product. */
        addCustom(name, note) {
            name = (name || '').trim();
            if (!name) return;

            this.items.push({
                key: 'c' + Date.now(),
                product_id: null,
                name,
                name_ar: name,
                price: 0,          // priced by the call-center after confirmation
                quantity: 1,
                vendor_id: null,   // loose item — picked up from any store
                vendor: null,
                is_custom: true,
                note: (note || '').trim(),
            });
            this.save();
            this.toast(window.WASSILI.t.added);
        },

        increment(key) {
            const item = this.items.find((i) => i.key === key);
            if (item) { item.quantity++; this.save(); }
        },

        decrement(key) {
            const item = this.items.find((i) => i.key === key);
            if (!item) return;
            item.quantity--;
            if (item.quantity <= 0) {
                this.remove(key);
            } else {
                this.save();
            }
        },

        remove(key) {
            this.items = this.items.filter((i) => i.key !== key);
            this.save();
        },

        clear() {
            this.items = [];
            this.save();
        },

        // ---- derived values ----
        get count() {
            return this.items.reduce((sum, i) => sum + i.quantity, 0);
        },

        get subtotal() {
            return this.items.reduce((sum, i) => sum + i.price * i.quantity, 0);
        },

        /** Distinct pickup points => drives the incremental delivery fee. */
        get distinctPickups() {
            const vendorIds = new Set(
                this.items.filter((i) => i.vendor_id).map((i) => i.vendor_id)
            );
            // Any universal/custom item = at least one extra "loose" pickup group.
            const hasLoose = this.items.some((i) => !i.vendor_id);
            return vendorIds.size + (hasLoose ? 1 : 0);
        },

        get deliveryFee() {
            if (this.items.length === 0) return 0;
            const base = Number(window.WASSILI.baseFee) || 0;
            const extra = Number(window.WASSILI.multiVendorFee) || 0;
            const extraGroups = Math.max(0, this.distinctPickups - 1);
            return base + extraGroups * extra;
        },

        get total() {
            return this.subtotal + this.deliveryFee;
        },

        // ---- helpers ----
        displayName(item) {
            return window.WASSILI.locale === 'ar' ? item.name_ar : item.name;
        },

        /** Whether money may be displayed at all (Admin → Settings). */
        get showPrices() {
            return window.WASSILI.showPrices !== false;
        },

        /** How many of a catalog product are in the cart (0 when absent). */
        qtyOf(productId) {
            const item = this.items.find((i) => i.key === 'p' + productId);
            return item ? item.quantity : 0;
        },

        /** Step a catalog product up/down by product id. */
        stepUp(productId) {
            this.increment('p' + productId);
        },
        stepDown(productId) {
            this.decrement('p' + productId);
        },

        /** Dual USD/LBP display, e.g. "$5.00 · 445,000 LL". */
        money(value) {
            const c = window.WASSILI.currency;
            const usd = c.usdSymbol + Number(value).toFixed(2);
            const lbp = Math.round(Number(value) * c.lbpRate).toLocaleString('en-US') + ' ' + c.lbpLabel;
            return usd + ' · ' + lbp;
        },

        toast(message) {
            window.dispatchEvent(new CustomEvent('wassili-toast', { detail: { message } }));
        },
    });

    /**
     * Checkout component: validates the form, builds the WhatsApp text block,
     * POSTs the order to Laravel, then opens WhatsApp and redirects to tracking.
     */
    Alpine.data('checkout', () => ({
        form: { customer_name: '', customer_phone: '', address: '', notes: '' },
        sending: false,
        error: '',

        get cart() {
            return Alpine.store('cart');
        },

        /**
         * WhatsApp order sent to the call centre. Written in Arabic; item and
         * store names print exactly as saved, so English products stay English.
         * Money is omitted entirely when prices are switched off in Settings.
         */
        buildMessage() {
            const c = this.cart;
            const showPrices = window.WASSILI.showPrices;
            const L = [];

            L.push('🛒 *طلب جديد عبر وصّلي*');
            L.push('——————————————');
            L.push('👤 الزبون: ' + this.form.customer_name);
            L.push('📞 الهاتف: +961 ' + this.form.customer_phone);
            L.push('📍 العنوان: ' + this.form.address);
            if (this.form.notes) L.push('📝 ملاحظات: ' + this.form.notes);
            L.push('——————————————');
            L.push('🛒 *الطلبية:*');

            c.items.forEach((i) => {
                let line = `• ${i.quantity}× ${i.name}`;
                if (i.vendor) line += ` (${i.vendor})`;
                else if (i.is_custom) line += ' (طلب خاص)';
                if (i.note) line += ` — ${i.note}`;
                if (showPrices && i.price > 0) line += ` — ${c.money(i.price * i.quantity)}`;
                L.push(line);
            });

            if (showPrices) {
                L.push('——————————————');
                L.push('💵 المجموع: ' + c.money(c.subtotal));
                L.push('🚚 التوصيل: ' + c.money(c.deliveryFee));
                L.push('✅ *الإجمالي: ' + c.money(c.total) + '*');
            }

            return L.join('\n');
        },

        validate() {
            if (this.cart.items.length === 0) { this.error = window.WASSILI.t.empty_cart; return false; }
            if (!this.form.customer_name.trim()) { this.error = window.WASSILI.t.name_required; return false; }
            if (!this.form.customer_phone.trim()) { this.error = window.WASSILI.t.phone_required; return false; }
            if (!this.form.address.trim()) { this.error = window.WASSILI.t.address_required; return false; }
            this.error = '';
            return true;
        },

        async submit() {
            if (this.sending || !this.validate()) return;
            this.sending = true;

            // Open a blank tab NOW, inside the click gesture, so popup blockers
            // don't kill it after the awaited fetch. We fill its URL afterwards.
            const waTab = window.open('', '_blank');

            const message = this.buildMessage();
            const waUrl =
                'https://wa.me/' + window.WASSILI.callCenter + '?text=' + encodeURIComponent(message);

            try {
                const res = await fetch(window.WASSILI.storeUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.WASSILI.csrf,
                        Accept: 'application/json',
                    },
                    body: JSON.stringify({
                        ...this.form,
                        items: this.cart.items,
                        subtotal: this.cart.subtotal,
                        delivery_fee: this.cart.deliveryFee,
                        total_price: this.cart.total,
                    }),
                });

                if (!res.ok) throw new Error('Server error');
                const data = await res.json();

                // Dispatch to the call-center WhatsApp in the pre-opened tab.
                if (waTab) waTab.location = waUrl;
                else window.open(waUrl, '_blank');

                this.cart.clear();

                // Send the customer to their live tracking page.
                window.location = window.WASSILI.trackBase + '/' + data.tracking_number;
            } catch (e) {
                if (waTab) waTab.close();
                this.error = window.WASSILI.t.send_failed;
                this.sending = false;
            }
        },
    }));
}
