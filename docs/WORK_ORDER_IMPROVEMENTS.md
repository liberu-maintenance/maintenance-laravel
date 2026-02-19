# Work Order System Improvements

## Overview

The work order system has been significantly enhanced to provide better tracking, assignment, and management capabilities for maintenance tasks. These improvements align with industry-standard CMMS (Computerized Maintenance Management System) practices.

## New Features

### 1. Work Assignment

Work orders can now be assigned to specific technicians/users:

- **Field**: `assigned_to` (User relationship)
- **Purpose**: Track which technician is responsible for completing the work
- **Usage**: Select a user from the "Assigned To" dropdown in the work order form

### 2. Due Date Tracking

Work orders can have due dates for SLA (Service Level Agreement) management:

- **Field**: `due_date` (DateTime)
- **Purpose**: Track when work must be completed
- **Features**:
  - Due dates display in red when overdue
  - Navigation badge shows count of overdue work orders
  - Filter work orders by due date

### 3. Completion Tracking

Automatic tracking of work progress timestamps:

- **Fields**:
  - `started_at` - When work actually begins
  - `completed_at` - When work is finished
- **Purpose**: Track actual work duration and completion times
- **Automation**: Timestamps are automatically set when status changes:
  - `started_at` is set when status changes to "in_progress"
  - `completed_at` is set when status changes to "completed"

### 4. Labor Hour Tracking

Track estimated vs. actual labor hours:

- **Fields**:
  - `estimated_hours` - Planned hours for completion
  - `actual_hours` - Actual hours spent
- **Purpose**: Improve future estimates and track labor costs
- **Usage**: Enter estimated hours when creating work order, actual hours when completing

### 5. Soft Deletes

Work orders are now soft-deleted instead of permanently removed:

- **Purpose**: Maintain audit trail and allow recovery
- **Features**:
  - Deleted work orders can be restored
  - Historical data is preserved
  - Queries exclude deleted records by default

### 6. Database Indexes

Performance improvements through strategic indexing:

- Indexes on: `status`, `priority`, `assigned_to`, `due_date`, `submitted_at`
- Composite index on `status` + `created_at`
- **Purpose**: Faster queries and improved performance at scale

### 7. Comments and Activity Log

Work orders now support comments and notes for documenting progress:

- **Model**: `WorkOrderComment`
- **Features**:
  - Add comments to document work progress
  - Internal vs. public comments
  - Track comment author and timestamp
  - Comments ordered by recency
- **Automation**: Workflow actions automatically create comment entries
- **Usage**: Access via "Comments & Notes" tab when editing a work order

### 8. Enhanced Workflow Management

Streamlined workflow actions on the Edit page:

- **Approve**: Approve pending work orders with confirmation
- **Reject**: Reject with required reason (creates comment log)
- **Start Work**: Begin work with automatic timestamp tracking
- **Complete**: Mark as complete with optional outcome notes
- **Reopen**: Reopen completed work orders with reason
- **All actions**:
  - Include confirmation dialogs
  - Create audit trail comments
  - Show notifications for success/errors
  - Only visible for appropriate statuses

### 9. Progress Tracking

Visual progress indicators throughout the UI:

- **Progress column**: Shows workflow stage (1/4 to 4/4)
- **Status tabs**: Filter by All, Pending, Approved, In Progress, Completed, Overdue
- **Badge counts**: Real-time counts on tabs and navigation (cached for 5 minutes)
- **Color coding**: Visual cues for status and urgency

## Model Enhancements

### New Relationships

```php
// Get assigned user
$workOrder->assignedTo; // Returns User model

// Get work orders assigned to a user
WorkOrder::assignedTo($userId)->get();

// Get comments for a work order
$workOrder->comments; // Returns collection of WorkOrderComment models

// Create a new comment
$workOrder->comments()->create([
    'user_id' => auth()->id(),
    'comment' => 'Work in progress, replaced faulty component',
    'is_internal' => false,
]);
```

### New Query Scopes

```php
// Get overdue work orders
WorkOrder::overdue()->get();

// Get work orders assigned to specific user
WorkOrder::assignedTo($userId)->get();

// Get work orders due within X days
WorkOrder::dueWithin(7)->get();
```

## Filament Resource Updates

### Form Sections

The work order form now includes:

1. **Assignment & Schedule Section**:
   - Team selection
   - Assigned to (user)
   - Due date
   - Started at (auto-filled)
   - Completed at (auto-filled)

2. **Labor Hours Section** (collapsible):
   - Estimated hours
   - Actual hours (only for completed work orders)

3. **Comments & Notes Tab**:
   - View all comments and notes
   - Add new comments with internal/public visibility
   - Filter by comment type
   - Shows author, timestamp, and content

### Table Columns

New columns in the work orders list:

- **Progress**: Visual indicator showing workflow stage (1/4 to 4/4)
- **Assigned To**: Shows assigned technician
- **Due Date**: Highlights overdue items in red
- **Started At**: Shows when work began (hidden by default)
- **Completed At**: Shows completion time (hidden by default)
- **Est. Hours**: Estimated labor hours (hidden by default)
- **Actual Hours**: Actual labor hours (hidden by default)

### Status Tabs

Filter work orders by status with badge counts:

- **All**: Shows all work orders
- **Pending**: Pending approval (yellow badge)
- **Approved**: Approved and ready to start (green badge)
- **In Progress**: Currently being worked on (blue badge)
- **Completed**: Finished work orders (green badge)
- **Overdue**: Past due date (red badge)

### Filters

New filter options:

- Filter by assigned user
- Existing filters: status, priority, equipment

### Navigation Badge

The navigation badge now shows:
- **Overdue count** (in red) if there are overdue work orders
- **Pending count** (in yellow) if no overdue items but pending items exist
- **Success** (green) if no pending or overdue items

## Automatic Behaviors

### WorkOrderObserver

The system automatically:

1. Sets `submitted_at` when a work order is created (if not already set)
2. Sets `started_at` when status changes to "in_progress"
3. Sets `completed_at` when status changes to "completed"

## Database Migrations

Three new migrations have been added:

1. **2026_02_14_000001_add_assignment_and_tracking_fields_to_work_orders_table.php**
   - Adds: assigned_to, due_date, started_at, completed_at, estimated_hours, actual_hours
   - Adds indexes for performance

2. **2026_02_14_000002_add_soft_deletes_to_work_orders_table.php**
   - Adds soft delete functionality

3. **2026_02_19_000001_create_work_order_comments_table.php**
   - Creates work_order_comments table
   - Supports internal vs public comments
   - Indexes for performance

To apply these migrations:

```bash
php artisan migrate
```

## Testing

A comprehensive test suite has been added:

**File**: `tests/Feature/WorkOrderImprovementsTest.php`

Tests cover:
- Assignment functionality
- Due date tracking
- Timestamp tracking
- Labor hour tracking
- Query scopes (overdue, assignedTo, dueWithin)
- Soft delete functionality
- Observer automatic behaviors
- Comment system functionality (new)
- Internal vs public comment visibility (new)
- Comment ordering and relationships (new)

Run tests:

```bash
php artisan test --filter WorkOrderImprovementsTest
```

## Seeder Updates

The WorkOrderSeeder now generates realistic test data with:
- Assigned users for approved/in-progress/completed work orders
- Due dates based on priority levels:
  - Urgent: 2-24 hours
  - High: 1-3 days
  - Medium: 3-7 days
  - Low: 7-14 days
- Started/completed timestamps where appropriate
- Estimated and actual hours (with realistic variance)

## Best Practices

### When Creating Work Orders

1. Always set a `due_date` for time-sensitive work
2. Assign an `estimated_hours` value to help with planning
3. Assign to a specific user when the technician is known
4. Update `actual_hours` when completing work for better future estimates
5. Add comments to document progress and outcomes
6. Use workflow action buttons for proper status transitions

### Monitoring Overdue Work

- Check the navigation badge for overdue count
- Use the overdue scope: `WorkOrder::overdue()->get()`
- Filter the work orders table by status and sort by due date

### Status Workflow

The recommended workflow using action buttons:

1. **Pending** → Submit work order
2. **Approved** → Click "Approve" button (sets `reviewed_by`, `reviewed_at`, creates comment)
3. **In Progress** → Click "Start Work" button (automatically sets `started_at`, creates comment)
4. **Completed** → Click "Complete" button with outcome notes (automatically sets `completed_at`, creates comment)

Alternative paths:
- **Pending** → **Rejected** using "Reject" button (requires reason, creates comment)
- **Completed** → **In Progress** using "Reopen" button (requires reason, creates comment)

All workflow actions:
- Create audit trail comments automatically
- Show confirmation dialogs
- Provide feedback via notifications
- Ensure data consistency

## API Reference

### Model Properties

```php
$workOrder->assigned_to;      // User ID
$workOrder->due_date;          // Carbon datetime
$workOrder->started_at;        // Carbon datetime
$workOrder->completed_at;      // Carbon datetime
$workOrder->estimated_hours;   // Integer
$workOrder->actual_hours;      // Integer
```

### Model Methods

```php
$workOrder->assignedTo();      // BelongsTo relationship to User
$workOrder->deleted_at;        // Soft delete timestamp
$workOrder->restore();         // Restore soft-deleted record
```

### Query Scopes

```php
WorkOrder::overdue()                    // Overdue work orders
WorkOrder::assignedTo($userId)          // Work orders for specific user
WorkOrder::dueWithin($days)             // Work orders due within X days
WorkOrder::pending()                    // Pending work orders
WorkOrder::approved()                   // Approved work orders
WorkOrder::inProgress()                 // In progress work orders
WorkOrder::completed()                  // Completed work orders
```

## Future Enhancements

### Recently Added (2026-02-19)

1. **Comments/Activity Log System** ✅ IMPLEMENTED
   - Track work progress with comments and notes
   - Support for internal vs. public comments
   - Automatic comment logging for workflow actions
   - Full-featured relation manager in Filament UI

2. **Enhanced Workflow Actions** ✅ IMPLEMENTED
   - Quick action buttons on Edit page
   - Approve/Reject with reasons
   - Start Work with confirmation
   - Complete with outcome documentation
   - Reopen completed work orders
   - All actions create audit trail comments

3. **Improved List View** ✅ IMPLEMENTED
   - Progress indicators showing workflow stage
   - Status-based tabs (All, Pending, Approved, In Progress, Completed, Overdue)
   - Badge counts on tabs
   - Better visual tracking

### Planned for Future Releases

1. **File Attachments**: Upload photos, documents, receipts
2. **Cost Tracking**: Track parts and total costs
3. **SLA Monitoring**: Automated alerts for due/overdue items
4. **Authorization Policies**: Team-based access control
5. **Notifications**: Email/SMS alerts on status changes
6. **Templates**: Reusable work order templates
7. **Mobile API**: Access for field technicians

## Migration Guide

For existing installations:

1. Backup your database
2. Run migrations: `php artisan migrate`
3. (Optional) Update existing records:

```php
// Example: Set due dates for existing pending work orders
WorkOrder::pending()->update([
    'due_date' => now()->addDays(7)
]);
```

4. Run seeders to see examples: `php artisan db:seed --class=WorkOrderSeeder`

## Support

For questions or issues:
- Open an issue on GitHub
- Check the README.md for contact information
- Review the test suite for usage examples
