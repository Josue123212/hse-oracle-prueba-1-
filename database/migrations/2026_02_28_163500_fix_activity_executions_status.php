<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('activity_executions')
            ->where('estado', 'pendiente')
            ->update(['estado' => 'programado']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No easy reverse, as 'programado' might have been set intentionally.
    }
};
