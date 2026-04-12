<?php

namespace Wezlo\FilamentLookups\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Wezlo\FilamentLookups\Enums\TenancyMode;

class LookupType extends Model
{
    use HasUuids;

    protected $guarded = [];

    public function getTable(): string
    {
        return config('filament-lookups.tables.lookup_types', 'lookup_types');
    }

    protected function casts(): array
    {
        return [
            'is_hierarchical' => 'boolean',
            'is_active' => 'boolean',
            'tenancy_mode' => TenancyMode::class,
            'sort_order' => 'integer',
        ];
    }

    public function values(): HasMany
    {
        return $this->hasMany(LookupValue::class, 'lookup_type_id');
    }

    public function activeValues(): HasMany
    {
        return $this->values()->where('is_active', true);
    }

    public function rootValues(): HasMany
    {
        return $this->values()->whereNull('parent_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeShared(Builder $query): Builder
    {
        return $query->whereIn('tenancy_mode', [TenancyMode::Shared->value, TenancyMode::Both->value]);
    }

    public function scopeForTenant(Builder $query, string $tenantId): Builder
    {
        $column = config('filament-lookups.tenancy.tenant_id_column', 'tenant_id');

        return $query->where(function (Builder $q) use ($column, $tenantId) {
            $q->whereIn('tenancy_mode', [TenancyMode::Shared->value, TenancyMode::Both->value])
                ->orWhere(function (Builder $q) use ($column, $tenantId) {
                    $q->where('tenancy_mode', TenancyMode::Tenant->value)
                        ->where($column, $tenantId);
                });
        });
    }

    public static function findBySlug(string $slug): ?static
    {
        return static::where('slug', $slug)->first();
    }
}
