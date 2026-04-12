<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tenancyEnabled = config('filament-lookups.tenancy.enabled', false);
        $tenantColumn = config('filament-lookups.tenancy.tenant_id_column', 'tenant_id');
        $typesTable = config('filament-lookups.tables.lookup_types', 'lookup_types');
        $valuesTable = config('filament-lookups.tables.lookup_values', 'lookup_values');

        Schema::create($valuesTable, function (Blueprint $table) use ($tenancyEnabled, $tenantColumn, $typesTable, $valuesTable) {
            $table->uuid('id')->primary();
            $table->uuid('lookup_type_id');
            $table->uuid('parent_id')->nullable();
            $table->string('name');
            $table->string('code');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->uuid($tenantColumn)->nullable()->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('lookup_type_id')
                ->references('id')
                ->on($typesTable)
                ->cascadeOnDelete();

            $table->foreign('parent_id')
                ->references('id')
                ->on($valuesTable)
                ->nullOnDelete();

            if ($tenancyEnabled) {
                $table->unique(['lookup_type_id', $tenantColumn, 'code'], 'lookup_values_type_tenant_code_unique');
            } else {
                $table->unique(['lookup_type_id', 'code'], 'lookup_values_type_code_unique');
            }

            $table->index(['lookup_type_id', 'parent_id', 'is_active', 'sort_order'], 'lookup_values_hierarchy_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('filament-lookups.tables.lookup_values', 'lookup_values'));
    }
};
