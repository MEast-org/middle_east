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
        Schema::create('job_opportunities', function (Blueprint $table) {
            $table->id();

            $table->morphs('publisher');//new
            $table->string('name');


            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->foreignId('country_id')->nullable()->constrained('countries')->onDelete('set null');
            $table->foreignId('region_id')->nullable()->constrained('regions')->onDelete('set null');
            $table->text('description')->nullable();
            $table->decimal('min_salary', 10, 3)->nullable();//new
            $table->decimal('max_salary', 10, 3)->nullable();//new
            $table->date('starts_at')->nullable();//new
            $table->date('expires_at')->nullable();
            // $table->enum('type', ['full_time', 'part_time', 'contract', 'internship', 'remote']);
            $table->string('type');
            $table->json('social_links')->nullable();//new
            $table->enum('state', ['active', 'inactive'])->default('active');//new

            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('shares')->default(0);
            $table->unsignedInteger('applicants')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_opportunities');
    }
};
