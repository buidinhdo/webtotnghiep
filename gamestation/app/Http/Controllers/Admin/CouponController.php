<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index()
    {
        $search = request()->query('search');
        $status = request()->query('status');
        $type = request()->query('type');

        $query = Coupon::query();

        if ($search) {
            $query->where('code', 'like', "%{$search}%");
        }

        if ($type && in_array($type, ['percentage', 'fixed'], true)) {
            $query->where('type', $type);
        }

        if ($status) {
            if ($status === 'active') {
                $query->where('is_active', true)
                      ->where(function ($q) {
                          $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                      })
                      ->where(function ($q) {
                          $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                      })
                      ->where(function ($q) {
                          $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit');
                      });
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($status === 'expired') {
                $query->whereNotNull('ends_at')->where('ends_at', '<', now());
            } elseif ($status === 'scheduled') {
                $query->whereNotNull('starts_at')->where('starts_at', '>', now());
            } elseif ($status === 'maxed') {
                $query->whereNotNull('usage_limit')->whereColumn('used_count', '>=', 'usage_limit');
            }
        }

        $coupons = $query->orderBy('id', 'asc')
            ->paginate(10)
            ->appends(request()->query());

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('admin.coupons.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code'        => 'required|string|unique:coupons,code',
            'type'        => 'required|in:percentage,fixed',
            'value'       => 'required|numeric|min:0',
            'min_order'   => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'starts_at'   => 'nullable|date',
            'ends_at'     => 'nullable|date',
            'is_active'   => 'boolean',
        ]);

        Coupon::create([
            'code'        => strtoupper($request->code),
            'type'        => $request->type,
            'value'       => $request->value,
            'min_order'   => $request->min_order ?? 0,
            'usage_limit' => $request->usage_limit,
            'starts_at'   => $request->starts_at,
            'ends_at'     => $request->ends_at,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.coupons.index')
                         ->with('success', 'Tạo mã giảm giá thành công!');
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $request->validate([
            'code'        => 'required|string|unique:coupons,code,' . $coupon->id,
            'type'        => 'required|in:percentage,fixed',
            'value'       => 'required|numeric|min:0',
            'min_order'   => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'starts_at'   => 'nullable|date',
            'ends_at'     => 'nullable|date',
            'is_active'   => 'boolean',
        ]);

        $coupon->update([
            'code'        => strtoupper($request->code),
            'type'        => $request->type,
            'value'       => $request->value,
            'min_order'   => $request->min_order ?? 0,
            'usage_limit' => $request->usage_limit,
            'starts_at'   => $request->starts_at,
            'ends_at'     => $request->ends_at,
            'is_active'   => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.coupons.index')
                         ->with('success', 'Cập nhật mã giảm giá thành công!');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return redirect()->route('admin.coupons.index')
                         ->with('success', 'Xóa mã giảm giá thành công!');
    }
}