<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PermissionStoreRequest;
use App\Models\Permission;
use App\Models\UserLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $levels = UserLevel::all();

        $modules = Permission::matrix();

        $selectedModule = $request->query('module');
        if (! is_string($selectedModule) || ! array_key_exists($selectedModule, $modules)) {
            $selectedModule = array_key_first($modules);
        }

        $selectedLevel = $request->query('level');
        if (! $levels->contains('id', (int) $selectedLevel)) {
            $selectedLevel = $levels->first()?->id;
        } else {
            $selectedLevel = (int) $selectedLevel;
        }

        $permissions = Permission::where('level_id', $selectedLevel)
            ->where('module', $selectedModule)
            ->pluck('action')
            ->toArray();

        return view('admin.system.permission', compact(
            'levels',
            'modules',
            'selectedModule',
            'selectedLevel',
            'permissions'
        ));
    }

    public function store(PermissionStoreRequest $request)
    {
        $data = $request->validated();

        $module = $data['module'];
        $levelId = (int) $data['level_id'];
        $allowedActions = Permission::matrix()[$module];

        $level = UserLevel::findOrFail($levelId);

        if ($level->is_system) {
            return back()->withErrors(['level_id' => 'System level permissions cannot be changed.']);
        }

        $actions = array_values(array_intersect($data['actions'] ?? [], $allowedActions));

        if ($module === 'system'
            && ! in_array('manage', $actions, true)
            && admin_user()?->level_id === $levelId) {
            return back()->withErrors(['actions' => 'You cannot remove system access from your own level.']);
        }

        DB::transaction(function () use ($levelId, $module, $actions) {
            Permission::where('level_id', $levelId)
                ->where('module', $module)
                ->delete();

            foreach ($actions as $action) {
                Permission::create([
                    'level_id' => $levelId,
                    'module' => $module,
                    'action' => $action,
                ]);
            }
        });

        foreach ($allowedActions as $action) {
            Cache::forget("permissions:{$levelId}:{$module}:{$action}");
        }

        return back()->with('success', 'permissions updated');
    }
}
