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
            Stat::make(__('reva.vendors'), Vendor::count())
                ->icon('heroicon-o-building-storefront')
                ->color('success'),
            Stat::make(__('reva.products'), Product::count())
                ->icon('heroicon-o-cube')
                ->color('info'),
            Stat::make(__('reva.categories'), Category::count())
                ->icon('heroicon-o-tag')
                ->color('warning'),
            Stat::make(__('reva.drivers'), Driver::count())
                ->icon('heroicon-o-truck')
                ->color('gray'),
            Stat::make(__('reva.orders'), Order::count())
                ->icon('heroicon-o-shopping-cart')
                ->color('danger'),
        ];
    }
}
