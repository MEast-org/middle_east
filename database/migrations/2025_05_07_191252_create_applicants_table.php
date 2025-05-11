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
        Schema::create('applicants', function (Blueprint $table) {
            $table->id();

            $table->morphs('applicant'); // user OR company
            $table->foreignId('opportunity_id')->constrained('job_opportunities')->onDelete('cascade');

            $table->string('name'); // اسم المقدم (للعرض)
            $table->text('description')->nullable(); // نبذة أو رسالة المقدم
            $table->string('cv')->nullable(); // مسار ملف الـ CV المخزن

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicants');
    }
};
