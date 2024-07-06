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
        Schema::create('lessons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid("category_id");
            $table->uuid("user_id");
            $table->string('title');
            $table->text('content')->nallable();
            $table->integer("upload_type"); // 0 youtub - 1 platform
            $table->string("video_url")->nallable();
            $table->string('video')->nallable();
            $table->string('image')->nallable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
