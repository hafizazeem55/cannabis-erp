<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('strains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('code')->nullable();
            $table->enum('type', ['indica', 'sativa', 'hybrid'])->nullable();
            $table->text('genetics')->nullable();
            $table->text('description')->nullable();
            
            // Cannabinoid profiles (expected ranges)
            $table->decimal('thc_min', 5, 2)->nullable()->comment('Expected THC % minimum');
            $table->decimal('thc_max', 5, 2)->nullable()->comment('Expected THC % maximum');
            $table->decimal('cbd_min', 5, 2)->nullable()->comment('Expected CBD % minimum');
            $table->decimal('cbd_max', 5, 2)->nullable()->comment('Expected CBD % maximum');
            
            // Yield benchmarks
            $table->decimal('expected_yield_per_plant', 8, 2)->nullable()->comment('Expected yield in grams per plant');
            $table->integer('expected_flowering_days')->nullable();
            $table->integer('expected_vegetative_days')->nullable();
            
            // Growth characteristics
            $table->text('growth_notes')->nullable();
            $table->text('nutrient_requirements')->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('organization_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('strains');
    }
};

