<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Publisher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublisherController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $publishers = Publisher::paginate(20);
        return view('admin.publishers.index', compact('publishers'));
    }

    public function create()
    {
        return view('admin.publishers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:publishers,name',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        Publisher::create($validated);

        return redirect()->route('admin.publishers.index')->with('success', 'Nhà phát hành đã được thêm.');
    }

    public function edit(Publisher $publisher)
    {
        return view('admin.publishers.edit', compact('publisher'));
    }

    public function update(Request $request, Publisher $publisher)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:publishers,name,' . $publisher->id,
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $publisher->update($validated);

        $page = $request->input('page', 1);
        return redirect()->route('admin.publishers.index', ['page' => $page])->with('success', 'Nhà phát hành đã được cập nhật.');
    }

    public function destroy(Publisher $publisher)
    {
        $page = request()->input('page', 1);
        $publisher->delete();
        return redirect()->route('admin.publishers.index', ['page' => $page])->with('success', 'Nhà phát hành đã được xóa.');
    }
}
