<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminUsersController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'id'       => trim((string) $request->query('id', '')),
            'name'     => trim((string) $request->query('name', '')),
            'surname'  => trim((string) $request->query('surname', '')),
            'email'    => trim((string) $request->query('email', '')),
            'ip'       => trim((string) $request->query('ip', '')),
            'level'    => trim((string) $request->query('level', '')),
            'locale'   => trim((string) $request->query('locale', '')),
        ];

        $q = DB::table('users')
            ->select([
                'id',
                'email',
                'name',
                'locale',
                'created_at',
            ]);

        if ($filters['id'] !== '') {
            $q->where('id', (int) $filters['id']);
        }

        if ($filters['email'] !== '') {
            $q->where('email', 'like', '%' . $this->escapeLike($filters['email']) . '%');
        }

        if ($filters['name'] !== '') {
            $q->where('name', 'like', '%' . $this->escapeLike($filters['name']) . '%');
        }

        $q->orderByDesc('id');

        $users = $q->simplePaginate(25)->appends($request->query());

        $approxTotal = $this->approxTableRows('users');

        $levels = ['' => '(livello)', 'user' => 'user', 'admin' => 'admin', 'banned' => 'banned'];
        $locales = ['' => '(Locale)', 'en_US' => 'en_US', 'fr_FR' => 'fr_FR', 'it_IT' => 'it_IT', 'da_DK' => 'da_DK'];

        $selectedId = $request->query('select');
        $selectedUser = null;
        if ($selectedId) {
            $selectedUser = DB::table('users')->where('id', (int) $selectedId)->first();
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
            'id'       => ['nullable', 'integer'],
            'username' => ['nullable', 'string'],
            'password' => ['nullable', 'string'],
            'level'    => ['nullable', 'string'],

            'name'     => ['nullable', 'string'],
            'surname'  => ['nullable', 'string'],
            'display'  => ['nullable', 'string'],
            'verified' => ['nullable'],
            'email'    => ['nullable', 'string'],
            'locale'   => ['nullable', 'string'],
        ]);

        if (empty($data['id'])) {
            return back()->withErrors(['id' => 'missing user id']);
        }

        $update = [
            'name'    => $data['name'] ?? '',
            'surname' => $data['surname'] ?? '',
            'email'   => $data['email'] ?? '',
            'locale'  => $data['locale'] ?? '',
        ];

        if ($request->has('level')) {
            $update['level'] = $data['level'] ?? '';
        }
        if ($request->has('verified')) {
            $update['verified'] = $request->boolean('verified') ? 1 : 0;
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

    private function approxTableRows(string $table): int
    {
        return (int) Cache::remember("approx_rows:$table", 3600, function () use ($table) {
            $db = DB::getDatabaseName();

            $row = DB::table('information_schema.TABLES')
                ->select('TABLE_ROWS')
                ->where('TABLE_SCHEMA', $db)
                ->where('TABLE_NAME', $table)
                ->first();

            return $row ? (int) $row->TABLE_ROWS : 0;
        });
    }
}
