<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained()->onDelete('cascade');
            $table->foreignId('from_room_id')->constrained('rooms')->onDelete('restrict');
            $table->foreignId('to_room_id')->constrained('rooms')->onDelete('restrict');
            $table->date('transfer_date');
            $table->time('transfer_time')->nullable();
            $table->integer('plant_count')->default(0);
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            
            // Flags
            $table->boolean('is_planned')->default(true)->comment('Planned vs unplanned transfer');
            $table->boolean('triggered_deviation')->default(false)->comment('If unplanned, did it trigger deviation');
            
            // Audit
            $table->foreignId('transferred_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index('batch_id');
            $table->index('transfer_date');
            $table->index(['from_room_id', 'to_room_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_transfers');
    }
};

