<?php

namespace Wezlo\FilamentLookups\Services;

use Illuminate\Support\Collection;
use Wezlo\FilamentLookups\Enums\TenancyMode;
use Wezlo\FilamentLookups\Models\LookupType;
use Wezlo\FilamentLookups\Models\LookupValue;

class LookupService
{
    /**
     * Get all active values for a lookup type, respecting tenancy.
     */
    public function getValuesForType(string $slugOrId, ?string $tenantId = null): Collection
    {
        $type = $this->resolveType($slugOrId);

        if (! $type) {
            return collect();
        }

        $query = $type->activeValues()->orderBy('sort_order')->orderBy('name');

        return $this->applyTenancyScope($query, $type, $tenantId)->get();
    }

    /**
     * Get only root-level active values for a lookup type.
     */
    public function getRootValues(string $slugOrId, ?string $tenantId = null): Collection
    {
        $type = $this->resolveType($slugOrId);

        if (! $type) {
            return collect();
        }

        $query = $type->activeValues()->whereNull('parent_id')->orderBy('sort_order')->orderBy('name');

        return $this->applyTenancyScope($query, $type, $tenantId)->get();
    }

    /**
     * Get active children of a specific lookup value.
     */
    public function getChildValues(string $parentId): Collection
    {
        return LookupValue::where('parent_id', $parentId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get formatted options for use in a Select field.
     *
     * @return array<string, string>
     */
    public function getOptionsForSelect(string $slugOrId, ?string $tenantId = null, bool $hierarchical = true): array
    {
        $type = $this->resolveType($slugOrId);

        if (! $type) {
            return [];
        }

        $tenantId = $tenantId ?? $this->resolveTenantId();

        if ($hierarchical && $type->is_hierarchical) {
            $rootValues = $type->activeValues()
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->orderBy('name');

            $rootValues = $this->applyTenancyScope($rootValues, $type, $tenantId)->get();

            return $this->buildHierarchicalOptions($rootValues);
        }

        $values = $type->activeValues()->orderBy('sort_order')->orderBy('name');
        $values = $this->applyTenancyScope($values, $type, $tenantId)->get();

        return $values->pluck('name', 'id')->all();
    }

    /**
     * Get options for a dependent select, filtered by parent value ID.
     *
     * @return array<string, string>
     */
    public function getOptionsForDependentSelect(string $slugOrId, ?string $parentValueId, ?string $tenantId = null): array
    {
        if (! $parentValueId) {
            return [];
        }

        $type = $this->resolveType($slugOrId);

        if (! $type) {
            return [];
        }

        $tenantId = $tenantId ?? $this->resolveTenantId();

        $query = LookupValue::where('lookup_type_id', $type->id)
            ->where('parent_id', $parentValueId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($this->isTenancyEnabled() && $tenantId) {
            $column = config('filament-lookups.tenancy.tenant_id_column', 'tenant_id');
            $query->where(function ($q) use ($column, $tenantId) {
                $q->whereNull($column)->orWhere($column, $tenantId);
            });
        }

        return $query->pluck('name', 'id')->all();
    }

    /**
     * Resolve the current tenant ID from the Filament panel context.
     */
    public function resolveTenantId(): ?string
    {
        if (! $this->isTenancyEnabled()) {
            return null;
        }

        try {
            $tenant = filament()->getTenant();

            return $tenant?->getKey();
        } catch (\Throwable) {
            return null;
        }
    }

    public function isTenancyEnabled(): bool
    {
        return (bool) config('filament-lookups.tenancy.enabled', false);
    }

    protected function resolveType(string $slugOrId): ?LookupType
    {
        return LookupType::where('slug', $slugOrId)
            ->orWhere('id', $slugOrId)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Apply tenancy scoping to a query based on the type's tenancy mode.
     */
    protected function applyTenancyScope(mixed $query, LookupType $type, ?string $tenantId): mixed
    {
        if (! $this->isTenancyEnabled() || ! $tenantId) {
            return $query;
        }

        $column = config('filament-lookups.tenancy.tenant_id_column', 'tenant_id');

        return match ($type->tenancy_mode) {
            TenancyMode::Shared => $query->whereNull($column),
            TenancyMode::Tenant => $query->where($column, $tenantId),
            TenancyMode::Both => $query->where(function ($q) use ($column, $tenantId) {
                $q->whereNull($column)->orWhere($column, $tenantId);
            }),
        };
    }

    /**
     * Build indented options for hierarchical display.
     *
     * @return array<string, string>
     */
    protected function buildHierarchicalOptions(Collection $values, int $depth = 0, string $prefix = ''): array
    {
        $options = [];

        foreach ($values as $value) {
            $indent = $depth > 0 ? str_repeat('— ', $depth) : '';
            $options[$value->id] = $indent . $value->name;

            $children = $value->activeChildren()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            if ($children->isNotEmpty()) {
                $options += $this->buildHierarchicalOptions($children, $depth + 1);
            }
        }

        return $options;
    }
}
