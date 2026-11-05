<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('audit_logs', 'description')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->string('description')->nullable()->after('module');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('audit_logs', 'description')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }
    }
};
