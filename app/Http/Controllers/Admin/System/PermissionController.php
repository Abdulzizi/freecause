<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\UserLevel;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    private array $modules = [
        'users' => ['view', 'create', 'edit', 'delete'],
        'petitions' => ['view', 'create', 'edit', 'delete', 'feature'],
        'categories' => ['view', 'create', 'edit', 'delete'],
        'logs' => ['view', 'filter', 'cancel', 'delete', 'empty'],
        'pages' => ['view', 'edit'],
        'spam' => ['view', 'delete'],
    ];

    public function index(Request $request)
    {
        $levels = UserLevel::all();

        $modules = $this->modules;

        $selectedModule = $request->module ?? array_key_first($modules);
        $selectedLevel  = $request->level ?? $levels->first()?->id;

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

    public function store(Request $request)
    {
        $request->validate([
            'module' => 'required',
            'level_id' => 'required',
        ]);

        Permission::where('level_id', $request->level_id)
            ->where('module', $request->module)
            ->delete();

        foreach ($request->input('actions', []) as $action) {
            Permission::create([
                'level_id' => $request->level_id,
                'module' => $request->module,
                'action' => $action,
            ]);
        }

        return back()->with('success', 'permissions updated');
    }
}
