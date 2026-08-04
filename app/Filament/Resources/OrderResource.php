<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Driver;
use App\Models\Order;
use App\Support\WhatsappFormatter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?int $navigationSort = 0;

    public static function getNavigationLabel(): string
    {
        return __('wassili.orders');
    }

    // Live counter badge of pending orders in the sidebar.
    public static function getNavigationBadge(): ?string
    {
        return (string) Order::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('customer_name')->label(__('wassili.customer_name'))->required(),
            Forms\Components\TextInput::make('customer_phone')
                ->label(__('wassili.customer_phone'))
                ->tel()
                ->required()
                ->prefix(__('wassili.phone_prefix')),
            Forms\Components\Textarea::make('address')->label(__('wassili.address'))->required()->columnSpanFull(),
            Forms\Components\Textarea::make('notes')->label(__('wassili.notes'))->columnSpanFull(),
            Forms\Components\TextInput::make('tracking_number')->disabled()->dehydrated(false),
            Forms\Components\TextInput::make('delivery_fee')->numeric()->prefix('$'),
            Forms\Components\TextInput::make('total_price')->numeric()->prefix('$'),
            Forms\Components\Select::make('status')
                ->options([
                    'pending'     => __('wassili.pending'),
                    'in_progress' => __('wassili.in_progress'),
                    'delivered'   => __('wassili.delivered'),
                    'cancelled'   => __('wassili.cancelled'),
                ])->required(),
            Forms\Components\Select::make('driver_id')
                ->label(__('wassili.driver'))
                ->relationship('driver', 'name')
                ->searchable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // Real-time awareness: auto-refresh every 15 seconds for new orders.
            ->poll('15s')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('tracking_number')
                    ->label(__('wassili.tracking'))
                    ->searchable()->copyable()->weight('bold'),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label(__('wassili.customer_name'))->searchable(),

                Tables\Columns\TextColumn::make('customer_phone')
                    ->label(__('wassili.customer_phone'))
                    ->searchable()
                    ->formatStateUsing(fn ($state) => $state ? '+961 ' . $state : null),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('wassili.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => __("wassili.$state"))
                    ->color(fn (string $state): string => match ($state) {
                        'pending'     => 'warning', // yellow
                        'in_progress' => 'info',    // blue
                        'delivered'   => 'success', // green
                        'cancelled'   => 'danger',  // red
                        default       => 'gray',
                    }),

                Tables\Columns\TextColumn::make('driver.name')
                    ->label(__('wassili.driver'))
                    ->badge()->placeholder(__('wassili.unassigned')),

                Tables\Columns\TextColumn::make('total_price')
                    ->label(__('wassili.total'))
                    ->sortable()
                    ->formatStateUsing(fn ($state) => \App\Support\Money::both((float) $state)),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('wassili.received_at'))
                    ->since()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'pending'     => __('wassili.pending'),
                    'in_progress' => __('wassili.in_progress'),
                    'delivered'   => __('wassili.delivered'),
                    'cancelled'   => __('wassili.cancelled'),
                ]),
            ])
            ->actions([
                // --- Quick inline status transitions ---
                Action::make('markInProgress')
                    ->label(__('wassili.mark_in_progress'))
                    ->icon('heroicon-o-play')
                    ->color('info')
                    ->visible(fn (Order $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(fn (Order $record) => $record->update(['status' => 'in_progress'])),

                Action::make('markDelivered')
                    ->label(__('wassili.mark_delivered'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Order $record) => in_array($record->status, ['pending', 'in_progress'], true))
                    ->requiresConfirmation()
                    ->action(function (Order $record) {
                        $record->update(['status' => 'delivered']);
                        // Free the driver back to the available pool.
                        $record->driver?->update(['status' => 'available']);
                    }),

                // --- Custom "Assign to Driver" dispatch action ---
                Action::make('assignDriver')
                    ->label(__('wassili.assign_driver'))
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->visible(fn (Order $record) => $record->status !== 'delivered' && $record->status !== 'cancelled')
                    ->modalHeading(__('wassili.assign_driver'))
                    ->modalSubmitActionLabel(__('wassili.dispatch_whatsapp'))
                    ->form([
                        Forms\Components\Select::make('driver_id')
                            ->label(__('wassili.select_driver'))
                            ->relationship('driver', 'name', modifyQueryUsing: fn ($query) => $query->where('status', 'available')->where('is_active', true))
                            ->required(),
                    ])
                    ->action(function (Order $record, array $data): void {
                        $driver = Driver::findOrFail($data['driver_id']);

                        // Assign + move order to in_progress, mark driver busy.
                        $record->update([
                            'driver_id' => $driver->id,
                            'status'    => 'in_progress',
                        ]);
                        $driver->update(['status' => 'busy']);

                        $record->refresh()->load('driver');
                        $whatsappUrl = WhatsappFormatter::driverDispatchUrl($record);

                        // Surface a WhatsApp button that opens the pre-filled chat
                        // in a NEW TAB, keeping the admin on the orders screen.
                        Notification::make()
                            ->title(__('wassili.driver_assigned'))
                            ->body(__('wassili.click_to_dispatch', ['name' => $driver->name]))
                            ->success()
                            ->persistent()
                            ->actions([
                                NotificationAction::make('openWhatsapp')
                                    ->label(__('wassili.open_whatsapp'))
                                    ->icon('heroicon-o-chat-bubble-left-right')
                                    ->button()
                                    ->url($whatsappUrl, shouldOpenInNewTab: true),
                            ])
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'edit'  => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
