<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_expected_fillable_attributes(): void
    {
        $task     = new Task();
        $fillable = $task->getFillable();

        $this->assertContains('description', $fillable);
        $this->assertContains('due_date', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('team_id', $fillable);
        $this->assertContains('assigned_to', $fillable);
    }

    #[Test]
    public function it_uses_task_id_as_primary_key(): void
    {
        $task = new Task();

        $this->assertEquals('task_id', $task->getKeyName());
    }

    #[Test]
    public function it_belongs_to_a_team(): void
    {
        $team = Team::factory()->create();
        $task = Task::factory()->create(['team_id' => $team->id]);

        $this->assertInstanceOf(Team::class, $task->team);
        $this->assertEquals($team->id, $task->team->id);
    }

    #[Test]
    public function it_belongs_to_a_contact(): void
    {
        $contact = Contact::factory()->create();
        $task    = Task::factory()->create(['contact_id' => $contact->contact_id]);

        $this->assertInstanceOf(Contact::class, $task->contact);
    }

    #[Test]
    public function it_belongs_to_a_company(): void
    {
        $company = Company::factory()->create();
        $task    = Task::factory()->create(['company_id' => $company->company_id]);

        $this->assertInstanceOf(Company::class, $task->company);
    }

    #[Test]
    public function it_belongs_to_an_assigned_user(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['assigned_to' => $user->id]);

        $this->assertInstanceOf(User::class, $task->assignedUser);
        $this->assertEquals($user->id, $task->assignedUser->id);
    }

    #[Test]
    public function it_can_be_created_with_minimal_data(): void
    {
        $task = Task::create([
            'task_id'     => 99998,
            'description' => 'Minimal task description',
            'due_date'    => now()->addDays(7),
            'status'      => 'pending',
            'priority'    => 1,
        ]);

        $this->assertDatabaseHas('tasks', ['description' => 'Minimal task description']);
    }
}
