<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('harvests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained()->onDelete('cascade');
            $table->foreignId('room_id')->nullable()->constrained()->onDelete('set null');
            $table->date('harvest_date');
            $table->time('harvest_time')->nullable();
            
            // Weights (in grams)
            $table->decimal('wet_weight', 10, 2)->nullable()->comment('Total wet weight');
            $table->decimal('trim_weight', 10, 2)->default(0)->comment('Trim weight');
            $table->decimal('waste_weight', 10, 2)->default(0)->comment('Waste weight');
            $table->decimal('dry_weight', 10, 2)->nullable()->comment('Dry weight after curing');
            
            // Plant counts
            $table->integer('harvested_plant_count')->default(0);
            
            // Yield calculations
            $table->decimal('expected_yield', 10, 2)->nullable();
            $table->decimal('actual_yield', 10, 2)->nullable();
            $table->decimal('yield_percentage', 5, 2)->nullable();
            
            // Quality notes
            $table->text('quality_notes')->nullable();
            $table->text('harvest_notes')->nullable();
            
            // Status
            $table->enum('status', [
                'pending',
                'completed',
                'cancelled'
            ])->default('pending');
            
            // Flags
            $table->boolean('low_yield_deviation_raised')->default(false)->comment('If yield <85%, deviation raised');
            $table->boolean('lots_created')->default(false)->comment('Material lots created from harvest');
            
            // Audit
            $table->foreignId('harvested_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('supervisor_approved_at')->nullable();
            $table->timestamps();

            $table->index('batch_id');
            $table->index('harvest_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harvests');
    }
};

