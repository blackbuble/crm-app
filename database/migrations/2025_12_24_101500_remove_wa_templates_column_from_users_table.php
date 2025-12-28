<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'wa_templates')) {
                $table->dropColumn('wa_templates');
            }
            if (Schema::hasColumn('users', 'wa_template')) {
                $table->dropColumn('wa_template');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('wa_templates')->nullable()->after('area');
        });
    }
};
