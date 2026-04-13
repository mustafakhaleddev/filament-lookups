# Filament Lookups

Hierarchical lookup management for Filament with configurable multi-tenant support.

Manage lookup tables (countries, categories, regions, statuses, etc.) with optional parent-child hierarchies. Each lookup type is defined as a PHP class with full control over permissions and behavior.

## Installation

```bash
composer require wezlo/filament-lookups
```

Run migrations:

```bash
php artisan migrate
```

Register the plugin in your panel provider:

```php
use Wezlo\FilamentLookups\FilamentLookupsPlugin;

$panel
    ->plugin(FilamentLookupsPlugin::make()
        ->navigationGroup('Settings'));
```

## Defining Lookup Types

Each lookup type is a PHP class that extends `Lookup`. Create them with the artisan command and sync to the database.

### 1. Create a Lookup class

```bash
php artisan make:lookup Countries
php artisan make:lookup ProductCategories
```

This creates a class in `app/Lookups/`:

```php
namespace App\Lookups;

use Illuminate\Database\Eloquent\Model;
use Wezlo\FilamentLookups\Lookup;

class Countries extends Lookup
{
    public function name(): string
    {
        return 'Countries';
    }

    public function description(): ?string
    {
        return 'List of supported countries';
    }

    public function isHierarchical(): bool
    {
        return false;
    }

    public function canAdd(): bool
    {
        return true;
    }

    public function canEdit(?Model $record = null): bool
    {
        return true;
    }

    public function canDelete(?Model $record = null): bool
    {
        return false; // protect country values from deletion
    }
}
```

### 2. Sync to database

```bash
php artisan lookups:sync
```

This command will:
- **Create** types for new Lookup classes
- **Update** existing types with any class changes
- **Deactivate** types whose class was removed

Run this during deployment or in your CI pipeline.

### 3. Manage values in the panel

The plugin registers a **Lookups** page with a sidebar listing all synced types. Click a type to view and manage its values. The create/edit/delete actions respect the permissions defined in your Lookup class.

## Available Lookup Methods

| Method | Default | Description |
|--------|---------|-------------|
| `name()` | Class name as headline | Display name |
| `slug()` | Slugified name | URL-safe identifier |
| `description()` | `null` | Optional description shown as subheading |
| `isHierarchical()` | `false` | Enable parent-child values |
| `tenancyMode()` | `'shared'` | `'shared'`, `'tenant'`, or `'both'` |
| `sortOrder()` | `0` | Navigation sort order |
| `canAdd()` | `true` | Show/hide create button |
| `canEdit(?Model $record = null)` | `true` | Show/hide edit action per record |
| `canDelete(?Model $record = null)` | `true` | Show/hide delete action per record |
| `canView(?Model $record = null)` | `true` | Show/hide lookup type visibility |
| `canReorder()` | `true` | Enable drag-to-reorder |

## Per-Record Permissions

The `canEdit()`, `canDelete()`, and `canView()` methods receive the current record, allowing conditional logic per row:

```php
use Illuminate\Database\Eloquent\Model;
use Wezlo\FilamentLookups\Lookup;

class OrderStatus extends Lookup
{
    public function canEdit(?Model $record = null): bool
    {
        // prevent editing system-defined statuses
        return ! $record?->metadata['is_system'];
    }

    public function canDelete(?Model $record = null): bool
    {
        // only allow deleting statuses that are not in use
        return $record?->orders()->doesntExist() ?? true;
    }
}
```

When called without a record (e.g. for navigation visibility), `$record` is `null` — the default `true` applies.

## Using `LookupSelect` in Forms

```php
use Wezlo\FilamentLookups\Forms\Components\LookupSelect;

LookupSelect::make('country_id')
    ->lookupType('countries')
```

### Hierarchical display

```php
LookupSelect::make('category_id')
    ->lookupType('product-categories')
    ->hierarchical()
```

### Dependent / cascading selects

```php
LookupSelect::make('region_id')
    ->lookupType('regions')
    ->live(),

LookupSelect::make('city_id')
    ->lookupType('regions')
    ->dependsOn('region_id'),
```

## Using `LookupService` Programmatically

```php
use Wezlo\FilamentLookups\Services\LookupService;

$service = app(LookupService::class);

$values = $service->getValuesForType('countries');
$roots = $service->getRootValues('product-categories');
$children = $service->getChildValues($parentId);
$options = $service->getOptionsForSelect('countries');
```

## Multi-Tenancy

Enable tenancy in config:

```php
'tenancy' => [
    'enabled' => true,
    'tenant_model' => \App\Models\Company::class,
    'tenant_id_column' => 'tenant_id',
],
```

Set the tenancy mode per Lookup class:

```php
public function tenancyMode(): string
{
    return 'both'; // 'shared', 'tenant', or 'both'
}
```

| Mode | Behavior |
|------|----------|
| **shared** | Visible to all tenants |
| **tenant** | Only visible to the owning tenant |
| **both** | System defaults + tenant-specific values merged |

### `HasLookups` Trait

```php
use Wezlo\FilamentLookups\Concerns\HasLookups;

class Company extends Model
{
    use HasLookups;
}

$company->getLookupValues('countries');
```

## Configuration

```bash
php artisan vendor:publish --tag="filament-lookups-config"
```

| Option | Description | Default |
|--------|-------------|---------|
| `lookups_path` | Directory containing Lookup classes | `app_path('Lookups')` |
| `lookups_namespace` | PSR-4 namespace for Lookup classes | `App\Lookups` |
| `tables.lookup_types` | Types table name | `lookup_types` |
| `tables.lookup_values` | Values table name | `lookup_values` |
| `tenancy.enabled` | Enable multi-tenancy | `false` |
| `tenancy.tenant_model` | Tenant model class | `null` |
| `tenancy.tenant_id_column` | Tenant foreign key | `tenant_id` |
| `navigation_group` | Panel navigation group | `Settings` |
| `register_resource` | Register the Filament page | `true` |

## Plugin Configuration

```php
FilamentLookupsPlugin::make()
    ->navigationGroup('Admin')
    ->navigationIcon('heroicon-o-rectangle-stack')
    ->navigationSort(10)
    ->tenancy()
    ->tenantModel(\App\Models\Company::class)
```

## License

MIT
