<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('talent_id')->constrained('users')->onDelete('cascade');
            $table->enum('source', ['apply', 'invitation']);
            $table->text('message')->nullable();
            $table->decimal('proposed_price', 15, 2)->nullable();
            $table->decimal('offered_price', 15, 2)->nullable(); // Used when it's an invitation
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->timestamps();
            
            // Talent cannot apply twice or be invited twice dynamically for the same event
            $table->unique(['event_id', 'talent_id', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
