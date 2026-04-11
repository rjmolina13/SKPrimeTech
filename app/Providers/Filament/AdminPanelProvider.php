<?php

namespace App\Providers\Filament;

use App\Filament\Auth\Login;
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

use Filament\Support\Facades\FilamentView;
use Filament\Support\Facades\FilamentIcon;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

use Filament\Navigation\MenuItem;
use App\Filament\Pages\EditProfile;
use Filament\Tables\Columns\TextColumn;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('dashboard')
            ->login(Login::class)
            ->brandName('SKPrimeTech')
            ->favicon(asset('favicon.svg'))
            ->userMenuItems([
                'profile' => MenuItem::make()->label('My Profile')
                    ->url(fn (): string => EditProfile::getUrl())
                    ->icon('heroicon-o-user-circle'),
            ])
            ->brandLogo(fn () => view('filament.brand'))
            ->brandLogoHeight('3.3rem')
            // ->viteTheme('resources/css/filament/custom.css')
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString(Blade::render('@vite(["resources/css/filament/custom.css", "resources/js/app.js"])')),
            )
            ->sidebarCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::hex('#1A508E'),
                'danger' => Color::hex('#AF1E21'),
                'warning' => Color::hex('#F4AD1D'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                // AccountWidget::class,
                // FilamentInfoWidget::class,
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

    public function boot(): void
    {
        FileUpload::configureUsing(function (FileUpload $component) {
            $component->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file): string {
                $filename = $file->getClientOriginalName();
                $name = pathinfo($filename, PATHINFO_FILENAME);
                $extension = pathinfo($filename, PATHINFO_EXTENSION);
                return (string) Str::of($name)
                    ->slug()
                    ->append('.', $extension);
            });
        });

        FilamentIcon::register([
            'panels::sidebar.expand-button' => 'heroicon-o-bars-3',
        ]);

        FilamentView::registerRenderHook(
            PanelsRenderHook::FOOTER,
            fn (): HtmlString => new HtmlString(Blade::render('@include("filament.footer")')),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::SCRIPTS_AFTER,
            fn (): HtmlString => new HtmlString(
                '<script data-navigate-once defer src="' . asset('js/filament/admin-print.js') . '"></script>' .
                '',
            ),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::GLOBAL_SEARCH_AFTER,
            fn () => view('filament.navbar-weather-desktop'),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::USER_MENU_PROFILE_AFTER,
            fn () => view('filament.navbar-weather-mobile'),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn (): HtmlString => new HtmlString(Blade::render('@livewire(\App\Livewire\MunicipalityRecordsTable::class)')),
        );

        TextColumn::configureUsing(function (TextColumn $column) {
            if (in_array($column->getName(), ['created_at', 'updated_at'])) {
                $column->dateTime('M j, Y h:i A');
            }
        });
    }
}
