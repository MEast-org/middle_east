<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Kalnoy\Nestedset\NestedSet;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('ar_name', 100);
            $table->string('en_name',100);
            $table->string('icon')->nullable();
            $table->enum('state', ['active', 'inactive'])->default('active');
            $table->integer('sort_order')->default(0); // حقل الترتيب
            NestedSet::columns($table); // تضيف _lft, _rgt, parent_id
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
