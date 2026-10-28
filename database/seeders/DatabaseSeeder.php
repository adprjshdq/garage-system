<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's base/production database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            WorkshopSettingSeeder::class,
        ]);

        // Default Owner Account
        $ownerRole = Role::where('name', 'owner')->first();

        $owner = User::firstOrCreate(
            ['email' => 'owner@motocare.com'],
            [
                'name' => 'Budi Santoso (Owner)',
                'username' => 'owner',
                'password' => Hash::make('password123'),
                'phone' => '081234567890',
                'is_active' => true,
            ]
        );

        if ($ownerRole && ! $owner->hasRole('owner')) {
            $owner->roles()->syncWithoutDetaching([$ownerRole->id]);
        }
    }
}
