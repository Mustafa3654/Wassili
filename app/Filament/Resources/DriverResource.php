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
        return __('reva.drivers');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label(__('reva.name'))->required(),

            Forms\Components\TextInput::make('phone')
                ->label(__('reva.phone'))
                ->tel()
                ->required()
                ->prefix(__('reva.phone_prefix')),

            Forms\Components\Select::make('vehicle_type')
                ->label(__('reva.vehicle_type'))
                ->options([
                    'motorcycle' => __('reva.motorcycle'),
                    'car'        => __('reva.car'),
                    'bicycle'    => __('reva.bicycle'),
                ])
                ->default('motorcycle')
                ->required(),

            Forms\Components\Select::make('status')
                ->label(__('reva.status'))
                ->options([
                    'available' => __('reva.available'),
                    'busy'      => __('reva.busy'),
                    'offline'   => __('reva.offline'),
                ])
                ->default('available')
                ->required(),

            Forms\Components\Toggle::make('is_active')
                ->label(__('reva.active'))->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->formatStateUsing(fn ($state) => $state ? '+961 ' . $state : null),
                Tables\Columns\TextColumn::make('vehicle_type')
                    ->label(__('reva.vehicle_type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => __("reva.$state"))
                    ->color(fn (string $state) => match ($state) {
                        'motorcycle' => 'info',
                        'car'        => 'primary',
                        'bicycle'    => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('reva.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => __("reva.$state"))
                    ->color(fn (string $state) => match ($state) {
                        'available' => 'success',
                        'busy'      => 'warning',
                        'offline'   => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'available' => __('reva.available'),
                    'busy'      => __('reva.busy'),
                    'offline'   => __('reva.offline'),
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
