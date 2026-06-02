<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Contact;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ContactApiTest extends TestCase
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
        $this->getJson('/api/v1/contacts')->assertStatus(401);
    }

    #[Test]
    public function authenticated_user_can_list_contacts(): void
    {
        Contact::factory()->count(3)->create(['team_id' => $this->team->id]);

        $this->actingAs($this->user)
            ->getJson('/api/v1/contacts')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'total']);
    }

    #[Test]
    public function user_can_create_a_contact(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/contacts', [
                'name'         => 'Jane',
                'last_name'    => 'Doe',
                'email'        => 'jane.doe@example.com',
                'phone_number' => '555-1234',
            ]);

        $response->assertStatus(201)->assertJsonFragment(['name' => 'Jane']);
        $this->assertDatabaseHas('contacts', ['email' => 'jane.doe@example.com', 'team_id' => $this->team->id]);
    }

    #[Test]
    public function create_requires_name(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/v1/contacts', ['email' => 'bad@example.com'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function user_can_view_a_contact(): void
    {
        $contact = Contact::factory()->create(['team_id' => $this->team->id]);

        $this->actingAs($this->user)
            ->getJson("/api/v1/contacts/{$contact->contact_id}")
            ->assertStatus(200)
            ->assertJsonFragment(['name' => $contact->name]);
    }

    #[Test]
    public function user_cannot_view_another_teams_contact(): void
    {
        $otherTeam    = Team::factory()->create();
        $otherContact = Contact::factory()->create(['team_id' => $otherTeam->id]);

        $this->actingAs($this->user)
            ->getJson("/api/v1/contacts/{$otherContact->contact_id}")
            ->assertStatus(404);
    }

    #[Test]
    public function user_can_update_a_contact(): void
    {
        $contact = Contact::factory()->create(['team_id' => $this->team->id]);

        $this->actingAs($this->user)
            ->putJson("/api/v1/contacts/{$contact->contact_id}", ['name' => 'Updated Name'])
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Name']);
    }

    #[Test]
    public function user_can_delete_a_contact(): void
    {
        $contact = Contact::factory()->create(['team_id' => $this->team->id]);

        $this->actingAs($this->user)
            ->deleteJson("/api/v1/contacts/{$contact->contact_id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('contacts', ['contact_id' => $contact->contact_id]);
    }
}
