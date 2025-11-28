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
        Schema::create('ai_embeddings', function (Blueprint $table) {
            $table->id();
            $table->string('source_table')->comment('batches, strains, batch_logs, etc.');
            $table->unsignedBigInteger('source_id');
            $table->string('content_hash', 64)->comment('SHA256 hash for idempotency');
            $table->longText('content')->comment('Original text content');
            $table->json('embedding_vector')->comment('Vector embeddings as JSON array');
            $table->json('metadata')->nullable()->comment('Tags, filters, batch_code, etc.');
            $table->string('embedding_model')->default('text-embedding-3-large');
            $table->integer('vector_dimensions')->default(3072);
            $table->timestamps();

            // Indexes
            $table->index(['source_table', 'source_id']);
            $table->unique(['source_table', 'source_id', 'content_hash'], 'unique_embedding');
            $table->index('content_hash');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_embeddings');
    }
};
