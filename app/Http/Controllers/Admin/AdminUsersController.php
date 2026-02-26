<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLevel;
use App\Support\ApproxRows;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Language;
use App\Support\Locale;

class AdminUsersController extends Controller
{
    use ApproxRows;

    public function index(Request $request)
    {
        $filters = [
            'id'         => trim((string) $request->query('id', '')),
            'first_name' => trim((string) $request->query('first_name', '')),
            'last_name'  => trim((string) $request->query('last_name', '')),
            'email'      => trim((string) $request->query('email', '')),
            'ip'         => trim((string) $request->query('ip', '')),
            'level'      => trim((string) $request->query('level', '')),
            'locale'     => trim((string) $request->query('locale', '')),
        ];

        $q = User::with('level');

        if ($filters['id'] !== '') {
            $q->where('id', (int) $filters['id']);
        }

        if ($filters['first_name'] !== '') {
            $q->where('first_name', 'like', "%{$filters['first_name']}%");
        }

        if ($filters['last_name'] !== '') {
            $q->where('last_name', 'like', "%{$filters['last_name']}%");
        }

        if ($filters['email'] !== '') {
            $q->where('email', 'like', "%{$filters['email']}%");
        }

        if ($filters['locale'] !== '') {
            $fullLocale = Locale::toFull($filters['locale']);
            $q->where('locale', $fullLocale);
        }

        if ($filters['ip'] !== '') {
            $q->where('ip', 'like', "%{$filters['ip']}%");
        }

        if ($filters['level'] !== '') {
            $q->whereHas('level', function ($query) use ($filters) {
                $query->where('name', $filters['level']);
            });
        }

        $users = $q->orderByDesc('id')->paginate(25)->withQueryString();
        $approxTotal = $this->approxTableRows('users');

        $levels = UserLevel::pluck('name', 'name')->toArray();
        $levels = ['' => '(Level)'] + $levels;

        $languages = Language::where('is_active', true)->get();

        $locales = ['' => '(Language)'];

        foreach ($languages as $lang) {
            $locales[$lang->code] = $lang->name;
        }

        $selectedUser = null;

        if ($request->query('select')) {
            $selectedUser = User::with('level')
                ->find((int) $request->query('select'));
        }

        return view('admin.users.index', compact(
            'users',
            'filters',
            'levels',
            'locales',
            'approxTotal',
            'selectedUser'
        ));
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'id'         => ['required', 'integer'],
            'username'   => ['nullable', 'string', 'max:120'],
            'first_name' => ['nullable', 'string', 'max:120'],
            'last_name'  => ['nullable', 'string', 'max:120'],
            'password'   => ['nullable', 'string', 'min:6'],
            'level'      => ['nullable', 'string'],
            'email'      => ['nullable', 'email'],
            'locale'     => ['nullable', 'string'],
            'verified'   => ['nullable'],
        ]);

        $user = User::find($data['id']);

        if (!$user) {
            return back()->withErrors(['id' => 'User not found']);
        }

        if ($request->filled('level')) {
            $level = UserLevel::where('name', $data['level'])->first();
            if ($level) {
                $user->level_id = $level->id;
            }
        }

        if (auth()->id() == $user->id && $user->hasLevel('banned')) {
            return back()->withErrors(['level' => 'You cannot ban yourself.']);
        }

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->name       = $data['username'] ?? $user->name;
        $user->first_name = $data['first_name'] ?? $user->first_name;
        $user->last_name  = $data['last_name'] ?? $user->last_name;
        $user->email      = $data['email'] ?? $user->email;
        $user->locale     = $data['locale'] ?? $user->locale;
        $user->verified   = $request->boolean('verified');

        $user->save();

        return redirect()
            ->route('admin.users', ['select' => $user->id])
            ->with('success', 'saved');
    }

    public function bulkBan(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) return response()->json(['ok' => false]);

        $bannedLevel = UserLevel::where('name', 'banned')->first();

        // User::whereIn('id', $ids)
        //     ->where('id', '!=', auth()->id())
        //     ->update(['level_id' => $bannedLevel->id]);

        User::whereIn('id', $ids)
            ->where('id', '!=', auth()->id())
            ->whereHas('level', function ($q) {
                $q->where('name', '!=', 'admin');
            })
            ->update(['level_id' => $bannedLevel->id]);

        return response()->json(['ok' => true]);
    }

    public function bulkUnban(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) return response()->json(['ok' => false]);

        $userLevel = UserLevel::where('name', 'user')->first();

        User::whereIn('id', $ids)
            ->update(['level_id' => $userLevel->id]);

        return response()->json(['ok' => true]);
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json(['ok' => false]);
        }

        $adminLevelId = UserLevel::where('name', 'admin')->value('id');

        $deleted = User::whereIn('id', $ids)
            ->where('id', '!=', auth()->id())
            ->where(function ($q) use ($adminLevelId) {
                $q->whereNull('level_id')
                    ->orWhere('level_id', '!=', $adminLevelId);
            })
            ->delete();

        return response()->json([
            'ok' => $deleted > 0,
            'deleted' => $deleted
        ]);
    }
}
