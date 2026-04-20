<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class TranslationsController extends Controller
{
    public function index(Request $request)
    {
        $locale = $request->query('locale', 'en');
        $languages = Language::where('is_active', true)->orderByDesc('is_default')->get();

        $translations = $this->loadTranslations($locale);

        return view('admin.translations.index', compact('languages', 'locale', 'translations'));
    }

    public function update(Request $request)
    {
        $locale = $request->input('locale');

        $request->validate([
            'locale' => 'required|exists:languages,code',
        ]);

        $translations = [];
        foreach ($request->except('_token', 'locale') as $key => $value) {
            $translations[$key] = $value;
        }

        $content = "<?php\n\nreturn [\n";
        foreach ($translations as $key => $value) {
            $content .= "    '$key' => '".addslashes($value)."',\n";
        }
        $content .= "];\n";

        $path = resource_path("lang/{$locale}/messages.php");

        if (File::put($path, $content)) {
            return back()->with('success', 'Translations saved successfully.');
        }

        return back()->with('error', 'Failed to save translations.');
    }

    private function loadTranslations(string $locale): array
    {
        $path = resource_path("lang/{$locale}/messages.php");

        if (! File::exists($path)) {
            return [];
        }

        return File::getRequire($path);
    }
}
