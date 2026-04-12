<?php

namespace Wezlo\FilamentLookups\Concerns;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Wezlo\FilamentLookups\Models\LookupType;
use Wezlo\FilamentLookups\Models\LookupValue;
use Wezlo\FilamentLookups\Services\LookupService;

trait HasLookups
{
    public function lookupTypes(): HasMany
    {
        return $this->hasMany(
            LookupType::class,
            config('filament-lookups.tenancy.tenant_id_column', 'tenant_id'),
        );
    }

    public function lookupValues(): HasMany
    {
        return $this->hasMany(
            LookupValue::class,
            config('filament-lookups.tenancy.tenant_id_column', 'tenant_id'),
        );
    }

    public function getLookupValues(string $typeSlug): Collection
    {
        return app(LookupService::class)->getValuesForType($typeSlug, $this->getKey());
    }
}
