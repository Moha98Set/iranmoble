<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
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
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->brandName('ایران مبله')
            // Mark only, not the full vertical lockup: the header strip is
            // short and wide, so the stacked wordmark would be unreadable.
            ->brandLogo(asset('images/logo-mark.svg'))
            ->brandLogoHeight('2.25rem')
            ->favicon(asset('images/logo-mark.svg'))
            ->login()
            ->colors([
                // Brand orange #F7931D. Filament maps the hue onto its own
                // lightness ramp, so the button shade (600) lands darker than
                // the raw brand colour — which is what keeps white label text
                // readable on it.
                'primary' => Color::hex('#F7931D'),

                // Warm neutrals: cool greys read slightly off next to an
                // orange brand.
                'gray' => Color::Stone,

                // Pulled apart in hue so they do not get mistaken for the
                // orange primary.
                'success' => Color::Emerald,
                'danger' => Color::Red,
                'info' => Color::Sky,
                'warning' => Color::Yellow,
            ])
            ->font(
                'Vazirmatn',
                url: asset('fonts/vazirmatn/vazirmatn.css'),
                provider: \Filament\FontProviders\LocalFontProvider::class,
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
