<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ApproxRows;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminUsersController extends Controller
{
    use ApproxRows;

    public function index(Request $request)
    {
        $filters = [
            'id'        => trim((string) $request->query('id', '')),
            'name'      => trim((string) $request->query('name', '')),
            'last_name' => trim((string) $request->query('last_name', '')),
            'email'     => trim((string) $request->query('email', '')),
            'ip'        => trim((string) $request->query('ip', '')),
            'level'     => trim((string) $request->query('level', '')),
            'locale'    => trim((string) $request->query('locale', '')),
        ];

        $hasEmailVerifiedAt = Schema::hasColumn('users', 'email_verified_at');
        $hasVerified        = Schema::hasColumn('users', 'verified');
        $hasLevel           = Schema::hasColumn('users', 'level');
        $hasIp              = Schema::hasColumn('users', 'ip');

        $q = DB::table('users');

        $select = [
            'id',
            'email',
            'name',
            'last_name',
            'locale',
            'created_at',
        ];

        if ($hasVerified) {
            $q->addSelect(DB::raw('verified as is_verified'));
        } elseif ($hasEmailVerifiedAt) {
            $q->addSelect(DB::raw('CASE WHEN email_verified_at IS NULL THEN 0 ELSE 1 END as is_verified'));
        } else {
            $q->addSelect(DB::raw('0 as is_verified'));
        }

        $q->addSelect($select);

        if ($filters['id'] !== '') {
            $q->where('id', (int) $filters['id']);
        }

        if ($filters['email'] !== '') {
            $q->where('email', 'like', '%' . $this->escapeLike($filters['email']) . '%');
        }

        if ($filters['name'] !== '') {
            $q->where('name', 'like', '%' . $this->escapeLike($filters['name']) . '%');
        }

        if ($filters['last_name'] !== '') {
            $q->where('last_name', 'like', '%' . $this->escapeLike($filters['last_name']) . '%');
        }

        if ($filters['locale'] !== '') {
            $q->where('locale', $filters['locale']);
        }

        if ($filters['level'] !== '' && $hasLevel) {
            $q->where('level', $filters['level']);
        }

        if ($filters['ip'] !== '' && $hasIp) {
            $q->where('ip', 'like', '%' . $this->escapeLike($filters['ip']) . '%');
        }

        $q->orderByDesc('id');

        // $users = $q->simplePaginate(25)->appends($request->query());
        $users = $q->paginate(25)->withQueryString();

        $approxTotal = $this->approxTableRows('users');

        $levels = ['' => '(Level)', 'superadmin' => 'superadmin', 'user' => 'user', 'mukesh' => 'mukesh'];
        $locales = ['' => '(Local)', 'en_US' => 'en_US', 'fr_FR' => 'fr_FR', 'it_IT' => 'it_IT', 'da_DK' => 'da_DK'];

        $selectedId = $request->query('select');
        $selectedUser = null;

        if ($selectedId) {
            $su = DB::table('users')
                ->select(
                    ['id', 'email', 'name', 'last_name', 'locale', 'created_at']
                )
                ->where('id', (int) $selectedId);

            if ($hasVerified) {
                $su->addSelect(DB::raw('verified as is_verified'));
            } elseif ($hasEmailVerifiedAt) {
                $su->addSelect(DB::raw('CASE WHEN email_verified_at IS NULL THEN 0 ELSE 1 END as is_verified'));
            } else {
                $su->addSelect(DB::raw('0 as is_verified'));
            }

            if ($hasLevel) {
                $su->addSelect('level');
            }

            $selectedUser = $su->first();
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
        $hasEmailVerifiedAt = Schema::hasColumn('users', 'email_verified_at');
        $hasVerified        = Schema::hasColumn('users', 'verified');
        $hasLevel           = Schema::hasColumn('users', 'level');

        $data = $request->validate([
            'id'        => ['required', 'integer'],
            'username'  => ['nullable', 'string'],
            'password'  => ['nullable', 'string'],
            'level'     => ['nullable', 'string'],
            'name'      => ['nullable', 'string'],
            'last_name' => ['nullable', 'string'],
            'email'     => ['nullable', 'string'],
            'locale'    => ['nullable', 'string'],
            'verified'  => ['nullable'], // checkbox
        ]);

        $update = [
            'name'      => $data['name'] ?? '',
            'last_name' => $data['last_name'] ?? '',
            'email'     => $data['email'] ?? '',
            'locale'    => $data['locale'] ?? '',
        ];

        if ($hasLevel && $request->has('level')) {
            $update['level'] = $data['level'] ?? '';
        }

        if ($request->has('verified')) {
            $isVerified = $request->boolean('verified');

            if ($hasVerified) {
                $update['verified'] = $isVerified ? 1 : 0;
            } elseif ($hasEmailVerifiedAt) {
                $update['email_verified_at'] = $isVerified ? now() : null;
            }
        }

        DB::table('users')->where('id', (int) $data['id'])->update($update);

        return redirect()
            ->route('admin.users', ['select' => (int) $data['id']])
            ->with('success', 'saved');
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $value);
    }

    public function bulkBan(Request $request)
    {
        $ids = $request->input('ids', []);

        if (!is_array($ids) || empty($ids)) {
            return response()->json(['ok' => false], 400);
        }

        if (\Schema::hasColumn('users', 'level')) {
            DB::table('users')
                ->whereIn('id', $ids)
                ->update(['level' => 'banned']);
        }

        return response()->json(['ok' => true]);
    }

    public function bulkAction(Request $request)
    {
        $data = $request->validate([
            'action' => ['required', 'string'],
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        $ids = $data['ids'];
        if (!$ids) {
            return response()->json(['ok' => false, 'msg' => 'no ids'], 400);
        }

        switch ($data['action']) {
            case 'ban':
            case 'banned':
                if (\Schema::hasColumn('users', 'level')) {
                    DB::table('users')->whereIn('id', $ids)->update(['level' => 'banned']);
                    return response()->json(['ok' => true]);
                }
                return response()->json(['ok' => false, 'msg' => 'no level column'], 400);

            case 'unban':
                if (\Schema::hasColumn('users', 'level')) {
                    DB::table('users')->whereIn('id', $ids)->where('level', 'banned')->update(['level' => 'user']);
                    return response()->json(['ok' => true]);
                }
                return response()->json(['ok' => false, 'msg' => 'no level column'], 400);

            default:
                return response()->json(['ok' => false, 'msg' => 'not implemented'], 400);
        }
    }
}
