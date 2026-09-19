<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminLanguagesController extends Controller
{
    public function index()
    {
        $languages = Language::orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return view('admin.languages.index', compact('languages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'regex:/^[a-z]{2,8}$/', 'unique:languages,code'],
            'name' => ['required', 'max:255'],
            // 'flag' => ['nullable', 'max:100'],
        ]);

        $code = strtolower($request->code);

        if (! is_dir(base_path('lang/'.$code))) {
            return back()
                ->withInput()
                ->withErrors(['code' => "Missing translation directory lang/{$code}."]);
        }

        Language::create([
            'code' => $code,
            'name' => $request->name,
            // 'flag' => $request->flag ?? null,
            'is_active' => true,
            'is_default' => false,
        ]);

        $this->flushLanguageCache();

        return back()->with('success', 'language created');
    }

    public function update(Request $request, Language $language)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        if ($language->is_default && ! $request->has('is_active')) {
            return back()->with('error', 'cannot deactivate default language');
        }

        $language->update([
            'name' => $validated['name'],
            'is_active' => $request->has('is_active'),
        ]);

        $this->flushLanguageCache();

        return back()->with('success', 'language updated');
    }

    public function setDefault(Language $language)
    {
        DB::transaction(function () use ($language) {
            Language::where('is_default', 1)->update(['is_default' => 0]);
            $language->update(['is_default' => 1, 'is_active' => 1]);
        });

        $this->flushLanguageCache();

        return back()->with('success', 'default language updated');
    }

    public function destroy(Language $language)
    {
        if ($language->is_default) {
            return back()->with('error', 'cannot delete default language');
        }

        $language->delete();

        $this->flushLanguageCache();

        return back()->with('success', 'language deleted');
    }

    private function flushLanguageCache(): void
    {
        Cache::forget('active_languages');
        Cache::forget('default_language');
        Cache::forget('active_languages_full');
        Cache::forget('languages:codes');
    }
}
