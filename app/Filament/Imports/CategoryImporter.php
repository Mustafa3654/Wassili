<?php

namespace App\Filament\Imports;

use App\Models\Category;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class CategoryImporter extends Importer
{
    protected static ?string $model = Category::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('name_ar'),
            ImportColumn::make('slug')
                ->requiredMapping()
                ->rules(['required', 'max:255', 'unique:categories,slug']),
            ImportColumn::make('icon'),
            ImportColumn::make('sort_order')
                ->numeric()
                ->rules(['integer', 'min:0']),
            ImportColumn::make('is_active')
                ->boolean()
                ->rules(['boolean']),
        ];
    }

    public function resolveRecord(): ?Category
    {
        $slug = $this->data['slug'] ?? null;

        if ($slug) {
            return Category::where('slug', $slug)->first() ?? new Category;
        }

        return new Category;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        return __('wassili.import_completed', [
            'successful' => $import->successful_rows,
            'total'      => $import->total_rows,
        ]);
    }
}
