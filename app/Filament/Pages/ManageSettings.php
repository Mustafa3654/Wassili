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
        return __('wassili.settings');
    }

    public function getTitle(): string
    {
        return __('wassili.settings');
    }

    public function mount(): void
    {
        // Pre-fill from stored settings (falling back to config defaults).
        $this->form->fill([
            'lbp_rate'           => Settings::lbpRate(),
            'base_delivery_fee'  => Settings::baseDeliveryFee(),
            'multi_vendor_fee'   => Settings::multiVendorFee(),
            'call_center_number'   => Settings::callCenterNumber(),
            'country_code'         => Settings::countryCode(),
            'show_price_on_main_page' => Settings::showPriceOnMainPage(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('wassili.currency_section'))
                    ->description(__('wassili.currency_section_help'))
                    ->schema([
                        TextInput::make('lbp_rate')
                            ->label(__('wassili.lbp_rate'))
                            ->numeric()->required()->minValue(1)
                            ->suffix(__('wassili.lbp_per_usd'))
                            ->helperText(__('wassili.lbp_rate_help')),
                    ]),

                Section::make(__('wassili.delivery_section'))
                    ->schema([
                        TextInput::make('base_delivery_fee')
                            ->label(__('wassili.base_delivery_fee'))
                            ->numeric()->required()->minValue(0)->prefix('$'),
                        TextInput::make('multi_vendor_fee')
                            ->label(__('wassili.multi_vendor_fee'))
                            ->numeric()->required()->minValue(0)->prefix('$')
                            ->helperText(__('wassili.multi_vendor_fee_help')),
                    ])->columns(2),

                Section::make(__('wassili.dispatch_section'))
                    ->schema([
                        TextInput::make('call_center_number')
                            ->label(__('wassili.call_center_number'))
                            ->tel()
                            ->helperText(__('wassili.call_center_help')),

                        TextInput::make('country_code')
                            ->label(__('wassili.country_code'))
                            ->required()
                            ->placeholder('+961')
                            ->helperText(__('wassili.country_code_help')),
                    ]),

                Section::make(__('wassili.display_section'))
                    ->schema([
                        Toggle::make('show_price_on_main_page')
                            ->label(__('wassili.show_price_on_main_page'))
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
            ->title(__('wassili.settings_saved'))
            ->success()
            ->send();
    }
}
