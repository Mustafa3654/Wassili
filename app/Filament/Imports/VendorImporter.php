<?php

namespace App\Filament\Imports;

use App\Models\Category;
use App\Models\Vendor;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class VendorImporter extends Importer
{
    protected static ?string $model = Vendor::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('name_ar'),
            ImportColumn::make('slug')
                ->requiredMapping()
                ->rules(['required', 'max:255', 'unique:vendors,slug']),
            ImportColumn::make('category_id')
                ->numeric()
                ->rules(['exists:categories,id']),
            ImportColumn::make('phone'),
            ImportColumn::make('address'),
            ImportColumn::make('logo'),
            ImportColumn::make('is_active')
                ->boolean()
                ->rules(['boolean']),
        ];
    }

    public function resolveRecord(): ?Vendor
    {
        $slug = $this->data['slug'] ?? null;

        if ($slug) {
            return Vendor::where('slug', $slug)->first() ?? new Vendor;
        }

        return new Vendor;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        return __('wassili.import_completed', [
            'successful' => $import->successful_rows,
            'total'      => $import->total_rows,
        ]);
    }
}
