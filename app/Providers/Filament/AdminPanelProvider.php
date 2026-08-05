<?php

namespace App\Providers\Filament;

use App\Http\Middleware\SetLocale;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Pages\Auth\Login::class)
            // Custom profile page: the stock one edits email, but this app
            // signs in with a username, so it edits username + password.
            ->profile(\App\Filament\Pages\Auth\EditProfile::class, isSimple: false)
            // "Wassili Control Center" branding.
            ->brandName('Wassili — وصّلي')
            ->favicon(asset('favicon.ico'))
            ->colors([
                'primary' => Color::Emerald,
            ])
            // Dark/Light toggle is on by default in the user menu; keep it explicit.
            ->darkMode(true)
            // Arabic/English switcher in the admin user menu. SetLocale (below)
            // persists the choice in the session, so the whole panel — including
            // RTL layout — follows it.
            ->userMenuItems([
                MenuItem::make()
                    ->label(fn () => app()->getLocale() === 'ar' ? 'English' : 'العربية')
                    ->icon('heroicon-o-language')
                    ->url(fn () => request()->fullUrlWithQuery([
                        'lang' => app()->getLocale() === 'ar' ? 'en' : 'ar',
                    ])),
            ])
            ->navigationItems([
                NavigationItem::make('storefront')
                    ->label(fn () => __('wassili.back_to_store'))
                    ->url('/', shouldOpenInNewTab: true)
                    ->icon('heroicon-o-arrow-left')
                    ->sort(-1),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')

            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                // Resolve ar/en (and therefore RTL/LTR) for the admin panel too.
                SetLocale::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
