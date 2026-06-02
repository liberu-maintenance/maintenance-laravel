<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TaskApiTest extends TestCase
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
        $this->getJson('/api/v1/tasks')->assertStatus(401);
    }

    #[Test]
    public function authenticated_user_can_list_tasks(): void
    {
        Task::factory()->count(3)->create(['team_id' => $this->team->id]);

        $this->actingAs($this->user)
            ->getJson('/api/v1/tasks')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'total']);
    }

    #[Test]
    public function user_can_create_a_task(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/tasks', [
                'description' => 'Inspect HVAC unit',
                'status'      => 'pending',
                'due_date'    => now()->addDays(7)->toDateString(),
            ]);

        $response->assertStatus(201)->assertJsonFragment(['description' => 'Inspect HVAC unit']);
        $this->assertDatabaseHas('tasks', ['description' => 'Inspect HVAC unit', 'team_id' => $this->team->id]);
    }

    #[Test]
    public function create_requires_description(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/v1/tasks', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['description']);
    }

    #[Test]
    public function user_can_view_a_task(): void
    {
        $task = Task::factory()->create(['team_id' => $this->team->id]);

        $this->actingAs($this->user)
            ->getJson("/api/v1/tasks/{$task->task_id}")
            ->assertStatus(200)
            ->assertJsonFragment(['description' => $task->description]);
    }

    #[Test]
    public function user_cannot_view_another_teams_task(): void
    {
        $otherTeam = Team::factory()->create();
        $otherTask = Task::factory()->create(['team_id' => $otherTeam->id]);

        $this->actingAs($this->user)
            ->getJson("/api/v1/tasks/{$otherTask->task_id}")
            ->assertStatus(404);
    }

    #[Test]
    public function user_can_update_a_task(): void
    {
        $task = Task::factory()->create(['team_id' => $this->team->id]);

        $this->actingAs($this->user)
            ->putJson("/api/v1/tasks/{$task->task_id}", ['status' => 'completed'])
            ->assertStatus(200)
            ->assertJsonFragment(['status' => 'completed']);
    }

    #[Test]
    public function user_can_delete_a_task(): void
    {
        $task = Task::factory()->create(['team_id' => $this->team->id]);

        $this->actingAs($this->user)
            ->deleteJson("/api/v1/tasks/{$task->task_id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('tasks', ['task_id' => $task->task_id]);
    }
}
