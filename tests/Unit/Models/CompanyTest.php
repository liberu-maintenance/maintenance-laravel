<?php

namespace Tests\Unit\Models;

use App\Models\Company;
use App\Models\InventoryPart;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'name',
            'address',
            'city',
            'state',
            'zip',
            'phone_number',
            'website',
            'industry',
            'description',
            'type',
            'contact_person',
            'email',
            'payment_terms',
            'is_active',
            'team_id',
        ];

        $company = new Company();

        $this->assertEquals($fillable, $company->getFillable());
    }

    /** @test */
    public function it_can_be_created_as_a_supplier()
    {
        $company = Company::create([
            'name' => 'Test Supplier',
            'type' => 'supplier',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'TS',
            'zip' => '12345',
            'phone_number' => '555-1234',
            'is_active' => true,
        ]);

        $this->assertEquals('supplier', $company->type);
        $this->assertTrue($company->isSupplier());
        $this->assertFalse($company->isCustomer());
    }

    /** @test */
    public function it_can_be_created_as_a_customer()
    {
        $company = Company::create([
            'name' => 'Test Customer',
            'type' => 'customer',
            'address' => '456 Test Ave',
            'city' => 'Test City',
            'state' => 'TS',
            'zip' => '12345',
            'phone_number' => '555-5678',
            'is_active' => true,
        ]);

        $this->assertEquals('customer', $company->type);
        $this->assertTrue($company->isCustomer());
        $this->assertFalse($company->isSupplier());
    }

    /** @test */
    public function it_can_be_created_as_both_supplier_and_customer()
    {
        $company = Company::create([
            'name' => 'Test Both',
            'type' => 'both',
            'address' => '789 Test Blvd',
            'city' => 'Test City',
            'state' => 'TS',
            'zip' => '12345',
            'phone_number' => '555-9012',
            'is_active' => true,
        ]);

        $this->assertEquals('both', $company->type);
        $this->assertTrue($company->isSupplier());
        $this->assertTrue($company->isCustomer());
    }

    /** @test */
    public function it_can_scope_to_suppliers_only()
    {
        Company::create([
            'name' => 'Supplier 1',
            'type' => 'supplier',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'TS',
            'zip' => '12345',
            'phone_number' => '555-1111',
        ]);

        Company::create([
            'name' => 'Customer 1',
            'type' => 'customer',
            'address' => '456 Test Ave',
            'city' => 'Test City',
            'state' => 'TS',
            'zip' => '12345',
            'phone_number' => '555-2222',
        ]);

        Company::create([
            'name' => 'Both 1',
            'type' => 'both',
            'address' => '789 Test Blvd',
            'city' => 'Test City',
            'state' => 'TS',
            'zip' => '12345',
            'phone_number' => '555-3333',
        ]);

        $suppliers = Company::suppliers()->get();

        $this->assertCount(2, $suppliers); // supplier and both
        $this->assertTrue($suppliers->contains('name', 'Supplier 1'));
        $this->assertTrue($suppliers->contains('name', 'Both 1'));
        $this->assertFalse($suppliers->contains('name', 'Customer 1'));
    }

    /** @test */
    public function it_can_scope_to_customers_only()
    {
        Company::create([
            'name' => 'Supplier 1',
            'type' => 'supplier',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'TS',
            'zip' => '12345',
            'phone_number' => '555-1111',
        ]);

        Company::create([
            'name' => 'Customer 1',
            'type' => 'customer',
            'address' => '456 Test Ave',
            'city' => 'Test City',
            'state' => 'TS',
            'zip' => '12345',
            'phone_number' => '555-2222',
        ]);

        Company::create([
            'name' => 'Both 1',
            'type' => 'both',
            'address' => '789 Test Blvd',
            'city' => 'Test City',
            'state' => 'TS',
            'zip' => '12345',
            'phone_number' => '555-3333',
        ]);

        $customers = Company::customers()->get();

        $this->assertCount(2, $customers); // customer and both
        $this->assertTrue($customers->contains('name', 'Customer 1'));
        $this->assertTrue($customers->contains('name', 'Both 1'));
        $this->assertFalse($customers->contains('name', 'Supplier 1'));
    }

    /** @test */
    public function it_can_scope_to_active_companies()
    {
        Company::create([
            'name' => 'Active Company',
            'type' => 'customer',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'TS',
            'zip' => '12345',
            'phone_number' => '555-1111',
            'is_active' => true,
        ]);

        Company::create([
            'name' => 'Inactive Company',
            'type' => 'customer',
            'address' => '456 Test Ave',
            'city' => 'Test City',
            'state' => 'TS',
            'zip' => '12345',
            'phone_number' => '555-2222',
            'is_active' => false,
        ]);

        $activeCompanies = Company::active()->get();

        $this->assertCount(1, $activeCompanies);
        $this->assertEquals('Active Company', $activeCompanies->first()->name);
    }

    /** @test */
    public function it_can_have_inventory_parts_as_supplier()
    {
        $supplier = Company::create([
            'name' => 'Parts Supplier',
            'type' => 'supplier',
            'address' => '123 Supply St',
            'city' => 'Supply City',
            'state' => 'TS',
            'zip' => '12345',
            'phone_number' => '555-1111',
        ]);

        $part = InventoryPart::create([
            'part_number' => 'TEST-001',
            'name' => 'Test Part',
            'supplier_id' => $supplier->company_id,
            'unit_cost' => 10.00,
        ]);

        $this->assertCount(1, $supplier->inventoryParts);
        $this->assertEquals('Test Part', $supplier->inventoryParts->first()->name);
    }

    /** @test */
    public function it_can_have_work_orders_as_customer()
    {
        $customer = Company::create([
            'name' => 'Customer Company',
            'type' => 'customer',
            'address' => '456 Customer Ave',
            'city' => 'Customer City',
            'state' => 'TS',
            'zip' => '12345',
            'phone_number' => '555-2222',
        ]);

        $workOrder = WorkOrder::create([
            'title' => 'Test Work Order',
            'description' => 'Test Description',
            'customer_id' => $customer->company_id,
            'priority' => 'medium',
            'status' => 'pending',
            'guest_name' => 'Test Guest',
            'guest_email' => 'test@example.com',
            'submitted_at' => now(),
        ]);

        $customer->refresh();

        $this->assertCount(1, $customer->workOrders);
        $this->assertEquals('Test Work Order', $customer->workOrders->first()->title);
    }

    /** @test */
    public function it_casts_is_active_to_boolean()
    {
        $company = Company::create([
            'name' => 'Test Company',
            'type' => 'customer',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'TS',
            'zip' => '12345',
            'phone_number' => '555-1111',
            'is_active' => true,
        ]);

        $this->assertIsBool($company->is_active);
        $this->assertTrue($company->is_active);
    }
}
