<?php

namespace Tests\Feature;

use App\Mail\PetitionDigestMail;
use App\Mail\VerifyAccountMail;
use App\Models\BannedIp;
use App\Models\Permission;
use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Models\Signature;
use App\Models\User;
use App\Models\UserLevel;
use App\Support\Settings;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuditRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);
    }

    protected function systemAdmin(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'level_id' => UserLevel::where('name', 'admin')->value('id'),
            'verified' => true,
        ], $overrides));
    }

    protected function publishedPetition(string $slug): Petition
    {
        $owner = User::factory()->create(['verified' => false, 'notify_product' => false]);
        $petition = Petition::factory()->create([
            'user_id' => $owner->id,
            'status' => 'published',
            'is_active' => true,
        ]);
        PetitionTranslation::create([
            'petition_id' => $petition->id,
            'locale' => 'en',
            'title' => 'Audit '.$slug,
            'slug' => $slug,
            'description' => 'Description',
        ]);

        return $petition;
    }

    public function test_01_banned_ip_gets_403_on_public_page(): void
    {
        BannedIp::create(['ip' => '127.0.0.1', 'reason' => 'audit']);

        $this->get('/en/petitions')->assertForbidden();
    }

    public function test_02_expired_user_ban_can_log_in(): void
    {
        $user = User::factory()->create([
            'email' => 'expired-ban@example.com',
            'password' => Hash::make('password123'),
            'locale' => 'en_US',
            'verified' => true,
            'banned_until' => now()->subDay(),
        ]);

        $this->post('/en/login', [
            'email' => 'expired-ban@example.com',
            'password' => 'password123',
        ])->assertRedirect('/en');

        $this->assertAuthenticatedAs($user);
    }

    public function test_03_active_user_ban_cannot_log_in(): void
    {
        User::factory()->create([
            'email' => 'active-ban@example.com',
            'password' => Hash::make('password123'),
            'verified' => true,
            'banned_until' => now()->addDays(3),
        ]);

        $this->from('/en/login')->post('/en/login', [
            'email' => 'active-ban@example.com',
            'password' => 'password123',
        ])->assertRedirect('/en/login');

        $this->assertGuest();
    }

    public function test_04_banned_admin_refused_at_login_and_admin_page(): void
    {
        $admin = $this->systemAdmin([
            'email' => 'banned-admin@example.com',
            'password' => Hash::make('password123'),
            'banned_until' => now()->addDays(3),
        ]);

        $this->post('/admin/login', [
            'login' => 'banned-admin@example.com',
            'password' => 'password123',
        ])->assertSessionHasErrors('login');

        $this->assertGuest('admin');

        $this->actingAs($admin, 'admin')->get('/admin/dashboard')->assertForbidden();
    }

    public function test_05_admin_logout_invalidates_session(): void
    {
        $admin = $this->systemAdmin();

        $this->actingAs($admin, 'admin')
            ->post('/admin/logout')
            ->assertRedirect(route('admin.login'));

        $this->get('/admin/dashboard')->assertRedirect(route('admin.login'));
    }

    public function test_06_password_reset_clears_other_sessions_and_rotates_token(): void
    {
        config(['session.driver' => 'database']);

        $user = User::factory()->create([
            'email' => 'reset-sessions@example.com',
            'password' => Hash::make('old-password-123'),
            'locale' => 'en_US',
            'country' => 'US',
            'gender' => 'male',
            'verified' => true,
        ]);
        $before = $user->remember_token;

        $table = config('session.table', 'sessions');
        DB::table($table)->insert([
            'id' => 'other-session-id',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'audit',
            'payload' => 'other',
            'last_activity' => time(),
        ]);

        $token = Password::createToken($user);

        $this->post('/en/reset-password', [
            'token' => $token,
            'email' => 'reset-sessions@example.com',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
            'redirect' => '/en/petitions',
        ])->assertRedirect('/en/petitions');

        $this->assertDatabaseMissing($table, ['id' => 'other-session-id']);
        $this->assertNotSame($before, $user->fresh()->remember_token);
        $this->assertContains(
            AuthenticateSession::class,
            app('router')->getMiddlewareGroups()['web'] ?? []
        );
    }

    public function test_07_thanks_page_404_for_closed_or_unpublished_petition(): void
    {
        $draft = Petition::factory()->create(['status' => 'draft', 'is_active' => false]);
        PetitionTranslation::create([
            'petition_id' => $draft->id,
            'locale' => 'en',
            'title' => 'Audit draft',
            'slug' => 'audit-draft',
            'description' => 'Description',
        ]);

        $offline = Petition::factory()->create(['status' => 'published', 'is_active' => false]);
        PetitionTranslation::create([
            'petition_id' => $offline->id,
            'locale' => 'en',
            'title' => 'Audit offline',
            'slug' => 'audit-offline',
            'description' => 'Description',
        ]);

        $this->get("/en/petition/audit-draft/{$draft->id}/thanks")->assertNotFound();
        $this->get("/en/petition/audit-offline/{$offline->id}/thanks")->assertNotFound();
    }

    public function test_08_guest_sign_with_existing_email_mirrors_new_email_without_signature(): void
    {
        Settings::set('smtp_enabled', true, 'global');
        Mail::fake();

        $petition = $this->publishedPetition('audit-sign-mirror');
        User::factory()->create(['email' => 'mirror-existing@example.com', 'verified' => true]);

        $sign = fn (string $email, string $name) => $this->post("/en/petition/audit-sign-mirror/{$petition->id}/sign", [
            'name' => $name,
            'email' => $email,
            'comment' => 'I support this petition',
            'agree1' => '1',
            'agree2' => '1',
            'agree3' => '1',
        ]);

        $fresh = $sign('mirror-fresh@example.com', 'Fresh Guest');
        $mirror = $sign('mirror-existing@example.com', 'Existing Holder');

        $this->assertSame($fresh->headers->get('Location'), $mirror->headers->get('Location'));
        $this->assertDatabaseMissing('signatures', [
            'petition_id' => $petition->id,
            'email' => 'mirror-existing@example.com',
        ]);
        $this->assertDatabaseHas('signatures', [
            'petition_id' => $petition->id,
            'email' => 'mirror-fresh@example.com',
        ]);

        Mail::assertQueued(VerifyAccountMail::class, 2);
        Mail::assertQueued(VerifyAccountMail::class, fn ($mail) => $mail->hasTo('mirror-fresh@example.com'));
        Mail::assertQueued(VerifyAccountMail::class, fn ($mail) => $mail->hasTo('mirror-existing@example.com'));

        $strip = fn (string $html) => preg_replace(
            ['/_token" value="[^"]*"/', '/\b\d+\b/'],
            ['_token" value="TOKEN"', 'N'],
            $html
        );

        $freshBody = $strip((string) $this->followingRedirects()->get($fresh->headers->get('Location'))->getContent());
        $mirrorBody = $strip((string) $this->followingRedirects()->get($mirror->headers->get('Location'))->getContent());

        $this->assertSame($freshBody, $mirrorBody);
    }

    public function test_09_ads_config_404_when_ads_disabled(): void
    {
        config(['ads.enabled' => true]);
        Settings::set('ads_enabled', false, 'global');

        $this->get('/ads/config/rectangle')->assertNotFound();
    }

    public function test_10_ads_frame_csp_and_sandbox(): void
    {
        config(['ads.enabled' => true]);

        $frame = $this->get('/ads/frame/rectangle')->assertOk();
        $csp = (string) $frame->headers->get('Content-Security-Policy');

        $this->assertStringNotContainsString('unsafe-eval', $csp);
        $this->assertStringContainsString('frame-ancestors', $csp);

        $petition = $this->publishedPetition('audit-ads-sandbox');
        $html = $this->get("/en/petition/audit-ads-sandbox/{$petition->id}")->assertOk()->getContent();

        $this->assertStringContainsString('sandbox="', $html);
        $this->assertStringNotContainsString('allow-same-origin', $html);
    }

    public function test_11_digest_overlap_lock_blocks_sending(): void
    {
        Settings::set('smtp_enabled', true, 'global');
        $this->publishedPetition('audit-digest-lock');
        User::factory()->create([
            'email' => 'digest-lock@example.com',
            'locale' => 'en',
            'verified' => true,
            'notify_product' => true,
            'unsubscribed_at' => null,
        ]);

        Mail::fake();

        $lock = Cache::lock('petition-digest', 21600);
        $this->assertTrue($lock->acquire());

        try {
            Artisan::call('petition:send-digest');

            Mail::assertNothingQueued();
        } finally {
            $lock->release();
        }
    }

    public function test_12_view_only_signatures_admin_forbidden_from_bulk_action(): void
    {
        $level = UserLevel::create([
            'name' => 'audit_sig_viewer',
            'visible_name' => 'Audit viewer',
            'is_system' => false,
        ]);
        Permission::create(['level_id' => $level->id, 'module' => 'signatures', 'action' => 'view']);
        $viewer = User::factory()->create(['level_id' => $level->id, 'verified' => true]);

        $signature = Signature::factory()->create();

        $this->actingAs($viewer, 'admin')
            ->postJson('/admin/signatures/bulk-action', ['action' => 'approve', 'ids' => [$signature->id]])
            ->assertForbidden();
    }

    public function test_13_digest_does_not_query_translations_per_recipient(): void
    {
        Settings::set('smtp_enabled', true, 'global');
        $this->publishedPetition('audit-digest-eager');

        foreach (['a', 'b', 'c'] as $key) {
            User::factory()->create([
                'email' => "digest-eager-{$key}@example.com",
                'locale' => 'en',
                'verified' => true,
                'notify_product' => true,
                'unsubscribed_at' => null,
            ]);
        }

        Mail::fake();
        DB::flushQueryLog();
        DB::enableQueryLog();

        Artisan::call('petition:send-digest');

        $translationQueries = collect(DB::getQueryLog())
            ->filter(fn (array $q) => str_contains(strtolower($q['query']), 'petition_translations'))
            ->count();

        Mail::assertQueued(PetitionDigestMail::class, 3);
        $this->assertLessThan(3, $translationQueries);
    }
}
