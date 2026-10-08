<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminUserLevelDeleteRequest;
use App\Http\Requests\Admin\AdminUserLevelStoreRequest;
use App\Models\Permission;
use App\Models\UserLevel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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

    public function store(AdminUserLevelStoreRequest $request)
    {
        $data = $request->validated();

        UserLevel::create([
            'name' => $data['name'],
            'visible_name' => $data['visible_name'],
            'is_system' => false,
        ]);

        Cache::forget('level:user_counts');

        return back()->with('success', 'Level created successfully.');
    }

    public function delete(AdminUserLevelDeleteRequest $request)
    {
        $ids = $request->validated()['ids'];
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

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'deleted' => count($deletable), 'msg' => $message]);
        }

        return back()->with($deletable ? 'success' : 'warning', $message);
    }
}
