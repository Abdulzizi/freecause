<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ApproxRows;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

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

        $q = DB::table('users')
            ->select(
                'id',
                'name',
                'first_name',
                'last_name',
                'email',
                'locale',
                'level',
                'verified',
                'created_at'
            );

        if ($filters['id'] !== '') {
            $q->where('id', (int) $filters['id']);
        }

        if ($filters['first_name'] !== '') {
            $q->where('first_name', 'like', '%' . $this->escapeLike($filters['first_name']) . '%');
        }

        if ($filters['last_name'] !== '') {
            $q->where('last_name', 'like', '%' . $this->escapeLike($filters['last_name']) . '%');
        }

        if ($filters['email'] !== '') {
            $q->where('email', 'like', '%' . $this->escapeLike($filters['email']) . '%');
        }

        if ($filters['locale'] !== '') {
            $q->where('locale', $filters['locale']);
        }

        if ($filters['level'] !== '') {
            $q->where('level', $filters['level']);
        }

        $q->orderByDesc('id');

        $users = $q->paginate(25)->withQueryString();
        $approxTotal = $this->approxTableRows('users');

        $levels = [
            '' => '(Level)',
            'admin' => 'admin',
            'user' => 'user',
            'banned' => 'banned',
        ];

        $locales = [
            '' => '(Local)',
            'en_US' => 'en_US',
            'fr_FR' => 'fr_FR',
            'it_IT' => 'it_IT',
            'da_DK' => 'da_DK',
        ];

        $selectedUser = null;

        if ($request->query('select')) {
            $selectedUser = DB::table('users')
                ->where('id', (int) $request->query('select'))
                ->first();
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

        $user = DB::table('users')->where('id', $data['id'])->first();
        if (!$user) {
            return back()->withErrors(['id' => 'User not found']);
        }

        if (auth()->id() == $user->id && ($data['level'] ?? '') === 'banned') {
            return back()->withErrors(['level' => 'You cannot ban yourself.']);
        }

        if (!empty($data['email'])) {
            $exists = DB::table('users')
                ->where('email', $data['email'])
                ->where('id', '!=', $data['id'])
                ->exists();

            if ($exists) {
                return back()->withErrors(['email' => 'Email already used.']);
            }
        }

        $update = [
            'name'       => $data['username'] ?? $user->name,
            'first_name' => $data['first_name'] ?? $user->first_name,
            'last_name'  => $data['last_name'] ?? $user->last_name,
            'email'      => $data['email'] ?? $user->email,
            'locale'     => $data['locale'] ?? $user->locale,
            'verified'   => $request->boolean('verified') ? 1 : 0,
        ];

        if (isset($data['level'])) {
            $update['level'] = $data['level'];
        }

        if (!empty($data['password'])) {
            $update['password'] = Hash::make($data['password']);
        }

        DB::table('users')->where('id', $data['id'])->update($update);

        return redirect()
            ->route('admin.users', ['select' => $data['id']])
            ->with('success', 'saved');
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $value);
    }
}
