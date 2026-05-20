# Preventive Maintenance Scheduler

## Overview

The Preventive Maintenance Scheduler automates the scheduling and reminder system for routine equipment inspections and servicing. It helps ensure that maintenance tasks are completed on time, reducing equipment downtime and extending asset life.

## Features

### 1. Automated Schedule Checking
- **Daily checks** for overdue and upcoming maintenance
- **Configurable lookhead period** (default: 7 days)
- **Priority-based notifications** for critical maintenance

### 2. Smart Reminder System
- **Multi-stage reminders**: 3 days, 1 day, and same-day notifications
- **Context-aware messaging** with priority indicators
- **Email and database notifications** for easy tracking

### 3. User-Friendly Management
- **Filament interface** for creating and managing maintenance schedules
- **Calendar widget** showing upcoming maintenance
- **Equipment-based organization** for easy tracking

## How It Works

### Scheduler Configuration

The system runs four scheduled tasks daily:

1. **8:00 AM UTC** - Check for overdue and due-soon maintenance (7-day lookhead)
2. **7:00 AM UTC** - Remind users of maintenance due today
3. **9:00 AM UTC** - Remind users of maintenance due in 3 days
4. **9:15 AM UTC** - Remind users of maintenance due tomorrow

### Notification Types

#### 1. Overdue Notifications
Sent when maintenance is past its due date:
- **Subject**: "OVERDUE: Maintenance Required - [Maintenance Name]"
- **Priority**: Automatically set to critical
- **Content**: Days overdue, equipment info, instructions

#### 2. Due Soon Notifications
Sent when maintenance is approaching (within 7 days):
- **Subject**: "Upcoming Maintenance: [Maintenance Name]"
- **Priority**: Based on schedule priority
- **Content**: Days until due, equipment info, estimated duration

#### 3. Reminder Notifications
Sent at specific intervals before due date:
- **Subject**: "⚠️ Reminder: Maintenance Due in X days - [Name]"
- **Priority Indicators**: 🚨 for critical, ⚠️ for high
- **Content**: Full details including checklist if available

## Usage

### Creating a Maintenance Schedule

1. Navigate to **Maintenance Management** > **Maintenance Schedules**
2. Click **New Maintenance Schedule**
3. Fill in the required information:
   - **Name**: Descriptive name for the maintenance task
   - **Equipment**: Select the equipment requiring maintenance
   - **Frequency**: Choose interval (daily, weekly, monthly, yearly, hours)
   - **Frequency Value**: How often (e.g., every 3 months)
   - **Next Due Date**: When the maintenance is next due
   - **Assigned To**: User responsible for the maintenance
   - **Priority**: Low, Medium, High, or Critical
   - **Instructions**: Detailed maintenance instructions
   - **Checklist** (optional): Link to a maintenance checklist

### Managing Schedules

- **View schedules**: Access the list view with filtering options
- **Mark as completed**: Use the "Mark Completed" action to record completion
- **Edit schedules**: Update any schedule details
- **Filter**: By status, priority, overdue, or due soon

### Running Commands Manually

You can manually trigger the scheduler commands:

```bash
# Check for due maintenance (default 7 days ahead)
php artisan maintenance:check-due

# Check with custom lookhead period
php artisan maintenance:check-due --days=14

# Send reminders for maintenance due in 3 days
php artisan maintenance:send-reminders --days=3

# Send reminders for maintenance due tomorrow
php artisan maintenance:send-reminders --days=1

# Send reminders for maintenance due today
php artisan maintenance:send-reminders --days=0
```

### Testing the Scheduler

To test that the scheduler is working:

```bash
# Run all scheduled tasks
php artisan schedule:run

# Test a specific command
php artisan schedule:test

# View scheduled tasks
php artisan schedule:list
```

## Notification Channels

### Email Notifications
- Sent to the assigned user's email address
- Rich HTML formatting with action buttons
- Includes all relevant maintenance details

### Database Notifications
- Stored in the database for in-app viewing
- Includes metadata for filtering and sorting
- Accessible through the Filament admin panel

## Customization

### Adjusting Scheduler Times

Edit `app/Console/Kernel.php` to change when tasks run:

```php
// Example: Run overdue check at 6 AM instead of 8 AM
$schedule->command('maintenance:check-due --days=7')
    ->dailyAt('06:00')
    ->timezone('UTC');
```

### Changing Lookhead Period

Modify the default lookhead period in the command:

```php
// In app/Console/Kernel.php
$schedule->command('maintenance:check-due --days=14') // 14 days instead of 7
```

### Adding Custom Reminder Intervals

Add additional reminder commands:

```php
// Weekly advance notice
$schedule->command('maintenance:send-reminders --days=7')
    ->dailyAt('08:00')
    ->timezone('UTC')
    ->name('Send 7-day maintenance reminders');
```

## Best Practices

1. **Assign users to all schedules**: Unassigned schedules won't generate notifications
2. **Set appropriate priorities**: Use Critical for essential equipment
3. **Include detailed instructions**: Help users complete maintenance correctly
4. **Use checklists**: Link checklists for consistent maintenance quality
5. **Review regularly**: Check the maintenance calendar weekly
6. **Complete on time**: Mark schedules as completed promptly

## Troubleshooting

### Notifications Not Sending

1. **Check cron/scheduler**: Ensure Laravel's scheduler is running
   ```bash
   # Add to crontab
   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
   ```

2. **Verify email configuration**: Check `.env` file for correct mail settings

3. **Check queue workers**: If using queues, ensure workers are running
   ```bash
   php artisan queue:work
   ```

### Schedules Not Being Detected

1. **Check status**: Ensure schedules are set to "Active"
2. **Verify dates**: Confirm next_due_date is set correctly
3. **Check assignment**: Ensure a user is assigned to the schedule

### Testing Notifications

Use tinker to manually trigger notifications:

```bash
php artisan tinker

# Get a maintenance schedule
$schedule = App\Models\MaintenanceSchedule::first();

# Send a test notification
$schedule->assignedUser->notify(
    new App\Notifications\MaintenanceReminderNotification($schedule, 3)
);
```

## API Reference

### Console Commands

#### `maintenance:check-due`
Check for overdue and upcoming maintenance schedules.

**Options:**
- `--days=N`: Number of days to look ahead (default: 7)

**Returns:** Success or failure status

#### `maintenance:send-reminders`
Send reminder notifications for maintenance due on a specific date.

**Options:**
- `--days=N`: Number of days until due date (default: 3)

**Returns:** Count of notifications sent

## Database Schema

### Maintenance Schedules Table

Key fields used by the scheduler:

- `status`: Must be 'active' to trigger notifications
- `next_due_date`: Date when maintenance is due
- `assigned_to`: User ID to receive notifications
- `priority`: Affects notification appearance and urgency
- `last_completed_date`: Used to calculate next due date

## Performance Considerations

- Commands use database indexes for efficient queries
- Notifications are queued for async processing
- `withoutOverlapping()` prevents duplicate executions
- Scoped queries minimize database load

## Support

For issues or questions:
1. Check the [GitHub Issues](https://github.com/liberu-maintenance/maintenance-laravel/issues)
2. Review application logs in `storage/logs/laravel.log`
3. Verify scheduler execution with `php artisan schedule:list`

## License

This feature is part of the Liberu Maintenance CMMS system and is licensed under the MIT License.
