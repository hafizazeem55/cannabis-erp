<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('code')->nullable();
            $table->enum('type', [
                'nursery',
                'veg',
                'flower',
                'cure',
                'packaging',
                'warehouse',
                'quarantine'
            ]);
            $table->integer('capacity')->default(0)->comment('Maximum plant/batch capacity');
            $table->decimal('temperature_min', 5, 2)->nullable();
            $table->decimal('temperature_max', 5, 2)->nullable();
            $table->decimal('humidity_min', 5, 2)->nullable();
            $table->decimal('humidity_max', 5, 2)->nullable();
            $table->decimal('co2_min', 5, 2)->nullable();
            $table->decimal('co2_max', 5, 2)->nullable();
            $table->decimal('ph_min', 3, 2)->nullable();
            $table->decimal('ph_max', 3, 2)->nullable();
            $table->decimal('ec_min', 5, 2)->nullable();
            $table->decimal('ec_max', 5, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('facility_id');
            $table->index('type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};

