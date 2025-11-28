<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('batch_code')->unique();
            $table->foreignId('strain_id')->constrained()->onDelete('restrict');
            $table->foreignId('room_id')->constrained()->onDelete('restrict');
            $table->foreignId('parent_batch_id')->nullable()->constrained('batches')->onDelete('set null');
            
            // Batch status and stage
            $table->enum('status', [
                'clone',
                'propagation',
                'vegetative',
                'flower',
                'harvest',
                'completed',
                'cancelled'
            ])->default('clone');
            
            // Plant counts
            $table->integer('initial_plant_count')->default(0);
            $table->integer('current_plant_count')->default(0);
            $table->integer('mortality_count')->default(0);
            
            // Dates
            $table->date('planting_date');
            $table->date('clone_date')->nullable();
            $table->date('veg_start_date')->nullable();
            $table->date('flower_start_date')->nullable();
            $table->date('harvest_date')->nullable();
            $table->date('expected_harvest_date')->nullable();
            
            // Progress tracking
            $table->decimal('progress_percentage', 5, 2)->default(0)->comment('Percentage complete');
            
            // Yield tracking
            $table->decimal('expected_yield', 10, 2)->nullable()->comment('Expected total yield in grams');
            $table->decimal('actual_yield', 10, 2)->nullable()->comment('Actual yield in grams');
            $table->decimal('yield_percentage', 5, 2)->nullable()->comment('Actual vs expected yield %');
            
            // Metadata
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable()->comment('Additional structured data');
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('organization_id');
            $table->index('strain_id');
            $table->index('room_id');
            $table->index('status');
            $table->index('batch_code');
            $table->index('parent_batch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};

