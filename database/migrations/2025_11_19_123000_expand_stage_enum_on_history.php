<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $stages = [
        'cloning',
        'vegetative',
        'flowering',
        'harvest',
        'drying',
        'curing',
        'packaging',
        'completed',
    ];

    public function up(): void
    {
        $enum = "'" . implode("','", $this->stages) . "'";

        DB::statement("ALTER TABLE batch_stage_history MODIFY COLUMN from_stage ENUM($enum) NULL");
        DB::statement("ALTER TABLE batch_stage_history MODIFY COLUMN to_stage ENUM($enum) NOT NULL");
    }

    public function down(): void
    {
        $enum = "'" . implode("','", [
            'clone',
            'propagation',
            'vegetative',
            'flower',
            'harvest',
            'completed',
        ]) . "'";

        DB::statement("ALTER TABLE batch_stage_history MODIFY COLUMN from_stage ENUM($enum) NULL");
        DB::statement("ALTER TABLE batch_stage_history MODIFY COLUMN to_stage ENUM($enum) NOT NULL");
    }
};
