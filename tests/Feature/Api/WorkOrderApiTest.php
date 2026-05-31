<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Team;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WorkOrderApiTest extends TestCase
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
        $response = $this->getJson('/api/v1/work-orders');

        $response->assertStatus(401);
    }

    #[Test]
    public function authenticated_user_can_list_work_orders(): void
    {
        WorkOrder::factory()->count(3)->create(['team_id' => $this->team->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/work-orders');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function index_only_returns_team_work_orders(): void
    {
        WorkOrder::factory()->count(2)->create(['team_id' => $this->team->id]);

        $otherTeam = Team::factory()->create();
        WorkOrder::factory()->count(4)->create(['team_id' => $otherTeam->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/work-orders');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    // -----------------------------------------------------------------------
    // Store
    // -----------------------------------------------------------------------

    #[Test]
    public function unauthenticated_requests_to_store_return_401(): void
    {
        $response = $this->postJson('/api/v1/work-orders', ['title' => 'Test']);

        $response->assertStatus(401);
    }

    #[Test]
    public function authenticated_user_can_create_work_order(): void
    {
        $payload = [
            'title'       => 'Fix HVAC Unit',
            'description' => 'The HVAC is making a noise',
            'priority'    => 'high',
            'status'      => 'pending',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/work-orders', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'Fix HVAC Unit']);

        $this->assertDatabaseHas('work_orders', [
            'title'   => 'Fix HVAC Unit',
            'team_id' => $this->team->id,
        ]);
    }

    #[Test]
    public function store_returns_422_when_title_is_missing(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/work-orders', ['description' => 'No title']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    #[Test]
    public function store_returns_422_for_invalid_priority(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/work-orders', [
                'title'    => 'Valid title',
                'priority' => 'super-urgent',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['priority']);
    }

    // -----------------------------------------------------------------------
    // Show
    // -----------------------------------------------------------------------

    #[Test]
    public function unauthenticated_requests_to_show_return_401(): void
    {
        $workOrder = WorkOrder::factory()->create(['team_id' => $this->team->id]);

        $response = $this->getJson("/api/v1/work-orders/{$workOrder->id}");

        $response->assertStatus(401);
    }

    #[Test]
    public function authenticated_user_can_view_own_team_work_order(): void
    {
        $workOrder = WorkOrder::factory()->create([
            'team_id' => $this->team->id,
            'title'   => 'My Work Order',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/work-orders/{$workOrder->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'My Work Order']);
    }

    #[Test]
    public function show_returns_404_for_other_teams_work_order(): void
    {
        $otherTeam = Team::factory()->create();
        $workOrder = WorkOrder::factory()->create(['team_id' => $otherTeam->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/work-orders/{$workOrder->id}");

        $response->assertStatus(404);
    }

    // -----------------------------------------------------------------------
    // Update
    // -----------------------------------------------------------------------

    #[Test]
    public function authenticated_user_can_update_own_team_work_order(): void
    {
        $workOrder = WorkOrder::factory()->create([
            'team_id' => $this->team->id,
            'title'   => 'Old Title',
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/work-orders/{$workOrder->id}", ['title' => 'Updated Title']);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Updated Title']);

        $this->assertDatabaseHas('work_orders', ['id' => $workOrder->id, 'title' => 'Updated Title']);
    }

    #[Test]
    public function update_returns_404_for_other_teams_work_order(): void
    {
        $otherTeam = Team::factory()->create();
        $workOrder = WorkOrder::factory()->create(['team_id' => $otherTeam->id]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/work-orders/{$workOrder->id}", ['title' => 'Hack']);

        $response->assertStatus(404);
    }

    // -----------------------------------------------------------------------
    // Destroy
    // -----------------------------------------------------------------------

    #[Test]
    public function authenticated_user_can_delete_own_team_work_order(): void
    {
        $workOrder = WorkOrder::factory()->create(['team_id' => $this->team->id]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/work-orders/{$workOrder->id}");

        $response->assertStatus(204);

        // WorkOrder uses SoftDeletes — check it's not in unscoped query
        $this->assertSoftDeleted('work_orders', ['id' => $workOrder->id]);
    }

    #[Test]
    public function destroy_returns_404_for_other_teams_work_order(): void
    {
        $otherTeam = Team::factory()->create();
        $workOrder = WorkOrder::factory()->create(['team_id' => $otherTeam->id]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/work-orders/{$workOrder->id}");

        $response->assertStatus(404);
        $this->assertDatabaseHas('work_orders', ['id' => $workOrder->id]);
    }
}
