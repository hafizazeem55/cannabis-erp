<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('environmental_thresholds')) {
            return;
        }

        Schema::table('environmental_thresholds', function (Blueprint $table) {
            // Drop legacy unique on stage if it exists.
            if ($this->hasIndex('environmental_thresholds', 'environmental_thresholds_stage_unique')) {
                $table->dropUnique('environmental_thresholds_stage_unique');
            }
            if ($this->hasIndex('environmental_thresholds', 'stage_unique')) {
                $table->dropUnique('stage_unique');
            }
        });

        // Ensure parameter is non-null for existing rows to satisfy the new composite unique.
        DB::table('environmental_thresholds')
            ->whereNull('parameter')
            ->update(['parameter' => 'temperature']);

        Schema::table('environmental_thresholds', function (Blueprint $table) {
            if (! $this->hasIndex('environmental_thresholds', 'environmental_thresholds_stage_parameter_unique')) {
                $table->unique(['stage', 'parameter'], 'environmental_thresholds_stage_parameter_unique');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('environmental_thresholds')) {
            return;
        }

        Schema::table('environmental_thresholds', function (Blueprint $table) {
            if ($this->hasIndex('environmental_thresholds', 'environmental_thresholds_stage_parameter_unique')) {
                $table->dropUnique('environmental_thresholds_stage_parameter_unique');
            }
        });
    }

    private function hasIndex(string $table, string $index): bool
    {
        return collect(Schema::getIndexes($table) ?? [])->contains(function ($definition) use ($index) {
            return isset($definition['name']) && $definition['name'] === $index;
        });
    }
};
