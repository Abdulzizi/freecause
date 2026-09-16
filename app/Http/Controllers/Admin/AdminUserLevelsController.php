<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\UserLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
        $request->merge(['name' => strtolower(trim((string) $request->input('name')))]);

        $data = $request->validate([
            'name' => ['required', 'alpha_dash', 'max:50', 'unique:user_levels,name', Rule::notIn(['admin', 'superadmin', 'system', 'root'])],
            'visible_name' => ['required', 'string', 'max:100'],
        ]);

        UserLevel::create([
            'name' => $data['name'],
            'visible_name' => $data['visible_name'],
            'is_system' => false,
        ]);

        return back()->with('success', 'Level created successfully.');
    }

    public function delete(Request $request)
    {
        $ids = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:user_levels,id'],
        ])['ids'];

        $ownLevelId = admin_user()?->level_id;

        $deletable = UserLevel::whereIn('id', $ids)
            ->where('is_system', false)
            ->when($ownLevelId, fn ($q) => $q->where('id', '!=', $ownLevelId))
            ->pluck('id')
            ->all();

        if (! empty($deletable)) {
            DB::transaction(function () use ($deletable) {
                Permission::whereIn('level_id', $deletable)->delete();
                UserLevel::whereIn('id', $deletable)->delete();
            });

            $modules = Permission::matrix();

            foreach ($deletable as $levelId) {
                foreach ($modules as $module => $actions) {
                    foreach ($actions as $action) {
                        Cache::forget("permissions:{$levelId}:{$module}:{$action}");
                    }
                }
            }
        }

        return back()->with('success', 'Selected levels deleted.');
    }
}
