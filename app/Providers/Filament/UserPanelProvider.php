<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
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
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Admin\Widgets\SensorStatsOverview;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use App\Filament\User\Widgets\ActiveFireAlertWidget;
use App\Models\SensorData;

class UserPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('user')
            ->path('user')
            ->colors([
                'primary' => '#ff3d00',
            ])
            ->brandName('FlameGuard User')
            ->discoverResources(in: app_path('Filament/User/Resources'), for: 'App\Filament\User\Resources')
            ->discoverPages(in: app_path('Filament/User/Pages'), for: 'App\Filament\User\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/User/Widgets'), for: 'App\Filament\User\Widgets')
            ->widgets([
                // AccountWidget::class,
                // FilamentInfoWidget::class,
                    // ActiveFireAlertWidget::class,   
                    // TemperatureChartWidget::class,
                    // GasChartWidget::class,
                    // HumidityChartWidget::class,
                    SensorStatsOverview::class,
                    ActiveFireAlertWidget::class,
                    
               
            ])

            ->navigationGroups([
                NavigationGroup::make('🔥 Monitoring')
                    ->collapsed(false),
            ])
            ->navigationItems([
                NavigationItem::make('Dashboard')
                    ->icon('heroicon-o-home')
                    ->url(fn (): string => Dashboard::getUrl())
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.user.pages.dashboard'))
                    ->sort(1)
                    ->group('🔥 Monitoring'),

                NavigationItem::make('Active Alerts')
                    ->icon('heroicon-o-bell-alert')
                    ->url('/dashboard/active-alerts')
                    ->badge(fn (): string => (string) SensorData::whereJsonContains('ml_results->fire_detected', true)
                    ->where('created_at', '>=', now()->subHours(24))
                    ->count()
                )
                
                    ->sort(10)
                    ->group('🔥 Monitoring'),
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
            ])
            ->authGuard('web');
    }
}
