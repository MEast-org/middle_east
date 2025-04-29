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
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('en_name', 100);
            $table->string('ar_name', 100);
            $table->enum('type', [
                'text',
                'number',
                'textarea',
                'select',
                'checkbox',
                'radio',
                'date',
                'file'
            ]);
            $table->integer('min_length')->nullable();
            $table->integer('max_length')->nullable();
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(true);
            $table->string('custom_icon')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_fields');
    }
};
