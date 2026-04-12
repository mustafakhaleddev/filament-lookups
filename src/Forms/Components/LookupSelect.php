<?php

namespace Wezlo\FilamentLookups\Forms\Components;

use Closure;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Wezlo\FilamentLookups\Services\LookupService;

class LookupSelect extends Select
{
    protected string|Closure|null $lookupTypeSlug = null;

    protected string|Closure|null $parentFieldName = null;

    protected bool|Closure $hierarchicalDisplay = true;

    public function lookupType(string|Closure $slug): static
    {
        $this->lookupTypeSlug = $slug;

        return $this;
    }

    public function dependsOn(string|Closure $parentFieldName): static
    {
        $this->parentFieldName = $parentFieldName;

        return $this;
    }

    public function hierarchical(bool|Closure $condition = true): static
    {
        $this->hierarchicalDisplay = $condition;

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->searchable();

        $this->options(function (Get $get): array {
            $slug = $this->evaluate($this->lookupTypeSlug);

            if (! $slug) {
                return [];
            }

            $service = app(LookupService::class);
            $tenantId = $service->resolveTenantId();

            $parentFieldName = $this->evaluate($this->parentFieldName);

            if ($parentFieldName) {
                $parentValue = $get($parentFieldName);

                return $service->getOptionsForDependentSelect($slug, $parentValue, $tenantId);
            }

            return $service->getOptionsForSelect(
                $slug,
                $tenantId,
                $this->evaluate($this->hierarchicalDisplay),
            );
        });

        $this->afterStateHydrated(function () {
            if ($this->parentFieldName) {
                $this->live();
            }
        });
    }
}
