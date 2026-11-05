<?php

namespace App\Jobs;

use App\Models\PageContent;
use App\Services\EmailTemplateTranslator;
use App\Support\MailText;
use App\Support\Settings;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class SyncEmailTemplates implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $timeout = 60;

    public int $tries = 3;

    public int $uniqueFor = 3600;

    public function __construct(public string $source, public string $target, public array $texts, public array $previousHashes = []) {}

    public function uniqueId(): string
    {
        return $this->source.':'.$this->target.':'.$this->fingerprint($this->texts);
    }

    public function backoff(): array
    {
        return [60, 300];
    }

    public function handle(EmailTemplateTranslator $translator): void
    {
        $current = MailText::catalog($this->source);
        if ($this->fingerprint($current) !== $this->fingerprint($this->texts)) {
            $this->status('outdated', 'The source changed. Use Translate and sync again.');

            return;
        }
        $this->status('running');
        try {
            $existing = PageContent::where('page', 'emails')->where('locale', $this->target)->pluck('value', 'key')->all();
            $changed = array_filter($this->texts, fn ($text, $field) => ! filled($existing[$field] ?? null) || ($this->previousHashes[$field] ?? null) !== hash('sha256', $text), ARRAY_FILTER_USE_BOTH);
            $translated = $changed ? $translator->translate($changed, $this->source, $this->target) : [];
            DB::transaction(function () use ($translated) {
                $saved = PageContent::where('page', 'emails')->where('locale', $this->source)->lockForUpdate()->pluck('value', 'key')->all();
                $source = array_replace(MailText::defaults($this->source), array_intersect_key($saved, MailText::defaults($this->source)));
                if ($this->fingerprint($source) !== $this->fingerprint($this->texts)) {
                    throw new \RuntimeException('The source changed during translation. Use Translate and sync again.');
                }
                foreach ($translated as $field => $text) {
                    PageContent::updateOrCreate(['page' => 'emails', 'locale' => $this->target, 'key' => $field], ['value' => $text]);
                }
            });
            MailText::clear($this->target);
            $this->status('completed');
        } catch (\Throwable $e) {
            $this->status('failed', $e->getMessage());
            throw $e;
        }
    }

    private function fingerprint(array $texts): string
    {
        ksort($texts);

        return hash('sha256', json_encode($texts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function status(string $state, string $message = ''): void
    {
        Settings::set('sync_'.$this->target, ['state' => $state, 'source' => $this->source,
            'updated_at' => now()->toIso8601String(), 'message' => $message,
            'source_hashes' => $state === 'completed' ? array_map(fn ($text) => hash('sha256', $text), $this->texts) : $this->previousHashes], 'email_templates');
    }
}
