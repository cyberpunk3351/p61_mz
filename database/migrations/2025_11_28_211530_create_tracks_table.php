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
        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('release_date')->nullable();
            $table->integer('rating')->nullable();
            $table->integer('genre_id')->nullable();
            $table->integer('parent_genre_id')->nullable();
            $table->string('spotify_id')->nullable();
            $table->string('isrc')->nullable();
            $table->string('label_id')->nullable();
//            $table->foreign('album_id')->references('id')->on('albums')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracks');
    }
};
