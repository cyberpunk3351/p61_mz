<?php

use App\Enums\FileType;
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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', array_column(FileType::cases(), 'value'))->nullable();
            $table->string('hash')->unique();
            $table->boolean('success')->default(false);
            $table->string('morphable_type');
            $table->unsignedBigInteger('morphable_id');
            $table->index(['morphable_id', 'morphable_type']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
