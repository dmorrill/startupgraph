<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_oss_alternatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('oss_project_id')->constrained('open_source_projects')->cascadeOnDelete();
            $table->enum('relationship_type', [
                'alternative_to',
                'fork_of',
                'built_on',
                'commercial_version_of',
            ]);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'oss_project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_oss_alternatives');
    }
};
