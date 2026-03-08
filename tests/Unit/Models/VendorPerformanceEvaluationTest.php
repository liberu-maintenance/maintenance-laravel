<?php

namespace Tests\Unit\Models;

use App\Models\VendorPerformanceEvaluation;
use App\Models\Company;
use App\Models\User;
use App\Models\VendorContract;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VendorPerformanceEvaluationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'vendor_id',
            'vendor_contract_id',
            'work_order_id',
            'evaluation_date',
            'evaluated_by',
            'quality_rating',
            'timeliness_rating',
            'communication_rating',
            'cost_effectiveness_rating',
            'professionalism_rating',
            'overall_rating',
            'strengths',
            'areas_for_improvement',
            'comments',
            'would_recommend',
            'team_id',
        ];

        $evaluation = new VendorPerformanceEvaluation();

        $this->assertEquals($fillable, $evaluation->getFillable());
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
        ]);

        $user = User::factory()->create();

        $evaluation = VendorPerformanceEvaluation::create([
            'vendor_id' => $vendor->company_id,
            'evaluation_date' => now(),
            'evaluated_by' => $user->id,
            'quality_rating' => 5,
            'timeliness_rating' => 4,
            'communication_rating' => 5,
            'cost_effectiveness_rating' => 4,
            'professionalism_rating' => 5,
        ]);

        $this->assertInstanceOf(VendorPerformanceEvaluation::class, $evaluation);
        $this->assertEquals(5, $evaluation->quality_rating);
    }

    #[Test]
    public function it_automatically_calculates_overall_rating()
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

        $evaluation = VendorPerformanceEvaluation::create([
            'vendor_id' => $vendor->company_id,
            'evaluation_date' => now(),
            'evaluated_by' => $user->id,
            'quality_rating' => 5,
            'timeliness_rating' => 4,
            'communication_rating' => 3,
            'cost_effectiveness_rating' => 4,
            'professionalism_rating' => 4,
        ]);

        // Average of 5, 4, 3, 4, 4 = 4.0
        $this->assertEquals(4.0, $evaluation->overall_rating);
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

        $user = User::factory()->create();

        $evaluation = VendorPerformanceEvaluation::create([
            'vendor_id' => $vendor->company_id,
            'evaluation_date' => now(),
            'evaluated_by' => $user->id,
            'quality_rating' => 5,
            'timeliness_rating' => 5,
            'communication_rating' => 5,
            'cost_effectiveness_rating' => 5,
            'professionalism_rating' => 5,
        ]);

        $this->assertInstanceOf(Company::class, $evaluation->vendor);
        $this->assertEquals('Test Vendor', $evaluation->vendor->name);
    }

    #[Test]
    public function it_belongs_to_an_evaluator()
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

        $user = User::factory()->create(['name' => 'John Evaluator']);

        $evaluation = VendorPerformanceEvaluation::create([
            'vendor_id' => $vendor->company_id,
            'evaluation_date' => now(),
            'evaluated_by' => $user->id,
            'quality_rating' => 4,
            'timeliness_rating' => 4,
            'communication_rating' => 4,
            'cost_effectiveness_rating' => 4,
            'professionalism_rating' => 4,
        ]);

        $this->assertInstanceOf(User::class, $evaluation->evaluator);
        $this->assertEquals('John Evaluator', $evaluation->evaluator->name);
    }

    #[Test]
    public function it_can_scope_to_high_performance()
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
            'quality_rating' => 2,
            'timeliness_rating' => 2,
            'communication_rating' => 2,
            'cost_effectiveness_rating' => 2,
            'professionalism_rating' => 2,
        ]);

        $highPerformance = VendorPerformanceEvaluation::highPerformance(4.0)->get();

        $this->assertCount(1, $highPerformance);
        $this->assertGreaterThanOrEqual(4.0, $highPerformance->first()->overall_rating);
    }

    #[Test]
    public function it_can_scope_to_low_performance()
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
            'quality_rating' => 2,
            'timeliness_rating' => 2,
            'communication_rating' => 2,
            'cost_effectiveness_rating' => 2,
            'professionalism_rating' => 2,
        ]);

        $lowPerformance = VendorPerformanceEvaluation::lowPerformance(3.0)->get();

        $this->assertCount(1, $lowPerformance);
        $this->assertLessThan(3.0, $lowPerformance->first()->overall_rating);
    }

    #[Test]
    public function it_can_be_related_to_a_contract()
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
            'contract_number' => 'CNT-001',
            'title' => 'Test Contract',
            'contract_type' => 'service',
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'status' => 'active',
        ]);

        $user = User::factory()->create();

        $evaluation = VendorPerformanceEvaluation::create([
            'vendor_id' => $vendor->company_id,
            'vendor_contract_id' => $contract->id,
            'evaluation_date' => now(),
            'evaluated_by' => $user->id,
            'quality_rating' => 4,
            'timeliness_rating' => 4,
            'communication_rating' => 4,
            'cost_effectiveness_rating' => 4,
            'professionalism_rating' => 4,
        ]);

        $this->assertInstanceOf(VendorContract::class, $evaluation->contract);
        $this->assertEquals('Test Contract', $evaluation->contract->title);
    }
}
