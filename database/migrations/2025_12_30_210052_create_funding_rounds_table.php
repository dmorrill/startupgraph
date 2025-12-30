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
        Schema::create('funding_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('round_type')->nullable(); // seed, series_a, series_b, etc.
            $table->decimal('amount', 15, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->date('announced_date')->nullable();
            $table->decimal('pre_money_valuation', 15, 2)->nullable();
            $table->string('source_url')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'announced_date']);
        });

        Schema::create('funding_round_investor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funding_round_id')->constrained()->onDelete('cascade');
            $table->foreignId('investor_id')->constrained()->onDelete('cascade');
            $table->boolean('is_lead')->default(false);
            $table->timestamps();

            $table->unique(['funding_round_id', 'investor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('funding_round_investor');
        Schema::dropIfExists('funding_rounds');
    }
};
