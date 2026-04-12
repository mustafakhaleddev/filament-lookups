<?php

namespace Wezlo\FilamentLookups;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Wezlo\FilamentLookups\Pages\ManageLookups;

class FilamentLookupsPlugin implements Plugin
{
    protected bool $hasResource = true;

    protected ?bool $tenancyEnabled = null;

    protected ?string $tenantModel = null;

    protected ?string $navigationGroup = null;

    protected ?string $navigationIcon = null;

    protected ?int $navigationSort = null;

    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'filament-lookups';
    }

    public function registerResource(bool $enabled = true): static
    {
        $this->hasResource = $enabled;

        return $this;
    }

    public function tenancy(bool $enabled = true): static
    {
        $this->tenancyEnabled = $enabled;

        return $this;
    }

    public function tenantModel(string $model): static
    {
        $this->tenantModel = $model;

        return $this;
    }

    public function navigationGroup(?string $group): static
    {
        $this->navigationGroup = $group;

        return $this;
    }

    public function navigationIcon(?string $icon): static
    {
        $this->navigationIcon = $icon;

        return $this;
    }

    public function navigationSort(?int $sort): static
    {
        $this->navigationSort = $sort;

        return $this;
    }

    public function getNavigationGroup(): ?string
    {
        return $this->navigationGroup ?? config('filament-lookups.navigation_group', 'Settings');
    }

    public function getNavigationIcon(): ?string
    {
        return $this->navigationIcon ?? config('filament-lookups.navigation_icon');
    }

    public function getNavigationSort(): ?int
    {
        return $this->navigationSort ?? config('filament-lookups.navigation_sort');
    }

    public function isTenancyEnabled(): bool
    {
        return $this->tenancyEnabled ?? (bool) config('filament-lookups.tenancy.enabled', false);
    }

    public function getTenantModel(): ?string
    {
        return $this->tenantModel ?? config('filament-lookups.tenancy.tenant_model');
    }

    public function register(Panel $panel): void
    {
        if ($this->hasResource) {
            $panel->pages([
                ManageLookups::class,
            ]);
        }
    }

    public function boot(Panel $panel): void {}

    /**
     * Get the current plugin instance from the active Filament panel.
     */
    public static function current(): ?static
    {
        try {
            return filament()->getCurrentOrDefaultPanel()->getPlugin('filament-lookups');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Resolve the navigation group, preferring plugin override then config.
     */
    public static function resolveNavigationGroup(): ?string
    {
        return static::current()?->getNavigationGroup()
            ?? config('filament-lookups.navigation_group', 'Settings');
    }

    /**
     * Resolve the navigation icon, preferring plugin override then config.
     */
    public static function resolveNavigationIcon(): ?string
    {
        return static::current()?->getNavigationIcon()
            ?? config('filament-lookups.navigation_icon');
    }

    /**
     * Resolve the navigation sort, preferring plugin override then config.
     */
    public static function resolveNavigationSort(): ?int
    {
        return static::current()?->getNavigationSort()
            ?? config('filament-lookups.navigation_sort');
    }

    /**
     * Resolve whether tenancy is enabled.
     */
    public static function resolveTenancyEnabled(): bool
    {
        return static::current()?->isTenancyEnabled()
            ?? (bool) config('filament-lookups.tenancy.enabled', false);
    }
}
