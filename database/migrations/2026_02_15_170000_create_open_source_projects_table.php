<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('open_source_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('github_url')->unique();
            $table->string('github_owner');
            $table->string('github_repo');
            $table->text('description')->nullable();
            $table->integer('stars')->default(0);
            $table->integer('forks')->default(0);
            $table->integer('watchers')->default(0);
            $table->integer('contributors_count')->nullable();
            $table->string('primary_language')->nullable();
            $table->json('topics')->nullable();
            $table->string('license')->nullable();
            $table->timestamp('last_commit_at')->nullable();
            $table->timestamp('github_created_at')->nullable();
            $table->string('category')->nullable();
            $table->boolean('self_hostable')->default(false);
            $table->boolean('has_commercial_version')->default(false);
            $table->string('commercial_url')->nullable();
            $table->timestamps();

            $table->index('stars');
            $table->index('category');
            $table->index(['github_owner', 'github_repo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('open_source_projects');
    }
};
