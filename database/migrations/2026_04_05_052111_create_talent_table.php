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
        Schema::create('talents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('stage_name');
            $table->decimal('price_min', 15, 2)->nullable();
            $table->decimal('price_max', 15, 2)->nullable();
            $table->string('city');
            $table->text('bio')->nullable();
            $table->string('portfolio_link')->nullable();
            $table->boolean('verified')->default(false);
            $table->float('average_rating')->default(0);
            $table->integer('total_reviews')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('talent');
    }
};
