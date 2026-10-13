<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\AppLog;
use Illuminate\Http\Request;

class PreferencesController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(lroute('login'));
        }

        $user->ensureUnsubscribeToken();

        return view('preferences', [
            'state' => 'preferences',
            'user' => $user,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(lroute('login'));
        }

        $request->validate([
            'notify_milestones' => ['nullable', 'boolean'],
            'notify_product' => ['nullable', 'boolean'],
            'unsubscribe_all' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('unsubscribe_all')) {
            $user->notify_milestones = false;
            $user->notify_product = false;
            $user->unsubscribed_at = now();
        } else {
            $user->notify_milestones = $request->boolean('notify_milestones');
            $user->notify_product = $request->boolean('notify_product');
            $user->unsubscribed_at = ($user->notify_milestones || $user->notify_product) ? null : now();
        }

        try {
            $user->save();
        } catch (\Throwable $e) {
            AppLog::error('Preferences update failed', 'User ID: '.$user->id.' | '.$e->getMessage(), 'preferences.update');

            toast(__('ui.error'), 'error');

            return redirect()->to(lroute('preferences.show'));
        }

        toast(__('preferences.saved'), 'success');

        return redirect()->to(lroute('preferences.show'));
    }

    public function unsubscribe(Request $request, string $locale, string $token)
    {
        try {
            $user = strlen($token) <= 128
                ? User::where('unsubscribe_token', $token)->first()
                : null;
        } catch (\Throwable $e) {
            AppLog::error('Unsubscribe lookup failed', $e->getMessage(), 'preferences.unsubscribe');

            $user = null;
        }

        if (! $user) {
            return response()->view('preferences', [
                'state' => 'invalid',
                'user' => $request->user(),
            ], 200);
        }

        try {
            $user->notify_milestones = false;
            $user->notify_product = false;
            $user->unsubscribed_at = now();
            $user->save();
        } catch (\Throwable $e) {
            AppLog::error('Unsubscribe failed', 'User ID: '.$user->id.' | '.$e->getMessage(), 'preferences.unsubscribe');

            return response()->view('preferences', [
                'state' => 'invalid',
                'user' => $request->user(),
            ], 200);
        }

        return view('preferences', [
            'state' => 'unsubscribed',
            'user' => $request->user(),
        ]);
    }
}
