<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class OrdersChart extends ChartWidget
{
    public function getHeading(): string
    {
        return __('wassili.orders_last_7_days');
    }

    protected function getData(): array
    {
        $from = Carbon::today()->subDays(6);

        // One grouped query instead of seven counts.
        $counts = Order::query()
            ->whereDate('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $values = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->translatedFormat('D');
            $values[] = (int) ($counts[$date->toDateString()] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label'           => __('wassili.orders'),
                    'data'            => $values,
                    'backgroundColor' => 'rgba(27, 107, 76, 0.15)',
                    'borderColor'     => '#1B6B4C',
                    'borderWidth'     => 2,
                    'pointBackgroundColor' => '#1B6B4C',
                    'pointRadius'     => 4,
                    'tension'         => 0.3,
                    'fill'            => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    /**
     * Orders are whole numbers and can never be negative, but Chart.js
     * auto-scales a flat zero series to -1…1 in 0.2 steps. Pin the axis to
     * integers from zero so the empty state reads as "no orders yet".
     */
    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'min'         => 0,
                    // Keeps a readable 0–4 grid before any orders exist, then
                    // grows with the data.
                    'suggestedMax' => 4,
                    'ticks' => [
                        'precision' => 0,
                        'stepSize'  => 1,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'maintainAspectRatio' => false,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
