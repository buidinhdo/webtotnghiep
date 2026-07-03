<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $customers = User::where('is_admin', false)->paginate(15);
        return view('admin.customers.index', compact('customers'));
    }

    public function show(User $customer)
    {
        return view('admin.customers.show', compact('customer'));
    }

    public function toggleStatus(Request $request, User $customer)
    {
        $customer->update(['is_active' => !$customer->is_active]);
        return redirect()->route('admin.customers.index')->with('success', 'Trạng thái khách hàng đã được cập nhật.');
    }
}
