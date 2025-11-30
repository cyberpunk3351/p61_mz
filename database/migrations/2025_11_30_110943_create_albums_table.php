<?php

declare(strict_types=1);

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
        Schema::create('albums', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('year')->nullable();
            $table->integer('rating')->nullable();
            $table->integer('type')->nullable();
            $table->foreignId('file_id')->nullable();
            $table->string('spotify_id')->nullable();
            $table->string('label_id')->nullable();
            $table->string('img_640_url')->nullable();
            $table->string('img_300_url')->nullable();
            $table->string('img_64_url')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('albums');
    }
};
