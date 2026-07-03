<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration will create missing publishers from distinct product `publisher` strings
     * and populate `products.publisher_id` accordingly. It does not drop the old `publisher` column.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('products') || !Schema::hasTable('publishers')) {
            // nothing to do
            return;
        }

        $names = DB::table('products')
            ->whereNotNull('publisher')
            ->where('publisher', '<>', '')
            ->distinct()
            ->pluck('publisher');

        foreach ($names as $name) {
            $name = trim($name);
            if ($name === '') {
                continue;
            }

            $existing = DB::table('publishers')->where('name', $name)->first();
            if ($existing) {
                $publisherId = $existing->id;
            } else {
                $publisherId = DB::table('publishers')->insertGetId([
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('products')
                ->where('publisher', $name)
                ->update(['publisher_id' => $publisherId]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * This will nullify `publisher_id` on products that match publishers table.
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('products') || !Schema::hasTable('publishers')) {
            return;
        }

        // Nullify publisher_id for all products
        DB::table('products')->update(['publisher_id' => null]);
    }
};
