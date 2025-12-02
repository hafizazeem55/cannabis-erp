<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('environmental_thresholds')) {
            return;
        }

        Schema::create('environmental_thresholds', function (Blueprint $table) {
            $table->id();
            $table->enum('stage', [
                'clone',
                'propagation',
                'vegetative',
                'flower',
                'harvest',
                'completed',
            ]);
            $table->enum('parameter', [
                'temperature',
                'humidity',
                'co2',
                'ph',
                'ec',
            ]);
            $table->decimal('min_value', 6, 2);
            $table->decimal('max_value', 6, 2);
            $table->decimal('target_value', 6, 2);
            $table->decimal('tolerance_percent', 5, 2)->default(0);
            $table->enum('severity', ['standard', 'warning', 'critical'])->default('standard');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['stage', 'parameter']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('environmental_thresholds');
    }
};
