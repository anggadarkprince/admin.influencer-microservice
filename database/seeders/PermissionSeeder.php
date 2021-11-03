<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Permission::insert([
            ['name' => 'view-users'],
            ['name' => 'create-users'],
            ['name' => 'edit-users'],
            ['name' => 'delete-users'],

            ['name' => 'view-roles'],
            ['name' => 'create-roles'],
            ['name' => 'edit-roles'],
            ['name' => 'delete-roles'],

            ['name' => 'view-products'],
            ['name' => 'create-products'],
            ['name' => 'edit-products'],
            ['name' => 'delete-products'],

            ['name' => 'view-orders'],
            ['name' => 'create-orders'],
            ['name' => 'edit-orders'],
            ['name' => 'delete-orders'],
        ]);
    }
}
