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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');//news
            $table->string('email')->unique();
            $table->string('password')->nullable();//update
            $table->foreignId('country_id')->nullable()->constrained('countries')->onDelete('set null');
            $table->foreignId('region_id')->nullable()->constrained('regions')->onDelete('set null');
            $table->string('phone')->nullable();
            $table->string('logo')->nullable();
            $table->string('trade_log')->nullable(); // ملف السجل التجاري
            $table->enum('state', ['active', 'inactive', 'pending'])->default('active');
            //new
            $table->string('verification_code')->nullable();
            $table->timestamp('code_expires_at')->nullable();
            $table->timestamp('verified_at')->nullable();

            $table->string('fcm_token')->nullable();

              // حقول جديدة لتسجيل الدخول الاجتماعي
              $table->enum('provider', ['email', 'google', 'apple'])->default('email');
              $table->string('provider_id')->nullable();//->comment('المعرف الفريد من مزود الخدمة');
              $table->string('provider_token')->nullable();//->comment('Token من مزود الخدمة');
              $table->string('provider_refresh_token')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
