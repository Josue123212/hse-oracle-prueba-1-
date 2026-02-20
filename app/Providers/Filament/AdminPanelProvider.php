<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\View;
use Filament\Navigation\NavigationGroup;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
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
        $primaryColorName = config('hse_theme.colors.primary', 'Blue');
        $primaryConstantName = ucfirst(strtolower(preg_replace('/[^A-Za-z]/', '', (string) $primaryColorName)));
        $primaryPalette = \defined(Color::class . '::' . $primaryConstantName)
            ? \constant(Color::class . '::' . $primaryConstantName)
            : Color::Blue;

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->font(config('hse_theme.font_family', 'Inria Sans'), provider: GoogleFontProvider::class)
            ->favicon(fn () => asset(config('hse_theme.brand.favicon_path', 'oracle-logo.png')) . '?v=2')
            ->topbar(false)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->collapsibleNavigationGroups()
            ->navigationGroups(
                collect(config('hse_theme.navigation_groups', []))
                    ->map(
                        fn (array $group) => NavigationGroup::make($group['label'] ?? '')
                            ->icon($group['icon'] ?? null),
                    )
                    ->all(),
            )
            ->login()
            ->colors([
                'primary' => $primaryPalette,
            ])
            ->renderHook(
                'panels::sidebar.logo.after',
                fn () => config('hse_theme.sidebar.inject_brand_footer', true)
                    ? view('filament.admin.sidebar-brand-footer')
                    : '',
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
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
