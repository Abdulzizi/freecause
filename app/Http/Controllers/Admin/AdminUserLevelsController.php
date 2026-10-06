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
        $levels = UserLevel::orderBy('id')->paginate(20);

        // A correlated withCount() scans the 18M-row users table once per
        // level on every render; one grouped pass cached for an hour instead.
        $counts = Cache::remember('level:user_counts', 3600, function () {
            return DB::table('users')
                ->selectRaw('level_id, COUNT(*) as n')
                ->whereNotNull('level_id')
                ->groupBy('level_id')
                ->pluck('n', 'level_id');
        });

        $levels->getCollection()->each(function ($level) use ($counts) {
            $level->users_count = (int) ($counts[$level->id] ?? 0);
        });

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

        Cache::forget('level:user_counts');

        return back()->with('success', 'Level created successfully.');
    }

    public function delete(Request $request)
    {
        $ids = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:user_levels,id'],
        ])['ids'];
        $ids = array_values(array_unique(array_map('intval', $ids)));

        $ownLevelId = admin_user()?->level_id;

        $deletable = DB::transaction(function () use ($ids, $ownLevelId) {
            $deletable = UserLevel::whereIn('id', $ids)
                ->where('is_system', false)
                ->whereNotIn('name', ['user', 'banned'])
                ->whereDoesntHave('users')
                ->when($ownLevelId, fn ($q) => $q->where('id', '!=', $ownLevelId))
                ->lockForUpdate()
                ->pluck('id')
                ->all();

            if (! empty($deletable)) {
                Permission::whereIn('level_id', $deletable)->delete();
                UserLevel::whereIn('id', $deletable)->delete();
            }

            return $deletable;
        });

        if (! empty($deletable)) {

            $modules = Permission::matrix();

            foreach ($deletable as $levelId) {
                foreach ($modules as $module => $actions) {
                    foreach ($actions as $action) {
                        Cache::forget("permissions:{$levelId}:{$module}:{$action}");
                    }
                }
            }

            Cache::forget('level:user_counts');
        }

        $skipped = count($ids) - count($deletable);
        $message = count($deletable)
            ? 'Deleted '.count($deletable).' level(s). '.$skipped.' protected, assigned, or current level(s) were kept.'
            : 'No levels were deleted; protected, assigned, and your current level cannot be deleted.';

        return back()->with($deletable ? 'success' : 'warning', $message);
    }
}
