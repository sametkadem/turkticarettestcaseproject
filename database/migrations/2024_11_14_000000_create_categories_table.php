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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('display_name')->nullable();
            $table->string('path')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->boolean('is_leaf')->default(false);
            $table->boolean('is_root')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Foreign key constraint for self-referencing parent_id
            $table->foreign('parent_id')->references('id')->on('categories');
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
