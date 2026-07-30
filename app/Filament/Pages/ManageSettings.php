<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Support\Settings;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 9;

    protected static string $view = 'filament.pages.manage-settings';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return __('reva.settings');
    }

    public function getTitle(): string
    {
        return __('reva.settings');
    }

    public function mount(): void
    {
        // Pre-fill from stored settings (falling back to config defaults).
        $this->form->fill([
            'lbp_rate'           => Settings::lbpRate(),
            'base_delivery_fee'  => Settings::baseDeliveryFee(),
            'multi_vendor_fee'   => Settings::multiVendorFee(),
            'call_center_number'   => Settings::callCenterNumber(),
            'show_price_on_main_page' => Settings::showPriceOnMainPage(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('reva.currency_section'))
                    ->description(__('reva.currency_section_help'))
                    ->schema([
                        TextInput::make('lbp_rate')
                            ->label(__('reva.lbp_rate'))
                            ->numeric()->required()->minValue(1)
                            ->suffix(__('reva.lbp_per_usd'))
                            ->helperText(__('reva.lbp_rate_help')),
                    ]),

                Section::make(__('reva.delivery_section'))
                    ->schema([
                        TextInput::make('base_delivery_fee')
                            ->label(__('reva.base_delivery_fee'))
                            ->numeric()->required()->minValue(0)->prefix('$'),
                        TextInput::make('multi_vendor_fee')
                            ->label(__('reva.multi_vendor_fee'))
                            ->numeric()->required()->minValue(0)->prefix('$')
                            ->helperText(__('reva.multi_vendor_fee_help')),
                    ])->columns(2),

                Section::make(__('reva.dispatch_section'))
                    ->schema([
                        TextInput::make('call_center_number')
                            ->label(__('reva.call_center_number'))
                            ->tel()
                            ->helperText(__('reva.call_center_help')),
                    ]),

                Section::make(__('reva.display_section'))
                    ->schema([
                        Toggle::make('show_price_on_main_page')
                            ->label(__('reva.show_price_on_main_page'))
                            ->default(true),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        foreach ($this->form->getState() as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? '1' : '0';
            }
            Setting::set($key, (string) $value);
        }

        Notification::make()
            ->title(__('reva.settings_saved'))
            ->success()
            ->send();
    }
}
