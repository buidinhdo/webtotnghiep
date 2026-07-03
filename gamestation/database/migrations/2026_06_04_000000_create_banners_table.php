<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('image_path');
            $table->integer('order_column')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Migrate existing banners from the old json file
        $orderFile = storage_path('app/banner_order.json');
        if (file_exists($orderFile)) {
            $orderMap = json_decode(file_get_contents($orderFile), true);
            if (is_array($orderMap)) {
                foreach ($orderMap as $filename => $order) {
                    $filePath = public_path('images/banners/' . $filename);
                    if (file_exists($filePath)) {
                        DB::table('banners')->insert([
                            'image_path' => 'images/banners/' . $filename,
                            'order_column' => (int) $order,
                            'is_active' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
