<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growth_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
            $table->foreignId('primary_strain_id')->nullable()->constrained('strains')->nullOnDelete();
            $table->string('name');
            $table->string('status')->default('planning');
            $table->date('start_date');
            $table->date('expected_end_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('growth_cycle_strain', function (Blueprint $table) {
            $table->id();
            $table->foreignId('growth_cycle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('strain_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['growth_cycle_id', 'strain_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growth_cycle_strain');
        Schema::dropIfExists('growth_cycles');
    }
};

