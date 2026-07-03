<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EsrbRating;
use Illuminate\Http\Request;

class ESRBController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $esrbRatings = EsrbRating::paginate(15);
        return view('admin.esrb.index', compact('esrbRatings'));
    }

    public function create()
    {
        return view('admin.esrb.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:esrb_ratings',
            'name' => 'required|string|max:255|unique:esrb_ratings',
            'description' => 'nullable|string',
            'age_group' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        EsrbRating::create($validated);
        return redirect()->route('admin.esrb.index')->with('success', 'ESRB đã được thêm.');
    }

    public function edit(EsrbRating $esrb)
    {
        return view('admin.esrb.edit', ['esrbRating' => $esrb]);
    }

    public function update(Request $request, EsrbRating $esrb)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:esrb_ratings,code,' . $esrb->id,
            'name' => 'required|string|max:255|unique:esrb_ratings,name,' . $esrb->id,
            'description' => 'nullable|string',
            'age_group' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $esrb->update($validated);
        return redirect()->route('admin.esrb.index')->with('success', 'ESRB đã được cập nhật.');
    }

    public function destroy(EsrbRating $esrb)
    {
        $esrb->delete();
        return redirect()->route('admin.esrb.index')->with('success', 'ESRB đã được xóa.');
    }
}
