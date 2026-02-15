<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('is_indie')->default(false)->after('category');
            $table->boolean('is_open_source')->default(false)->after('is_indie');
            $table->integer('github_stars')->nullable()->after('is_open_source');
            $table->boolean('solo_builder')->default(false)->after('github_stars');
            $table->text('tech_stack')->nullable()->after('solo_builder');
            $table->string('submitted_by')->nullable()->after('tech_stack');
            $table->string('submission_url')->nullable()->after('submitted_by');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'is_indie',
                'is_open_source',
                'github_stars',
                'solo_builder',
                'tech_stack',
                'submitted_by',
                'submission_url',
            ]);
        });
    }
};
