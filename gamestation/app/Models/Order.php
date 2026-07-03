<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    private const STATUS_LABELS = [
        'pending' => 'Chờ xử lý',
        'processing' => 'Đang xử lý',
        'shipped' => 'Đang giao',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
    ];

    private const PAYMENT_STATUS_LABELS = [
        'unpaid' => 'Chưa thanh toán',
        'paid' => 'Đã thanh toán',
        'refunded' => 'Đã hoàn tiền',
        'failed' => 'Thanh toán thất bại',
        'pending' => 'Đang chờ thanh toán',
    ];

    protected $fillable = [
        'user_id',
        'status',
        'payment_method',
        'payment_status',
        'subtotal',
        'discount',
        'shipping_fee',
        'shipping_distance_km',
        'total',
        'coupon_code',
        'shipping_name',
        'shipping_phone',
        'shipping_address',
        'shipping_method',
        'notes',
        'placed_at',
        'completed_at',
    ];

    protected $casts = [
        'placed_at' => 'datetime',
        'completed_at' => 'datetime',
        'shipping_fee' => 'decimal:2',
        'shipping_distance_km' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Backwards-compatible accessor for templates using `total_amount`
    public function getTotalAmountAttribute()
    {
        return $this->total;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst((string) $this->status);
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return self::PAYMENT_STATUS_LABELS[$this->payment_status] ?? ucfirst((string) $this->payment_status);
    }
}
