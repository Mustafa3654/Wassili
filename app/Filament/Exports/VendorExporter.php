<?php

namespace App\Filament\Exports;

use App\Models\Vendor;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class VendorExporter extends Exporter
{
    protected static ?string $model = Vendor::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name'),
            ExportColumn::make('name_ar'),
            ExportColumn::make('slug'),
            ExportColumn::make('category_id'),
            ExportColumn::make('phone'),
            ExportColumn::make('address'),
            ExportColumn::make('logo'),
            ExportColumn::make('is_active'),
            ExportColumn::make('opening_hours'),
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
