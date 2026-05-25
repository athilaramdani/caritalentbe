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

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE notifications DROP CONSTRAINT IF EXISTS notifications_type_check");
            DB::statement("ALTER TABLE notifications ADD CONSTRAINT notifications_type_check CHECK (type IN ('application', 'booking', 'invitation', 'review', 'event', 'talent'))");
        } else {
            Schema::table('notifications', function (Blueprint $table) {
                $table->enum('type', ['application', 'booking', 'invitation', 'review', 'event', 'talent'])->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE notifications DROP CONSTRAINT IF EXISTS notifications_type_check");
            DB::statement("ALTER TABLE notifications ADD CONSTRAINT notifications_type_check CHECK (type IN ('application', 'booking', 'invitation', 'review'))");
        } else {
            Schema::table('notifications', function (Blueprint $table) {
                $table->enum('type', ['application', 'booking', 'invitation', 'review'])->change();
            });
        }
    }
};
