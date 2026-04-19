<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CustomerRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \Spatie\Permission\Models\Role::findOrCreate('customer', 'web');
    }
}
