<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Note;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NoteTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_expected_fillable_attributes(): void
    {
        $note     = new Note();
        $fillable = $note->getFillable();

        $this->assertContains('content', $fillable);
        $this->assertContains('contact_id', $fillable);
        $this->assertContains('company_id', $fillable);
        $this->assertContains('team_id', $fillable);
    }

    #[Test]
    public function it_uses_note_id_as_primary_key(): void
    {
        $note = new Note();

        $this->assertEquals('note_id', $note->getKeyName());
    }

    #[Test]
    public function it_belongs_to_a_team(): void
    {
        $team = Team::factory()->create();
        $note = Note::factory()->create(['team_id' => $team->id]);

        $this->assertInstanceOf(Team::class, $note->team);
        $this->assertEquals($team->id, $note->team->id);
    }

    #[Test]
    public function it_belongs_to_a_contact(): void
    {
        $contact = Contact::factory()->create();
        $note    = Note::factory()->create(['contact_id' => $contact->contact_id]);

        $this->assertInstanceOf(Contact::class, $note->contact);
    }

    #[Test]
    public function it_belongs_to_a_company(): void
    {
        $company = Company::factory()->create();
        $note    = Note::factory()->create(['company_id' => $company->company_id]);

        $this->assertInstanceOf(Company::class, $note->company);
    }

    #[Test]
    public function it_can_be_created_with_just_content(): void
    {
        $note = Note::create(['content' => 'Simple note content']);

        $this->assertDatabaseHas('notes', ['content' => 'Simple note content']);
    }
}
