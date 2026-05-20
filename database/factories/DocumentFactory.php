<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    protected $model = Document::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $documentTypes = ['manual', 'service_record', 'compliance', 'procedure', 'checklist', 'report', 'certificate'];
        $statuses = ['draft', 'active', 'archived', 'obsolete'];
        $complianceStandards = ['ISO 9001', 'ISO 14001', 'OSHA', 'FDA', 'CE', null];
        $approvalStatuses = ['pending', 'approved', 'rejected'];

        return [
            'name' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'document_type' => fake()->randomElement($documentTypes),
            'file_path' => 'documents/' . fake()->uuid() . '.pdf',
            'file_name' => fake()->word() . '_' . fake()->randomNumber(4) . '.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => fake()->numberBetween(1024, 10485760), // 1KB to 10MB
            'version' => '1.0',
            'status' => fake()->randomElement($statuses),
            'compliance_standard' => fake()->randomElement($complianceStandards),
            'effective_date' => fake()->optional()->dateTimeBetween('-1 year', 'now'),
            'expiry_date' => fake()->optional()->dateTimeBetween('now', '+2 years'),
            'review_date' => fake()->optional()->dateTimeBetween('now', '+6 months'),
            'approval_status' => fake()->randomElement($approvalStatuses),
            'approved_by' => fake()->optional()->randomElement([null, User::factory()]),
            'approved_at' => fake()->optional()->dateTimeBetween('-6 months', 'now'),
            'team_id' => Team::factory(),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the document is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the document is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_status' => 'approved',
            'approved_by' => User::factory(),
            'approved_at' => now(),
        ]);
    }

    /**
     * Indicate that the document is a compliance document.
     */
    public function compliance(): static
    {
        return $this->state(fn (array $attributes) => [
            'document_type' => 'compliance',
            'compliance_standard' => fake()->randomElement(['ISO 9001', 'ISO 14001', 'OSHA']),
            'effective_date' => now()->subMonths(6),
            'expiry_date' => now()->addYear(),
        ]);
    }

    /**
     * Indicate that the document is expiring soon.
     */
    public function expiringSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'expiry_date' => now()->addDays(15),
        ]);
    }

    /**
     * Indicate that the document is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'expiry_date' => now()->subDays(15),
        ]);
    }
}
