<?php

namespace App\Providers\Filament;

use App\Models\SensorData;
use App\Models\User;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
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
        return $panel
            ->id('admin')
            ->path('admin')
            ->default()
            ->colors([
                'primary' => '#ff3d00',
            ])
            ->brandName('FlameGuard Admin')
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                AccountWidget::class,
                // FilamentInfoWidget::class,
            ])
            ->navigationGroups([
                NavigationGroup::make('🔥 Monitoring')
                    ->collapsed(false),
                    
                NavigationGroup::make('🚨 Fire & Alerts')
                    ->collapsed(false),
                    
                NavigationGroup::make('📡 Devices')
                    ->collapsed(true),
                    
                NavigationGroup::make('📊 Data & Reports')
                    ->collapsed(true),
                    
                NavigationGroup::make('⚙️ System')
                    ->collapsed(true),
                    
                NavigationGroup::make('👥 Administration')
                    ->collapsed(true),
            ])
            ->navigationItems([
                // 🔥 Monitoring Group
                NavigationItem::make('Dashboard')
                    ->icon('heroicon-o-home')
                    ->url(fn (): string => Dashboard::getUrl())
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.pages.dashboard'))
                    ->sort(1)
                    ->group('🔥 Monitoring'),
                    
                // NavigationItem::make('Live Monitoring')
                //     ->icon('heroicon-o-eye')
                //     ->url('/admin')
                //     ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.pages.dashboard'))
                //     ->sort(2)
                //     ->group('🔥 Monitoring'),

                // 🚨 Fire & Alerts Group
                NavigationItem::make('Active Alerts')
                ->icon('heroicon-o-bell-alert')
                ->url('/admin/active-alerts')
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.pages.active-alerts'))
                ->badge(fn (): string => (string) SensorData::where('created_at', '>=', now()->subHour())
                    ->whereJsonContains('ml_results->fire_detected', true)
                    ->count())
                ->sort(10)
                ->group('🚨 Fire & Alerts'),

                NavigationItem::make('Alert History')
                ->icon('heroicon-o-clock')
                ->url(fn (): string => \App\Filament\Admin\Pages\AlertHistory::getUrl())
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.pages.alert-history'))
                ->sort(11)
                ->group('🚨 Fire & Alerts'),

                // 📡 Devices Group
                NavigationItem::make('ESP32 Devices')
                    ->icon('heroicon-o-cpu-chip')
                    ->url('#')
                    ->badge('1')
                    ->sort(20)
                    ->group('📡 Devices'),

                NavigationItem::make('Sensor Status')
                    ->icon('heroicon-o-beaker')
                    ->url('#')
                    ->sort(21)
                    ->group('📡 Devices'),

                // 📊 Data & Reports Group
                NavigationItem::make('Sensor Data')
                    ->icon('heroicon-o-table-cells')
                    ->url('#')
                    ->badge(fn (): string => (string) SensorData::count())
                    ->sort(30)
                    ->group('📊 Data & Reports'),

                NavigationItem::make('Analytics')
                    ->icon('heroicon-o-chart-pie')
                    ->url('#')
                    ->sort(31)
                    ->group('📊 Data & Reports'),

                // ⚙️ System Group
                NavigationItem::make('ML Settings')
                    ->icon('heroicon-o-cpu-chip')
                    ->url('#')
                    ->sort(40)
                    ->group('⚙️ System'),

                NavigationItem::make('Alert Rules')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->url('#')
                    ->sort(41)
                    ->group('⚙️ System'),

                // 👥 Administration Group
                NavigationItem::make('User Management')
                    ->icon('heroicon-o-users')
                    ->url('#')
                    ->badge(fn (): string => (string) User::count())
                    ->sort(50)
                    ->group('👥 Administration'),

                NavigationItem::make('System Logs')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->url('#')
                    ->sort(51)
                    ->group('👥 Administration'),
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