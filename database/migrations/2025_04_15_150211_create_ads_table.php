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
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
             $table->morphs('publisher');

             $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
             $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
             $table->foreignId('category_id')->constrained()->onDelete('cascade');

             $table->decimal('latitude', 10, 8)->nullable();
             $table->decimal('longitude', 11, 8)->nullable();
             $table->text('description')->nullable(); //new

             $table->enum('state', ['inactive','active'])->default('active');//new
             $table->json('social_links')->nullable();//new

             $table->decimal('price', 10, 2)->nullable();
             
             $table->unsignedInteger('views')->default(0);
             $table->unsignedInteger('shares')->default(0);
             $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
