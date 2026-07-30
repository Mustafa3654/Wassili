@extends('layouts.app')

@section('title', __('Track your order').' — ' . __('app_name'))

@section('content')
    @php
        $steps = [
            'pending'     => ['label' => __('Order received'), 'icon' => '📝'],
            'in_progress' => ['label' => __('On the way'),     'icon' => '🛵'],
            'delivered'   => ['label' => __('Delivered'),      'icon' => '✅'],
        ];
        $order       = $order;
        $cancelled   = $order->status === 'cancelled';
        $currentIdx  = $order->progressIndex();
    @endphp

    <div class="mx-auto max-w-xl">
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800/50">
            <div class="mb-6 text-center">
                <p class="text-sm text-gray-500">{{ __('Tracking number') }}</p>
                <p class="text-2xl font-bold tracking-wide">{{ $order->tracking_number }}</p>
            </div>

            {{-- Status badge --}}
            @php
                $badge = match ($order->status) {
                    'pending'     => 'bg-yellow-100 text-yellow-800',
                    'in_progress' => 'bg-blue-100 text-blue-800',
                    'delivered'   => 'bg-green-100 text-green-800',
                    'cancelled'   => 'bg-red-100 text-red-800',
                };
            @endphp
            <div class="mb-8 text-center">
                <span class="inline-block rounded-full px-4 py-1.5 text-sm font-semibold {{ $badge }}">
                    {{ __('reva.'.$order->status) }}
                </span>
            </div>

            @if ($cancelled)
                <div class="rounded-xl bg-red-50 p-6 text-center text-red-600 dark:bg-red-900/30">
                    <p class="text-3xl">❌</p>
                    <p class="mt-2 font-semibold">{{ __('This order was cancelled') }}</p>
                </div>
            @else
                {{-- Progress bar --}}
                <ol class="relative flex items-center justify-between">
                    {{-- connecting line --}}
                    <div class="absolute top-5 start-0 end-0 h-1 bg-gray-200 dark:bg-gray-700"></div>
                    <div class="absolute top-5 start-0 h-1 bg-green-500 transition-all"
                         style="width: {{ count($steps) > 1 ? ($currentIdx / (count($steps) - 1)) * 100 : 0 }}%"></div>

                    @foreach ($steps as $key => $step)
                        @php $done = $loop->index <= $currentIdx; @endphp
                        <li class="relative z-10 flex flex-1 flex-col items-center text-center">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full text-lg
                                         {{ $done ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-400 dark:bg-gray-700' }}">
                                {{ $step['icon'] }}
                            </span>
                            <span class="mt-2 text-xs font-medium {{ $done ? 'text-green-600' : 'text-gray-400' }}">
                                {{ $step['label'] }}
                            </span>
                        </li>
                    @endforeach
                </ol>
            @endif

            {{-- Order summary --}}
            <div class="mt-8 space-y-2 border-t border-gray-100 pt-4 text-sm dark:border-gray-800">
                <div class="flex justify-between"><span class="text-gray-500">{{ __('Customer name') }}</span><span>{{ $order->customer_name }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">{{ __('Address') }}</span><span class="text-end">{{ $order->address }}</span></div>
                @if ($order->driver)
                    <div class="flex justify-between"><span class="text-gray-500">{{ __('Driver') }}</span><span>{{ $order->driver->name }}</span></div>
                @endif
                <div class="flex justify-between"><span class="text-gray-500">{{ __('Delivery fee') }}</span><span>{{ \App\Support\Money::both((float) $order->delivery_fee) }}</span></div>
                <div class="flex justify-between font-bold"><span>{{ __('Total') }}</span><span>{{ \App\Support\Money::both((float) $order->total_price) }}</span></div>
            </div>
        </div>

        {{-- Lightweight auto-refresh (no login, no websockets) every 20s. --}}
        <p class="mt-4 text-center text-xs text-gray-400">{{ __('This page refreshes automatically.') }}</p>
    </div>

    @if (! $cancelled && $order->status !== 'delivered')
        <script>setTimeout(() => window.location.reload(), 20000);</script>
    @endif
@endsection
