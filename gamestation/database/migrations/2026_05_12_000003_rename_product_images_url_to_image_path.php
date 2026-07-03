<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rename url to image_path để consistency
        Schema::table('product_images', function (Blueprint $table) {
            $table->renameColumn('url', 'image_path');
        });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->renameColumn('image_path', 'url');
        });
    }
};
