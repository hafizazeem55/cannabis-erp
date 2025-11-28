<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_classification_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->nullable()->constrained('batches')->onDelete('cascade');
            $table->foreignId('room_id')->nullable()->constrained('rooms')->onDelete('set null');
            $table->string('image_path');
            $table->json('classifications')->comment('Multi-class predictions with scores');
            $table->string('top_label')->nullable();
            $table->string('top_category')->nullable();
            $table->decimal('confidence', 5, 4)->nullable()->comment('0.0 to 1.0');
            $table->string('growth_stage')->nullable();
            $table->string('health_status')->nullable();
            $table->json('leaf_issues')->nullable();
            $table->string('strain_type_prediction')->nullable();
            $table->string('provider')->default('openai')->comment('openai, local');
            $table->json('raw_response')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['batch_id', 'created_at']);
            $table->index('growth_stage');
            $table->index('health_status');
            $table->index('top_label');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_classification_results');
    }
};
