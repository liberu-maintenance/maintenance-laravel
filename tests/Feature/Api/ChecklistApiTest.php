<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Checklist;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ChecklistApiTest extends TestCase
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
        $this->getJson('/api/v1/checklists')->assertStatus(401);
    }

    #[Test]
    public function authenticated_user_can_list_checklists(): void
    {
        Checklist::factory()->count(3)->create(['team_id' => $this->team->id]);

        $this->actingAs($this->user)
            ->getJson('/api/v1/checklists')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'total']);
    }

    #[Test]
    public function user_can_create_a_checklist(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/checklists', [
                'name'     => 'Safety Inspection',
                'category' => 'Safety',
                'status'   => 'active',
            ]);

        $response->assertStatus(201)->assertJsonFragment(['name' => 'Safety Inspection']);
        $this->assertDatabaseHas('checklists', ['name' => 'Safety Inspection', 'team_id' => $this->team->id]);
    }

    #[Test]
    public function create_requires_name(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/v1/checklists', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function user_can_view_a_checklist(): void
    {
        $checklist = Checklist::factory()->create(['team_id' => $this->team->id]);

        $this->actingAs($this->user)
            ->getJson("/api/v1/checklists/{$checklist->id}")
            ->assertStatus(200)
            ->assertJsonFragment(['name' => $checklist->name]);
    }

    #[Test]
    public function user_cannot_view_another_teams_checklist(): void
    {
        $otherTeam      = Team::factory()->create();
        $otherChecklist = Checklist::factory()->create(['team_id' => $otherTeam->id]);

        $this->actingAs($this->user)
            ->getJson("/api/v1/checklists/{$otherChecklist->id}")
            ->assertStatus(404);
    }

    #[Test]
    public function user_can_update_a_checklist(): void
    {
        $checklist = Checklist::factory()->create(['team_id' => $this->team->id]);

        $this->actingAs($this->user)
            ->putJson("/api/v1/checklists/{$checklist->id}", ['name' => 'Updated Checklist'])
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Checklist']);
    }

    #[Test]
    public function user_can_delete_a_checklist(): void
    {
        $checklist = Checklist::factory()->create(['team_id' => $this->team->id]);

        $this->actingAs($this->user)
            ->deleteJson("/api/v1/checklists/{$checklist->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('checklists', ['id' => $checklist->id]);
    }
}
