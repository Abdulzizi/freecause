<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;

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
            'code' => ['required', 'max:8', 'unique:languages,code'],
            'name' => ['required', 'max:255'],
            'flag' => ['nullable', 'max:100'],
        ]);

        Language::create([
            'code' => strtolower($request->code),
            'name' => $request->name,
            'flag' => $request->flag ?? null,
            'is_active' => true,
            'is_default' => false,
        ]);

        cache()->forget('active_languages');
        cache()->forget('default_language');

        return back()->with('success', 'language created');
    }

    public function update(Request $request, Language $language)
    {
        if ($language->is_default && !$request->has('is_active')) {
            return back()->with('error', 'cannot deactivate default language');
        }

        $language->update([
            'name' => $request->name,
            'is_active' => $request->has('is_active'),
        ]);

        cache()->forget('active_languages');

        return back()->with('success', 'language updated');
    }

    public function setDefault(Language $language)
    {
        Language::where('is_default', 1)->update(['is_default' => 0]);

        $language->update(['is_default' => 1]);

        cache()->forget('default_language');

        return back()->with('success', 'default language updated');
    }

    public function destroy(Language $language)
    {
        if ($language->is_default) {
            return back()->with('error', 'cannot delete default language');
        }

        $language->delete();

        cache()->forget('active_languages');
        cache()->forget('default_language');

        return back()->with('success', 'language deleted');
    }
}
