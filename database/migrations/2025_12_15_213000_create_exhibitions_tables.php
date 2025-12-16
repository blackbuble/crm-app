<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exhibitions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('booth_cost', 15, 2)->default(0);
            $table->decimal('operational_cost', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('exhibition_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->foreignId('exhibition_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_on_the_spot')->default(false)->comment('Closing deal happened on the spot');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropForeign(['exhibition_id']);
            $table->dropColumn(['exhibition_id', 'is_on_the_spot']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['exhibition_id']);
            $table->dropColumn('exhibition_id');
        });

        Schema::dropIfExists('exhibitions');
    }
};
