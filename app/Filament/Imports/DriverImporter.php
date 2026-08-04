<?php

namespace App\Filament\Imports;

use App\Models\Driver;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class DriverImporter extends Importer
{
    protected static ?string $model = Driver::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('phone')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('vehicle_type')
                ->requiredMapping()
                ->rules(['required', 'in:motorcycle,car,bicycle']),
            ImportColumn::make('status')
                ->rules(['in:available,busy,offline']),
            ImportColumn::make('is_active')
                ->boolean()
                ->rules(['boolean']),
        ];
    }

    public function resolveRecord(): ?Driver
    {
        $phone = $this->data['phone'] ?? null;

        if ($phone) {
            return Driver::where('phone', $phone)->first() ?? new Driver;
        }

        return new Driver;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        return __('wassili.import_completed', [
            'successful' => $import->successful_rows,
            'total'      => $import->total_rows,
        ]);
    }
}
