<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Equipment;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EquipmentApiTest extends TestCase
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

    // -----------------------------------------------------------------------
    // Index
    // -----------------------------------------------------------------------

    #[Test]
    public function unauthenticated_requests_to_index_return_401(): void
    {
        $response = $this->getJson('/api/v1/equipment');

        $response->assertStatus(401);
    }

    #[Test]
    public function authenticated_user_can_list_equipment(): void
    {
        Equipment::factory()->count(3)->create(['team_id' => $this->team->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/equipment');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function index_only_returns_team_equipment(): void
    {
        Equipment::factory()->count(2)->create(['team_id' => $this->team->id]);

        $otherTeam = Team::factory()->create();
        Equipment::factory()->count(3)->create(['team_id' => $otherTeam->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/equipment');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    // -----------------------------------------------------------------------
    // Store
    // -----------------------------------------------------------------------

    #[Test]
    public function unauthenticated_requests_to_store_return_401(): void
    {
        $response = $this->postJson('/api/v1/equipment', ['name' => 'Test']);

        $response->assertStatus(401);
    }

    #[Test]
    public function authenticated_user_can_create_equipment(): void
    {
        $payload = [
            'name'        => 'Test Compressor',
            'description' => 'A compressor',
            'category'    => 'Mechanical',
            'status'      => 'active',
            'criticality' => 'high',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/equipment', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Test Compressor']);

        $this->assertDatabaseHas('equipment', [
            'name'    => 'Test Compressor',
            'team_id' => $this->team->id,
        ]);
    }

    #[Test]
    public function store_returns_422_when_name_is_missing(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/equipment', ['description' => 'No name']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    // -----------------------------------------------------------------------
    // Show
    // -----------------------------------------------------------------------

    #[Test]
    public function unauthenticated_requests_to_show_return_401(): void
    {
        $equipment = Equipment::factory()->create(['team_id' => $this->team->id]);

        $response = $this->getJson("/api/v1/equipment/{$equipment->id}");

        $response->assertStatus(401);
    }

    #[Test]
    public function authenticated_user_can_view_own_team_equipment(): void
    {
        $equipment = Equipment::factory()->create([
            'team_id' => $this->team->id,
            'name'    => 'My Equipment',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/equipment/{$equipment->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'My Equipment']);
    }

    #[Test]
    public function show_returns_404_for_other_teams_equipment(): void
    {
        $otherTeam = Team::factory()->create();
        $equipment = Equipment::factory()->create(['team_id' => $otherTeam->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/equipment/{$equipment->id}");

        $response->assertStatus(404);
    }

    // -----------------------------------------------------------------------
    // Update
    // -----------------------------------------------------------------------

    #[Test]
    public function authenticated_user_can_update_own_team_equipment(): void
    {
        $equipment = Equipment::factory()->create([
            'team_id' => $this->team->id,
            'name'    => 'Old Name',
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/equipment/{$equipment->id}", ['name' => 'New Name']);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'New Name']);

        $this->assertDatabaseHas('equipment', ['id' => $equipment->id, 'name' => 'New Name']);
    }

    #[Test]
    public function update_returns_404_for_other_teams_equipment(): void
    {
        $otherTeam = Team::factory()->create();
        $equipment = Equipment::factory()->create(['team_id' => $otherTeam->id]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/equipment/{$equipment->id}", ['name' => 'Hack']);

        $response->assertStatus(404);
    }

    // -----------------------------------------------------------------------
    // Destroy
    // -----------------------------------------------------------------------

    #[Test]
    public function authenticated_user_can_delete_own_team_equipment(): void
    {
        $equipment = Equipment::factory()->create(['team_id' => $this->team->id]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/equipment/{$equipment->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('equipment', ['id' => $equipment->id]);
    }

    #[Test]
    public function destroy_returns_404_for_other_teams_equipment(): void
    {
        $otherTeam = Team::factory()->create();
        $equipment = Equipment::factory()->create(['team_id' => $otherTeam->id]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/equipment/{$equipment->id}");

        $response->assertStatus(404);
        $this->assertDatabaseHas('equipment', ['id' => $equipment->id]);
    }
}
