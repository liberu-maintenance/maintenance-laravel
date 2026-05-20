# Inventory Management Module

## Overview

The Inventory Management module provides comprehensive tracking and control of parts, consumables, and materials used in maintenance operations. This module integrates seamlessly with the existing work order and equipment management systems.

## Features

### Core Functionality

1. **Parts Catalog Management**
   - Track part numbers, names, descriptions
   - Categorize parts (Mechanical, Electrical, Hydraulic, etc.)
   - Manage supplier information and lead times
   - Set pricing and reorder levels

2. **Multi-Location Stock Tracking**
   - Track inventory across multiple warehouses/locations
   - Monitor available vs reserved quantities
   - Real-time stock level updates

3. **Stock Transactions**
   - Stock in (receiving)
   - Stock out (consumption)
   - Stock adjustments (inventory counts)
   - Full transaction history with audit trail

4. **Work Order Integration**
   - Associate parts with work orders
   - Track planned vs actual parts usage
   - Automatic stock reservation
   - Cost tracking per work order

5. **Inventory Alerts**
   - Low stock detection
   - Out of stock notifications
   - Reorder level monitoring

## Database Schema

### Tables

#### `inventory_parts`
Main parts catalog table storing all inventory items.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| part_number | string | Unique part identifier |
| name | string | Part name |
| description | text | Detailed description |
| category | string | Part category |
| unit_of_measure | string | UOM (piece, liter, etc.) |
| unit_cost | decimal | Cost per unit |
| reorder_level | integer | Minimum stock before reorder |
| reorder_quantity | integer | Quantity to order when low |
| location | string | Default storage location |
| supplier | string | Supplier name |
| lead_time_days | integer | Expected delivery time |
| team_id | bigint | Team association |

#### `inventory_stock_levels`
Current stock quantities by location.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| inventory_part_id | bigint | Foreign key to parts |
| location | string | Storage location |
| quantity | integer | Current quantity |
| reserved_quantity | integer | Reserved for work orders |

#### `inventory_transactions`
Historical record of all stock movements.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| inventory_part_id | bigint | Foreign key to parts |
| type | enum | in, out, adjustment |
| quantity | integer | Transaction quantity |
| location | string | Transaction location |
| work_order_id | bigint | Associated work order (nullable) |
| user_id | bigint | User who performed transaction |
| notes | text | Transaction notes |
| unit_cost | decimal | Cost per unit at transaction time |
| reference_number | string | PO or reference number |

#### `work_order_parts`
Many-to-many relationship between work orders and parts.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| work_order_id | bigint | Foreign key to work orders |
| inventory_part_id | bigint | Foreign key to parts |
| quantity_planned | integer | Planned quantity |
| quantity_used | integer | Actual quantity used |
| unit_cost | decimal | Cost per unit |

## Models

### InventoryPart

Main model for inventory items.

**Key Methods:**
- `getTotalQuantityAttribute()` - Total stock across all locations
- `getAvailableQuantityAttribute()` - Available stock (total - reserved)
- `isLowStock()` - Check if stock is below reorder level
- `scopeLowStock()` - Query scope for low stock items
- `scopeOutOfStock()` - Query scope for out of stock items

**Relationships:**
- `stockLevels()` - HasMany InventoryStockLevel
- `transactions()` - HasMany InventoryTransaction
- `workOrders()` - BelongsToMany WorkOrder

### InventoryStockLevel

Tracks stock quantities by location.

**Key Methods:**
- `addStock($quantity)` - Add stock to location
- `removeStock($quantity)` - Remove stock from location
- `reserveStock($quantity)` - Reserve stock for work order
- `releaseReservedStock($quantity)` - Release reserved stock

### InventoryTransaction

Records all stock movements.

**Static Methods:**
- `stockIn()` - Create stock receipt transaction
- `stockOut()` - Create stock issue transaction
- `stockAdjustment()` - Create inventory adjustment

## Services

### InventoryService

Central service for inventory operations with transaction safety.

**Methods:**

```php
// Add stock to inventory
addStock(
    int $inventoryPartId,
    int $quantity,
    string $location,
    ?int $userId = null,
    ?float $unitCost = null,
    ?string $notes = null,
    ?string $referenceNumber = null
): InventoryTransaction

// Remove stock from inventory
removeStock(
    int $inventoryPartId,
    int $quantity,
    string $location,
    ?int $workOrderId = null,
    ?int $userId = null,
    ?string $notes = null
): InventoryTransaction

// Adjust stock levels (for inventory counts)
adjustStock(
    int $inventoryPartId,
    int $newQuantity,
    string $location,
    ?int $userId = null,
    ?string $notes = null
): InventoryTransaction

// Reserve stock for a work order
reserveStock(
    int $inventoryPartId,
    int $quantity,
    string $location
): void

// Release reserved stock
releaseReservedStock(
    int $inventoryPartId,
    int $quantity,
    string $location
): void

// Get parts that are low on stock
getLowStockParts()

// Get parts that are out of stock
getOutOfStockParts()

// Get stock level for a specific part and location
getStockLevel(int $inventoryPartId, string $location): ?InventoryStockLevel

// Get total stock across all locations for a part
getTotalStock(int $inventoryPartId): int

// Get available stock (total - reserved) for a part
getAvailableStock(int $inventoryPartId): int
```

## Usage Examples

### Adding Inventory

```php
use App\Services\InventoryService;

$service = new InventoryService();

// Receive new stock
$transaction = $service->addStock(
    inventoryPartId: 1,
    quantity: 100,
    location: 'Warehouse A',
    userId: auth()->id(),
    unitCost: 12.50,
    notes: 'PO-12345 received',
    referenceNumber: 'PO-12345'
);
```

### Issuing Parts for Work Order

```php
// Issue parts for a work order
$transaction = $service->removeStock(
    inventoryPartId: 1,
    quantity: 5,
    location: 'Warehouse A',
    workOrderId: 123,
    userId: auth()->id(),
    notes: 'Used for pump maintenance'
);
```

### Inventory Count Adjustment

```php
// Adjust stock based on physical count
$transaction = $service->adjustStock(
    inventoryPartId: 1,
    newQuantity: 95,
    location: 'Warehouse A',
    userId: auth()->id(),
    notes: 'Annual inventory count'
);
```

### Checking Stock Levels

```php
// Get total stock for a part
$total = $service->getTotalStock($partId);

// Get available stock (excluding reserved)
$available = $service->getAvailableStock($partId);

// Check for low stock items
$lowStockParts = $service->getLowStockParts();
```

## Filament Admin Interface

The inventory management UI is available in the admin panel under the "Inventory Management" navigation group.

### Parts Management
- Create/Edit/Delete parts
- View stock levels in the table
- Color-coded badges for low stock warnings
- Filter by category
- Search by part number, name, or supplier

### Features
- Responsive data tables with sorting and filtering
- Stock level indicators with color coding
- Toggle-able columns for detailed information
- Bulk operations support

## Testing

The module includes comprehensive tests:

### Unit Tests

**InventoryServiceTest** - Tests all service methods:
- Adding stock
- Removing stock
- Stock adjustments
- Stock reservation
- Stock validation

**InventoryPartTest** - Tests model behavior:
- Relationships
- Attribute calculations
- Low stock detection
- Work order associations

Run tests with:
```bash
php artisan test --filter Inventory
```

## Migration

To add inventory management to an existing installation:

```bash
# Run migrations
php artisan migrate

# Seed with sample data (optional)
php artisan db:seed --class=InventorySeeder
```

## Future Enhancements

Potential additions for future versions:
- Barcode/QR code scanning
- Batch/lot number tracking
- Expiration date management
- Purchase order management
- Supplier portal integration
- Automated reorder workflows
- Advanced reporting and analytics
- Mobile app for stock counting
- Integration with external ERP systems

## Support

For questions or issues related to inventory management, please refer to the main project documentation or create an issue on GitHub.
