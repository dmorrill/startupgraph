<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_imports', function (Blueprint $table) {
            $table->id();
            $table->string('source', 50)->index();
            $table->string('batch_id', 100)->nullable()->index();
            $table->integer('companies_created')->default(0);
            $table->integer('companies_updated')->default(0);
            $table->integer('companies_skipped')->default(0);
            $table->integer('total_processed')->default(0);
            $table->string('status', 20)->default('running'); // running, completed, failed
            $table->integer('last_page')->nullable();
            $table->string('last_offset')->nullable();
            $table->json('metadata')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_imports');
    }
};
