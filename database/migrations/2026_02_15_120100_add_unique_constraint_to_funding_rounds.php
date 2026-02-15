<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add a unique constraint to prevent exact duplicate funding rounds.
     * Same company + same round type + same announced date = duplicate.
     */
    public function up(): void
    {
        Schema::table('funding_rounds', function (Blueprint $table) {
            $table->unique(['company_id', 'round_type', 'announced_date'], 'funding_rounds_company_round_date_unique');
        });
    }

    public function down(): void
    {
        Schema::table('funding_rounds', function (Blueprint $table) {
            $table->dropUnique('funding_rounds_company_round_date_unique');
        });
    }
};
