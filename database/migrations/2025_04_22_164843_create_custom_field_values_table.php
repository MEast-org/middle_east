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
        Schema::create('custom_field_values', function (Blueprint $table) {
            $table->id();

            // إضافة الحقلين للمورف
           // $table->morphs('owner_table');
            $table->foreignId('ad_id')->constrained('ads')->onDelete('cascade');

            $table->foreignId('custom_field_id')->constrained()->onDelete('cascade');
            $table->json('value')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_field_values');
    }
};
