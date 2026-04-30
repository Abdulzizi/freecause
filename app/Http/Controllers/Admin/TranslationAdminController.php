<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TranslationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TranslationAdminController extends Controller
{
    protected $translationService;

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    public function index(Request $request)
    {
        $locale = $request->query('locale', 'en');
        $group = $request->query('group', 'auth');
        $search = $request->query('search', '');

        $languages = \App\Models\Language::where('is_active', true)->orderByDesc('is_default')->get();
        $groups = $this->translationService->getAllGroups();
        
        // Get translations for current locale and group
        $translations = $this->translationService->getGroup($locale, $group);
        
        // Get default locale translations for comparison
        $defaultLocale = config('app.locale', 'en');
        $defaultTranslations = $this->translationService->getGroup($defaultLocale, $group);
        
        // Get missing keys
        $missingKeys = $this->translationService->getMissingKeys($locale, $group);

        return view('admin.translations.manage', compact(
            'languages',
            'locale',
            'group',
            'groups',
            'translations',
            'defaultTranslations',
            'missingKeys',
            'search'
        ));
    }

    public function update(Request $request)
    {
        $request->validate([
            'locale' => 'required|string',
            'group' => 'required|string',
            'key' => 'required|string',
            'value' => 'nullable|string',
        ]);

        $this->translationService->set(
            $request->input('locale'),
            $request->input('group'),
            $request->input('key'),
            $request->input('value')
        );

        return back()->with('success', 'Translation updated successfully.');
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'locale' => 'required|string',
            'group' => 'required|string',
            'translations' => 'required|array',
        ]);

        $this->translationService->bulkSet(
            $request->input('locale'),
            $request->input('group'),
            $request->input('translations')
        );

        return back()->with('success', 'Translations updated successfully.');
    }

    public function export(Request $request)
    {
        $locale = $request->query('locale', 'en');
        $data = $this->translationService->exportToJson($locale);
        
        $filename = "translations-{$locale}.json";
        $headers = [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->json($data, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'locale' => 'required|string',
            'file' => 'required|file|mimes:json',
        ]);

        $content = file_get_contents($request->file('file')->path());
        $data = json_decode($content, true);

        if (!$data) {
            return back()->with('error', 'Invalid JSON file.');
        }

        $locale = $request->input('locale');
        $count = $this->translationService->importFromJson($locale, $data);

        return back()->with('success', "Imported {$count} translations successfully.");
    }

    public function copyFromSource(Request $request)
    {
        $request->validate([
            'locale' => 'required|string',
            'group' => 'required|string',
        ]);

        $count = $this->translationService->copyFromSource(
            $request->input('locale'),
            $request->input('group')
        );

        return back()->with('success', "Copied {$count} translations from source locale.");
    }

    public function destroy($id)
    {
        $translation = \App\Models\Translation::findOrFail($id);
        $locale = $translation->locale;
        $group = $translation->group;
        
        $translation->delete();
        
        $this->translationService->clearCache($locale, $group);

        return back()->with('success', 'Translation deleted successfully.');
    }

    public function clearCache(Request $request)
    {
        $this->translationService->clearCache();
        return back()->with('success', 'Translation cache cleared.');
    }
}
