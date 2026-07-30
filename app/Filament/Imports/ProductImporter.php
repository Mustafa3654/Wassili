<?php

namespace App\Filament\Imports;

use App\Models\Product;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('name_ar'),
            ImportColumn::make('description'),
            ImportColumn::make('description_ar'),
            ImportColumn::make('price')
                ->numeric()
                ->requiredMapping()
                ->rules(['required', 'numeric', 'min:0']),
            ImportColumn::make('image'),
            ImportColumn::make('is_available')
                ->boolean()
                ->rules(['boolean']),
            ImportColumn::make('category')
                ->relationship('category', 'name'),
            ImportColumn::make('vendor')
                ->relationship('vendor', 'name'),
        ];
    }

    public function resolveRecord(): ?Product
    {
        $name = $this->data['name'] ?? null;

        if ($name) {
            return Product::where('name', $name)->first() ?? new Product;
        }

        return new Product;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        return __('reva.import_completed', [
            'successful' => $import->successful_rows,
            'total'      => $import->total_rows,
        ]);
    }
}
