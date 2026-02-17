<?php

namespace Tests\Unit\Models;

use App\Models\Company;
use App\Models\VendorContract;
use App\Models\VendorPerformanceEvaluation;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyVendorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_company_can_be_a_vendor()
    {
        $vendor = Company::create([
            'name' => 'Test Vendor',
            'type' => 'vendor',
            'address' => '123 Vendor St',
            'city' => 'Test City',
            'state' => 'TS',
            'zip' => '12345',
            'phone_number' => '555-1234',
        ]);

        $this->assertTrue($vendor->isVendor());
        $this->assertFalse($vendor->isCustomer());
    }

    /** @test */
    public function a_company_can_be_both_customer_and_vendor()
    {
        $company = Company::create([
            'name' => 'Test Company',
            'type' => 'both',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'TS',
            'zip' => '12345',
            'phone_number' => '555-1234',
        ]);

        $this->assertTrue($company->isVendor());
        $this->assertTrue($company->isCustomer());
    }

    /** @test */
    public function it_can_have_vendor_contracts()
    {
        $vendor = Company::create([
            'name' => 'Test Vendor',
            'type' => 'vendor',
            'address' => '123 Vendor St',
            'city' => 'Test City',
            'state' => 'TS',
            'zip' => '12345',
            'phone_number' => '555-1234',
        ]);

        VendorContract::create([
            'vendor_id' => $vendor->company_id,
            'contract_number' => 'CNT-001',
            'title' => 'Maintenance Contract',
            'contract_type' => 'maintenance',
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'status' => 'active',
        ]);

        VendorContract::create([
            'vendor_id' => $vendor->company_id,
            'contract_number' => 'CNT-002',
            'title' => 'Service Contract',
            'contract_type' => 'service',
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'status' => 'active',
        ]);

        $vendor->refresh();

        $this->assertCount(2, $vendor->vendorContracts);
    }

    /** @test */
    public function it_can_have_performance_evaluations()
    {
        $vendor = Company::create([
            'name' => 'Test Vendor',
            'type' => 'vendor',
            'address' => '123 Vendor St',
            'city' => 'Test City',
            'state' => 'TS',
            'zip' => '12345',
            'phone_number' => '555-1234',
        ]);

        $user = User::factory()->create();

        VendorPerformanceEvaluation::create([
            'vendor_id' => $vendor->company_id,
            'evaluation_date' => now(),
            'evaluated_by' => $user->id,
            'quality_rating' => 5,
            'timeliness_rating' => 5,
            'communication_rating' => 5,
            'cost_effectiveness_rating' => 5,
            'professionalism_rating' => 5,
        ]);

        VendorPerformanceEvaluation::create([
            'vendor_id' => $vendor->company_id,
            'evaluation_date' => now(),
            'evaluated_by' => $user->id,
            'quality_rating' => 4,
            'timeliness_rating' => 4,
            'communication_rating' => 4,
            'cost_effectiveness_rating' => 4,
            'professionalism_rating' => 4,
        ]);

        $vendor->refresh();

        $this->assertCount(2, $vendor->vendorPerformanceEvaluations);
    }

    /** @test */
    public function it_can_calculate_average_performance_rating()
    {
        $vendor = Company::create([
            'name' => 'Test Vendor',
            'type' => 'vendor',
            'address' => '123 Vendor St',
            'city' => 'Test City',
            'state' => 'TS',
            'zip' => '12345',
            'phone_number' => '555-1234',
        ]);

        $user = User::factory()->create();

        // Rating 5.0
        VendorPerformanceEvaluation::create([
            'vendor_id' => $vendor->company_id,
            'evaluation_date' => now(),
            'evaluated_by' => $user->id,
            'quality_rating' => 5,
            'timeliness_rating' => 5,
            'communication_rating' => 5,
            'cost_effectiveness_rating' => 5,
            'professionalism_rating' => 5,
        ]);

        // Rating 3.0
        VendorPerformanceEvaluation::create([
            'vendor_id' => $vendor->company_id,
            'evaluation_date' => now(),
            'evaluated_by' => $user->id,
            'quality_rating' => 3,
            'timeliness_rating' => 3,
            'communication_rating' => 3,
            'cost_effectiveness_rating' => 3,
            'professionalism_rating' => 3,
        ]);

        $averageRating = $vendor->getAveragePerformanceRating();

        // Average of 5.0 and 3.0 is 4.0
        $this->assertEquals(4.0, $averageRating);
    }

    /** @test */
    public function it_can_get_active_contracts_count()
    {
        $vendor = Company::create([
            'name' => 'Test Vendor',
            'type' => 'vendor',
            'address' => '123 Vendor St',
            'city' => 'Test City',
            'state' => 'TS',
            'zip' => '12345',
            'phone_number' => '555-1234',
        ]);

        VendorContract::create([
            'vendor_id' => $vendor->company_id,
            'contract_number' => 'CNT-001',
            'title' => 'Active Contract 1',
            'contract_type' => 'service',
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'status' => 'active',
        ]);

        VendorContract::create([
            'vendor_id' => $vendor->company_id,
            'contract_number' => 'CNT-002',
            'title' => 'Active Contract 2',
            'contract_type' => 'maintenance',
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'status' => 'active',
        ]);

        VendorContract::create([
            'vendor_id' => $vendor->company_id,
            'contract_number' => 'CNT-003',
            'title' => 'Draft Contract',
            'contract_type' => 'service',
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'status' => 'draft',
        ]);

        $activeCount = $vendor->getActiveContractsCount();

        $this->assertEquals(2, $activeCount);
    }

    /** @test */
    public function it_can_scope_to_vendors()
    {
        Company::create([
            'name' => 'Vendor 1',
            'type' => 'vendor',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'TS',
            'zip' => '12345',
            'phone_number' => '555-1111',
        ]);

        Company::create([
            'name' => 'Supplier 1',
            'type' => 'supplier',
            'address' => '456 Test Ave',
            'city' => 'Test City',
            'state' => 'TS',
            'zip' => '12345',
            'phone_number' => '555-2222',
        ]);

        Company::create([
            'name' => 'Customer 1',
            'type' => 'customer',
            'address' => '789 Test Blvd',
            'city' => 'Test City',
            'state' => 'TS',
            'zip' => '12345',
            'phone_number' => '555-3333',
        ]);

        Company::create([
            'name' => 'Both 1',
            'type' => 'both',
            'address' => '101 Test St',
            'city' => 'Test City',
            'state' => 'TS',
            'zip' => '12345',
            'phone_number' => '555-4444',
        ]);

        $vendors = Company::vendors()->get();

        // Should include vendor, supplier, and both
        $this->assertCount(3, $vendors);
        $this->assertTrue($vendors->contains('name', 'Vendor 1'));
        $this->assertTrue($vendors->contains('name', 'Supplier 1'));
        $this->assertTrue($vendors->contains('name', 'Both 1'));
        $this->assertFalse($vendors->contains('name', 'Customer 1'));
    }

    /** @test */
    public function it_can_have_vendor_work_orders()
    {
        $vendor = Company::create([
            'name' => 'Test Vendor',
            'type' => 'vendor',
            'address' => '123 Vendor St',
            'city' => 'Test City',
            'state' => 'TS',
            'zip' => '12345',
            'phone_number' => '555-1234',
        ]);

        WorkOrder::create([
            'title' => 'Vendor Work Order',
            'description' => 'Test work order for vendor',
            'vendor_id' => $vendor->company_id,
            'priority' => 'medium',
            'status' => 'pending',
            'guest_name' => 'Test Guest',
            'guest_email' => 'test@example.com',
            'submitted_at' => now(),
        ]);

        $vendor->refresh();

        $this->assertCount(1, $vendor->vendorWorkOrders);
        $this->assertEquals('Vendor Work Order', $vendor->vendorWorkOrders->first()->title);
    }
}
