<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        // 1. Drop check constraint first for PostgreSQL to prevent check constraint violations when updating status
        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE events DROP CONSTRAINT IF EXISTS events_status_check");
        }

        // 2. Update existing rows with old status values to the new ones
        DB::table('events')->where('status', 'draft')->update(['status' => 'dibuka']);
        DB::table('events')->where('status', 'open')->update(['status' => 'dibuka']);
        DB::table('events')->where('status', 'closed')->update(['status' => 'ditutup']);
        DB::table('events')->where('status', 'completed')->update(['status' => 'selesai']);
        DB::table('events')->where('status', 'cancelled')->update(['status' => 'dibatalkan']);

        // 3. Adjust constraint/column based on DB driver
        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE events ADD CONSTRAINT events_status_check CHECK (status IN ('dibuka', 'ditutup', 'selesai', 'dibatalkan'))");
            DB::statement("ALTER TABLE events ALTER COLUMN status SET DEFAULT 'dibuka'");
        } else {
            Schema::table('events', function (Blueprint $table) {
                $table->enum('status', ['dibuka', 'ditutup', 'selesai', 'dibatalkan'])->default('dibuka')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        // 1. Drop check constraint first for PostgreSQL to prevent check constraint violations when updating status
        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE events DROP CONSTRAINT IF EXISTS events_status_check");
        }

        // 2. Update existing rows with new status values to the old ones
        DB::table('events')->where('status', 'dibuka')->update(['status' => 'open']);
        DB::table('events')->where('status', 'ditutup')->update(['status' => 'closed']);
        DB::table('events')->where('status', 'selesai')->update(['status' => 'completed']);
        DB::table('events')->where('status', 'dibatalkan')->update(['status' => 'cancelled']);

        // 3. Revert constraint/column based on DB driver
        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE events ADD CONSTRAINT events_status_check CHECK (status IN ('draft', 'open', 'closed', 'completed', 'cancelled'))");
            DB::statement("ALTER TABLE events ALTER COLUMN status SET DEFAULT 'draft'");
        } else {
            Schema::table('events', function (Blueprint $table) {
                $table->enum('status', ['draft', 'open', 'closed', 'completed', 'cancelled'])->default('draft')->change();
            });
        }
    }
};
