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
            ->brandLogo(fn () => view('filament.admin.sidebar-brand-footer'))
            ->brandLogoHeight('auto')
            ->font(config('hse_theme.font_family', 'Inria Sans'), provider: GoogleFontProvider::class)
            ->favicon(fn () => asset(config('hse_theme.brand.favicon_path', 'logo-pestana.png')) . '?v=2')
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
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                // FilamentInfoWidget::class,
                // Widgets específicos desactivados por solicitud del usuario (se usa EventualActivities general)
                // \App\Filament\Resources\Incidents\Widgets\EventualIncidents::class,
                // \App\Filament\Resources\Audits\Widgets\EventualAudits::class,
                // \App\Filament\Resources\Committees\Widgets\EventualCommittees::class,
                // \App\Filament\Resources\Documentations\Widgets\EventualDocuments::class,
                // \App\Filament\Resources\Drills\Widgets\EventualDrills::class,
                // \App\Filament\Resources\Inspections\Widgets\EventualInspections::class,
                // \App\Filament\Resources\OperationalControls\Widgets\EventualOperationalControls::class,
                // \App\Filament\Resources\Promotions\Widgets\EventualPromotions::class,
                // \App\Filament\Resources\Trainings\Widgets\EventualTrainings::class,
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
