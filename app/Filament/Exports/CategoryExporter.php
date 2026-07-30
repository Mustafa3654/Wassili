<?php

namespace App\Filament\Exports;

use App\Models\Category;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class CategoryExporter extends Exporter
{
    protected static ?string $model = Category::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name'),
            ExportColumn::make('name_ar'),
            ExportColumn::make('slug'),
            ExportColumn::make('icon'),
            ExportColumn::make('sort_order'),
            ExportColumn::make('is_active'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return __('reva.export_completed', [
            'successful' => $export->successful_rows,
            'total'      => $export->total_rows,
        ]);
    }
}
