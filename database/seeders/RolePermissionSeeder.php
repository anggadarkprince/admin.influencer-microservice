<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = Permission::all();

        $admin = Role::whereName('Admin')->first();

        foreach ($permissions as $permission) {
            RolePermission::insert([
                'role_id' => $admin->id,
                'permission_id' => $permission->id,
            ]);
        }

        $editor = Role::whereName('Editor')->first();

        foreach ($permissions as $permission) {
            if (!in_array($permission->name, ['edit_roles'])) {
                RolePermission::insert([
                    'role_id' => $editor->id,
                    'permission_id' => $permission->id,
                ]);
            }
        }

        $viewer = Role::whereName('Viewer')->first();

        $viewerRoles = [
            'view-users',
            'view-roles',
            'view-products',
            'view-orders',
        ];

        foreach ($permissions as $permission) {
            if (in_array($permission->name, $viewerRoles)) {
                RolePermission::insert([
                    'role_id' => $viewer->id,
                    'permission_id' => $permission->id,
                ]);
            }
        }
    }
}
