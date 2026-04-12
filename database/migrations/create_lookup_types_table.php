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
        $tableName = config('filament-lookups.tables.lookup_types', 'lookup_types');

        Schema::create($tableName, function (Blueprint $table) use ($tenancyEnabled, $tenantColumn) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->boolean('is_hierarchical')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('tenancy_mode')->default('shared');
            $table->uuid($tenantColumn)->nullable()->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            if ($tenancyEnabled) {
                $table->unique([$tenantColumn, 'slug'], 'lookup_types_tenant_slug_unique');
            } else {
                $table->unique(['slug'], 'lookup_types_slug_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('filament-lookups.tables.lookup_types', 'lookup_types'));
    }
};
