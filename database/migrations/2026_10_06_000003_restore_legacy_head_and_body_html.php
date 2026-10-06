<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const GA_HEAD = <<<'HTML'
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-9CYCSY3PWD"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-9CYCSY3PWD');
</script>
HTML;

    private const GTM_BODY = <<<'HTML'
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-9CYCSY3PWD"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
HTML;

    public function up(): void
    {
        $existing = DB::table('settings')
            ->where('group', 'global')
            ->whereIn('key', ['inject_head_html', 'inject_body_html'])
            ->pluck('value', 'key');

        if ($existing->get('inject_head_html') === null || trim((string) $existing->get('inject_head_html')) === '') {
            DB::table('settings')->updateOrInsert(
                ['key' => 'inject_head_html', 'group' => 'global'],
                ['value' => self::GA_HEAD]
            );
        }

        if ($existing->get('inject_body_html') === null || trim((string) $existing->get('inject_body_html')) === '') {
            DB::table('settings')->updateOrInsert(
                ['key' => 'inject_body_html', 'group' => 'global'],
                ['value' => self::GTM_BODY]
            );
        }

        Cache::forget('settings:all');
    }

    public function down(): void {}
};
