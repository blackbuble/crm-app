<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('source')->nullable()->after('status');
        });

        Schema::create('ad_spends', function (Blueprint $table) {
            $table->id();
            $table->string('platform'); // Meta, Google, TikTok, etc.
            $table->string('campaign_name')->nullable();
            $table->date('date');
            $table->decimal('amount', 15, 2);
            $table->integer('impressions')->nullable();
            $table->integer('clicks')->nullable();
            $table->integer('leads')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('source');
        });
        Schema::dropIfExists('ad_spends');
    }
};
