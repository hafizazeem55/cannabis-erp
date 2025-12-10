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
        'cancelled',
    ];

    public function up(): void
    {
        $enum = "'" . implode("','", $this->stages) . "'";

        DB::statement("ALTER TABLE batches MODIFY COLUMN status ENUM($enum) NOT NULL DEFAULT 'cloning'");
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
            'cancelled',
        ]) . "'";

        DB::statement("ALTER TABLE batches MODIFY COLUMN status ENUM($enum) NOT NULL DEFAULT 'clone'");
    }
};
