<?php

namespace App\Providers\Filament;


use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Awcodes\Overlook\OverlookPlugin;
use Cmsmaxinc\FilamentErrorPages\FilamentErrorPagesPlugin;
use MartinPetricko\FilamentSentryFeedback\FilamentSentryFeedbackPlugin;
use MartinPetricko\FilamentSentryFeedback\Enums\ColorScheme;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use MartinPetricko\FilamentSentryFeedback\Entities\SentryUser;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Awcodes\LightSwitch\LightSwitchPlugin;
use JeffersonGoncalves\Filament\WhatsappWidget\WhatsappWidgetPlugin;
use Moataz01\FilamentNotificationSound\FilamentNotificationSoundPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        // Incluir Driver.js para tours de onboarding
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn(): string => '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.css"/>',
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn(): string => '<script src="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.js.iife.js"></script><script src="' . asset('js/tour-afiliaciones.js') . '"></script><script src="' . asset('js/chart-export.js') . '"></script>',
        );

        // html2canvas: para el botón "Tomar pantallazo" del comprobante de Plan de Adquisición.
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn(): string => <<<'HTML'
            <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
            <script>
            window.tomarPantallazoPlan = async function () {
                const target = document.querySelector('#comprobante-plan') || document.querySelector('.fi-in') || document.querySelector('.fi-page-content');
                if (!target) { alert('No se encontró el contenido a capturar.'); return; }
                try {
                    const isDark = document.documentElement.classList.contains('dark');
                    const canvas = await html2canvas(target, { scale: 2, backgroundColor: isDark ? '#18181b' : '#ffffff', useCORS: true, logging: false });
                    const link = document.createElement('a');
                    link.download = 'plan-adquisicion-' + Date.now() + '.png';
                    link.href = canvas.toDataURL('image/png');
                    link.click();
                } catch (e) {
                    console.error('html2canvas', e);
                    alert('No se pudo generar el pantallazo: ' + (e && e.message ? e.message : e));
                }
            };
            </script>
            HTML,
        );
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->passwordReset()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
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
            ->plugins([
                FilamentShieldPlugin::make(),
                // OverlookPlugin desactivado: su rejilla auto-generada (una tarjeta
                // por recurso, con sparklines) se montaba sobre los números y se
                // veía rota. El Escritorio usa widgets propios más limpios.
                FilamentErrorPagesPlugin::make(),
                // LightSwitchPlugin::make(),
                FilamentSentryFeedbackPlugin::make()
                    // ->sentryUser(function (): ?SentryUser {
                    //     return new SentryUser(auth()->user()->name, auth()->user()->email);
                    // }),
                    ->colorScheme(ColorScheme::Auto)
                    ->showBranding(false)
                    ->showName(true)
                    ->showEmail(true)
                    ->isEmailRequired(true)
                    ->isNameRequired(true)
                    ->enableScreenshot(true),
                WhatsappWidgetPlugin::make(),
                FilamentNotificationSoundPlugin::make()
                    ->volume(1.0)
                    ->showAnimation(true)
                    ->enabled(true),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
