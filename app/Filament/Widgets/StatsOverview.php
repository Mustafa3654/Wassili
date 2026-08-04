<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Driver;
use App\Models\Order;
use App\Models\Product;
use App\Models\Vendor;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(__('wassili.vendors'), Vendor::count())
                ->icon('heroicon-o-building-storefront')
                ->color('success'),
            Stat::make(__('wassili.products'), Product::count())
                ->icon('heroicon-o-cube')
                ->color('info'),
            Stat::make(__('wassili.categories'), Category::count())
                ->icon('heroicon-o-tag')
                ->color('warning'),
            Stat::make(__('wassili.drivers'), Driver::count())
                ->icon('heroicon-o-truck')
                ->color('gray'),
            Stat::make(__('wassili.orders'), Order::count())
                ->icon('heroicon-o-shopping-cart')
                ->color('danger'),
        ];
    }
}
