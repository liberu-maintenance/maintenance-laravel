# Vendor Management System

## Overview

The maintenance system now includes a comprehensive vendor management system to streamline communication, contracts, and service agreements with external maintenance service providers. This enhancement enables better tracking of vendor relationships, contract management, and performance evaluation.

## Features

### Vendor Types

Vendors are managed as a specialized type of Company with support for:
- **Vendor**: Organizations that provide external maintenance services
- **Supplier**: Organizations that provide parts and materials (also considered vendors)
- **Both**: Companies that are both customers and vendors/suppliers

### Vendor Contracts Management

Track and manage service agreements with vendors:
- Contract numbers and titles
- Contract types (service, maintenance, supply, other)
- Financial terms (contract value, currency, payment frequency)
- Contract periods with start/end dates
- Status tracking (draft, active, expired, terminated, renewed)
- Auto-renewal settings
- Terms and conditions
- Expiration alerts

### Vendor Performance Evaluation

Comprehensive performance tracking system with:
- 5-category rating system (1-5 stars each):
  - Quality of Work
  - Timeliness
  - Communication
  - Cost Effectiveness
  - Professionalism
- Automatic overall rating calculation
- Related contract and work order tracking
- Strengths and areas for improvement notes
- Recommendation tracking
- Evaluation history per vendor

### Work Order - Vendor Integration

Work orders can now be assigned to external vendors:
- Link work orders to vendor companies
- Track vendor-performed maintenance
- Associate performance evaluations with specific work orders
- View all work orders assigned to a vendor

## Database Schema

### Vendor Contracts Table

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| vendor_id | bigint | Foreign key to companies table |
| contract_number | string | Unique contract identifier |
| title | string | Contract title |
| description | text | Contract description |
| contract_type | enum | Type: 'service', 'maintenance', 'supply', 'other' |
| start_date | date | Contract start date |
| end_date | date | Contract end date |
| contract_value | decimal(10,2) | Total contract value |
| currency | string(3) | Currency code (USD, EUR, GBP) |
| status | enum | Status: 'draft', 'active', 'expired', 'terminated', 'renewed' |
| terms_and_conditions | text | Contract terms |
| payment_frequency | enum | 'one_time', 'monthly', 'quarterly', 'annually' |
| renewal_period_months | integer | Renewal period in months |
| auto_renewal | boolean | Auto-renewal flag |
| renewal_date | date | Next renewal date |
| notes | text | Additional notes |
| team_id | bigint | Team association |

### Vendor Performance Evaluations Table

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| vendor_id | bigint | Foreign key to companies table |
| vendor_contract_id | bigint | Related contract (nullable) |
| work_order_id | bigint | Related work order (nullable) |
| evaluation_date | date | Date of evaluation |
| evaluated_by | bigint | Foreign key to users table |
| quality_rating | integer | Quality rating (1-5) |
| timeliness_rating | integer | Timeliness rating (1-5) |
| communication_rating | integer | Communication rating (1-5) |
| cost_effectiveness_rating | integer | Cost effectiveness rating (1-5) |
| professionalism_rating | integer | Professionalism rating (1-5) |
| overall_rating | decimal(3,2) | Calculated average rating |
| strengths | text | Vendor strengths |
| areas_for_improvement | text | Areas for improvement |
| comments | text | Additional comments |
| would_recommend | boolean | Recommendation flag |
| team_id | bigint | Team association |

### Work Orders Table Additions

| Column | Type | Description |
|--------|------|-------------|
| vendor_id | bigint | Foreign key to companies table (nullable) |

## Model Enhancements

### Company Model

**New Methods:**

```php
// Scope queries
scopeVendors($query)           // Get vendors, suppliers, and both
scopeActive($query)            // Get only active companies

// Helper methods
isVendor(): bool               // Check if company is a vendor
getAveragePerformanceRating(): float  // Get average rating from evaluations
getActiveContractsCount(): int // Count active contracts
```

**New Relationships:**

```php
vendorContracts()              // HasMany - Contracts with this vendor
vendorPerformanceEvaluations() // HasMany - Performance evaluations
vendorWorkOrders()             // HasMany - Work orders assigned to vendor
```

### VendorContract Model

**Methods:**

```php
// Scope queries
scopeActive($query)            // Get only active contracts
scopeExpiringSoon($query, $days)  // Get contracts expiring soon
scopeExpired($query)           // Get expired contracts

// Helper methods
isActive(): bool               // Check if contract is currently active
isExpiringSoon($days): bool    // Check if expiring within specified days
getDaysUntilExpiration(): int  // Get days until contract expires
```

**Relationships:**

```php
vendor()                       // BelongsTo - The vendor company
team()                         // BelongsTo - Associated team
performanceEvaluations()       // HasMany - Related evaluations
```

### VendorPerformanceEvaluation Model

**Methods:**

```php
// Scope queries
scopeForVendor($query, $vendorId)     // Get evaluations for specific vendor
scopeHighPerformance($query, $threshold) // Get high-rated evaluations
scopeLowPerformance($query, $threshold)  // Get low-rated evaluations

// Helper methods
calculateOverallRating(): void  // Automatically calculates average rating
```

**Relationships:**

```php
vendor()                       // BelongsTo - The vendor being evaluated
contract()                     // BelongsTo - Related contract (nullable)
workOrder()                    // BelongsTo - Related work order (nullable)
evaluator()                    // BelongsTo - User who performed evaluation
team()                         // BelongsTo - Associated team
```

### WorkOrder Model

**New Relationships:**

```php
vendor()                       // BelongsTo - Assigned vendor
vendorPerformanceEvaluations() // HasMany - Related evaluations
```

## Admin Interface

### Vendors Resource

A specialized view of companies filtered to vendors:
- Type selection (Vendor, Supplier, Both)
- Contact information management
- Address details
- Active status toggle
- Average performance rating display
- Active contracts count
- Badge colors:
  - Primary (Blue) for Vendors
  - Info (Cyan) for Suppliers
  - Warning (Yellow) for Both
- Filter by vendor type and active status

### Vendor Contracts Resource

Comprehensive contract management interface:
- Contract information section (vendor, number, title, type, status)
- Financial terms section (value, currency, payment frequency, T&C)
- Contract period section (start/end dates, renewal settings)
- Status badges with colors:
  - Success (Green) for Active
  - Gray for Draft
  - Warning (Orange) for Expired
  - Danger (Red) for Terminated
  - Info (Blue) for Renewed
- Filter by status and contract type
- Sortable columns

### Vendor Performance Evaluations Resource

Detailed performance tracking interface:
- Evaluation details (vendor, contract, work order, date, evaluator)
- 5-category rating system with visual star indicators
- Feedback section (strengths, improvements, comments, recommendation)
- Overall rating badge with color coding:
  - Success (Green) for 4.0+
  - Warning (Orange) for 3.0-3.99
  - Danger (Red) for below 3.0
- Filter by vendor and overall rating
- Sortable by date and ratings

## Usage Examples

### Creating a Vendor

```php
use App\Models\Company;

$vendor = Company::create([
    'name' => 'Premier Maintenance Services',
    'type' => 'vendor',
    'contact_person' => 'Sarah Johnson',
    'email' => 'contact@premiermaint.com',
    'phone_number' => '555-7890',
    'address' => '456 Service Ave',
    'city' => 'Boston',
    'state' => 'MA',
    'zip' => '02101',
    'is_active' => true,
]);
```

### Creating a Vendor Contract

```php
use App\Models\VendorContract;

$contract = VendorContract::create([
    'vendor_id' => $vendor->company_id,
    'contract_number' => 'MAINT-2026-001',
    'title' => 'Annual HVAC Maintenance Contract',
    'description' => 'Quarterly maintenance of all HVAC systems',
    'contract_type' => 'maintenance',
    'start_date' => now(),
    'end_date' => now()->addYear(),
    'contract_value' => 24000.00,
    'currency' => 'USD',
    'status' => 'active',
    'payment_frequency' => 'quarterly',
    'auto_renewal' => true,
    'renewal_period_months' => 12,
]);
```

### Creating a Work Order for a Vendor

```php
use App\Models\WorkOrder;

$workOrder = WorkOrder::create([
    'title' => 'Emergency HVAC Repair',
    'description' => 'Unit 3 cooling system failure',
    'vendor_id' => $vendor->company_id,
    'priority' => 'high',
    'status' => 'in_progress',
    'due_date' => now()->addDays(2),
    'estimated_hours' => 4,
    // ... other fields
]);
```

### Evaluating Vendor Performance

```php
use App\Models\VendorPerformanceEvaluation;

$evaluation = VendorPerformanceEvaluation::create([
    'vendor_id' => $vendor->company_id,
    'vendor_contract_id' => $contract->id,
    'work_order_id' => $workOrder->id,
    'evaluation_date' => now(),
    'evaluated_by' => auth()->id(),
    'quality_rating' => 5,
    'timeliness_rating' => 4,
    'communication_rating' => 5,
    'cost_effectiveness_rating' => 4,
    'professionalism_rating' => 5,
    // overall_rating is calculated automatically
    'strengths' => 'Excellent workmanship, professional staff',
    'areas_for_improvement' => 'Could improve response time',
    'comments' => 'Very satisfied with the service provided',
    'would_recommend' => true,
]);

// Overall rating is automatically calculated: (5+4+5+4+5)/5 = 4.6
```

### Querying

```php
// Get all active vendors
$vendors = Company::vendors()->active()->get();

// Get vendor's average performance rating
$avgRating = $vendor->getAveragePerformanceRating();

// Get vendor's active contracts count
$activeContracts = $vendor->getActiveContractsCount();

// Get contracts expiring soon (within 30 days)
$expiring = VendorContract::active()
    ->expiringSoon(30)
    ->with('vendor')
    ->get();

// Get all high-performing vendors (4.0+ rating)
$highPerformers = VendorPerformanceEvaluation::highPerformance(4.0)
    ->with('vendor')
    ->get()
    ->pluck('vendor')
    ->unique('company_id');

// Get all evaluations for a specific vendor
$evaluations = VendorPerformanceEvaluation::forVendor($vendor->company_id)
    ->orderBy('evaluation_date', 'desc')
    ->get();

// Check contract status
if ($contract->isActive()) {
    if ($contract->isExpiringSoon(30)) {
        // Send renewal reminder
    }
}
```

## Migration Notes

### Migration Order

Migrations are numbered to ensure proper execution order:
1. `2026_02_17_000001_create_vendor_contracts_table.php`
2. `2026_02_17_000002_create_vendor_performance_evaluations_table.php`
3. `2026_02_17_000003_add_vendor_id_to_work_orders_table.php`

### Running Migrations

```bash
# Run new migrations
php artisan migrate

# Or fresh migration with seed data
php artisan migrate:fresh --seed
```

## Testing

The vendor management system includes comprehensive unit tests:

### Test Files

- `tests/Unit/Models/VendorContractTest.php` - Tests for contract model
- `tests/Unit/Models/VendorPerformanceEvaluationTest.php` - Tests for evaluation model
- `tests/Unit/Models/CompanyVendorTest.php` - Tests for vendor-specific Company features
- `tests/Unit/Models/CompanyTest.php` - Updated to include vendor type

### Running Tests

```bash
# Run all tests
php artisan test

# Run only vendor-related tests
php artisan test --filter Vendor

# Run only company tests
php artisan test --filter Company
```

### Test Coverage

The test suite covers:
- Model fillable attributes
- Model relationships
- Scope queries
- Helper methods
- Rating calculations
- Contract status checking
- Expiration date calculations

## Best Practices

### Contract Management

1. **Always set expiration dates**: Ensure all contracts have clear end dates
2. **Use status tracking**: Keep contract status updated (draft → active → expired/renewed)
3. **Enable alerts**: Set up notifications for contracts expiring soon
4. **Document terms**: Record all terms and conditions clearly
5. **Track renewals**: Use auto-renewal flags and renewal dates appropriately

### Performance Evaluation

1. **Regular evaluations**: Evaluate vendors after each major work order
2. **Honest ratings**: Provide accurate ratings across all categories
3. **Constructive feedback**: Include specific strengths and areas for improvement
4. **Timely evaluations**: Complete evaluations soon after service completion
5. **Link to work orders**: Always associate evaluations with specific work orders when possible

### Vendor Selection

1. **Review ratings**: Check average performance ratings before vendor selection
2. **Contract status**: Ensure vendors have active contracts before assignment
3. **Specialization**: Match vendors with appropriate contract types
4. **Communication**: Maintain clear communication channels with vendors
5. **Performance tracking**: Monitor trends in vendor performance over time

## Reporting and Analytics

### Available Metrics

- Average vendor performance rating
- Contract expiration tracking
- Vendor workload (work orders assigned)
- Performance trends over time
- Cost analysis by vendor
- Contract value by type
- Evaluation completion rate

### Report Ideas

1. **Vendor Performance Dashboard**: Display top and bottom performers
2. **Contract Expiration Report**: List contracts expiring in the next 30/60/90 days
3. **Vendor Utilization Report**: Show work order distribution across vendors
4. **Cost Analysis**: Compare contract values and actual costs
5. **Quality Trends**: Track performance rating trends over time

## Future Enhancements

Potential improvements for future versions:
- Vendor portal for contract management and work order access
- Automated contract renewal workflows
- Vendor certification and compliance tracking
- Insurance and license document management
- Vendor comparison and selection tools
- Service Level Agreement (SLA) tracking
- Automated performance report generation
- Vendor payment processing integration
- Mobile app for vendor communication
- Real-time vendor availability tracking
- Vendor scheduling and dispatch system
- Multi-vendor bidding for work orders
- Vendor rating public display (vendor marketplace)

## Integration Points

### With Equipment Management
- Assign vendors specialized in specific equipment types
- Track vendor maintenance history per equipment

### With Work Order System
- Automatically assign vendors based on contract type
- Track vendor response times and completion rates

### With Inventory Management
- Link vendors who also supply parts (supplier + vendor)
- Track parts used in vendor-performed work

### With Team Management
- Assign vendor contracts to specific teams
- Team-based vendor performance tracking

## Support

For questions or issues related to the vendor management system, please refer to the main project documentation or create an issue on GitHub.

## License

This feature is part of the Liberu Maintenance CMMS system and is released under the MIT License.
