<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuSectionResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Sub-categories: the menu sections inside a category (Breakfast, Grills,
 * Shawarma …). Same Category model as {@see CategoryResource}, scoped to rows
 * that have a parent, so each screen stays readable on its own.
 *
 * Sections are shared across vendors — "Grills" is one row no matter how many
 * restaurants serve grills — and never appear on the storefront home page,
 * which lists top-level categories only.
 */
class MenuSectionResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?int $navigationSort = 2;

    // Distinct route prefix so it doesn't collide with CategoryResource.
    protected static ?string $slug = 'menu-sections';

    public static function getNavigationLabel(): string
    {
        return __('wassili.sub_categories');
    }

    public static function getModelLabel(): string
    {
        return __('wassili.sub_category');
    }

    public static function getPluralModelLabel(): string
    {
        return __('wassili.sub_categories');
    }

    /** Only categories that sit under a parent. */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereNotNull('parent_id');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('parent_id')
                ->label(__('wassili.parent_category'))
                ->relationship(
                    'parent',
                    'name',
                    // Only top-level categories can be a parent; nesting a
                    // section under another section would hide it everywhere.
                    fn (Builder $query) => $query->whereNull('parent_id')
                )
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\TextInput::make('name')
                ->label(__('wassili.name_en'))
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) =>
                    $operation === 'create' ? $set('slug', Str::slug($state)) : null),

            Forms\Components\TextInput::make('name_ar')
                ->label(__('wassili.name_ar')),

            Forms\Components\TextInput::make('slug')
                ->required()
                ->unique(ignoreRecord: true),

            Forms\Components\TextInput::make('icon')
                ->label(__('wassili.icon'))
                ->helperText(__('wassili.icon_help'))
                ->placeholder('🍳'),

            Forms\Components\TextInput::make('sort_order')
                ->label(__('wassili.sort_order'))
                ->numeric()
                ->default(0),

            Forms\Components\Toggle::make('is_active')
                ->label(__('wassili.active'))
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('icon')->label(''),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name_ar')->label(__('wassili.name_ar'))->searchable(),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label(__('wassili.parent_category'))
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('products_count')
                    ->label(__('wassili.products'))
                    ->counts('products')
                    ->badge()
                    ->color('success'),
                Tables\Columns\IconColumn::make('is_active')->label(__('wassili.active'))->boolean(),
            ])
            ->defaultSort('name')
            ->filters([
                // Plain options rather than ->relationship(): there are only a
                // handful of top-level categories, and this filters on the
                // column directly without Filament resolving a relation.
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label(__('wassili.parent_category'))
                    ->options(fn () => Category::query()
                        ->whereNull('parent_id')
                        ->orderBy('name')
                        ->pluck('name', 'id')),
                Tables\Filters\TernaryFilter::make('is_active'),
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
            'index'  => Pages\ListMenuSections::route('/'),
            'create' => Pages\CreateMenuSection::route('/create'),
            'edit'   => Pages\EditMenuSection::route('/{record}/edit'),
        ];
    }
}
