<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentTag;
use App\Models\Equipment;
use App\Models\Team;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $team;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->team = Team::factory()->create();
        $this->user->teams()->attach($this->team);
    }

    /** @test */
    public function document_can_be_created_with_required_fields()
    {
        $document = Document::factory()->create([
            'name' => 'Safety Manual',
            'document_type' => 'manual',
            'status' => 'active',
            'team_id' => $this->team->id,
        ]);

        $this->assertDatabaseHas('documents', [
            'name' => 'Safety Manual',
            'document_type' => 'manual',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function document_can_be_tagged()
    {
        $document = Document::factory()->create(['team_id' => $this->team->id]);
        $tag1 = DocumentTag::factory()->create(['name' => 'Safety', 'team_id' => $this->team->id]);
        $tag2 = DocumentTag::factory()->create(['name' => 'Compliance', 'team_id' => $this->team->id]);

        $document->tags()->attach([$tag1->id, $tag2->id]);

        $this->assertDatabaseHas('document_tag', [
            'document_id' => $document->id,
            'document_tag_id' => $tag1->id,
        ]);

        $this->assertDatabaseHas('document_tag', [
            'document_id' => $document->id,
            'document_tag_id' => $tag2->id,
        ]);
    }

    /** @test */
    public function document_can_be_searched_by_type()
    {
        Document::factory()->create(['document_type' => 'manual', 'team_id' => $this->team->id]);
        Document::factory()->create(['document_type' => 'manual', 'team_id' => $this->team->id]);
        Document::factory()->create(['document_type' => 'compliance', 'team_id' => $this->team->id]);

        $manuals = Document::where('team_id', $this->team->id)
            ->ofType('manual')
            ->get();

        $this->assertCount(2, $manuals);
    }

    /** @test */
    public function document_can_be_filtered_by_tag()
    {
        $tag = DocumentTag::factory()->create(['name' => 'Safety', 'team_id' => $this->team->id]);
        
        $doc1 = Document::factory()->create(['team_id' => $this->team->id]);
        $doc2 = Document::factory()->create(['team_id' => $this->team->id]);
        $doc3 = Document::factory()->create(['team_id' => $this->team->id]);

        $doc1->tags()->attach($tag);
        $doc2->tags()->attach($tag);

        $safetyDocs = Document::whereHas('tags', function ($query) use ($tag) {
            $query->where('document_tags.id', $tag->id);
        })->get();

        $this->assertCount(2, $safetyDocs);
    }

    /** @test */
    public function document_can_be_versioned()
    {
        $document = Document::factory()->create([
            'version' => '1.0',
            'team_id' => $this->team->id,
        ]);

        // Create a new version
        $document->versions()->create([
            'version' => '2.0',
            'file_path' => 'documents/version2.pdf',
            'file_name' => 'document_v2.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 2048,
            'change_notes' => 'Updated procedures',
            'created_by' => $this->user->id,
        ]);

        $this->assertDatabaseHas('document_versions', [
            'document_id' => $document->id,
            'version' => '2.0',
        ]);

        $this->assertCount(1, $document->versions);
    }

    /** @test */
    public function document_can_be_attached_to_equipment()
    {
        $equipment = Equipment::factory()->create(['team_id' => $this->team->id]);
        
        $document = Document::factory()->create([
            'documentable_type' => Equipment::class,
            'documentable_id' => $equipment->id,
            'team_id' => $this->team->id,
        ]);

        $this->assertInstanceOf(Equipment::class, $document->documentable);
        $this->assertEquals($equipment->id, $document->documentable->id);
        $this->assertCount(1, $equipment->documents);
    }

    /** @test */
    public function document_can_be_attached_to_work_order()
    {
        $workOrder = WorkOrder::factory()->create(['team_id' => $this->team->id]);
        
        $document = Document::factory()->create([
            'documentable_type' => WorkOrder::class,
            'documentable_id' => $workOrder->id,
            'team_id' => $this->team->id,
        ]);

        $this->assertInstanceOf(WorkOrder::class, $document->documentable);
        $this->assertEquals($workOrder->id, $document->documentable->id);
        $this->assertCount(1, $workOrder->documents);
    }

    /** @test */
    public function compliance_document_tracks_expiry()
    {
        $document = Document::factory()->create([
            'document_type' => 'compliance',
            'compliance_standard' => 'ISO 9001',
            'effective_date' => now()->subMonths(6),
            'expiry_date' => now()->addMonths(6),
            'team_id' => $this->team->id,
        ]);

        $this->assertFalse($document->isExpired());
        
        // Update to expired
        $document->update(['expiry_date' => now()->subDays(1)]);
        
        $this->assertTrue($document->isExpired());
    }

    /** @test */
    public function document_approval_workflow()
    {
        $document = Document::factory()->create([
            'approval_status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
            'team_id' => $this->team->id,
        ]);

        $this->assertEquals('pending', $document->approval_status);

        // Approve the document
        $document->update([
            'approval_status' => 'approved',
            'approved_by' => $this->user->id,
            'approved_at' => now(),
        ]);

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'approval_status' => 'approved',
            'approved_by' => $this->user->id,
        ]);
    }

    /** @test */
    public function document_can_be_soft_deleted()
    {
        $document = Document::factory()->create(['team_id' => $this->team->id]);
        
        $document->delete();

        $this->assertSoftDeleted('documents', [
            'id' => $document->id,
        ]);

        // Can still retrieve with trashed
        $this->assertCount(1, Document::withTrashed()->where('id', $document->id)->get());
    }

    /** @test */
    public function expired_documents_can_be_identified()
    {
        Document::factory()->create([
            'status' => 'active',
            'expiry_date' => now()->subDays(10),
            'team_id' => $this->team->id,
        ]);

        Document::factory()->create([
            'status' => 'active',
            'expiry_date' => now()->addDays(10),
            'team_id' => $this->team->id,
        ]);

        $expired = Document::expired()->get();

        $this->assertCount(1, $expired);
    }

    /** @test */
    public function documents_expiring_soon_can_be_identified()
    {
        Document::factory()->create([
            'status' => 'active',
            'expiry_date' => now()->addDays(15),
            'team_id' => $this->team->id,
        ]);

        Document::factory()->create([
            'status' => 'active',
            'expiry_date' => now()->addDays(60),
            'team_id' => $this->team->id,
        ]);

        $expiringSoon = Document::expiringSoon(30)->get();

        $this->assertCount(1, $expiringSoon);
    }

    /** @test */
    public function documents_due_for_review_can_be_identified()
    {
        Document::factory()->create([
            'status' => 'active',
            'review_date' => now()->subDays(5),
            'team_id' => $this->team->id,
        ]);

        Document::factory()->create([
            'status' => 'active',
            'review_date' => now()->addDays(30),
            'team_id' => $this->team->id,
        ]);

        $dueForReview = Document::dueForReview()->get();

        $this->assertCount(1, $dueForReview);
    }

    /** @test */
    public function document_tag_slug_is_generated_automatically()
    {
        $tag = DocumentTag::factory()->create([
            'name' => 'Safety Manual',
            'team_id' => $this->team->id,
        ]);

        $this->assertEquals('safety-manual', $tag->slug);
    }

    /** @test */
    public function multiple_documents_can_share_same_tag()
    {
        $tag = DocumentTag::factory()->create(['name' => 'Safety', 'team_id' => $this->team->id]);
        
        $doc1 = Document::factory()->create(['team_id' => $this->team->id]);
        $doc2 = Document::factory()->create(['team_id' => $this->team->id]);
        $doc3 = Document::factory()->create(['team_id' => $this->team->id]);

        $doc1->tags()->attach($tag);
        $doc2->tags()->attach($tag);
        $doc3->tags()->attach($tag);

        $this->assertCount(3, $tag->documents);
    }

    /** @test */
    public function compliance_standard_filtering_works()
    {
        Document::factory()->create([
            'compliance_standard' => 'ISO 9001',
            'team_id' => $this->team->id,
        ]);

        Document::factory()->create([
            'compliance_standard' => 'ISO 9001',
            'team_id' => $this->team->id,
        ]);

        Document::factory()->create([
            'compliance_standard' => 'OSHA',
            'team_id' => $this->team->id,
        ]);

        $isoDocuments = Document::compliantWith('ISO 9001')->get();

        $this->assertCount(2, $isoDocuments);
    }
}
