<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DriverResource\Pages;
use App\Models\Driver;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use App\Filament\Exports\DriverExporter;
use App\Filament\Imports\DriverImporter;
use Filament\Tables;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Table;

class DriverResource extends Resource
{
    protected static ?string $model = Driver::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return __('wassili.drivers');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label(__('wassili.name'))->required(),

            Forms\Components\TextInput::make('phone')
                ->label(__('wassili.phone'))
                ->tel()
                ->required()
                ->prefix(fn () => \App\Support\Settings::countryCode()),

            Forms\Components\Select::make('vehicle_type')
                ->label(__('wassili.vehicle_type'))
                ->options([
                    'motorcycle' => __('wassili.motorcycle'),
                    'car'        => __('wassili.car'),
                    'bicycle'    => __('wassili.bicycle'),
                ])
                ->default('motorcycle')
                ->required(),

            Forms\Components\Select::make('status')
                ->label(__('wassili.status'))
                ->options([
                    'available' => __('wassili.available'),
                    'busy'      => __('wassili.busy'),
                    'offline'   => __('wassili.offline'),
                ])
                ->default('available')
                ->required()
                ->helperText(__('wassili.driver_status_help')),

            Forms\Components\TextInput::make('delivery_fee')
                ->label(__('wassili.driver_delivery_fee'))
                ->numeric()
                ->minValue(0)
                ->prefix('$')
                ->helperText(__('wassili.driver_delivery_fee_help')),

            Forms\Components\Toggle::make('is_active')
                ->label(__('wassili.active'))->default(true),

            Forms\Components\Section::make(__('wassili.working_hours'))
                ->description(__('wassili.working_hours_help'))
                ->columnSpanFull()
                ->collapsed()
                ->schema(function () {
                    $days = [
                        'monday'    => __('wassili.monday'),
                        'tuesday'   => __('wassili.tuesday'),
                        'wednesday' => __('wassili.wednesday'),
                        'thursday'  => __('wassili.thursday'),
                        'friday'    => __('wassili.friday'),
                        'saturday'  => __('wassili.saturday'),
                        'sunday'    => __('wassili.sunday'),
                    ];

                    return collect($days)->map(fn ($label, $key) =>
                        Forms\Components\Grid::make(4)->schema([
                            Forms\Components\Placeholder::make("{$key}_label")
                                ->hiddenLabel()
                                ->content($label),
                            Forms\Components\Toggle::make("working_hours.{$key}.is_open")
                                ->label(__('wassili.on_duty'))
                                ->default(true)
                                ->inline(false),
                            Forms\Components\TimePicker::make("working_hours.{$key}.open")
                                ->label(__('wassili.shift_start'))
                                ->default('09:00')
                                ->seconds(false),
                            Forms\Components\TimePicker::make("working_hours.{$key}.close")
                                ->label(__('wassili.shift_end'))
                                ->default('22:00')
                                ->seconds(false),
                        ])
                    )->toArray();
                }),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->formatStateUsing(fn ($state) => \App\Support\Settings::formatPhone($state)),
                Tables\Columns\TextColumn::make('vehicle_type')
                    ->label(__('wassili.vehicle_type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => __("wassili.$state"))
                    ->color(fn (string $state) => match ($state) {
                        'motorcycle' => 'info',
                        'car'        => 'primary',
                        'bicycle'    => 'gray',
                    }),
                // Combines the manual status with the shift: someone marked
                // "available" outside their hours is not actually available.
                Tables\Columns\TextColumn::make('availability')
                    ->label(__('wassili.status'))
                    ->badge()
                    ->state(fn (Driver $record) => $record->availability)
                    ->formatStateUsing(fn (string $state, Driver $record) => $state === 'off_shift'
                        ? ($record->shift_starts_at
                            ? __('wassili.starts_at', ['time' => $record->shift_starts_at])
                            : __('wassili.off_shift'))
                        : __("wassili.$state"))
                    ->color(fn (string $state) => match ($state) {
                        'available' => 'success',
                        'busy'      => 'warning',
                        'off_shift' => 'info',
                        default     => 'gray',
                    }),
                Tables\Columns\TextColumn::make('delivery_fee')
                    ->label(__('wassili.driver_delivery_fee'))
                    ->placeholder(__('wassili.uses_base_fee'))
                    ->formatStateUsing(fn ($state) => $state === null
                        ? null
                        : \App\Support\Money::both((float) $state))
                    ->visible(fn () => \App\Support\Settings::showPriceOnMainPage()),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'available' => __('wassili.available'),
                    'busy'      => __('wassili.busy'),
                    'offline'   => __('wassili.offline'),
                ]),
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->headerActions([
                ImportAction::make()->importer(DriverImporter::class),
                ExportAction::make()->exporter(DriverExporter::class),
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
            'index'  => Pages\ListDrivers::route('/'),
            'create' => Pages\CreateDriver::route('/create'),
            'edit'   => Pages\EditDriver::route('/{record}/edit'),
        ];
    }
}
