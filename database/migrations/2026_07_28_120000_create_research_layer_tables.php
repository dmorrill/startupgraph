<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The user-scoped research layer: lists, screens, notes, and signals.
 *
 * Everything here carries user_id from day one (multi-tenant by design)
 * and created_via provenance (which agent/token wrote it). The company
 * graph itself stays global — see docs/restart-plan-ios-first.md.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('created_via')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'name']);
        });

        Schema::create('list_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->text('rationale')->nullable();
            $table->string('created_via')->nullable();
            $table->timestamps();
            $table->unique(['list_id', 'company_id']);
        });

        Schema::create('screens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('criteria');
            $table->json('snapshot')->nullable();
            $table->integer('result_count')->nullable();
            $table->timestamp('refreshed_at')->nullable();
            $table->string('created_via')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'name']);
        });

        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('body');
            $table->string('created_via')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'company_id']);
        });

        Schema::create('signals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->string('title');
            $table->text('body')->nullable();
            $table->json('payload')->nullable();
            $table->string('created_via')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signals');
        Schema::dropIfExists('notes');
        Schema::dropIfExists('screens');
        Schema::dropIfExists('list_entries');
        Schema::dropIfExists('lists');
    }
};
