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
        Schema::create('purchase_codes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid("category_id");
            $table->uuid("teacher_id");
            $table->integer('status')->default(0);
            $table->string('code');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_codes');
    }
};
