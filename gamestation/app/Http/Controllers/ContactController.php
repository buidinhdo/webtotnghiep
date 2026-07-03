<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $product = null;
        if ($request->query('product_id')) {
            $product = \App\Models\Product::find($request->query('product_id'));
        }

        $prefillSubject = $request->query('subject');
        $prefillMessage = $request->query('message');

        return view('contact', compact('product', 'prefillSubject', 'prefillMessage'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        if ($request->user()) {
            $data['user_id'] = $request->user()->id;
        }

        ContactMessage::create($data);

        return back()->with('success', 'Đã gửi liên hệ. Chúng tôi sẽ phản hồi sớm.');
    }
}
