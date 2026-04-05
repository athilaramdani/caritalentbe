<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->decimal('budget', 15, 2);
            $table->date('event_date');
            $table->string('venue_name');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('city');
            $table->enum('status', ['draft', 'open', 'closed', 'completed', 'cancelled'])->default('draft');
            $table->timestamps();
        });
        
        Schema::create('event_genre', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->unsignedBigInteger('genre_id'); // Assuming genres table will be created by Athila
            $table->timestamps();
            $table->unique(['event_id', 'genre_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_genre');
        Schema::dropIfExists('events');
    }
};
