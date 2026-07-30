<?php

// Central configuration for Reva business rules.
// Values are driven by .env so they can differ per environment / deploy.

return [
    // Call-center WhatsApp number (full international format, digits only).
    'call_center_number' => env('REVA_CALL_CENTER_NUMBER', ''),

    // Delivery fee model (amounts are in the base currency: USD).
    'base_delivery_fee' => (float) env('REVA_BASE_DELIVERY_FEE', 2),

    // Extra fee added for EACH additional distinct vendor beyond the first.
    'multi_vendor_fee' => (float) env('REVA_MULTI_VENDOR_FEE', 1),

    // Currency: prices are stored/entered in USD and displayed alongside LBP,
    // converted with a configurable rate (Lebanon dual-pricing).
    'currency' => [
        'base'       => 'USD',
        'usd_symbol' => '$',
        // Lebanese Pounds per 1 USD. Update via REVA_LBP_RATE when the rate moves.
        'lbp_rate'   => (float) env('REVA_LBP_RATE', 89000),
    ],

    // Supported UI locales.
    'locales' => ['ar', 'en'],

    // Locales that render right-to-left.
    'rtl_locales' => ['ar'],
];
