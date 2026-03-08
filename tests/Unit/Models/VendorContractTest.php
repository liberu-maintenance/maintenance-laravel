<?php

namespace Tests\Unit\Models;

use App\Models\VendorContract;
use App\Models\Company;
use App\Models\VendorPerformanceEvaluation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VendorContractTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'vendor_id',
            'contract_number',
            'title',
            'description',
            'contract_type',
            'start_date',
            'end_date',
            'contract_value',
            'currency',
            'status',
            'terms_and_conditions',
            'payment_frequency',
            'renewal_period_months',
            'auto_renewal',
            'renewal_date',
            'notes',
            'team_id',
        ];

        $contract = new VendorContract();

        $this->assertEquals($fillable, $contract->getFillable());
    }

    #[Test]
    public function it_can_be_created_with_required_fields()
    {
        $vendor = Company::create([
            'name' => 'Test Vendor',
            'type' => 'vendor',
            'address' => '123 Vendor St',
            'city' => 'Test City',
            'state' => 'TS',
            'zip' => '12345',
            'phone_number' => '555-1234',
            'is_active' => true,
        ]);

        $contract = VendorContract::create([
            'vendor_id' => $vendor->company_id,
            'contract_number' => 'CNT-001',
            'title' => 'Maintenance Service Contract',
            'contract_type' => 'maintenance',
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'status' => 'active',
        ]);

        $this->assertInstanceOf(VendorContract::class, $contract);
        $this->assertEquals('CNT-001', $contract->contract_number);
        $this->assertEquals('active', $contract->status);
    }

    #[Test]
    public function it_belongs_to_a_vendor()
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

        $contract = VendorContract::create([
            'vendor_id' => $vendor->company_id,
            'contract_number' => 'CNT-002',
            'title' => 'Service Contract',
            'contract_type' => 'service',
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'status' => 'active',
        ]);

        $this->assertInstanceOf(Company::class, $contract->vendor);
        $this->assertEquals('Test Vendor', $contract->vendor->name);
    }

    #[Test]
    public function it_can_check_if_active()
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

        $activeContract = VendorContract::create([
            'vendor_id' => $vendor->company_id,
            'contract_number' => 'CNT-003',
            'title' => 'Active Contract',
            'contract_type' => 'service',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonth(),
            'status' => 'active',
        ]);

        $expiredContract = VendorContract::create([
            'vendor_id' => $vendor->company_id,
            'contract_number' => 'CNT-004',
            'title' => 'Expired Contract',
            'contract_type' => 'service',
            'start_date' => now()->subYear(),
            'end_date' => now()->subMonth(),
            'status' => 'expired',
        ]);

        $this->assertTrue($activeContract->isActive());
        $this->assertFalse($expiredContract->isActive());
    }

    #[Test]
    public function it_can_check_if_expiring_soon()
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

        $expiringSoon = VendorContract::create([
            'vendor_id' => $vendor->company_id,
            'contract_number' => 'CNT-005',
            'title' => 'Expiring Soon Contract',
            'contract_type' => 'service',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addDays(15),
            'status' => 'active',
        ]);

        $notExpiringSoon = VendorContract::create([
            'vendor_id' => $vendor->company_id,
            'contract_number' => 'CNT-006',
            'title' => 'Long Term Contract',
            'contract_type' => 'service',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addYear(),
            'status' => 'active',
        ]);

        $this->assertTrue($expiringSoon->isExpiringSoon(30));
        $this->assertFalse($notExpiringSoon->isExpiringSoon(30));
    }

    #[Test]
    public function it_can_get_days_until_expiration()
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

        $contract = VendorContract::create([
            'vendor_id' => $vendor->company_id,
            'contract_number' => 'CNT-007',
            'title' => 'Test Contract',
            'contract_type' => 'service',
            'start_date' => now(),
            'end_date' => now()->addDays(10),
            'status' => 'active',
        ]);

        $daysUntilExpiration = $contract->getDaysUntilExpiration();
        
        $this->assertGreaterThanOrEqual(9, $daysUntilExpiration);
        $this->assertLessThanOrEqual(10, $daysUntilExpiration);
    }

    #[Test]
    public function it_can_scope_to_active_contracts()
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
            'contract_number' => 'CNT-008',
            'title' => 'Active Contract 1',
            'contract_type' => 'service',
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'status' => 'active',
        ]);

        VendorContract::create([
            'vendor_id' => $vendor->company_id,
            'contract_number' => 'CNT-009',
            'title' => 'Draft Contract',
            'contract_type' => 'service',
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'status' => 'draft',
        ]);

        $activeContracts = VendorContract::active()->get();

        $this->assertCount(1, $activeContracts);
        $this->assertEquals('active', $activeContracts->first()->status);
    }
}
