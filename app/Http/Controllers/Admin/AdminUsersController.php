<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\User;
use App\Models\UserLevel;
use App\Support\ApproxRows;
use App\Support\Locale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUsersController extends Controller
{
    use ApproxRows;

    public function index(Request $request)
    {
        $filters = [
            'id' => trim((string) $request->query('id', '')),
            'first_name' => trim((string) $request->query('first_name', '')),
            'last_name' => trim((string) $request->query('last_name', '')),
            'nickname' => trim((string) $request->query('nickname', '')),
            'email' => trim((string) $request->query('email', '')),
            'ip' => trim((string) $request->query('ip', '')),
            'level' => trim((string) $request->query('level', '')),
            'locale' => trim((string) $request->query('locale', '')),
            'status' => trim((string) $request->query('status', '')),
            'registered_from' => trim((string) $request->query('registered_from', '')),
            'registered_to' => trim((string) $request->query('registered_to', '')),
        ];

        $q = User::with('level');

        if ($filters['id'] !== '') {
            $q->where('id', (int) $filters['id']);
        }

        if ($filters['first_name'] !== '') {
            $q->where('first_name', 'like', "{$filters['first_name']}%");
        }

        if ($filters['last_name'] !== '') {
            $q->where('last_name', 'like', "{$filters['last_name']}%");
        }

        if ($filters['nickname'] !== '') {
            $q->where('nickname', 'like', "{$filters['nickname']}%");
        }

        if ($filters['email'] !== '') {
            $q->where('email', 'like', "{$filters['email']}%");
        }

        if ($filters['locale'] !== '') {
            $fullLocale = Locale::toFull($filters['locale']);
            $q->where('locale', $fullLocale);
        }

        if ($filters['ip'] !== '') {
            $q->where('ip', 'like', "{$filters['ip']}%");
        }

        if ($filters['level'] !== '') {
            $q->whereHas('level', function ($query) use ($filters) {
                $query->where('name', $filters['level']);
            });
        }

        if ($filters['status'] === 'verified') {
            $q->where('verified', 1);
        } elseif ($filters['status'] === 'unverified') {
            $q->where('verified', 0);
        }

        if ($filters['registered_from'] !== '') {
            $q->whereDate('created_at', '>=', $filters['registered_from']);
        }

        if ($filters['registered_to'] !== '') {
            $q->whereDate('created_at', '<=', $filters['registered_to']);
        }

        $users = $q->orderByDesc('id')->simplePaginate(25)->withQueryString();
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
        if ($request->has('email')) {
            $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);
        }

        $data = $request->validate([
            'id' => ['required', 'integer', 'exists:users,id'],
            'username' => ['nullable', 'string', 'max:120'],
            'first_name' => ['nullable', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'nickname' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'identify_mode' => ['nullable', 'in:full,name,nick'],
            'password' => ['nullable', 'string', 'min:8'],
            'level' => ['nullable', 'string', 'exists:user_levels,name'],
            'email' => ['nullable', 'email', 'max:190', 'unique:users,email,'.(int) $request->input('id')],
            'locale' => ['nullable', 'string', Rule::exists('languages', 'code')->where('is_active', 1)],
            'verified' => ['nullable'],
        ]);

        $user = User::with('level')->find($data['id']);

        if (! $user) {
            return back()->withErrors(['id' => 'User not found']);
        }

        $isSelf = admin_user()?->id === $user->id;

        if ($isSelf && $request->filled('level') && $data['level'] !== ($user->level->name ?? null)) {
            return back()->withErrors(['level' => 'You cannot change your own role.'])->withInput();
        }

        if ($isSelf && ! $request->boolean('verified')) {
            return back()->withErrors(['verified' => 'You cannot deactivate your own account.'])->withInput();
        }

        try {
            DB::transaction(function () use ($request, $data, $user) {
                if ($request->filled('level')) {
                    $level = UserLevel::where('name', $data['level'])->first();
                    if ($level) {
                        $user->level_id = $level->id;
                    }
                }

                if (! empty($data['password'])) {
                    $user->password = Hash::make($data['password']);
                    $user->password_changed_at = now();
                }

                $user->name = $data['username'] ?? $user->name;
                $user->first_name = $data['first_name'] ?? $user->first_name;
                $user->last_name = $data['last_name'] ?? $user->last_name;
                $user->nickname = $data['nickname'] ?? $user->nickname;
                $user->city = $data['city'] ?? $user->city;
                $user->identify_mode = $data['identify_mode'] ?? $user->identify_mode;
                $user->email = $data['email'] ?? $user->email;
                $user->locale = isset($data['locale']) ? Locale::toFull($data['locale']) : $user->locale;
                $user->verified = $request->boolean('verified');

                $user->save();
            });
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['id' => 'Could not save user.']);
        }

        return redirect()
            ->route('admin.users', ['select' => $user->id])
            ->with('success', 'saved');
    }

    public function bulkBan(Request $request)
    {
        $ids = $this->validatedIds($request);
        if (empty($ids)) {
            return response()->json(['ok' => false]);
        }

        $bannedLevel = UserLevel::where('name', 'banned')->first();
        if (! $bannedLevel) {
            return response()->json(['ok' => false, 'error' => 'banned role missing']);
        }

        $reason = $request->input('reason') ?: null;
        $days = (int) $request->input('days', 0);
        $until = $days > 0 ? now()->addDays($days) : null;

        User::whereIn('id', $ids)
            ->where('id', '!=', admin_user()?->id)
            ->whereHas('level', function ($q) {
                $q->where('name', '!=', 'admin');
            })
            ->update([
                'level_id' => $bannedLevel->id,
                'banned_reason' => $reason,
                'banned_until' => $until,
            ]);

        return response()->json(['ok' => true]);
    }

    public function bulkUnban(Request $request)
    {
        $ids = $this->validatedIds($request);
        if (empty($ids)) {
            return response()->json(['ok' => false]);
        }

        $userLevel = UserLevel::where('name', 'user')->first();
        $bannedLevelId = UserLevel::where('name', 'banned')->value('id');
        $adminLevelId = UserLevel::where('name', 'admin')->value('id');

        if (! $userLevel || ! $bannedLevelId) {
            return response()->json(['ok' => false, 'error' => 'role missing']);
        }

        User::whereIn('id', $ids)
            ->where('id', '!=', admin_user()?->id)
            ->where('level_id', $bannedLevelId)
            ->where(function ($q) use ($adminLevelId) {
                $q->whereNull('level_id')->orWhere('level_id', '!=', $adminLevelId);
            })
            ->update([
                'level_id' => $userLevel->id,
                'banned_reason' => null,
                'banned_until' => null,
            ]);

        return response()->json(['ok' => true]);
    }

    public function bulkDelete(Request $request)
    {
        $ids = $this->validatedIds($request);
        if (empty($ids)) {
            return response()->json(['ok' => false]);
        }

        $adminLevelId = UserLevel::where('name', 'admin')->value('id');
        if (! $adminLevelId) {
            return response()->json(['ok' => false, 'error' => 'admin role missing']);
        }

        $bannedLevelId = UserLevel::where('name', 'banned')->value('id');

        $deleted = User::whereIn('id', $ids)
            ->where('id', '!=', admin_user()?->id)
            ->where(function ($q) use ($adminLevelId, $bannedLevelId) {
                $q->whereNull('level_id')
                    ->orWhereNotIn('level_id', array_filter([$adminLevelId, $bannedLevelId]));
            })
            ->delete();

        return response()->json([
            'ok' => $deleted > 0,
            'deleted' => $deleted,
        ]);
    }

    private function validatedIds(Request $request): array
    {
        $validator = validator(
            ['ids' => $request->input('ids', [])],
            [
                'ids' => ['required', 'array'],
                'ids.*' => ['integer', 'exists:users,id'],
            ]
        );

        if ($validator->fails()) {
            return [];
        }

        return array_map('intval', $validator->validated()['ids']);
    }

    public function bulkAction(Request $request)
    {
        $data = $request->validate([
            'action' => ['required', 'string', 'in:ban,unban,delete'],
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:users,id'],
        ]);

        return match ($data['action']) {
            'ban' => $this->bulkBan($request),
            'unban' => $this->bulkUnban($request),
            'delete' => $this->bulkDelete($request),
        };
    }
}
