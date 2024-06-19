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
        Schema::create('course__categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid("course_id");
            $table->uuid("user_id");
            $table->string('image');
            $table->string('title');
            $table->string('time_course');
            $table->text('description')->nullable();
            $table->double("price")->nullable();
            $table->integer("course_type"); // 0 free - 1 payment
            $table->double("offer")->nullable();
            $table->date("offer_expired")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course__categories');
    }
};
