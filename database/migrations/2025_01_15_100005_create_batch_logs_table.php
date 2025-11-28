<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained()->onDelete('cascade');
            $table->foreignId('room_id')->nullable()->constrained()->onDelete('set null');
            $table->date('log_date');
            
            // Activities (stored as JSON for flexibility)
            $table->json('activities')->nullable()->comment('Watering, pruning, nutrients, etc.');
            $table->text('notes')->nullable();
            
            // Environmental data (from sensors or manual entry)
            $table->decimal('temperature_avg', 5, 2)->nullable();
            $table->decimal('temperature_min', 5, 2)->nullable();
            $table->decimal('temperature_max', 5, 2)->nullable();
            $table->decimal('humidity_avg', 5, 2)->nullable();
            $table->decimal('humidity_min', 5, 2)->nullable();
            $table->decimal('humidity_max', 5, 2)->nullable();
            $table->decimal('co2_avg', 5, 2)->nullable();
            $table->decimal('ph_avg', 3, 2)->nullable();
            $table->decimal('ec_avg', 5, 2)->nullable();
            
            // Plant status
            $table->integer('plant_count')->nullable();
            $table->integer('mortality_count')->default(0);
            
            // Audit
            $table->foreignId('logged_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();

            $table->index('batch_id');
            $table->index('log_date');
            $table->index(['batch_id', 'log_date']);
            $table->unique(['batch_id', 'log_date'], 'unique_batch_log_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_logs');
    }
};

