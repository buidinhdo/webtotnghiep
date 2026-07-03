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
        Schema::table('products', function (Blueprint $table) {
            // Make esrb nullable
            $table->string('esrb')->nullable()->change();
            
            // Add game specification columns
            $table->string('genre')->nullable()->after('esrb');
            $table->string('release_date')->nullable()->after('genre');
            $table->string('players')->nullable()->after('release_date');
            $table->string('publisher')->nullable()->after('players');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Revert esrb change
            $table->string('esrb')->default('E')->change();
            
            // Drop added columns
            $table->dropColumn(['genre', 'release_date', 'players', 'publisher']);
        });
    }
};
