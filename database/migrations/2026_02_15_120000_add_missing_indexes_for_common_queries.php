<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add indexes for frequently queried columns that were missing indexes.
     *
     * - companies.country: used in filter dropdowns and WHERE clauses
     * - funding_rounds.announced_date: used in date range filters (funded_after/funded_before)
     * - funding_rounds.round_type: used in filtering and grouping by round type
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->index('country');
        });

        Schema::table('funding_rounds', function (Blueprint $table) {
            $table->index('announced_date');
            $table->index('round_type');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropIndex(['country']);
        });

        Schema::table('funding_rounds', function (Blueprint $table) {
            $table->dropIndex(['announced_date']);
            $table->dropIndex(['round_type']);
        });
    }
};
