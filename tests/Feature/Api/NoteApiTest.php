<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Note;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NoteApiTest extends TestCase
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
        $this->getJson('/api/v1/notes')->assertStatus(401);
    }

    #[Test]
    public function authenticated_user_can_list_notes(): void
    {
        Note::factory()->count(3)->create(['team_id' => $this->team->id]);

        $this->actingAs($this->user)
            ->getJson('/api/v1/notes')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'total']);
    }

    #[Test]
    public function user_can_create_a_note(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/notes', ['content' => 'Test note content']);

        $response->assertStatus(201)->assertJsonFragment(['content' => 'Test note content']);
        $this->assertDatabaseHas('notes', ['content' => 'Test note content', 'team_id' => $this->team->id]);
    }

    #[Test]
    public function create_requires_content(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/v1/notes', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    #[Test]
    public function user_can_view_a_note(): void
    {
        $note = Note::factory()->create(['team_id' => $this->team->id]);

        $this->actingAs($this->user)
            ->getJson("/api/v1/notes/{$note->note_id}")
            ->assertStatus(200)
            ->assertJsonFragment(['content' => $note->content]);
    }

    #[Test]
    public function user_cannot_view_another_teams_note(): void
    {
        $otherTeam = Team::factory()->create();
        $otherNote = Note::factory()->create(['team_id' => $otherTeam->id]);

        $this->actingAs($this->user)
            ->getJson("/api/v1/notes/{$otherNote->note_id}")
            ->assertStatus(404);
    }

    #[Test]
    public function user_can_update_a_note(): void
    {
        $note = Note::factory()->create(['team_id' => $this->team->id]);

        $this->actingAs($this->user)
            ->putJson("/api/v1/notes/{$note->note_id}", ['content' => 'Updated content'])
            ->assertStatus(200)
            ->assertJsonFragment(['content' => 'Updated content']);
    }

    #[Test]
    public function user_can_delete_a_note(): void
    {
        $note = Note::factory()->create(['team_id' => $this->team->id]);

        $this->actingAs($this->user)
            ->deleteJson("/api/v1/notes/{$note->note_id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('notes', ['note_id' => $note->note_id]);
    }
}
