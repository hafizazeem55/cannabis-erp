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
        Schema::create('ai_anomaly_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('batches')->onDelete('cascade');
            $table->foreignId('room_id')->nullable()->constrained('rooms')->onDelete('set null');
            $table->string('image_path');
            $table->boolean('is_anomaly')->default(false);
            $table->decimal('confidence', 5, 4)->nullable()->comment('0.0 to 1.0');
            $table->string('detected_issue')->nullable();
            $table->text('issue_description')->nullable();
            $table->text('recommended_action')->nullable();
            $table->string('severity')->nullable()->comment('low, medium, high, critical');
            $table->string('provider')->default('openai')->comment('openai, local');
            $table->json('raw_response')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('reviewed')->default(false);
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['batch_id', 'created_at']);
            $table->index(['is_anomaly', 'severity']);
            $table->index('detected_issue');
            $table->index('reviewed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_anomaly_reports');
    }
};
