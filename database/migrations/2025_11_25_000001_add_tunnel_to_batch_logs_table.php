<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('batch_logs', function (Blueprint $table) {
            $table->foreignId('tunnel_id')
                ->nullable()
                ->after('room_id')
                ->constrained()
                ->onDelete('set null');

            $table->index('tunnel_id');
        });
    }

    public function down(): void
    {
        Schema::table('batch_logs', function (Blueprint $table) {
            $table->dropIndex(['tunnel_id']);
            $table->dropConstrainedForeignId('tunnel_id');
        });
    }
};
