<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_stage_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained()->onDelete('cascade');
            $table->enum('from_stage', [
                'cloning',
                'vegetative',
                'flowering',
                'harvest',
                'drying',
                'curing',
                'packaging',
                'completed',
            ])->nullable();
            $table->enum('to_stage', [
                'cloning',
                'vegetative',
                'flowering',
                'harvest',
                'drying',
                'curing',
                'packaging',
                'completed'
            ]);
            $table->date('transition_date');
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            
            // Approval
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            // Audit
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();

            $table->index('batch_id');
            $table->index('transition_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_stage_history');
    }
};

