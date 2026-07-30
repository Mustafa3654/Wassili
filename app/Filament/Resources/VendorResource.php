<?php

namespace App\Filament\Resources;

use App\Filament\Exports\VendorExporter;
use App\Filament\Imports\VendorImporter;
use App\Filament\Resources\VendorResource\Pages;
use App\Models\Vendor;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('reva.vendors');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label(__('reva.name_en'))
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) =>
                    $operation === 'create' ? $set('slug', Str::slug($state)) : null),

            Forms\Components\TextInput::make('name_ar')->label(__('reva.name_ar')),

            Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),

            Forms\Components\Select::make('category_id')
                ->label(__('reva.category'))
                ->relationship('category', 'name')
                ->searchable()
                ->preload(),

            Forms\Components\TextInput::make('phone')
                ->tel()
                ->prefix(__('reva.phone_prefix')),

            Forms\Components\Textarea::make('address')->rows(2)->columnSpanFull(),

            Forms\Components\FileUpload::make('logo')
                ->image()
                ->directory('vendors')
                ->imageEditor(),

            Section::make(__('reva.opening_hours'))
                ->description(__('reva.opening_hours_help'))
                ->schema(function () {
                    $days = [
                        'monday'    => __('reva.monday'),
                        'tuesday'   => __('reva.tuesday'),
                        'wednesday' => __('reva.wednesday'),
                        'thursday'  => __('reva.thursday'),
                        'friday'    => __('reva.friday'),
                        'saturday'  => __('reva.saturday'),
                        'sunday'    => __('reva.sunday'),
                    ];

                    return collect($days)->map(fn ($label, $key) =>
                        Grid::make(4)
                            ->schema([
                                Forms\Components\Placeholder::make("{$key}_label")
                                    ->label($label)
                                    ->hiddenLabel()
                                    ->content($label),
                                Toggle::make("opening_hours.{$key}.is_open")
                                    ->label(__('reva.open'))
                                    ->default(true)
                                    ->inline(false),
                                TimePicker::make("opening_hours.{$key}.open")
                                    ->label(__('reva.open_time'))
                                    ->default('09:00')
                                    ->seconds(false),
                                TimePicker::make("opening_hours.{$key}.close")
                                    ->label(__('reva.close_time'))
                                    ->default('22:00')
                                    ->seconds(false),
                            ])
                            ->columns(4)
                    )->toArray();
                })
                ->columns(1)
                ->columnSpanFull(),

            Forms\Components\Toggle::make('is_active')
                ->label(__('reva.active'))
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')->circular(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category.name')->badge()->label(__('reva.category')),
                Tables\Columns\TextColumn::make('phone')
                    ->formatStateUsing(fn ($state) => $state ? '+961 ' . $state : null),
                Tables\Columns\TextColumn::make('is_open')
                    ->label(__('reva.is_open'))
                    ->badge()
                    ->state(fn ($record) => $record->is_open)
                    ->formatStateUsing(fn ($state) =>
                        $state ? __('reva.open_now') : __('reva.closed_now'))
                    ->color(fn ($state) => $state ? 'success' : 'danger'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->headerActions([
                ImportAction::make()->importer(VendorImporter::class),
                ExportAction::make()->exporter(VendorExporter::class),
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
            'index'  => Pages\ListVendors::route('/'),
            'create' => Pages\CreateVendor::route('/create'),
            'edit'   => Pages\EditVendor::route('/{record}/edit'),
        ];
    }
}
