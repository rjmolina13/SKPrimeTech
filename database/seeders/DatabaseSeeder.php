<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Municipality;
use App\Models\Barangay;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roles = [
            'super_admin',
            'admin',
            'municipal',
        ];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Seeding a sample municipality directly (no province)
        $municipality = Municipality::firstOrCreate(
            ['code' => 'MUN-001'],
            ['name' => 'Sample Municipality']
        );
        $barangay = Barangay::firstOrCreate(
            ['municipality_id' => $municipality->id, 'code' => 'BRGY-001'],
            ['name' => 'Sample Barangay']
        );

        $admin = User::firstOrCreate(
            ['email' => 'superadmin@rubyj.xyz'],
            ['name' => 'Super Admin', 'password' => 'supaSKPFadmin']
        );
        $admin->assignRole('super_admin');

        $regularAdmin = User::firstOrCreate(
            ['email' => 'admin@rubyj.xyz'],
            ['name' => 'Admin', 'password' => 'Adminskpf#69']
        );
        $regularAdmin->assignRole('admin');
    }
}
