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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('photo')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->date('birthday')->nullable();
            $table->foreignId('country_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('region_id')->nullable()->constrained()->onDelete('set null');
            $table->string('password')->nullable();
            $table->string('verification_code')->nullable();
            $table->timestamp('code_expires_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            // حقول جديدة لتسجيل الدخول الاجتماعي
            $table->enum('provider', ['email', 'google', 'apple'])->default('email');
            $table->string('provider_id')->nullable()->comment('المعرف الفريد من مزود الخدمة');
            $table->string('provider_token')->nullable()->comment('Token من مزود الخدمة');
            $table->string('provider_refresh_token')->nullable()->comment('Refresh Token من مزود الخدمة');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
