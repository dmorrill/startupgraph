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
        Schema::table('companies', function (Blueprint $table) {
            $table->timestamp('headcount_fetched_at')->nullable()->after('profile_refreshed_at');
            $table->unsignedSmallInteger('headcount_fetch_day')->nullable()->after('headcount_fetched_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['headcount_fetched_at', 'headcount_fetch_day']);
        });
    }
};
