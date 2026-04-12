<?php

namespace Wezlo\FilamentLookups;

use Illuminate\Support\Str;

abstract class Lookup
{
    public function name(): string
    {
        return Str::headline(class_basename(static::class));
    }

    public function slug(): string
    {
        return Str::kebab(class_basename(static::class));
    }

    public function description(): ?string
    {
        return null;
    }

    public function isHierarchical(): bool
    {
        return false;
    }

    public function tenancyMode(): string
    {
        return 'shared';
    }

    public function sortOrder(): int
    {
        return 0;
    }

    public function canView(): bool
    {
        return true;
    }

    public function canAdd(): bool
    {
        return true;
    }

    public function canEdit(): bool
    {
        return true;
    }

    public function canDelete(): bool
    {
        return true;
    }

    public function canReorder(): bool
    {
        return true;
    }
}
