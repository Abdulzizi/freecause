<?php

namespace App\Console\Commands;

use App\Models\PageContent;
use App\Support\Locale;
use App\Support\MailText;
use App\Support\Settings;
use Illuminate\Console\Command;

class SeedEmailTemplates extends Command
{
    protected $signature = 'emails:seed-templates';

    protected $description = 'Store missing translated email defaults for editing in Localized Content';

    public function handle(): int
    {
        $created = 0;
        foreach (Locale::supported() as $locale) {
            foreach (MailText::defaults($locale) as $key => $value) {
                $legacy = ['verify_subject' => 'email_verify_subject', 'verify_heading' => 'email_verify_greeting', 'verify_action' => 'email_verify_button_text', 'verify_footer' => 'email_verify_footer'];
                if ($locale === 'en' && isset($legacy[$key])) {
                    $value = Settings::get($legacy[$key], '') ?: $value;
                }
                $row = PageContent::firstOrCreate(['page' => 'emails', 'locale' => $locale, 'key' => $key], ['value' => $value]);
                $created += (int) $row->wasRecentlyCreated;
            }
            MailText::clear($locale);
        }
        $this->info("Saved {$created} missing email template fields; existing edits preserved.");

        return self::SUCCESS;
    }
}
