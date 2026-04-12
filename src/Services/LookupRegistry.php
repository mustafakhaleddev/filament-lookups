<?php

namespace Wezlo\FilamentLookups\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Wezlo\FilamentLookups\Lookup;
use Wezlo\FilamentLookups\Models\LookupType;

class LookupRegistry
{
    /**
     * @var array<string, Lookup>
     */
    protected array $resolved = [];

    /**
     * Discover all Lookup classes from the configured path.
     *
     * @return Collection<int, Lookup>
     */
    public function discover(): Collection
    {
        $path = config('filament-lookups.lookups_path', app_path('Lookups'));
        $namespace = config('filament-lookups.lookups_namespace', 'App\\Lookups');

        if (! is_dir($path)) {
            return collect();
        }

        return collect(File::files($path))
            ->filter(fn ($file) => $file->getExtension() === 'php')
            ->map(function ($file) use ($namespace) {
                $className = $namespace . '\\' . $file->getFilenameWithoutExtension();

                if (! class_exists($className)) {
                    return null;
                }

                if (! is_subclass_of($className, Lookup::class)) {
                    return null;
                }

                return new $className;
            })
            ->filter()
            ->values();
    }

    /**
     * Resolve the Lookup class instance for a given LookupType.
     */
    public function resolveForType(LookupType $type): ?Lookup
    {
        $slug = $type->slug;

        if (isset($this->resolved[$slug])) {
            return $this->resolved[$slug];
        }

        $lookup = $this->discover()->first(fn (Lookup $lookup) => $lookup->slug() === $slug);

        if ($lookup) {
            $this->resolved[$slug] = $lookup;
        }

        return $lookup;
    }
}
