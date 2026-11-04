<?php

namespace Tests\Feature;

use App\Models\Petition;
use App\Models\Signature;
use App\Models\User;
use App\Models\UserLevel;
use App\Services\PetitionSigning;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminSignatureRejectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([UserLevelSeeder::class, LanguageSeeder::class]);

        $this->actingAs(User::factory()->create([
            'level_id' => UserLevel::where('name', 'admin')->value('id'),
            'verified' => true,
        ]), 'admin');
    }

    private function pendingSignature(): array
    {
        $user = User::factory()->create(['verified' => false]);
        $petition = Petition::factory()->create(['signature_count' => 0]);
        $signature = Signature::factory()->create([
            'petition_id' => $petition->id,
            'user_id' => $user->id,
            'confirmed' => false,
            'is_spam' => false,
        ]);

        return [$user, $petition, $signature];
    }

    public static function rejectRoutes(): array
    {
        return ['single' => ['single'], 'bulk' => ['bulk']];
    }

    private function reject(string $via, Signature $signature): void
    {
        $via === 'single'
            ? $this->postJson(route('admin.signatures.action', $signature), ['action' => 'reject'])->assertOk()
            : $this->postJson(route('admin.signatures.bulkAction'), ['action' => 'reject', 'ids' => [$signature->id]])->assertOk();
    }

    #[DataProvider('rejectRoutes')]
    public function test_rejected_pending_signature_stays_uncounted_after_verify(string $via): void
    {
        [$user, $petition, $signature] = $this->pendingSignature();

        $this->reject($via, $signature);

        PetitionSigning::markVerified($user);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'verified' => 1]);
        $this->assertDatabaseHas('signatures', ['id' => $signature->id, 'confirmed' => 0, 'is_spam' => 1]);
        $this->assertDatabaseHas('petitions', ['id' => $petition->id, 'signature_count' => 0]);

        $this->postJson(route('admin.signatures.action', $signature), ['action' => 'reject'])
            ->assertStatus(409)
            ->assertJsonPath('msg', 'Already rejected');
    }

    #[DataProvider('rejectRoutes')]
    public function test_approve_after_reject_confirms_and_counts(string $via): void
    {
        [, $petition, $signature] = $this->pendingSignature();

        $this->reject($via, $signature);

        $via === 'single'
            ? $this->postJson(route('admin.signatures.action', $signature), ['action' => 'approve'])->assertOk()
            : $this->postJson(route('admin.signatures.bulkAction'), ['action' => 'approve', 'ids' => [$signature->id]])->assertOk();

        $this->assertDatabaseHas('signatures', ['id' => $signature->id, 'confirmed' => 1, 'is_spam' => 0]);
        $this->assertDatabaseHas('petitions', ['id' => $petition->id, 'signature_count' => 1]);
    }

    public function test_approve_is_409_when_approved_and_clears_spam_on_confirmed_spam_row(): void
    {
        [, $petition, $signature] = $this->pendingSignature();
        $signature->forceFill(['confirmed' => true, 'is_spam' => false])->save();

        $this->postJson(route('admin.signatures.action', $signature), ['action' => 'approve'])
            ->assertStatus(409)
            ->assertJsonPath('msg', 'Already confirmed');

        $signature->forceFill(['confirmed' => true, 'is_spam' => true])->save();

        $this->postJson(route('admin.signatures.action', $signature), ['action' => 'approve'])->assertOk();

        $this->assertDatabaseHas('signatures', ['id' => $signature->id, 'confirmed' => 1, 'is_spam' => 0]);
        $this->assertDatabaseHas('petitions', ['id' => $petition->id, 'signature_count' => 1]);
    }

    public function test_index_shows_rejected_state_and_enables_approve(): void
    {
        [, , $signature] = $this->pendingSignature();

        $this->get(route('admin.signatures'))
            ->assertOk()
            ->assertSee('title="Unconfirmed"', false)
            ->assertSee('title="Reject signature"', false);

        $this->reject('single', $signature);

        $this->get(route('admin.signatures'))
            ->assertOk()
            ->assertSee('title="Rejected"', false)
            ->assertSee('title="Already rejected"', false)
            ->assertSee('title="Approve signature"', false);
    }
}
