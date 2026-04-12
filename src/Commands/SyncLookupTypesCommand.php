<?php

namespace Wezlo\FilamentLookups\Commands;

use Illuminate\Console\Command;
use Wezlo\FilamentLookups\Enums\TenancyMode;
use Wezlo\FilamentLookups\Models\LookupType;
use Wezlo\FilamentLookups\Services\LookupRegistry;

class SyncLookupTypesCommand extends Command
{
    protected $signature = 'lookups:sync';

    protected $description = 'Sync lookup type definitions from Lookup classes to the database';

    public function handle(LookupRegistry $registry): int
    {
        $lookups = $registry->discover();

        if ($lookups->isEmpty()) {
            $path = config('filament-lookups.lookups_path', app_path('Lookups'));
            $this->warn("No Lookup classes found in {$path}.");
            $this->line('Create one with: php artisan make:lookup');

            return self::SUCCESS;
        }

        $syncedSlugs = [];

        foreach ($lookups as $lookup) {
            $type = LookupType::updateOrCreate(
                ['slug' => $lookup->slug()],
                [
                    'name' => $lookup->name(),
                    'is_hierarchical' => $lookup->isHierarchical(),
                    'is_active' => true,
                    'tenancy_mode' => TenancyMode::tryFrom($lookup->tenancyMode()) ?? TenancyMode::Shared,
                    'description' => $lookup->description(),
                    'sort_order' => $lookup->sortOrder(),
                ],
            );

            $syncedSlugs[] = $lookup->slug();

            $this->components->twoColumnDetail(
                $type->name,
                $type->wasRecentlyCreated ? '<fg=green>Created</>' : '<fg=blue>Updated</>',
            );
        }

        $deactivated = LookupType::whereNotIn('slug', $syncedSlugs)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        if ($deactivated > 0) {
            $this->components->warn("{$deactivated} type(s) without a Lookup class were deactivated.");
        }

        $this->newLine();
        $this->components->info('Lookup types synced successfully.');

        return self::SUCCESS;
    }
}
