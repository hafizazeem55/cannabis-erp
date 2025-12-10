<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('environmental_thresholds')) {
            return;
        }

        Schema::table('environmental_thresholds', function (Blueprint $table) {
            if (! Schema::hasColumn('environmental_thresholds', 'stage')) {
                $table->enum('stage', ['cloning', 'vegetative', 'flowering', 'harvest', 'drying', 'curing', 'packaging', 'completed'])
                    ->default('cloning');
            }

            if (! Schema::hasColumn('environmental_thresholds', 'parameter')) {
                $table->enum('parameter', ['temperature', 'humidity', 'co2', 'ph', 'ec'])
                    ->default('temperature');
            }

            if (! Schema::hasColumn('environmental_thresholds', 'min_value')) {
                $table->decimal('min_value', 6, 2)->default(0);
            }

            if (! Schema::hasColumn('environmental_thresholds', 'max_value')) {
                $table->decimal('max_value', 6, 2)->default(0);
            }

            if (! Schema::hasColumn('environmental_thresholds', 'target_value')) {
                $table->decimal('target_value', 6, 2)->default(0);
            }

            if (! Schema::hasColumn('environmental_thresholds', 'tolerance_percent')) {
                $table->decimal('tolerance_percent', 5, 2)->default(0);
            }

            if (! Schema::hasColumn('environmental_thresholds', 'severity')) {
                $table->enum('severity', ['standard', 'warning', 'critical'])->default('standard');
            }

            if (! Schema::hasColumn('environmental_thresholds', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }

            if (! Schema::hasColumn('environmental_thresholds', 'notes')) {
                $table->text('notes')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('environmental_thresholds')) {
            return;
        }

        Schema::table('environmental_thresholds', function (Blueprint $table) {
            foreach (['stage', 'parameter', 'min_value', 'max_value', 'target_value', 'tolerance_percent', 'severity', 'is_active', 'notes'] as $column) {
                if (Schema::hasColumn('environmental_thresholds', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
