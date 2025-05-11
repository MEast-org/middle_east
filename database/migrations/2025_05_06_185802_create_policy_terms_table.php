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
        Schema::create('policy_terms', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('locale');
            $table->string('title');
            $table->longText('content');
            $table->timestamps();

            $table->unique(['key', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_terms');
    }
};
