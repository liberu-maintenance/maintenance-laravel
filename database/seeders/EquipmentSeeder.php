<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Equipment;
use App\Models\Company;

class EquipmentSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $company = Company::first();

        $equipment = [
            [
                'name' => 'HVAC Unit - Building A',
                'description' => 'Main heating and cooling system for Building A',
                'serial_number' => 'HVAC-001-2023',
                'model' => 'ThermoMax Pro 5000',
                'manufacturer' => 'CoolAir Systems',
                'category' => 'HVAC',
                'location' => 'Building A - Roof',
                'purchase_date' => '2023-01-15',
                'warranty_expiry' => '2026-01-15',
                'status' => 'active',
                'criticality' => 'critical',
                'notes' => 'Primary HVAC system serving 50+ offices',
                'company_id' => $company?->id,
            ],
            [
                'name' => 'Emergency Generator',
                'description' => 'Backup power generator for critical systems',
                'serial_number' => 'GEN-002-2022',
                'model' => 'PowerGuard 750kW',
                'manufacturer' => 'GeneratorTech',
                'category' => 'Electrical',
                'location' => 'Building B - Basement',
                'purchase_date' => '2022-06-10',
                'warranty_expiry' => '2025-06-10',
                'status' => 'active',
                'criticality' => 'critical',
                'notes' => 'Monthly testing required',
                'company_id' => $company?->id,
            ],
            [
                'name' => 'Water Pump System',
                'description' => 'Main water circulation pump',
                'serial_number' => 'PUMP-003-2023',
                'model' => 'AquaFlow 2000',
                'manufacturer' => 'WaterTech Solutions',
                'category' => 'Plumbing',
                'location' => 'Utility Room',
                'purchase_date' => '2023-03-20',
                'warranty_expiry' => '2025-03-20',
                'status' => 'active',
                'criticality' => 'high',
                'notes' => 'Requires quarterly maintenance',
                'company_id' => $company?->id,
            ],
            [
                'name' => 'Fire Safety Panel',
                'description' => 'Central fire detection and alarm system',
                'serial_number' => 'FIRE-004-2021',
                'model' => 'SafeGuard Pro',
                'manufacturer' => 'FireTech Industries',
                'category' => 'Safety Equipment',
                'location' => 'Main Lobby',
                'purchase_date' => '2021-09-15',
                'warranty_expiry' => '2024-09-15',
                'status' => 'active',
                'criticality' => 'critical',
                'notes' => 'Annual inspection required by law',
                'company_id' => $company?->id,
            ],
            [
                'name' => 'Elevator System - East Wing',
                'description' => 'Passenger elevator serving floors 1-10',
                'serial_number' => 'ELEV-005-2020',
                'model' => 'VerticalMax 3000',
                'manufacturer' => 'ElevatorCorp',
                'category' => 'Mechanical',
                'location' => 'East Wing',
                'purchase_date' => '2020-11-30',
                'warranty_expiry' => '2023-11-30',
                'status' => 'active',
                'criticality' => 'high',
                'notes' => 'Monthly safety inspections required',
                'company_id' => $company?->id,
            ],
        ];

        foreach ($equipment as $item) {
            Equipment::create($item);
        }
    }
}