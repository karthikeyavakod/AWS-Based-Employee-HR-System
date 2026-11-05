<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('audit_logs', 'subject_name')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->string('subject_name')->nullable()->after('subject_type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('audit_logs', 'subject_name')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->dropColumn('subject_name');
            });
        }
    }
};
