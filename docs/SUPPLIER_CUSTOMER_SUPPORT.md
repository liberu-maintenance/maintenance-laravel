# Supplier and Customer Support

## Overview

The maintenance system now includes comprehensive support for managing suppliers and customers as distinct company types. This enhancement enables better tracking of business relationships and integrates directly with inventory and work order management.

## Features

### Company Types

Companies can be categorized as:
- **Customer**: Organizations that request maintenance services
- **Supplier**: Organizations that provide parts and materials
- **Both**: Companies that are both customers and suppliers

### Enhanced Company Management

#### New Fields

Companies now include:
- `type`: Category of the company (customer, supplier, both)
- `contact_person`: Primary contact name
- `email`: Company email address
- `payment_terms`: Payment terms for transactions
- `is_active`: Active status flag

#### Relationships

- **Suppliers**: Linked to inventory parts they supply
- **Customers**: Linked to work orders they request
- **Equipment**: Can be associated with customer companies

### Inventory Parts - Supplier Integration

Inventory parts now support proper supplier relationships:
- Select supplier from active supplier companies
- Create new supplier directly from the inventory part form
- Track lead times per supplier
- View all parts from a specific supplier

### Work Orders - Customer Integration

Work orders can be linked to customer companies:
- Select customer from active customer companies
- Create new customer directly from the work order form
- Track all work orders for a specific customer
- Customer information complements guest information fields

## Database Schema

### Companies Table Additions

| Column | Type | Description |
|--------|------|-------------|
| type | string | Company type: 'customer', 'supplier', or 'both' |
| contact_person | string | Primary contact person name |
| email | string | Company email address |
| payment_terms | text | Payment terms and conditions |
| is_active | boolean | Whether the company is active |

### Inventory Parts Table Additions

| Column | Type | Description |
|--------|------|-------------|
| supplier_id | bigint | Foreign key to companies table (nullable) |

### Work Orders Table Additions

| Column | Type | Description |
|--------|------|-------------|
| customer_id | bigint | Foreign key to companies table (nullable) |

## Model Enhancements

### Company Model

**New Methods:**

```php
// Scope queries
scopeSuppliers($query)    // Get only suppliers and both
scopeCustomers($query)     // Get only customers and both
scopeActive($query)        // Get only active companies

// Helper methods
isSupplier(): bool         // Check if company is a supplier
isCustomer(): bool         // Check if company is a customer
```

**New Relationships:**

```php
inventoryParts()   // HasMany - Parts supplied by this company
workOrders()       // HasMany - Work orders for this customer
```

### InventoryPart Model

**New Relationships:**

```php
supplierCompany()  // BelongsTo - The supplier company for this part
```

### WorkOrder Model

**New Relationships:**

```php
customer()         // BelongsTo - The customer company for this work order
```

## Admin Interface

### Companies Resource

Enhanced with:
- Type selection dropdown (Customer, Supplier, Both)
- Contact person and email fields
- Payment terms textarea
- Active status toggle
- Badge colors for different types:
  - Green for Customers
  - Blue for Suppliers
  - Yellow for Both
- Filter by company type
- Filter by active status
- Improved form layout with sections

### Inventory Parts Resource

Enhanced with:
- Supplier relationship selector with search
- Create new supplier inline
- Display supplier name in table (toggleable column)
- Old supplier text field kept for backward compatibility

### Work Orders Resource

Enhanced with:
- Customer relationship selector with search
- Create new customer inline
- Display customer name in table (toggleable column)

## Usage Examples

### Creating a Supplier

```php
use App\Models\Company;

$supplier = Company::create([
    'name' => 'ABC Parts Supply',
    'type' => 'supplier',
    'contact_person' => 'John Doe',
    'email' => 'john@abcparts.com',
    'phone_number' => '555-1234',
    'address' => '123 Supply St',
    'city' => 'Chicago',
    'state' => 'IL',
    'zip' => '60601',
    'payment_terms' => 'Net 30',
    'is_active' => true,
]);
```

### Linking a Part to a Supplier

```php
use App\Models\InventoryPart;

$part = InventoryPart::create([
    'part_number' => 'PART-001',
    'name' => 'Filter Element',
    'supplier_id' => $supplier->company_id,
    'lead_time_days' => 5,
    // ... other fields
]);
```

### Creating a Work Order for a Customer

```php
use App\Models\WorkOrder;

$workOrder = WorkOrder::create([
    'title' => 'Equipment Maintenance',
    'description' => 'Regular maintenance service',
    'customer_id' => $customer->company_id,
    'priority' => 'medium',
    'status' => 'pending',
    // ... other fields
]);
```

### Querying

```php
// Get all active suppliers
$suppliers = Company::suppliers()->active()->get();

// Get all customers
$customers = Company::customers()->get();

// Get all parts from a specific supplier
$parts = $supplier->inventoryParts;

// Get all work orders for a customer
$orders = $customer->workOrders;

// Check company type
if ($company->isSupplier()) {
    // Handle supplier-specific logic
}
```

## Seeders

### CompanySeeder

Seeds sample data including:
- 3 Supplier companies
- 3 Customer companies
- 1 Company that is both supplier and customer

### InventorySeeder

Updated to link parts with suppliers using the new relationship.

## Migration Notes

### Backward Compatibility

- The old `supplier` string field in `inventory_parts` is retained for backward compatibility
- Existing code using the string field will continue to work
- New code should use `supplier_id` and the `supplierCompany` relationship
- A future migration can migrate data from the string field to the relationship and remove the old field

### Migration Order

Migrations are numbered to ensure proper execution order:
1. `2026_02_14_100000_add_type_to_companies_table.php`
2. `2026_02_14_100001_add_supplier_id_to_inventory_parts_table.php`
3. `2026_02_14_100002_add_customer_id_to_work_orders_table.php`

## Testing

To test the new functionality:

```bash
# Fresh migration and seed
php artisan migrate:fresh --seed

# Check company types
php artisan tinker
>>> Company::suppliers()->count()
>>> Company::customers()->count()

# Test relationships
>>> $part = InventoryPart::first()
>>> $part->supplierCompany
>>> $supplier = Company::suppliers()->first()
>>> $supplier->inventoryParts
```

## Future Enhancements

Potential improvements for future versions:
- Purchase order management integrated with suppliers
- Customer portal for work order submission
- Supplier performance tracking
- Customer billing and invoicing
- Contract management for both suppliers and customers
- Automated reordering from preferred suppliers
- Customer service level agreements (SLAs)
- Supplier quality ratings
- Multi-currency support for international suppliers
- Customer-specific pricing

## Support

For questions or issues related to supplier and customer support, please refer to the main project documentation or create an issue on GitHub.
