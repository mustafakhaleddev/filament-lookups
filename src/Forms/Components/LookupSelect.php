<?php

namespace Wezlo\FilamentLookups\Forms\Components;

use Closure;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Wezlo\FilamentLookups\Lookup;
use Wezlo\FilamentLookups\Services\LookupService;

class LookupSelect extends Select
{
    protected string|Closure|null $lookupTypeSlug = null;

    protected string|Closure|null $parentFieldName = null;

    protected bool|Closure $hierarchicalDisplay = true;
    protected bool|Closure $onlyParents = false;

    /**
     * Set the lookup type by class or slug.
     *
     * @param  class-string<Lookup>|string|Closure  $type
     */
    public function lookupType(string|Closure $type): static
    {
        if (is_string($type) && is_subclass_of($type, Lookup::class)) {
            $this->lookupTypeSlug = (new $type)->slug();
        } else {
            $this->lookupTypeSlug = $type;
        }

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

    public function onlyParents(bool|Closure $condition = true): static
    {
        $this->onlyParents = $condition;

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

            $onlyParents = $this->evaluate($this->onlyParents);

            if ($parentFieldName) {
                $parentValue = $get($parentFieldName);

                return $service->getOptionsForDependentSelect($slug, $parentValue, $tenantId, $onlyParents);
            }

            return $service->getOptionsForSelect(
                $slug,
                $tenantId,
                $this->evaluate($this->hierarchicalDisplay),
                $onlyParents
            );
        });

        $this->afterStateHydrated(function () {
            if ($this->parentFieldName) {
                $this->live();
            }
        });
    }
}
