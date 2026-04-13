<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
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
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Pixelworxio\FilamentAiAction\FilamentAiActionPlugin;
use lockscreen\FilamentLockscreen\Lockscreen;

class AdminPanelProvider extends PanelProvider
{

    public function panel(Panel $panel): Panel
    {
        return $panel
            
            ->default()
            ->id('admin')
            ->path('admin')
            ->sidebarCollapsibleOnDesktop()
            ->login(Login::class)

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])

            ->widgets([
                FilamentInfoWidget::class,
            ])
            ->plugins([
                FilamentAiActionPlugin::make(),
                Lockscreen::make(),
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
                \App\Http\Middleware\LogAccountingSession::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            
            ->renderHook(
                PanelsRenderHook::PAGE_HEADER_ACTIONS_BEFORE,
                fn(): string => Blade::render('
                    <x-filament::button
                        color="gray"
                        icon="heroicon-m-arrow-uturn-left"
                        onclick="history.back()"
                        tag="button"
                        size="sm"
                        outline
                    >
                        {{ __("Back") }}
                    </x-filament::button>
                '),
            )

            ->renderHook(
                PanelsRenderHook::TOPBAR_START,
                fn(): string => Blade::render('
                    <div class="flex items-center gap-2 mb-4 overflow-x-auto no-scrollbar pb-1">
                        <x-filament::badge color="success" icon="heroicon-m-document-text" href="/admin/simple-entries" tag="a" class="shadow-sm border border-success-200">
                            قيد بسيط
                        </x-filament::badge>
                        <x-filament::badge color="info" icon="heroicon-m-document-duplicate" href="/admin/sell-currencies" tag="a" class="shadow-sm border border-info-200">
                           بيع عملة
                        </x-filament::badge>
                        <x-filament::badge color="warning" icon="heroicon-m-currency-dollar" href="/admin/buy-currencies" tag="a" class="shadow-sm border border-warning-200">
                            شراء عملة
                        </x-filament::badge>
                    </div>
                ')
            );
    }
}
