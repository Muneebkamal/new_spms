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
        Schema::create('photo_ais', function (Blueprint $table) {
            $table->id();
            $table->integer('photo_id')->nullable();
            $table->string('img_name')->nullable();
            $table->string('preset')->nullable();
            $table->string('style')->nullable();
            $table->string('prompt')->nullable();
            $table->string('code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photo_ais');
    }
};
