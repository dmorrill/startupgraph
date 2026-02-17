<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('twitter_url')->nullable()->after('linkedin_url');
            $table->string('github_url')->nullable()->after('twitter_url');
            $table->string('crunchbase_url')->nullable()->after('github_url');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['twitter_url', 'github_url', 'crunchbase_url']);
        });
    }
};
