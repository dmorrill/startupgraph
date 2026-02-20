<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (! Schema::hasColumn('companies', 'status')) {
                $table->string('status', 20)->default('operating')->after('country');
            }
            if (! Schema::hasColumn('companies', 'closed_at')) {
                $table->date('closed_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('companies', 'acquired_by')) {
                $table->string('acquired_by')->nullable()->after('closed_at');
            }
            if (! Schema::hasColumn('companies', 'import_source')) {
                $table->string('import_source')->nullable()->after('acquired_by');
            }
        });

        // Add index on status
        Schema::table('companies', function (Blueprint $table) {
            $table->index('status');
            $table->index('import_source');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['import_source']);
            $table->dropColumn(['status', 'closed_at', 'acquired_by', 'import_source']);
        });
    }
};
