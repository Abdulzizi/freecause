<?php

namespace App\Mail;

use App\Support\MailQuotaGate;

/** Set $transactional = true on mail that must be deferred, not dropped, while the quota is exhausted. */
trait GatedByMailQuota
{
    public function middleware(): array
    {
        return [new MailQuotaGate($this->transactional ?? false)];
    }
}
