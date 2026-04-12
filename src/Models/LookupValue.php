<?php

namespace Wezlo\FilamentLookups\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LookupValue extends Model
{
    use HasUuids;

    protected $guarded = [];

    public function getTable(): string
    {
        return config('filament-lookups.tables.lookup_values', 'lookup_values');
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'metadata' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(LookupType::class, 'lookup_type_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(static::class, 'parent_id');
    }

    public function activeChildren(): HasMany
    {
        return $this->children()->where('is_active', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeForTenant(Builder $query, string $tenantId): Builder
    {
        $column = config('filament-lookups.tenancy.tenant_id_column', 'tenant_id');

        return $query->where(function (Builder $q) use ($column, $tenantId) {
            $q->whereNull($column)
                ->orWhere($column, $tenantId);
        });
    }

    public function scopeShared(Builder $query): Builder
    {
        $column = config('filament-lookups.tenancy.tenant_id_column', 'tenant_id');

        return $query->whereNull($column);
    }

    public function getFullPath(): string
    {
        $parts = [$this->name];
        $current = $this;

        while ($current->parent) {
            $current = $current->parent;
            array_unshift($parts, $current->name);
        }

        return implode(' > ', $parts);
    }

    public function getDepth(): int
    {
        $depth = 0;
        $current = $this;

        while ($current->parent_id) {
            $depth++;
            $current = $current->parent;
        }

        return $depth;
    }

    /**
     * Get all descendant IDs to prevent circular parent references.
     *
     * @return array<string>
     */
    public function getDescendantIds(): array
    {
        $ids = [];

        foreach ($this->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $child->getDescendantIds());
        }

        return $ids;
    }
}
