# Documentation Management System

## Overview

The Documentation Management System provides a centralized repository for storing, organizing, and retrieving maintenance manuals, service records, and compliance documents efficiently. This system is designed to meet industry standards and regulatory guidelines for document management in maintenance operations.

## Features

### Core Capabilities

1. **Centralized Document Storage**
   - Store all maintenance-related documents in one secure location
   - Support for multiple file types (PDF, Word, Images)
   - Maximum file size: 10MB per document

2. **Document Organization**
   - Categorize documents by type (manuals, service records, compliance documents, procedures, checklists, reports, certificates)
   - Tag documents for easy filtering and search
   - Attach documents to specific equipment, work orders, or maintenance schedules

3. **Version Control**
   - Track document versions
   - Maintain version history with change notes
   - Access previous versions when needed

4. **Compliance & Regulatory Features**
   - Track compliance standards (ISO 9001, OSHA, FDA, CE, etc.)
   - Set effective and expiry dates for documents
   - Schedule review dates
   - Monitor expired and expiring documents

5. **Document Approval Workflow**
   - Multi-stage approval process (pending, approved, rejected)
   - Track approvers and approval dates
   - Document status management (draft, active, archived, obsolete)

6. **Search & Filtering**
   - Full-text search on document names and descriptions
   - Filter by document type, status, approval status
   - Filter by compliance standard
   - Identify expired, expiring soon, and due-for-review documents

## Database Structure

### Documents Table

The main table storing document metadata:

- **name**: Document name
- **description**: Detailed description
- **document_type**: Type of document (manual, service_record, compliance, etc.)
- **file_path**: Storage path of the file
- **file_name**: Original filename
- **mime_type**: File MIME type
- **file_size**: File size in bytes
- **version**: Current version (e.g., 1.0, 2.0)
- **status**: Document status (draft, active, archived, obsolete)
- **compliance_standard**: Applicable compliance standard
- **effective_date**: When document becomes effective
- **expiry_date**: When document expires
- **review_date**: Next scheduled review date
- **approval_status**: Approval state (pending, approved, rejected)
- **approved_by**: User who approved the document
- **approved_at**: Approval timestamp
- **documentable_type/documentable_id**: Polymorphic relationship to attach to Equipment, WorkOrder, or MaintenanceSchedule
- **team_id**: Team ownership for multi-tenancy
- **created_by/updated_by**: User tracking

### Document Versions Table

Tracks all versions of a document:

- **document_id**: Parent document
- **version**: Version number
- **file_path**: Storage path for this version
- **file_name**: Filename for this version
- **change_notes**: Description of changes
- **created_by**: User who created this version

### Document Tags Table

Categorization system:

- **name**: Tag name
- **slug**: URL-friendly slug
- **description**: Tag description
- **color**: Visual color code
- **team_id**: Team ownership

## Usage Guide

### Creating a New Document

1. Navigate to "Documentation" → "Documents" in the menu
2. Click "New Document"
3. Fill in the required fields:
   - Document Name (required)
   - Description
   - Document Type (required)
   - Version (default: 1.0)
   - Status (default: active)
4. Upload the document file
5. Optionally set compliance information:
   - Compliance Standard
   - Effective Date
   - Expiry Date
   - Review Date
6. Optionally attach to Equipment, Work Order, or Maintenance Schedule
7. Save the document

### Managing Document Tags

1. Navigate to "Documentation" → "Tags"
2. Create tags to categorize documents (e.g., "Safety", "Compliance", "Maintenance")
3. Assign colors to tags for visual identification
4. Apply tags to documents for better organization

### Document Versioning

To create a new version of an existing document:

1. Edit the document
2. Update the version number
3. Upload the new file
4. The system maintains the previous version in the version history

### Filtering and Searching

Use the built-in filters to find documents:

- **By Type**: Manual, Service Record, Compliance, etc.
- **By Status**: Draft, Active, Archived, Obsolete
- **By Approval**: Pending, Approved, Rejected
- **By Compliance**: Filter by compliance standard
- **Special Filters**:
  - Expired documents
  - Expiring soon (within 30 days)
  - Due for review

### Compliance Monitoring

The system automatically tracks:

1. **Expired Documents**: Documents past their expiry date
2. **Expiring Soon**: Documents expiring within 30 days
3. **Due for Review**: Documents that need scheduled review

These can be accessed via filters in the document list.

### Document Relationships

Documents can be attached to:

- **Equipment**: Manuals, specifications, warranty documents
- **Work Orders**: Service reports, completion certificates
- **Maintenance Schedules**: Procedures, checklists

This creates a comprehensive documentation trail for all maintenance activities.

## API/Model Usage

### Document Model

```php
use App\Models\Document;

// Create a new document
$document = Document::create([
    'name' => 'Safety Manual',
    'document_type' => 'manual',
    'file_path' => 'documents/safety-manual.pdf',
    'file_name' => 'safety-manual.pdf',
    'status' => 'active',
    'team_id' => $teamId,
]);

// Add tags
$document->tags()->attach([$tag1->id, $tag2->id]);

// Attach to equipment
$document->update([
    'documentable_type' => Equipment::class,
    'documentable_id' => $equipment->id,
]);

// Check expiry
if ($document->isExpired()) {
    // Handle expired document
}

// Get expired documents
$expired = Document::expired()->get();

// Get documents expiring soon
$expiringSoon = Document::expiringSoon(30)->get();

// Get documents by type
$manuals = Document::ofType('manual')->get();

// Get approved documents
$approved = Document::approved()->get();
```

### DocumentTag Model

```php
use App\Models\DocumentTag;

// Create a tag
$tag = DocumentTag::create([
    'name' => 'Safety',
    'description' => 'Safety-related documents',
    'color' => '#ef4444',
    'team_id' => $teamId,
]);

// Get documents with this tag
$documents = $tag->documents;
```

### Querying Documents

```php
// Find documents by compliance standard
$isoDocuments = Document::compliantWith('ISO 9001')->get();

// Get documents due for review
$dueForReview = Document::dueForReview()->get();

// Get active documents
$active = Document::active()->get();
```

## Security Considerations

1. **File Upload Security**: Only specific file types are allowed (PDF, Word, Images)
2. **File Size Limits**: Maximum 10MB per document
3. **Team Isolation**: Documents are scoped to teams for multi-tenancy
4. **User Tracking**: All create/update operations are tracked
5. **Soft Deletes**: Documents can be recovered if accidentally deleted

## Compliance Standards Supported

The system supports tracking for common compliance standards:

- ISO 9001 (Quality Management)
- ISO 14001 (Environmental Management)
- OSHA (Occupational Safety and Health)
- FDA (Food and Drug Administration)
- CE (Conformité Européenne)
- Custom standards as needed

## Best Practices

1. **Regular Reviews**: Set review dates for all critical documents
2. **Version Control**: Always update version numbers when modifying documents
3. **Tag Consistently**: Use consistent tagging for better organization
4. **Set Expiry Dates**: Especially important for compliance documents
5. **Approval Workflow**: Use the approval system for quality control
6. **Attach to Assets**: Link documents to relevant equipment and work orders
7. **Monitor Compliance**: Regularly check for expired and expiring documents

## Reporting and Analytics

The system provides insights through:

- Document count by type
- Documents per tag
- Expired document alerts
- Upcoming expiry notifications
- Review schedule tracking

## Integration Points

Documents can be integrated with:

- **Equipment Management**: Attach manuals and specifications
- **Work Orders**: Link service records and completion reports
- **Maintenance Schedules**: Associate procedures and checklists
- **Teams**: Multi-tenant document isolation

## Future Enhancements

Potential future improvements:

- OCR for searchable PDF content
- Document workflow automation
- Email notifications for expiring documents
- Document approval email workflows
- Advanced analytics dashboard
- Document templates
- Bulk document operations
