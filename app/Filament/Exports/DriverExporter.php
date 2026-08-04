<?php

namespace App\Filament\Exports;

use App\Models\Driver;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class DriverExporter extends Exporter
{
    protected static ?string $model = Driver::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name'),
            ExportColumn::make('phone'),
            ExportColumn::make('vehicle_type'),
            ExportColumn::make('status'),
            ExportColumn::make('is_active'),
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
