<?php

namespace Wezlo\FilamentLookups\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;

class MakeLookupCommand extends Command
{
    protected $signature = 'make:lookup {name?}';

    protected $description = 'Create a new Lookup class';

    public function handle(): int
    {
        $name = $this->argument('name') ?? text(
            label: 'What is the lookup name?',
            placeholder: 'Countries',
            required: true,
        );

        $name = Str::studly($name);
        $path = config('filament-lookups.lookups_path', app_path('Lookups'));
        $namespace = config('filament-lookups.lookups_namespace', 'App\\Lookups');

        $filePath = $path . '/' . $name . '.php';

        if (file_exists($filePath)) {
            $this->components->error("Lookup [{$name}] already exists.");

            return self::FAILURE;
        }

        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $hierarchical = confirm(
            label: 'Should this lookup be hierarchical?',
            default: false,
        );

        $stub = $this->buildStub($namespace, $name, $hierarchical);

        file_put_contents($filePath, $stub);

        $this->components->info("Lookup [{$filePath}] created successfully.");
        $this->components->warn('Run php artisan lookups:sync to register it in the database.');

        return self::SUCCESS;
    }

    protected function buildStub(string $namespace, string $name, bool $hierarchical): string
    {
        $humanName = Str::headline($name);
        $hierarchicalStr = $hierarchical ? 'true' : 'false';

        return <<<PHP
<?php

namespace {$namespace};

use Wezlo\FilamentLookups\Lookup;

class {$name} extends Lookup
{
    public function name(): string
    {
        return '{$humanName}';
    }

    public function description(): ?string
    {
        return null;
    }

    public function isHierarchical(): bool
    {
        return {$hierarchicalStr};
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
}

PHP;
    }
}
