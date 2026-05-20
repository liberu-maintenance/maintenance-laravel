# Asset Management Performance Optimizations

## Overview
This document describes the performance optimizations implemented to address performance issues in asset management when handling large volumes of asset data and maintenance histories.

## Optimizations Implemented

### 1. Database Indexes

Added composite indexes for common query patterns in migration `2026_02_17_000001_add_performance_indexes_to_asset_tables.php`:

#### Equipment Table
- `name` - For searching and sorting by equipment name
- `created_at` - For date-based filtering
- Existing: `status + criticality`, `category + location`

#### Work Orders Table
- `equipment_id + status` - For filtering work orders by equipment and status
- `assigned_to + status` - For user-specific work order queries
- `status + priority` - For priority-based filtering
- `team_id + status` - For team-based queries
- `customer_id + status` - For customer-specific work orders
- `due_date + status` - For overdue and due-soon queries

#### Maintenance Schedules Table
- `priority + status` - For priority-based filtering
- `team_id + status` - For team-based queries
- Existing: `next_due_date + status`, `equipment_id + status`, `assigned_to + status`

### 2. Eager Loading Optimizations

#### Model-Level Configuration
Added `$with` property to models for default eager loading (currently empty to avoid over-eager loading, but infrastructure is in place).

#### Resource-Level Eager Loading
Implemented selective eager loading in Filament resources:

**EquipmentResource**:
- Loads only required fields: `company:company_id,name`, `team:id,name`

**WorkOrderResource**:
- Uses `withRelatedData()` scope to load:
  - `equipment:id,name,serial_number,status`
  - `customer:company_id,name`
  - `assignedTo:id,name`
  - `reviewer:id,name`
  - `team:id,name`

**MaintenanceScheduleResource**:
- Uses `withRelatedData()` scope to load:
  - `equipment:id,name,serial_number,status`
  - `assignedUser:id,name`
  - `checklist:id,name`
  - `team:id,name`

### 3. Query Scopes for Common Patterns

#### Equipment Model
- `withWorkOrderCounts()` - Efficiently count work orders by status
- `withMaintenanceCounts()` - Count maintenance schedules (overdue, due soon)

#### WorkOrder Model
- `withRelatedData()` - Eager load all required relationships
- `countByStatus()` - Efficient grouping query for status counts
- `countByPriority()` - Efficient grouping query for priority counts

#### MaintenanceSchedule Model
- `withRelatedData()` - Eager load all required relationships
- `withWorkOrderCount()` - Count related work orders efficiently
- `upcoming($days)` - Get upcoming maintenance schedules

### 4. Caching Layer

Implemented caching for navigation badge counts in WorkOrderResource:

- Cache key: `work_orders.badge_counts`
- TTL: 5 minutes
- Cached data: pending count, overdue count
- Automatic cache invalidation via WorkOrderObserver when:
  - Work orders are created
  - Work orders are updated (status or due_date changed)
  - Work orders are deleted or restored

### 5. Observer Enhancements

Enhanced `WorkOrderObserver` to:
- Clear cache automatically on work order changes
- Maintain data consistency between cache and database

## Performance Benefits

### Query Optimization
- **Before**: N+1 queries when loading lists with relationships
- **After**: 1-2 queries for entire lists using eager loading

### Database Efficiency
- **Before**: Full table scans for complex filters
- **After**: Index-optimized queries with composite indexes

### Caching Impact
- **Before**: 2 queries per navigation render for badges
- **After**: 0 queries per navigation render (cached for 5 minutes)

### Aggregate Queries
- **Before**: Multiple queries to count different statuses
- **After**: Single GROUP BY query for all counts

## Testing

### Performance Test Suite
Created `AssetManagementPerformanceTest.php` with tests for:

1. **Query Efficiency**
   - Equipment with work order counts uses single query
   - Equipment with maintenance counts uses single query
   - Work orders with related data use eager loading
   - Maintenance schedules with related data use eager loading

2. **Caching Behavior**
   - Badge counts are properly cached
   - Cache is cleared on status changes
   - Cache TTL works correctly

3. **Bulk Operations**
   - Bulk equipment loading avoids N+1 queries
   - Count queries use efficient GROUP BY
   - Upcoming schedules use optimized queries

### Running Tests
```bash
php artisan test --filter AssetManagementPerformanceTest
```

## Usage Examples

### Loading Equipment with Counts
```php
// Get equipment with work order and maintenance counts
$equipment = Equipment::withWorkOrderCounts()
    ->withMaintenanceCounts()
    ->get();

// Access counts without additional queries
$equipment->each(function ($item) {
    echo "Pending work orders: " . $item->pending_work_orders_count;
    echo "Overdue schedules: " . $item->overdue_schedules_count;
});
```

### Loading Work Orders Efficiently
```php
// Load work orders with all related data
$workOrders = WorkOrder::withRelatedData()
    ->where('status', 'in_progress')
    ->get();

// Access relationships without N+1 queries
$workOrders->each(function ($wo) {
    echo $wo->equipment->name; // No additional query
    echo $wo->assignedTo->name; // No additional query
});
```

### Getting Status Counts Efficiently
```php
// Single query for all status counts
$statusCounts = WorkOrder::countByStatus()->get();
```

## Maintenance

### Adding New Indexes
When adding new query patterns, consider:
1. Identify the WHERE and JOIN conditions
2. Add composite indexes for frequently used combinations
3. Test query performance before and after

### Cache Management
The cache is automatically managed by the observer. Manual cache clearing:
```php
Cache::forget('work_orders.badge_counts');
```

### Monitoring
Monitor these metrics:
- Average query execution time for list pages
- Number of queries per page load
- Cache hit rate for navigation badges
- Database index usage statistics

## Future Improvements

Potential areas for further optimization:
1. Add Redis cache driver for distributed caching
2. Implement query result caching for frequently accessed data
3. Add database query logging and monitoring
4. Consider read replicas for heavy reporting queries
5. Implement pagination for large result sets
6. Add API response caching for external integrations
