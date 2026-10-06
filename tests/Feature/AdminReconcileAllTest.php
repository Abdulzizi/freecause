<?php

namespace Tests\Feature;

use App\Models\Petition;
use App\Models\Signature;
use App\Models\User;
use App\Models\UserLevel;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReconcileAllTest extends TestCase
{
    use RefreshDatabase;

    public function test_reconcile_all_uses_confirmed_signature_counts_and_zeros_empty_petitions(): void
    {
        $this->seed(UserLevelSeeder::class);
        $admin = User::factory()->create([
            'level_id' => UserLevel::where('name', 'admin')->value('id'),
            'verified' => true,
        ]);
        $this->actingAs($admin, 'admin');

        $withSignatures = Petition::factory()->create(['signature_count' => 99]);
        $empty = Petition::factory()->create(['signature_count' => 55]);

        Signature::factory()->count(2)->create([
            'petition_id' => $withSignatures->id,
            'confirmed' => true,
        ]);
        Signature::factory()->create([
            'petition_id' => $withSignatures->id,
            'confirmed' => false,
        ]);

        $this->postJson(route('admin.petitions.reconcileAll'))
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('total_petitions', 2);

        $this->assertDatabaseHas('petitions', [
            'id' => $withSignatures->id,
            'signature_count' => 2,
        ]);
        $this->assertDatabaseHas('petitions', [
            'id' => $empty->id,
            'signature_count' => 0,
        ]);
    }
}
