<?php

namespace Wezlo\FilamentLookups\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Wezlo\FilamentLookups\FilamentLookupsPlugin;
use Wezlo\FilamentLookups\Lookup;
use Wezlo\FilamentLookups\Models\LookupType;
use Wezlo\FilamentLookups\Models\LookupValue;
use Wezlo\FilamentLookups\Services\LookupRegistry;
use Wezlo\FilamentLookups\Services\LookupService;

class ManageLookups extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected string $view = 'filament-lookups::pages.manage-lookups';

    protected static ?string $slug = 'lookups/{type?}';

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Start;

    public ?LookupType $selectedType = null;

    protected ?Lookup $lookupClass = null;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return FilamentLookupsPlugin::resolveNavigationIcon() ?? Heroicon::OutlinedRectangleStack;
    }

    public static function getNavigationGroup(): ?string
    {
        return FilamentLookupsPlugin::resolveNavigationGroup();
    }

    public static function getNavigationSort(): ?int
    {
        return FilamentLookupsPlugin::resolveNavigationSort();
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-lookups::lookups.page_title');
    }

    public function getTitle(): string
    {
        $lookup = $this->getLookupClass();

        return $lookup?->name() ?? $this->selectedType?->name ?? __('filament-lookups::lookups.page_title');
    }

    public function getSubheading(): ?string
    {
        $lookup = $this->getLookupClass();

        return $lookup?->description() ?? $this->selectedType?->description;
    }

    public function mount(?string $type = null): void
    {
        if ($type) {
            $this->selectedType = $this->typesQuery()->where('slug', $type)->first();
        }

        if (! $this->selectedType) {
            $this->selectedType = $this->getFirstViewableType();

            if ($this->selectedType && $type !== $this->selectedType->slug) {
                $this->redirect(static::getUrl(['type' => $this->selectedType->slug]));
            }
        }

        $this->resolveLookupClass();
    }

    protected function resolveLookupClass(): void
    {
        if ($this->selectedType) {
            $this->lookupClass = $this->getRegistry()->resolveForType($this->selectedType);
        }
    }

    protected function getLookupClass(): ?Lookup
    {
        if (! $this->lookupClass && $this->selectedType) {
            $this->resolveLookupClass();
        }

        return $this->lookupClass;
    }

    protected function getRegistry(): LookupRegistry
    {
        return app(LookupRegistry::class);
    }

    protected function getLookupService(): LookupService
    {
        return app(LookupService::class);
    }

    /**
     * Base query for lookup types, scoped by tenant when enabled.
     */
    protected function typesQuery(): Builder
    {
        $query = LookupType::where('is_active', true);

        if ($this->isTenancyEnabled()) {
            $tenantId = $this->resolveTenantId();

            if ($tenantId) {
                $query->forTenant($tenantId);
            } else {
                $query->shared();
            }
        }

        return $query;
    }

    /**
     * Base query for lookup values, scoped by tenant when enabled.
     */
    protected function valuesQuery(): Builder
    {
        $query = LookupValue::where('lookup_type_id', $this->selectedType->id);

        if ($this->isTenancyEnabled() && $this->selectedType) {
            $tenantId = $this->resolveTenantId();

            if ($tenantId) {
                $query->forTenant($tenantId);
            } else {
                $query->shared();
            }
        }

        return $query;
    }

    protected function valuesCountForType(LookupType $type): int
    {
        $query = LookupValue::where('lookup_type_id', $type->id);

        if ($this->isTenancyEnabled()) {
            $tenantId = $this->resolveTenantId();

            if ($tenantId) {
                $query->forTenant($tenantId);
            } else {
                $query->shared();
            }
        }

        return $query->count();
    }

    protected function isTenancyEnabled(): bool
    {
        return $this->getLookupService()->isTenancyEnabled();
    }

    protected function resolveTenantId(): ?string
    {
        return $this->getLookupService()->resolveTenantId();
    }

    protected function getFirstViewableType(): ?LookupType
    {
        $registry = $this->getRegistry();
        $types = $this->typesQuery()->orderBy('sort_order')->get();

        foreach ($types as $type) {
            $lookup = $registry->resolveForType($type);

            if (! $lookup || $lookup->canView()) {
                return $type;
            }
        }

        return $types->first();
    }

    /**
     * @return array<NavigationItem>
     */
    public function getSubNavigation(): array
    {
        $types = $this->typesQuery()->orderBy('sort_order')->orderBy('name')->get();
        $registry = $this->getRegistry();

        return $types
            ->filter(function (LookupType $type) use ($registry) {
                $lookup = $registry->resolveForType($type);

                return ! $lookup || $lookup->canView();
            })
            ->map(function (LookupType $type) use ($registry) {
                $lookup = $registry->resolveForType($type);
                $name = $lookup?->name() ?? $type->name;

                return NavigationItem::make($name)
                    ->url(static::getUrl(['type' => $type->slug]))
                    ->isActiveWhen(fn (): bool => $this->selectedType?->id === $type->id)
                    ->icon($type->is_hierarchical ? Heroicon::OutlinedFolderOpen : Heroicon::OutlinedListBullet)
                    ->badge($this->valuesCountForType($type));
            })
            ->all();
    }

    public function table(Table $table): Table
    {
        $lookup = $this->getLookupClass();

        return $table
            ->query(fn (): Builder => $this->selectedType
                ? $this->valuesQuery()
                : LookupValue::whereRaw('1 = 0'))
            ->defaultSort('sort_order')
            ->reorderable($lookup?->canReorder() !== false ? 'sort_order' : null)
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament-lookups::lookups.value_table.name'))
                    ->searchable()
                    ->sortable()
                    ->description(fn (LookupValue $record): ?string => $record->parent_id ? $record->getFullPath() : null),
                TextColumn::make('code')
                    ->label(__('filament-lookups::lookups.value_table.code'))
                    ->searchable()
                    ->copyable(),
                TextColumn::make('parent.name')
                    ->label(__('filament-lookups::lookups.value_table.parent'))
                    ->placeholder('—')
                    ->visible(fn (): bool => (bool) $this->selectedType?->is_hierarchical),
                IconColumn::make('is_active')
                    ->label(__('filament-lookups::lookups.value_table.is_active'))
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label(__('filament-lookups::lookups.value_table.sort_order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('children_count')
                    ->label(__('filament-lookups::lookups.value_table.children_count'))
                    ->counts('children')
                    ->visible(fn (): bool => (bool) $this->selectedType?->is_hierarchical),
            ])
            ->headerActions([
                CreateAction::make()
                    ->model(LookupValue::class)
                    ->form($this->getValueFormSchema())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['lookup_type_id'] = $this->selectedType->id;

                        if ($this->isTenancyEnabled()) {
                            $column = config('filament-lookups.tenancy.tenant_id_column', 'tenant_id');
                            $data[$column] = $this->resolveTenantId();
                        }

                        return $data;
                    })
                    ->visible(fn (): bool => $lookup?->canAdd() ?? true),
            ])
            ->recordActions([
                EditAction::make()
                    ->form($this->getValueFormSchema())
                    ->visible(fn (): bool => $lookup?->canEdit() ?? true),
                DeleteAction::make()
                    ->visible(fn (): bool => $lookup?->canDelete() ?? true),
            ]);
    }

    /**
     * @return array<\Filament\Forms\Components\Component>
     */
    protected function getValueFormSchema(): array
    {
        return [
            TextInput::make('name')
                ->label(__('filament-lookups::lookups.value_form.name'))
                ->required()
                ->maxLength(255),
            TextInput::make('code')
                ->label(__('filament-lookups::lookups.value_form.code'))
                ->required()
                ->maxLength(255)
                ->unique(
                    table: LookupValue::class,
                    column: 'code',
                    ignoreRecord: true,
                    modifyRuleUsing: fn ($rule) => $rule->where('lookup_type_id', $this->selectedType?->id),
                )
                ->helperText(__('filament-lookups::lookups.value_form.code_helper')),
            Select::make('parent_id')
                ->label(__('filament-lookups::lookups.value_form.parent'))
                ->options(function (?LookupValue $record): array {
                    if (! $this->selectedType) {
                        return [];
                    }

                    $query = $this->selectedType->values()
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->orderBy('name');

                    if ($record) {
                        $excludeIds = array_merge([$record->id], $record->getDescendantIds());
                        $query->whereNotIn('id', $excludeIds);
                    }

                    return $query->pluck('name', 'id')->all();
                })
                ->searchable()
                ->placeholder(__('filament-lookups::lookups.value_form.no_parent'))
                ->visible(fn (): bool => (bool) $this->selectedType?->is_hierarchical),
            Textarea::make('description')
                ->label(__('filament-lookups::lookups.value_form.description'))
                ->maxLength(1000)
                ->columnSpanFull(),
            Toggle::make('is_active')
                ->label(__('filament-lookups::lookups.value_form.is_active'))
                ->default(true),
            TextInput::make('sort_order')
                ->label(__('filament-lookups::lookups.value_form.sort_order'))
                ->numeric()
                ->default(0)
                ->minValue(0),
            KeyValue::make('metadata')
                ->label(__('filament-lookups::lookups.value_form.metadata'))
                ->keyLabel(__('filament-lookups::lookups.value_form.metadata_key'))
                ->valueLabel(__('filament-lookups::lookups.value_form.metadata_value'))
                ->columnSpanFull(),
        ];
    }
}
