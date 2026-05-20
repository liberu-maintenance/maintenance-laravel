<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Team;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $team = Team::first();

        // Suppliers
        $suppliers = [
            [
                'name' => 'Industrial Parts Supply Co.',
                'type' => 'supplier',
                'contact_person' => 'John Smith',
                'email' => 'john@industrialparts.com',
                'phone_number' => '555-0101',
                'address' => '123 Industrial Blvd',
                'city' => 'Chicago',
                'state' => 'IL',
                'zip' => '60601',
                'website' => 'https://industrialparts.com',
                'industry' => 'Industrial Supplies',
                'description' => 'Leading supplier of industrial parts and equipment',
                'payment_terms' => 'Net 30',
                'is_active' => true,
                'team_id' => $team?->id,
            ],
            [
                'name' => 'HVAC Supply Warehouse',
                'type' => 'supplier',
                'contact_person' => 'Sarah Johnson',
                'email' => 'sarah@hvacsupply.com',
                'phone_number' => '555-0102',
                'address' => '456 Commerce St',
                'city' => 'Atlanta',
                'state' => 'GA',
                'zip' => '30301',
                'website' => 'https://hvacsupply.com',
                'industry' => 'HVAC Supplies',
                'description' => 'Comprehensive HVAC parts and equipment supplier',
                'payment_terms' => 'Net 30',
                'is_active' => true,
                'team_id' => $team?->id,
            ],
            [
                'name' => 'Bearing & Fastener Depot',
                'type' => 'supplier',
                'contact_person' => 'Mike Davis',
                'email' => 'mike@bearingdepot.com',
                'phone_number' => '555-0103',
                'address' => '789 Supply Lane',
                'city' => 'Houston',
                'state' => 'TX',
                'zip' => '77001',
                'website' => 'https://bearingdepot.com',
                'industry' => 'Bearings & Fasteners',
                'description' => 'Specialized in bearings, fasteners, and mechanical components',
                'payment_terms' => 'Net 30',
                'is_active' => true,
                'team_id' => $team?->id,
            ],
        ];

        // Customers
        $customers = [
            [
                'name' => 'ABC Manufacturing Inc.',
                'type' => 'customer',
                'contact_person' => 'Emily White',
                'email' => 'emily@abcmfg.com',
                'phone_number' => '555-0201',
                'address' => '100 Factory Road',
                'city' => 'Detroit',
                'state' => 'MI',
                'zip' => '48201',
                'website' => 'https://abcmfg.com',
                'industry' => 'Manufacturing',
                'description' => 'Large-scale manufacturing facility requiring regular maintenance',
                'payment_terms' => 'Net 45',
                'is_active' => true,
                'team_id' => $team?->id,
            ],
            [
                'name' => 'XYZ Logistics Center',
                'type' => 'customer',
                'contact_person' => 'Robert Brown',
                'email' => 'robert@xyzlogistics.com',
                'phone_number' => '555-0202',
                'address' => '200 Distribution Way',
                'city' => 'Memphis',
                'state' => 'TN',
                'zip' => '38101',
                'website' => 'https://xyzlogistics.com',
                'industry' => 'Logistics',
                'description' => 'Major distribution center with extensive equipment fleet',
                'payment_terms' => 'Net 30',
                'is_active' => true,
                'team_id' => $team?->id,
            ],
            [
                'name' => 'Global Tech Industries',
                'type' => 'customer',
                'contact_person' => 'Jennifer Lee',
                'email' => 'jennifer@globaltech.com',
                'phone_number' => '555-0203',
                'address' => '300 Innovation Drive',
                'city' => 'San Jose',
                'state' => 'CA',
                'zip' => '95101',
                'website' => 'https://globaltech.com',
                'industry' => 'Technology',
                'description' => 'Technology company with data center and facility maintenance needs',
                'payment_terms' => 'Net 60',
                'is_active' => true,
                'team_id' => $team?->id,
            ],
        ];

        // Both supplier and customer
        $both = [
            [
                'name' => 'MultiService Equipment Co.',
                'type' => 'both',
                'contact_person' => 'David Wilson',
                'email' => 'david@multiservice.com',
                'phone_number' => '555-0301',
                'address' => '400 Service Blvd',
                'city' => 'Phoenix',
                'state' => 'AZ',
                'zip' => '85001',
                'website' => 'https://multiservice.com',
                'industry' => 'Equipment Sales & Service',
                'description' => 'Equipment supplier and maintenance customer',
                'payment_terms' => 'Net 30',
                'is_active' => true,
                'team_id' => $team?->id,
            ],
        ];

        // Create all companies
        foreach (array_merge($suppliers, $customers, $both) as $company) {
            Company::create($company);
        }

        $this->command->info('Companies seeded successfully!');
    }
}
