<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LanguageOptionsUpdateRequest;
use App\Jobs\SyncEmailTemplates;
use App\Models\Language;
use App\Models\PageContent;
use App\Services\EmailTemplateTranslator;
use App\Services\PageContentService;
use App\Support\Locale;
use App\Support\MailText;
use App\Support\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LanguageOptionsController extends Controller
{
    protected array $homepageKeys = [
        'h1',
        'h2',
        'text_index_left',
        'text_index_right',
        'exclude_most_read',
    ];

    protected array $globalKeys = [
        'meta_keywords',
        'meta_description',
        'head_additional_html',
    ];

    protected array $preserveIfBlank = [
        'text_index_left',
        'text_index_right',
        'footer_about',
        'footer_links',
        'footer_bottom',
    ];

    protected array $layoutKeys = [
        'footer_about',
        'footer_links',
        'footer_bottom',
    ];

    private function getLocales(): array
    {
        return Language::where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->pluck('name', 'code')
            ->toArray();
    }

    public function edit(Request $request)
    {
        $locales = $this->getLocales();

        $locale = $request->query('locale');
        $showForm = $locale && isset($locales[$locale]);

        $isSystemAdmin = admin_user()?->load('level')?->level?->is_system ?? false;

        $values = [];
        $layoutValues = [];

        if ($showForm) {
            $values = PageContent::where('page', 'home')
                ->where('locale', $locale)
                ->pluck('value', 'key')
                ->toArray();

            $globalValues = PageContent::where('page', 'global')
                ->where('locale', $locale)
                ->pluck('value', 'key')
                ->toArray();

            foreach ($this->globalKeys as $key) {
                if ($key === 'head_additional_html' && ! $isSystemAdmin) {
                    continue;
                }

                $values[$key] = $globalValues[$key] ?? ($values[$key] ?? '');
            }

            if (! $isSystemAdmin) {
                unset($values['head_additional_html']);
            }

            $layoutValues = PageContent::where('page', 'layout')
                ->where('locale', $locale)
                ->pluck('value', 'key')
                ->toArray();
        }

        return view('admin.options.language', [
            'emailValues' => $showForm ? MailText::catalog($locale) : [],
            'translationConfigured' => EmailTemplateTranslator::configured(),
            'emailSyncStatus' => collect(array_keys($locales))->mapWithKeys(fn ($code) => [$code => Settings::get('sync_'.$code, [], 'email_templates')])->all(),
            'locales' => $locales,
            'locale' => $locale,
            'values' => $values,
            'layoutValues' => $layoutValues,
            'showForm' => $showForm,
            'is_system_admin' => $isSystemAdmin,
        ]);
    }

    public function update(LanguageOptionsUpdateRequest $request)
    {
        $locale = $request->input('locale');

        $isSystemAdmin = admin_user()?->load('level')?->level?->is_system ?? false;

        if ($request->boolean('translate_all')) {
            abort_unless($isSystemAdmin, 403);
        }

        $save = function (string $page, string $key, array $existing, bool $sanitize = false) use ($request, $locale) {
            $value = (string) $request->input($key, '');

            if (in_array($key, $this->preserveIfBlank, true) && trim($value) === '') {
                if (array_key_exists($key, $existing)) {
                    return;
                }
            }

            if ($sanitize) {
                $value = sanitizeAdminHtml($value);
            }

            PageContent::updateOrCreate(
                ['page' => $page, 'locale' => $locale, 'key' => $key],
                ['value' => $value]
            );
        };

        $existingHome = PageContent::where('page', 'home')->where('locale', $locale)->pluck('value', 'key')->toArray();
        $existingGlobal = PageContent::where('page', 'global')->where('locale', $locale)->pluck('value', 'key')->toArray();
        $existingLayout = PageContent::where('page', 'layout')->where('locale', $locale)->pluck('value', 'key')->toArray();

        $permittedGlobalKeys = array_values(array_filter(
            $this->globalKeys,
            fn ($key) => $key !== 'head_additional_html' || $isSystemAdmin
        ));

        DB::transaction(function () use ($save, $existingHome, $existingGlobal, $existingLayout, $locale, $permittedGlobalKeys) {
            foreach ($this->homepageKeys as $key) {

                $save('home', $key, $existingHome, true);
            }

            foreach ($permittedGlobalKeys as $key) {
                $save('global', $key, $existingGlobal, $key === 'head_additional_html');
            }

            PageContent::where('page', 'home')
                ->where('locale', $locale)
                ->whereIn('key', $permittedGlobalKeys)
                ->delete();

            foreach ($this->layoutKeys as $key) {
                $save('layout', $key, $existingLayout, true);
            }
        });

        foreach ($request->validated('email_templates', []) as $key => $value) {
            PageContent::updateOrCreate(['page' => 'emails', 'locale' => $locale, 'key' => $key], ['value' => $value]);
        }
        MailText::clear($locale);
        app(PageContentService::class)->clear('home', $locale);
        app(PageContentService::class)->clear('global', $locale);
        app(PageContentService::class)->clear('layout', $locale);

        if ($request->boolean('translate_all')) {
            abort_unless($isSystemAdmin, 403);
            if (! EmailTemplateTranslator::configured()) {
                return redirect()->route('admin.options.language', ['locale' => $locale])
                    ->with('warning', 'Saved. Configure the DeepL API key before synchronizing translations.');
            }
            $texts = MailText::catalog($locale);
            foreach (Locale::supported() as $target) {
                if ($target !== $locale) {
                    $previous = Settings::get('sync_'.$target, [], 'email_templates');
                    $hashes = ($previous['source'] ?? null) === $locale ? ($previous['source_hashes'] ?? []) : [];
                    Settings::set('sync_'.$target, ['state' => 'queued', 'source' => $locale, 'updated_at' => now()->toIso8601String()], 'email_templates');
                    SyncEmailTemplates::dispatch($locale, $target, $texts, $hashes)->afterCommit();
                }
            }
        }

        return redirect()
            ->route('admin.options.language', ['locale' => $locale])
            ->with('success', $request->boolean('translate_all') ? 'Saved. Translation and synchronization are queued for all other active languages. Refresh this page to check progress.' : 'saved');
    }

    public function saveTranslationKey(Request $request)
    {
        abort_unless(admin_user()?->load('level')?->level?->is_system ?? false, 403);
        $validator = Validator::make($request->only('deepl_api_key'), [
            'deepl_api_key' => ['required', 'string', 'min:10', 'max:500', 'regex:/^\S+$/'],
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator);
        }
        Settings::set('deepl_api_key', Crypt::encryptString($request->string('deepl_api_key')->trim()->toString()));
        $request->request->remove('deepl_api_key');

        return redirect()->route('admin.options.language', ['locale' => in_array($request->input('locale'), Locale::supported(), true) ? $request->input('locale') : default_locale()])
            ->with('success', 'DeepL API key saved securely. You can now translate and sync email templates.');
    }
}
