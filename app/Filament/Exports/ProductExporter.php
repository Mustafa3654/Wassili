<?php

namespace App\Filament\Exports;

use App\Models\Product;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ProductExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name'),
            ExportColumn::make('name_ar'),
            ExportColumn::make('description'),
            ExportColumn::make('description_ar'),
            ExportColumn::make('price'),
            ExportColumn::make('image'),
            ExportColumn::make('category.name'),
            ExportColumn::make('vendor.name'),
            ExportColumn::make('is_available'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return __('wassili.export_completed', [
            'successful' => $export->successful_rows,
            'total'      => $export->total_rows,
        ]);
    }
}
