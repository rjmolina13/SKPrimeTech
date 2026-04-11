<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class AboutUs extends Page
{
    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-information-circle';
    }

    public static function getNavigationGroup(): ?string
    {
        return null;
    }

    public static function getNavigationSort(): ?int
    {
        return 999;
    }

    protected string $view = 'filament.pages.about-us';
}
