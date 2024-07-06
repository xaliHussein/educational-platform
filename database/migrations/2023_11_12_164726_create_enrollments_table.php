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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid("category_id");
            $table->uuid("user_id");
            $table->uuid("teacher_id");
            $table->string("order_id")->nullable();
            $table->integer("payment_type"); // 0 Cash - 1 zainCash
            $table->integer("status"); // 0 fail - 1 success
            $table->double("price");
            $table->text("invoice")->nullable();
            $table->string("subscription_time");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
