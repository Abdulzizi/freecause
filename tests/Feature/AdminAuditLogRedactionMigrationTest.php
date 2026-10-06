<?php

namespace Tests\Feature;

use App\Models\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuditLogRedactionMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_historical_admin_passwords_are_redacted_from_audit_logs(): void
    {
        $log = Log::create([
            'level' => 'info',
            'context' => 'admin.audit',
            'title' => 'Admin action: POST /admin/system/user-info',
            'content' => 'Admin #1 (admin@example.test) | Status: 302 | Input: '.json_encode([
                'current_password' => 'old-secret',
                'new_password' => 'new-secret',
                'new_password_confirmation' => 'new-secret',
                'email' => 'admin@example.test',
            ]),
        ]);

        $migration = require database_path('migrations/2026_10_06_000002_redact_credentials_from_admin_audit_logs.php');
        $migration->up();

        $content = $log->fresh()->content;
        $this->assertStringNotContainsString('old-secret', $content);
        $this->assertStringNotContainsString('new-secret', $content);
        $this->assertStringContainsString('admin@example.test', $content);
        $this->assertStringContainsString('[REDACTED]', $content);
    }
}
