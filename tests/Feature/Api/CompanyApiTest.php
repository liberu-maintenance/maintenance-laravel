<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CompanyApiTest extends TestCase
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
        $this->getJson('/api/v1/companies')->assertStatus(401);
    }

    #[Test]
    public function authenticated_user_can_list_companies(): void
    {
        Company::factory()->count(3)->create(['team_id' => $this->team->id]);

        $this->actingAs($this->user)
            ->getJson('/api/v1/companies')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'total', 'per_page']);
    }

    #[Test]
    public function user_can_create_a_company(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/companies', [
                'name'     => 'Acme Corp',
                'email'    => 'contact@acme.com',
                'industry' => 'Manufacturing',
                'type'     => 'customer',
            ]);

        $response->assertStatus(201)->assertJsonFragment(['name' => 'Acme Corp']);
        $this->assertDatabaseHas('companies', ['name' => 'Acme Corp', 'team_id' => $this->team->id]);
    }

    #[Test]
    public function create_requires_name(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/v1/companies', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function user_can_view_a_company(): void
    {
        $company = Company::factory()->create(['team_id' => $this->team->id]);

        $this->actingAs($this->user)
            ->getJson("/api/v1/companies/{$company->company_id}")
            ->assertStatus(200)
            ->assertJsonFragment(['name' => $company->name]);
    }

    #[Test]
    public function user_cannot_view_another_teams_company(): void
    {
        $otherTeam    = Team::factory()->create();
        $otherCompany = Company::factory()->create(['team_id' => $otherTeam->id]);

        $this->actingAs($this->user)
            ->getJson("/api/v1/companies/{$otherCompany->company_id}")
            ->assertStatus(404);
    }

    #[Test]
    public function user_can_update_a_company(): void
    {
        $company = Company::factory()->create(['team_id' => $this->team->id]);

        $this->actingAs($this->user)
            ->putJson("/api/v1/companies/{$company->company_id}", ['name' => 'Updated Corp'])
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Corp']);
    }

    #[Test]
    public function user_can_delete_a_company(): void
    {
        $company = Company::factory()->create(['team_id' => $this->team->id]);

        $this->actingAs($this->user)
            ->deleteJson("/api/v1/companies/{$company->company_id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('companies', ['company_id' => $company->company_id]);
    }

    #[Test]
    public function user_cannot_delete_another_teams_company(): void
    {
        $otherTeam    = Team::factory()->create();
        $otherCompany = Company::factory()->create(['team_id' => $otherTeam->id]);

        $this->actingAs($this->user)
            ->deleteJson("/api/v1/companies/{$otherCompany->company_id}")
            ->assertStatus(404);
    }
}
