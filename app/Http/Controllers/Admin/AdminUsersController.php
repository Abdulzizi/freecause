<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\User;
use App\Models\UserLevel;
use App\Support\ApproxRows;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminUsersController extends Controller
{
    use ApproxRows;

    public function index(Request $request)
    {
        $filters = $this->filters($request);

        $users = $this->filteredQuery(User::with('level'), $filters)
            ->orderByDesc('id')
            ->simplePaginate(25)
            ->withQueryString();
        $approxTotal = $this->approxTableRows('users');

        $levels = UserLevel::pluck('name', 'name')->toArray();
        $levels = ['' => 'any level'] + $levels;
        $editableLevels = array_filter($levels, fn ($name, $code) => $code !== 'banned', ARRAY_FILTER_USE_BOTH);

        $languages = Language::where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $locales = ['' => 'any language'];

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
            'editableLevels',
            'locales',
            'approxTotal',
            'selectedUser'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);

        // Collect the bounded id set first: a limit() on the streamed query is
        // overridden by chunk(), which would paginate past the 10k cap.
        $ids = $this->filteredQuery(User::query(), $filters)
            ->orderByDesc('id')
            ->limit(10000)
            ->pluck('id')
            ->all();

        return response()->streamDownload(function () use ($ids) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id', 'email', 'name', 'locale', 'level', 'verified', 'created_at']);
            User::query()
                ->whereIn('id', $ids)
                ->orderByDesc('id')
                ->chunk(500, function ($rows) use ($handle) {
                    // One query per chunk batch instead of lazy-loading each
                    // row's level (N+1).
                    $levelNames = UserLevel::whereIn('id', $rows->pluck('level_id')->filter()->unique()->all())
                        ->pluck('name', 'id');

                    foreach ($rows as $row) {
                        fputcsv($handle, [
                            $row->id,
                            $row->email,
                            $row->name,
                            $row->locale,
                            $levelNames[$row->level_id] ?? '',
                            $row->verified ? 1 : 0,
                            $row->created_at,
                        ]);
                    }
                });
            fclose($handle);
        }, 'users-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * Normalise the request's filter query string into a single array.
     */
    private function filters(Request $request): array
    {
        return [
            'id' => trim((string) $request->query('id', '')),
            'first_name' => trim((string) $request->query('first_name', '')),
            'last_name' => trim((string) $request->query('last_name', '')),
            'nickname' => trim((string) $request->query('nickname', '')),
            'email' => trim((string) $request->query('email', '')),
            'email_mode' => trim((string) $request->query('email_mode', 'prefix')),
            'ip' => trim((string) $request->query('ip', '')),
            'level' => trim((string) $request->query('level', '')),
            'locale' => trim((string) $request->query('locale', '')),
            'status' => trim((string) $request->query('status', '')),
            'registered_from' => trim((string) $request->query('registered_from', '')),
            'registered_to' => trim((string) $request->query('registered_to', '')),
        ];
    }

    /**
     * Apply the user-list filters to a query. Shared by index() and export()
     * so the exported CSV always matches the on-screen listing.
     */
    private function filteredQuery(Builder $q, array $filters): Builder
    {
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
            if ($filters['email_mode'] === 'domain') {
                $domain = strtolower(trim($filters['email']));
                $domain = ltrim(str_contains($domain, '@') ? substr($domain, strrpos($domain, '@') + 1) : $domain, '@');
                if ($domain !== '') {
                    $q->where('email_domain', $domain);
                } else {
                    $q->where('email', 'like', "{$filters['email']}%");
                }
            } else {
                $q->where('email', 'like', "{$filters['email']}%");
            }
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

        return $q;
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
            'level' => ['nullable', 'string', 'exists:user_levels,name', 'not_in:banned'],
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

        // Privilege-escalation guard: only system-level admins may change a
        // user's level, and never for someone who already holds a system level
        // (that includes other admins; the self case is excluded above).
        $levelChangesRequested = $request->filled('level') && $data['level'] !== ($user->level->name ?? null);

        if ($levelChangesRequested) {
            $actorLevel = admin_user()?->loadMissing('level')?->level;

            if (! ($actorLevel?->is_system ?? false)) {
                return back()->withErrors(['level' => 'You do not have permission to change user roles.'])->withInput();
            }

            if ($user->level?->is_system) {
                return back()->withErrors(['level' => 'You cannot change the role of a system-level user.'])->withInput();
            }
        }

        if ($isSelf && ! $request->boolean('verified')) {
            return back()->withErrors(['verified' => 'You cannot deactivate your own account.'])->withInput();
        }

        try {
            DB::transaction(function () use ($request, $data, $user, $levelChangesRequested) {
                if ($levelChangesRequested) {
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

        if ($user->wasChanged('level_id')) {
            Cache::forget('level:user_counts');
        }

        return redirect()
            ->route('admin.users', ['select' => $user->id])
            ->with('success', 'saved');
    }

    public function bulkBan(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:users,id'],
            'reason' => ['nullable', 'string', 'max:255'],
            'days' => ['nullable', 'integer', 'min:0', 'max:36500'],
        ]);

        $ids = array_map('intval', $data['ids']);
        if (empty($ids)) {
            return response()->json(['ok' => false]);
        }

        $bannedLevel = UserLevel::where('name', 'banned')->first();
        if (! $bannedLevel) {
            return response()->json(['ok' => false, 'error' => 'banned role missing']);
        }

        $days = (int) ($data['days'] ?? 0);
        $reason = trim((string) ($data['reason'] ?? '')) ?: null;
        $until = $days > 0 ? now()->addDays($days) : null;

        $banned = User::whereIn('id', $ids)
            ->where('id', '!=', admin_user()?->id)
            ->where(function ($q) use ($bannedLevel) {
                $q->whereNull('level_id')->orWhere('level_id', '!=', $bannedLevel->id);
            })
            ->whereDoesntHave('level', function ($q) {
                $q->where('is_system', true);
            })
            ->update([
                'level_id' => $bannedLevel->id,
                'banned_reason' => $reason,
                'banned_until' => $until,
            ]);

        if ($banned > 0) {
            Cache::forget('level:user_counts');
        }

        $skipped = count($ids) - $banned;

        // The banned set may contain admin/system/own accounts which are
        // always protected; reporting success with zero changes explains
        // "why is the user still here" instead of a silent no-op.
        if ($banned === 0) {
            return response()->json([
                'ok' => false,
                'error' => 'No eligible accounts: already banned, your own, admin and system-level accounts cannot be banned.',
                'skipped' => $skipped,
            ]);
        }

        return response()->json([
            'ok' => true,
            'banned' => $banned,
            'skipped' => $skipped,
            'msg' => $skipped > 0
                ? "Banned {$banned} user(s); {$skipped} skipped (admin/system accounts)."
                : "Banned {$banned} user(s).",
        ]);
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

        $unbanned = User::whereIn('id', $ids)
            ->where('id', '!=', admin_user()?->id)
            ->where('level_id', $bannedLevelId)
            ->whereDoesntHave('level', fn ($q) => $q->where('is_system', true))
            ->where(function ($q) use ($adminLevelId) {
                $q->whereNull('level_id')->orWhere('level_id', '!=', $adminLevelId);
            })
            ->update([
                'level_id' => $userLevel->id,
                'banned_reason' => null,
                'banned_until' => null,
            ]);

        if ($unbanned > 0) {
            Cache::forget('level:user_counts');
        }

        return response()->json(['ok' => true]);
    }

    public function bulkDelete(Request $request)
    {
        if (! admin_can('users', 'delete')) {
            return response()->json(['ok' => false, 'error' => 'You do not have permission to delete users.'], 403);
        }

        $ids = $this->validatedIds($request);
        if (empty($ids)) {
            return response()->json(['ok' => false]);
        }

        $adminLevelId = UserLevel::where('name', 'admin')->value('id');
        if (! $adminLevelId) {
            return response()->json(['ok' => false, 'error' => 'admin role missing']);
        }

        $bannedLevelId = UserLevel::where('name', 'banned')->value('id');

        $query = User::whereIn('id', $ids)
            ->where('id', '!=', admin_user()?->id)
            ->where(function ($q) use ($adminLevelId, $bannedLevelId) {
                $q->whereNull('level_id')
                    ->orWhereNotIn('level_id', array_filter([$adminLevelId, $bannedLevelId]));
            })
            ->whereDoesntHave('level', fn ($q) => $q->where('is_system', true));

        $eligible = (clone $query)->count();

        if ($eligible === 0) {
            return response()->json([
                'ok' => false,
                'error' => 'No eligible accounts: banned, admin and system-level users cannot be deleted here.',
                'requested' => count($ids),
            ]);
        }

        $deleted = $query->delete();

        if ($deleted > 0) {
            Cache::forget('level:user_counts');
        }

        return response()->json([
            'ok' => true,
            'deleted' => $deleted,
            'skipped' => count($ids) - $deleted,
            'msg' => "Deleted {$deleted} user(s)".(count($ids) > $deleted ? '; protected accounts (banned/admin/system) were skipped' : ''),
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

    public function ban(Request $request, User $user)
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
            'days' => ['nullable', 'integer', 'min:0', 'max:36500'],
        ]);

        if ($this->isProtectedUser($user)) {
            return response()->json(['ok' => false, 'error' => 'You cannot ban yourself or a system-level user.'], 403);
        }

        $bannedLevel = UserLevel::where('name', 'banned')->first();
        if (! $bannedLevel) {
            return response()->json(['ok' => false, 'error' => 'banned role missing'], 422);
        }

        if ((int) $user->level_id === (int) $bannedLevel->id) {
            return response()->json(['ok' => false, 'error' => 'User is already banned.'], 409);
        }

        $user->level_id = $bannedLevel->id;
        $reason = trim((string) ($data['reason'] ?? '')) ?: null;
        $days = (int) ($data['days'] ?? 0);
        $user->banned_reason = $reason;
        $user->banned_until = $days > 0 ? now()->addDays($days) : null;
        $user->save();

        if ($user->wasChanged('level_id')) {
            Cache::forget('level:user_counts');
        }

        return response()->json(['ok' => true]);
    }

    public function unban(Request $request, User $user)
    {
        if ($this->isProtectedUser($user)) {
            return response()->json(['ok' => false, 'error' => 'You cannot unban yourself or a system-level user.'], 403);
        }

        $userLevel = UserLevel::where('name', 'user')->first();
        $bannedLevel = UserLevel::where('name', 'banned')->first();

        if (! $userLevel || ! $bannedLevel) {
            return response()->json(['ok' => false, 'error' => 'role missing'], 422);
        }

        if ($user->level_id === $bannedLevel->id) {
            $user->level_id = $userLevel->id;
            $user->banned_reason = null;
            $user->banned_until = null;
            $user->save();

            if ($user->wasChanged('level_id')) {
                Cache::forget('level:user_counts');
            }
        }

        return response()->json(['ok' => true]);
    }

    private function isProtectedUser(User $user): bool
    {
        $user->loadMissing('level');

        return $user->id === admin_user()?->id || (bool) ($user->level?->is_system ?? false);
    }
}
