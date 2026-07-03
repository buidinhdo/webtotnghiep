<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'publisher_id')) {
                $table->unsignedBigInteger('publisher_id')->nullable()->after('players');
                $table->foreign('publisher_id')->references('id')->on('publishers')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'publisher_id')) {
                $table->dropForeign(['publisher_id']);
                $table->dropColumn('publisher_id');
            }
        });
    }
};
