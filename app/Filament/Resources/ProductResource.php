<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use App\Filament\Exports\ProductExporter;
use App\Filament\Imports\ProductImporter;
use Filament\Tables;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('reva.products');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label(__('reva.name_en'))->required(),

            Forms\Components\TextInput::make('name_ar')->label(__('reva.name_ar')),

            Forms\Components\Textarea::make('description')
                ->label(__('reva.description_en'))->rows(2),

            Forms\Components\Textarea::make('description_ar')
                ->label(__('reva.description_ar'))->rows(2),

            Forms\Components\TextInput::make('price')
                ->numeric()->required()->prefix('$')->minValue(0)
                ->helperText(__('reva.price_usd_help')),

            Forms\Components\FileUpload::make('image')
                ->image()->directory('products')->imageEditor(),

            Forms\Components\Select::make('category_id')
                ->label(__('reva.category'))
                ->relationship('category', 'name')
                ->searchable()->preload()->required(),

            // NULL vendor => Universal Catalog item (any nearby store).
            Forms\Components\Select::make('vendor_id')
                ->label(__('reva.vendor'))
                ->relationship('vendor', 'name')
                ->searchable()->preload()->nullable()
                ->helperText(__('reva.vendor_null_help')),

            Forms\Components\Toggle::make('is_available')
                ->label(__('reva.is_available'))->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->square(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category.name')->badge()->label(__('reva.category')),
                Tables\Columns\TextColumn::make('vendor.name')
                    ->label(__('reva.vendor'))
                    ->badge()
                    ->placeholder(__('reva.universal_catalog')),
                Tables\Columns\TextColumn::make('price')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => \App\Support\Money::both((float) $state)),
                Tables\Columns\IconColumn::make('is_available')
                    ->label(__('reva.is_available'))->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('vendor')->relationship('vendor', 'name'),
                Tables\Filters\TernaryFilter::make('is_available'),
            ])
            ->headerActions([
                ImportAction::make()->importer(ProductImporter::class),
                ExportAction::make()->exporter(ProductExporter::class),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
