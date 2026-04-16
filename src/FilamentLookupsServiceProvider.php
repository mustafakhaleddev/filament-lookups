<?php

namespace Wezlo\FilamentLookups;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Wezlo\FilamentLookups\Commands\MakeLookupCommand;
use Wezlo\FilamentLookups\Commands\SyncLookupTypesCommand;
use Wezlo\FilamentLookups\Services\LookupRegistry;
use Wezlo\FilamentLookups\Services\LookupService;

class FilamentLookupsServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-lookups';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasConfigFile()
            ->hasMigrations([
                'create_lookup_types_table',
                'create_lookup_values_table',
            ])
            ->hasTranslations()
            ->hasViews()
            ->hasCommands([
                SyncLookupTypesCommand::class,
                MakeLookupCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(LookupService::class);
        $this->app->singleton(LookupRegistry::class);
    }
}
