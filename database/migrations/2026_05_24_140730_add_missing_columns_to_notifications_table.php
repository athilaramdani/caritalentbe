<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'action')) {
                $table->string('action')->nullable()->after('type');
            }
            if (!Schema::hasColumn('notifications', 'reference_type')) {
                $table->string('reference_type')->nullable()->after('action');
            }
            if (!Schema::hasColumn('notifications', 'data')) {
                $table->json('data')->nullable()->after('reference_id');
            }
            if (!Schema::hasColumn('notifications', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('is_read');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasColumn('notifications', 'action')) {
                $table->dropColumn('action');
            }
            if (Schema::hasColumn('notifications', 'reference_type')) {
                $table->dropColumn('reference_type');
            }
            if (Schema::hasColumn('notifications', 'data')) {
                $table->dropColumn('data');
            }
            if (Schema::hasColumn('notifications', 'read_at')) {
                $table->dropColumn('read_at');
            }
        });
    }
};
