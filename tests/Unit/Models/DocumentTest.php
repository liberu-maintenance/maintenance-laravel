<?php

namespace Tests\Unit\Models;

use App\Models\Document;
use App\Models\DocumentTag;
use App\Models\DocumentVersion;
use App\Models\Equipment;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'name',
            'description',
            'document_type',
            'file_path',
            'file_name',
            'mime_type',
            'file_size',
            'version',
            'status',
            'compliance_standard',
            'effective_date',
            'expiry_date',
            'review_date',
            'approval_status',
            'approved_by',
            'approved_at',
            'documentable_type',
            'documentable_id',
            'team_id',
            'created_by',
            'updated_by',
        ];

        $document = new Document();

        $this->assertEquals($fillable, $document->getFillable());
    }

    /** @test */
    public function it_can_be_attached_to_equipment()
    {
        $equipment = Equipment::factory()->create();
        $document = Document::factory()->create([
            'documentable_type' => Equipment::class,
            'documentable_id' => $equipment->id,
        ]);

        $this->assertInstanceOf(Equipment::class, $document->documentable);
        $this->assertEquals($equipment->id, $document->documentable->id);
    }

    /** @test */
    public function it_can_be_attached_to_work_order()
    {
        $workOrder = WorkOrder::factory()->create();
        $document = Document::factory()->create([
            'documentable_type' => WorkOrder::class,
            'documentable_id' => $workOrder->id,
        ]);

        $this->assertInstanceOf(WorkOrder::class, $document->documentable);
        $this->assertEquals($workOrder->id, $document->documentable->id);
    }

    /** @test */
    public function it_can_have_tags()
    {
        $document = Document::factory()->create();
        $tag1 = DocumentTag::factory()->create(['name' => 'Safety']);
        $tag2 = DocumentTag::factory()->create(['name' => 'Compliance']);

        $document->tags()->attach([$tag1->id, $tag2->id]);

        $this->assertCount(2, $document->tags);
        $this->assertTrue($document->tags->contains('name', 'Safety'));
        $this->assertTrue($document->tags->contains('name', 'Compliance'));
    }

    /** @test */
    public function it_can_have_versions()
    {
        $document = Document::factory()->create();
        
        DocumentVersion::create([
            'document_id' => $document->id,
            'version' => '2.0',
            'file_path' => 'documents/version2.pdf',
            'file_name' => 'document_v2.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 2048,
            'change_notes' => 'Updated safety procedures',
        ]);

        $this->assertCount(1, $document->versions);
        $this->assertEquals('2.0', $document->versions->first()->version);
    }

    /** @test */
    public function it_can_scope_active_documents()
    {
        Document::factory()->create(['status' => 'active']);
        Document::factory()->create(['status' => 'archived']);
        Document::factory()->create(['status' => 'active']);

        $activeDocuments = Document::active()->get();

        $this->assertCount(2, $activeDocuments);
    }

    /** @test */
    public function it_can_scope_approved_documents()
    {
        Document::factory()->create(['approval_status' => 'approved']);
        Document::factory()->create(['approval_status' => 'pending']);
        Document::factory()->create(['approval_status' => 'approved']);

        $approvedDocuments = Document::approved()->get();

        $this->assertCount(2, $approvedDocuments);
    }

    /** @test */
    public function it_can_detect_expired_documents()
    {
        $expiredDoc = Document::factory()->create([
            'expiry_date' => now()->subDays(10),
        ]);

        $validDoc = Document::factory()->create([
            'expiry_date' => now()->addDays(10),
        ]);

        $this->assertTrue($expiredDoc->isExpired());
        $this->assertFalse($validDoc->isExpired());
    }

    /** @test */
    public function it_can_detect_documents_expiring_soon()
    {
        $expiringSoon = Document::factory()->create([
            'expiry_date' => now()->addDays(15),
        ]);

        $notExpiringSoon = Document::factory()->create([
            'expiry_date' => now()->addDays(60),
        ]);

        $this->assertTrue($expiringSoon->isExpiringSoon(30));
        $this->assertFalse($notExpiringSoon->isExpiringSoon(30));
    }

    /** @test */
    public function it_can_detect_documents_due_for_review()
    {
        $dueForReview = Document::factory()->create([
            'review_date' => now()->subDays(5),
        ]);

        $notDueForReview = Document::factory()->create([
            'review_date' => now()->addDays(30),
        ]);

        $this->assertTrue($dueForReview->isDueForReview());
        $this->assertFalse($notDueForReview->isDueForReview());
    }

    /** @test */
    public function it_can_scope_documents_by_type()
    {
        Document::factory()->create(['document_type' => 'manual']);
        Document::factory()->create(['document_type' => 'compliance']);
        Document::factory()->create(['document_type' => 'manual']);

        $manuals = Document::ofType('manual')->get();

        $this->assertCount(2, $manuals);
    }

    /** @test */
    public function it_can_scope_documents_by_compliance_standard()
    {
        Document::factory()->create(['compliance_standard' => 'ISO 9001']);
        Document::factory()->create(['compliance_standard' => 'OSHA']);
        Document::factory()->create(['compliance_standard' => 'ISO 9001']);

        $isoDocuments = Document::compliantWith('ISO 9001')->get();

        $this->assertCount(2, $isoDocuments);
    }

    /** @test */
    public function it_formats_file_size_correctly()
    {
        $document = Document::factory()->create(['file_size' => 2048]);
        $this->assertEquals('2 KB', $document->formatted_file_size);

        $document2 = Document::factory()->create(['file_size' => 2097152]);
        $this->assertEquals('2 MB', $document2->formatted_file_size);
    }

    /** @test */
    public function it_belongs_to_a_creator()
    {
        $user = User::factory()->create();
        $document = Document::factory()->create(['created_by' => $user->id]);

        $this->assertInstanceOf(User::class, $document->creator);
        $this->assertEquals($user->id, $document->creator->id);
    }

    /** @test */
    public function it_belongs_to_an_approver()
    {
        $user = User::factory()->create();
        $document = Document::factory()->create([
            'approved_by' => $user->id,
            'approval_status' => 'approved',
        ]);

        $this->assertInstanceOf(User::class, $document->approver);
        $this->assertEquals($user->id, $document->approver->id);
    }

    /** @test */
    public function it_can_scope_expiring_soon_documents()
    {
        Document::factory()->create([
            'status' => 'active',
            'expiry_date' => now()->addDays(15),
        ]);
        
        Document::factory()->create([
            'status' => 'active',
            'expiry_date' => now()->addDays(60),
        ]);

        $expiringSoon = Document::expiringSoon(30)->get();

        $this->assertCount(1, $expiringSoon);
    }

    /** @test */
    public function it_can_scope_expired_documents()
    {
        Document::factory()->create([
            'status' => 'active',
            'expiry_date' => now()->subDays(10),
        ]);
        
        Document::factory()->create([
            'status' => 'active',
            'expiry_date' => now()->addDays(10),
        ]);

        $expired = Document::expired()->get();

        $this->assertCount(1, $expired);
    }

    /** @test */
    public function it_can_scope_documents_due_for_review()
    {
        Document::factory()->create([
            'status' => 'active',
            'review_date' => now()->subDays(5),
        ]);
        
        Document::factory()->create([
            'status' => 'active',
            'review_date' => now()->addDays(30),
        ]);

        $dueForReview = Document::dueForReview()->get();

        $this->assertCount(1, $dueForReview);
    }
}
