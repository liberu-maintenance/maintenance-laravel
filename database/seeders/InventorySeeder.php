<?php

namespace Database\Seeders;

use App\Models\InventoryPart;
use App\Models\InventoryStockLevel;
use App\Models\Company;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get suppliers for mapping
        $suppliers = [
            'HVAC Supply Co.' => Company::where('name', 'HVAC Supply Warehouse')->first(),
            'Bearing Depot' => Company::where('name', 'Bearing & Fastener Depot')->first(),
            'Electrical Supplies Inc.' => Company::where('name', 'Industrial Parts Supply Co.')->first(),
            'Industrial Lubricants' => Company::where('name', 'Industrial Parts Supply Co.')->first(),
            'Fastener World' => Company::where('name', 'Bearing & Fastener Depot')->first(),
            'Hydraulic Parts Supply' => Company::where('name', 'Industrial Parts Supply Co.')->first(),
            'Safety First Inc.' => Company::where('name', 'Industrial Parts Supply Co.')->first(),
            'Janitorial Supplies Co.' => Company::where('name', 'Industrial Parts Supply Co.')->first(),
        ];

        $parts = [
            [
                'part_number' => 'HVAC-FILTER-001',
                'name' => 'Air Filter 20x20x1',
                'description' => 'Standard air filter for HVAC systems',
                'category' => 'Filters',
                'unit_of_measure' => 'piece',
                'unit_cost' => 12.50,
                'reorder_level' => 10,
                'reorder_quantity' => 50,
                'location' => 'Warehouse A - Shelf 1',
                'supplier' => 'HVAC Supply Co.',
                'lead_time_days' => 5,
            ],
            [
                'part_number' => 'MECH-BEARING-001',
                'name' => 'Ball Bearing 6205',
                'description' => 'Standard ball bearing for motors and pumps',
                'category' => 'Mechanical',
                'unit_of_measure' => 'piece',
                'unit_cost' => 8.75,
                'reorder_level' => 5,
                'reorder_quantity' => 20,
                'location' => 'Warehouse A - Shelf 2',
                'supplier' => 'Bearing Depot',
                'lead_time_days' => 3,
            ],
            [
                'part_number' => 'ELEC-FUSE-001',
                'name' => '30A Fuse',
                'description' => '30 Amp electrical fuse',
                'category' => 'Electrical',
                'unit_of_measure' => 'piece',
                'unit_cost' => 2.50,
                'reorder_level' => 20,
                'reorder_quantity' => 100,
                'location' => 'Warehouse B - Bin 5',
                'supplier' => 'Electrical Supplies Inc.',
                'lead_time_days' => 2,
            ],
            [
                'part_number' => 'LUB-OIL-001',
                'name' => 'Motor Oil 10W-30',
                'description' => 'Synthetic motor oil for equipment lubrication',
                'category' => 'Lubricants',
                'unit_of_measure' => 'quart',
                'unit_cost' => 6.99,
                'reorder_level' => 15,
                'reorder_quantity' => 50,
                'location' => 'Warehouse C - Cabinet 1',
                'supplier' => 'Industrial Lubricants',
                'lead_time_days' => 7,
            ],
            [
                'part_number' => 'FAST-BOLT-001',
                'name' => 'Hex Bolt M10x50',
                'description' => 'Metric hex bolt 10mm x 50mm',
                'category' => 'Fasteners',
                'unit_of_measure' => 'piece',
                'unit_cost' => 0.45,
                'reorder_level' => 50,
                'reorder_quantity' => 500,
                'location' => 'Warehouse B - Bin 10',
                'supplier' => 'Fastener World',
                'lead_time_days' => 2,
            ],
            [
                'part_number' => 'HYDR-SEAL-001',
                'name' => 'O-Ring 2.5" ID',
                'description' => 'Hydraulic seal o-ring 2.5 inch inner diameter',
                'category' => 'Hydraulic',
                'unit_of_measure' => 'piece',
                'unit_cost' => 3.25,
                'reorder_level' => 10,
                'reorder_quantity' => 50,
                'location' => 'Warehouse A - Drawer 3',
                'supplier' => 'Hydraulic Parts Supply',
                'lead_time_days' => 5,
            ],
            [
                'part_number' => 'SAFE-GLOVE-001',
                'name' => 'Safety Gloves - Large',
                'description' => 'Heavy duty work gloves size large',
                'category' => 'Safety',
                'unit_of_measure' => 'pair',
                'unit_cost' => 12.99,
                'reorder_level' => 20,
                'reorder_quantity' => 100,
                'location' => 'Safety Equipment Room',
                'supplier' => 'Safety First Inc.',
                'lead_time_days' => 3,
            ],
            [
                'part_number' => 'CONS-RAGS-001',
                'name' => 'Shop Rags Box',
                'description' => 'Box of 100 shop rags for cleaning',
                'category' => 'Consumables',
                'unit_of_measure' => 'box',
                'unit_cost' => 24.99,
                'reorder_level' => 5,
                'reorder_quantity' => 20,
                'location' => 'Warehouse C - Shelf 5',
                'supplier' => 'Janitorial Supplies Co.',
                'lead_time_days' => 2,
            ],
        ];

        foreach ($parts as $partData) {
            // Add supplier_id based on supplier name mapping
            if (isset($partData['supplier']) && isset($suppliers[$partData['supplier']])) {
                $partData['supplier_id'] = $suppliers[$partData['supplier']]?->company_id;
            }
            
            $part = InventoryPart::create($partData);

            // Create initial stock levels for each part
            InventoryStockLevel::create([
                'inventory_part_id' => $part->id,
                'location' => $partData['location'],
                'quantity' => rand(15, 100), // Random initial quantity
                'reserved_quantity' => 0,
            ]);
        }
    }
}
