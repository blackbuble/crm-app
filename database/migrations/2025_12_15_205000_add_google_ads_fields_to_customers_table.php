<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('gad_source')->nullable()->comment('Google Ads Source');
            $table->string('gad_campaign')->nullable()->comment('Google Ads Campaign ID');
            $table->string('gbraid')->nullable()->comment('Google Ads Web-to-App Measurement');
            $table->string('wbraid')->nullable()->comment('Google Ads Web Measurement');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'gad_source', 
                'gad_campaign', 
                'gbraid',
                'wbraid'
            ]);
        });
    }
};
