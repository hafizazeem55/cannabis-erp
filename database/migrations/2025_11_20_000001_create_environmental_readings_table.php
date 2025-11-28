<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('environmental_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->onDelete('cascade');
            $table->string('space_type'); // 'room' or 'tunnel'
            $table->unsignedBigInteger('space_id');
            $table->decimal('temperature', 6, 2)->nullable();
            $table->decimal('humidity', 6, 2)->nullable();
            $table->decimal('co2', 10, 2)->nullable();
            $table->decimal('ph', 5, 2)->nullable();
            $table->decimal('ec', 8, 2)->nullable();
            $table->timestamp('recorded_at')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['space_type', 'space_id']);
            $table->index('facility_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('environmental_readings');
    }
};
