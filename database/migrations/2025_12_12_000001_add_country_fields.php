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
        // Add country and country_code to customers table
        Schema::table('customers', function (Blueprint $table) {
            $table->string('country', 100)->nullable()->after('address');
            $table->string('country_code', 5)->nullable()->after('country')->comment('Phone country code, e.g., +62, +1, +44');
        });

        // Add country to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('country', 100)->nullable()->after('email');
            $table->string('country_code', 5)->nullable()->after('country')->comment('Phone country code, e.g., +62, +1, +44');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['country', 'country_code']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['country', 'country_code']);
        });
    }
};
