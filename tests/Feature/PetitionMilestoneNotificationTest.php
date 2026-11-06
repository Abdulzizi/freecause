<?php

namespace Tests\Feature;

use App\Mail\PetitionMilestoneMail;
use App\Models\Petition;
use App\Models\User;
use App\Services\PetitionStats;
use Database\Seeders\UserLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PetitionMilestoneNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserLevelSeeder::class);
    }

    private function sign(Petition $petition, int $by = 1): void
    {
        $previous = PetitionStats::incrementMany([$petition->id => $by]);
        PetitionStats::notifyMilestoneIfCrossed($petition->id, $previous[$petition->id]);
    }

    private function petitionFor(User $owner, int $count): Petition
    {
        return Petition::factory()->create([
            'user_id' => $owner->id,
            'signature_count' => $count,
        ]);
    }

    public function test_opted_in_owner_gets_one_mail_when_crossing_100(): void
    {
        Mail::fake();
        $owner = User::factory()->create(['notify_milestones' => true, 'unsubscribed_at' => null]);
        $petition = $this->petitionFor($owner, 99);

        $this->sign($petition);

        Mail::assertQueued(PetitionMilestoneMail::class, 1);
        Mail::assertQueued(PetitionMilestoneMail::class, fn ($mail) => $mail->hasTo($owner->email));
    }

    public function test_no_extra_mail_for_the_next_sign_after_the_milestone(): void
    {
        Mail::fake();
        $owner = User::factory()->create(['notify_milestones' => true, 'unsubscribed_at' => null]);
        $petition = $this->petitionFor($owner, 99);

        $this->sign($petition);
        $this->sign($petition);

        Mail::assertQueued(PetitionMilestoneMail::class, 1);
    }

    public function test_opted_out_owner_gets_no_mail_when_crossing_100(): void
    {
        Mail::fake();
        $owner = User::factory()->create(['notify_milestones' => false]);
        $petition = $this->petitionFor($owner, 99);

        $this->sign($petition);

        Mail::assertNothingQueued();
    }
}
