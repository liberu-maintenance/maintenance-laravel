<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Core system seeders
            PermissionsSeeder::class,
            RolesSeeder::class,
            MenuSeeder::class,
//            SiteSettingsSeeder::class,

            // Application data seeders
            UserSeeder::class,
            CompanySeeder::class,
            EquipmentSeeder::class,
            ChecklistSeeder::class,
            MaintenanceScheduleSeeder::class,
            WorkOrderSeeder::class,
            InventorySeeder::class,
        ]);

        $this->command->info('All seeders completed successfully!');
    }
}
