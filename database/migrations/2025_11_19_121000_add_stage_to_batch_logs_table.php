<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('batch_logs', function (Blueprint $table) {
            $table->string('stage', 50)->nullable()->after('batch_id');
            $table->index('stage');
        });

        // Backfill existing logs with their batch's current stage.
        DB::table('batch_logs')
            ->chunkById(100, function ($logs) {
                foreach ($logs as $log) {
                    $stage = DB::table('batches')
                        ->where('id', $log->batch_id)
                        ->value('status');

                    DB::table('batch_logs')
                        ->where('id', $log->id)
                        ->update(['stage' => $stage]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('batch_logs', function (Blueprint $table) {
            $table->dropIndex(['stage']);
            $table->dropColumn('stage');
        });
    }
};
