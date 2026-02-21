<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserLevel;
use Illuminate\Http\Request;

class AdminUserLevelsController extends Controller
{
    public function index()
    {
        // $levels = UserLevel::orderBy('id')->get();
        $levels = UserLevel::withCount('users')
    ->orderBy('id')
    ->paginate(20);

        return view('admin.system.user-level', compact('levels'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'alpha_dash', 'unique:user_levels,name'],
            'visible_name' => ['required', 'string', 'max:100'],
        ]);

        UserLevel::create([
            'name' => strtolower($data['name']),
            'visible_name' => $data['visible_name'],
            'is_system' => false,
        ]);

        return back()->with('success', 'Level created successfully.');
    }

    public function delete(Request $request)
    {
        $ids = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ])['ids'];

        UserLevel::whereIn('id', $ids)
            ->where('is_system', false)
            ->delete();

        return back()->with('success', 'Selected levels deleted.');
    }
}
