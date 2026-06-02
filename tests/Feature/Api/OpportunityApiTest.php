<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Opportunity;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OpportunityApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Team $team;

    protected function setUp(): void
    {
        parent::setUp();

        $this->team = Team::factory()->create();
        $this->user = User::factory()->create(['current_team_id' => $this->team->id]);
        $this->team->users()->attach($this->user);
    }

    #[Test]
    public function unauthenticated_requests_return_401(): void
    {
        $this->getJson('/api/v1/opportunities')->assertStatus(401);
    }

    #[Test]
    public function authenticated_user_can_list_opportunities(): void
    {
        Opportunity::factory()->count(3)->create(['team_id' => $this->team->id]);

        $this->actingAs($this->user)
            ->getJson('/api/v1/opportunities')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'total']);
    }

    #[Test]
    public function user_can_create_an_opportunity(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/opportunities', [
                'stage'        => 'prospecting',
                'deal_size'    => 50000,
                'closing_date' => now()->addMonths(3)->toDateString(),
            ]);

        $response->assertStatus(201)->assertJsonFragment(['stage' => 'prospecting']);
        $this->assertDatabaseHas('opportunities', ['stage' => 'prospecting', 'team_id' => $this->team->id]);
    }

    #[Test]
    public function create_requires_stage(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/v1/opportunities', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['stage']);
    }

    #[Test]
    public function deal_size_must_be_non_negative(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/v1/opportunities', ['stage' => 'prospecting', 'deal_size' => -100])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['deal_size']);
    }

    #[Test]
    public function user_can_view_an_opportunity(): void
    {
        $opportunity = Opportunity::factory()->create(['team_id' => $this->team->id]);

        $this->actingAs($this->user)
            ->getJson("/api/v1/opportunities/{$opportunity->opportunity_id}")
            ->assertStatus(200)
            ->assertJsonFragment(['stage' => $opportunity->stage]);
    }

    #[Test]
    public function user_cannot_view_another_teams_opportunity(): void
    {
        $otherTeam        = Team::factory()->create();
        $otherOpportunity = Opportunity::factory()->create(['team_id' => $otherTeam->id]);

        $this->actingAs($this->user)
            ->getJson("/api/v1/opportunities/{$otherOpportunity->opportunity_id}")
            ->assertStatus(404);
    }

    #[Test]
    public function user_can_update_an_opportunity(): void
    {
        $opportunity = Opportunity::factory()->create(['team_id' => $this->team->id]);

        $this->actingAs($this->user)
            ->putJson("/api/v1/opportunities/{$opportunity->opportunity_id}", [
                'stage'     => 'negotiation',
                'deal_size' => 75000,
            ])
            ->assertStatus(200)
            ->assertJsonFragment(['stage' => 'negotiation']);
    }

    #[Test]
    public function user_can_delete_an_opportunity(): void
    {
        $opportunity = Opportunity::factory()->create(['team_id' => $this->team->id]);

        $this->actingAs($this->user)
            ->deleteJson("/api/v1/opportunities/{$opportunity->opportunity_id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('opportunities', ['opportunity_id' => $opportunity->opportunity_id]);
    }

    #[Test]
    public function user_cannot_delete_another_teams_opportunity(): void
    {
        $otherTeam        = Team::factory()->create();
        $otherOpportunity = Opportunity::factory()->create(['team_id' => $otherTeam->id]);

        $this->actingAs($this->user)
            ->deleteJson("/api/v1/opportunities/{$otherOpportunity->opportunity_id}")
            ->assertStatus(404);
    }
}
