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
        if (!Schema::hasColumn('contact_messages', 'admin_reply')) {
            Schema::table('contact_messages', function (Blueprint $table) {
                $table->text('admin_reply')->nullable()->after('message');
                $table->timestamp('admin_replied_at')->nullable()->after('admin_reply');
                $table->string('status')->default('new')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('contact_messages', 'admin_reply')) {
            Schema::table('contact_messages', function (Blueprint $table) {
                $table->dropColumn(['admin_reply', 'admin_replied_at']);
            });
        }
    }
};
