<?php

namespace Tests\Feature;

use App\Models\Petition;
use App\Models\PetitionTranslation;
use App\Models\User;
use App\Models\UserLevel;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AdminStatsLocaleFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Petition $enPetition;

    private Petition $dePetition;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->seed(LanguageSeeder::class);
        $this->seed(UserLevelSeeder::class);

        $this->admin = User::factory()->create([
            'email' => 'admin@example.com',
            'locale' => 'en_US',
            'verified' => 0,
            'level_id' => UserLevel::where('name', 'admin')->value('id'),
        ]);
        User::factory()->create(['email' => 'en1@gmail.com', 'locale' => 'en_US', 'verified' => 1]);
        $de = User::factory()->create(['email' => 'de1@gmail.com', 'locale' => 'de_DE', 'verified' => 1]);

        $this->enPetition = Petition::factory()->create(['user_id' => $this->admin->id]);
        PetitionTranslation::factory()->create(['petition_id' => $this->enPetition->id, 'locale' => 'en']);
        $this->dePetition = Petition::factory()->create(['user_id' => $de->id]);
        PetitionTranslation::factory()->create(['petition_id' => $this->dePetition->id, 'locale' => 'de']);
    }

    private function stats(array $query)
    {
        return $this->actingAs($this->admin, 'admin')->get(route('admin.stats', $query));
    }

    public function test_new_counts_follow_locale(): void
    {
        $all = $this->stats([])->viewData('summary');
        $de = $this->stats(['locale' => 'de'])->viewData('summary');

        $this->assertSame(3, $all['users_new']);
        $this->assertSame(2, $all['petitions_new']);
        $this->assertSame(1, $de['users_new']);
        $this->assertSame(1, $de['petitions_new']);
    }

    public function test_reports_follow_locale(): void
    {
        $emails = $this->stats(['type' => 'emails_verified', 'locale' => 'de'])->viewData('results');
        $this->assertSame(['de1@gmail.com'], collect($emails->items())->pluck('email')->all());

        $petitions = $this->stats(['type' => 'petitions_all', 'locale' => 'de'])->viewData('results');
        $this->assertSame([$this->dePetition->id], collect($petitions->items())->pluck('id')->all());

        $owners = $this->stats(['type' => 'top_petitioners', 'locale' => 'de'])->viewData('results');
        $this->assertSame(['de1@gmail.com'], collect($owners->items())->pluck('email')->all());
    }
}
