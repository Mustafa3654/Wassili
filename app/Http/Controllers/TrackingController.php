<?php

namespace App\Http\Controllers;

use App\Models\Order;

class TrackingController extends Controller
{
    /** Public, no-login order tracking by unguessable tracking number. */
    public function show(string $tracking_number)
    {
        $order = Order::with('driver')
            ->where('tracking_number', $tracking_number)
            ->firstOrFail();

        return view('track.show', compact('order'));
    }
}
