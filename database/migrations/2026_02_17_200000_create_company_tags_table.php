<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('type')->default('category'); // category, industry, business_model
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('company_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_tag');
        Schema::dropIfExists('tags');
    }
};
