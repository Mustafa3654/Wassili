<?php

return [
    // Navigation / resources
    'categories' => 'Categories',
    'vendors'    => 'Vendors',
    'products'   => 'Products',
    'drivers'    => 'Drivers',
    'orders'     => 'Orders',

    // Shared fields
    'name'            => 'Name',
    'username'        => 'Username',
    'name_en'         => 'Name (English)',
    'name_ar'         => 'Name (Arabic)',
    'description_en'  => 'Description (English)',
    'description_ar'  => 'Description (Arabic)',
    'category'        => 'Category',
    'parent_category' => 'Parent category',
    'icon'            => 'Icon',
    'active'          => 'Active',
    'phone'           => 'Phone',

    // Category / vendor / product
    'leave_empty_top_level' => 'Leave empty for a top-level category.',
    'is_open'               => 'Status',
    'is_open_help'          => 'When off, customers cannot add this vendor\'s items to the cart.',
    'opening_hours'         => 'Opening Hours',
    'opening_hours_help'    => 'Set the regular opening hours for each day of the week.',
    'open'                  => 'Open',
    'open_time'             => 'Opens at',
    'close_time'            => 'Closes at',
    'open_now'              => 'Open now',
    'closed_now'            => 'Closed',
    'monday'                => 'Monday',
    'tuesday'               => 'Tuesday',
    'wednesday'             => 'Wednesday',
    'thursday'              => 'Thursday',
    'friday'                => 'Friday',
    'saturday'              => 'Saturday',
    'sunday'                => 'Sunday',
    'vendor'                => 'Vendor',
    'vendor_null_help'      => 'Leave empty for a Universal Catalog item (any nearby store).',
    'is_available'          => 'Available',
    'universal_catalog'     => 'Universal Catalog',
    'price_usd_help'        => 'Enter the price in USD. LBP is shown automatically using the configured rate.',

    // Driver
    'phone_intl_help' => 'International format, digits only (e.g. 9665XXXXXXXX).',
    'vehicle_type'    => 'Vehicle',
    'motorcycle'      => 'Motorcycle',
    'car'             => 'Car',
    'bicycle'         => 'Bicycle',
    'status'          => 'Status',
    'available'       => 'Available',
    'busy'            => 'Busy',
    'offline'         => 'Offline',

    // Orders
    'customer_name'    => 'Customer name',
    'customer_phone'   => 'Customer phone',
    'address'          => 'Address',
    'notes'            => 'Notes',
    'pending'          => 'Pending',
    'in_progress'      => 'In progress',
    'delivered'        => 'Delivered',
    'cancelled'        => 'Cancelled',
    'driver'           => 'Driver',
    'tracking'         => 'Tracking #',
    'unassigned'       => 'Unassigned',
    'total'            => 'Total',
    'received_at'      => 'Received',

    // Order actions
    'mark_in_progress'      => 'Start',
    'mark_delivered'        => 'Delivered',
    'assign_driver'         => 'Assign to Driver',
    'dispatch_whatsapp'     => 'Dispatch via WhatsApp',
    'select_driver'         => 'Select a driver',
    'only_available_drivers'=> 'Only active & available drivers are shown.',
    'driver_assigned'       => 'Driver assigned',
    'click_to_dispatch'     => 'Order assigned to :name. Click below to send the details on WhatsApp.',
    'open_whatsapp'         => 'Open WhatsApp',

    // Settings page
    'settings'               => 'Settings',
    'save'                   => 'Save changes',
    'settings_saved'         => 'Settings saved successfully.',
    'currency_section'       => 'Currency',
    'currency_section_help'  => 'Prices are entered in USD; the LBP amount is shown automatically using this rate.',
    'lbp_rate'               => 'USD → LBP exchange rate',
    'lbp_per_usd'            => 'LBP per 1 USD',
    'lbp_rate_help'          => 'Example: 89000 means $1 = 89,000 LL.',
    'delivery_section'       => 'Delivery fees (USD)',
    'base_delivery_fee'      => 'Base delivery fee',
    'multi_vendor_fee'       => 'Extra fee per additional vendor',
    'multi_vendor_fee_help'  => 'Added for each distinct vendor/pickup beyond the first.',
    'dispatch_section'       => 'Dispatch',
    'call_center_number'     => 'Call-center WhatsApp number',
    'call_center_help'       => 'Full international format, digits only (e.g. 9611234567). Customer orders are sent here.',

    // Display
    'display_section'        => 'Display',
    'show_price_on_main_page' => 'Show prices on the main page',

    // Phone
    'phone_prefix' => '+961',

    // Import / Export
    'import_completed' => 'Import completed with :successful out of :total rows imported successfully.',
    'export_completed' => 'Your export of :total rows has completed with :successful successful rows.',
];
