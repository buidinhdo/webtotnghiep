<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Cập nhật các đơn có total = 0 bằng tổng của order_items.total
        DB::statement(<<<'SQL'
            UPDATE orders
            SET total = (
                SELECT COALESCE(SUM(total), 0)
                FROM order_items
                WHERE order_items.order_id = orders.id
            )
            WHERE total = 0;
        SQL
        );
    }

    public function down(): void
    {
        // Không làm gì khi rollback
    }
};
