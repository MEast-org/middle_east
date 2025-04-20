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
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->morphs('publisher');

            $table->string('name');
            $table->foreignId('category_id')->constrained()->cascadeOnDelete(); // الفئة الرئيسية
            $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete(); // المنطقة
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete(); // الدولة

            $table->text('description')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();

            $table->enum('status', [ 'pending','active', 'completed', 'expired'])->default('pending');


            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('whatsapp')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auctions');
    }
};
