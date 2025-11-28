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
        Schema::create('ai_chat_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('batch_id')->nullable()->constrained('batches')->onDelete('set null');
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->onDelete('cascade');
            $table->text('query')->comment('User question');
            $table->longText('response')->comment('AI response');
            $table->json('context_used')->nullable()->comment('RAG chunks used');
            $table->json('embeddings_ref')->nullable()->comment('Embedding IDs used');
            $table->text('context_snapshot')->nullable()->comment('Simplified context summary');
            $table->string('provider')->default('openai');
            $table->integer('tokens_used')->nullable();
            $table->decimal('response_time_seconds', 8, 3)->nullable();
            $table->boolean('was_helpful')->nullable();
            $table->text('feedback')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'created_at']);
            $table->index(['batch_id', 'created_at']);
            $table->index('organization_id');
            $table->fullText(['query', 'response']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_chat_logs');
    }
};
