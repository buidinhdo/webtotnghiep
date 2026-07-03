<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Only drop `publisher` column if it exists and `publisher_id` is present.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('products')) {
            return;
        }

        if (Schema::hasColumn('products', 'publisher') && Schema::hasColumn('products', 'publisher_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('publisher');
            });
        }
    }

    /**
     * Reverse the migrations.
     * Note: reverse cannot restore original string values.
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('products')) {
            return;
        }

        if (!Schema::hasColumn('products', 'publisher')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('publisher')->nullable()->after('publisher_id');
            });
        }
    }
};
