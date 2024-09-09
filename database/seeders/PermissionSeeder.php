<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{

    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $adminPermissions = [
            'add_restaurant',
            'edit_restaurant',
            'list_restaurant',
            'delete_restaurant',
            'block_restaurant',
            'activate_restaurant',
            'list_manager',
            'add_manager',
            'edit_manager',
            'delete_manager',
        ];

        $managerPermissions = [
            'manager_edit_restaurant',
            'list_product_category',
            'add_product_category',
            'edit_product_category',
            'delete_product_category',
            'list_product',
            'add_product',
            'edit_product',
            'delete_product',
            'list_table',
            'add_table',
            'edit_table',
            'delete_table',
            'list_staff',
            'add_staff',
            'edit_staff',
            'delete_staff',
            'list_menu',
            'add_menu',
            'edit_menu',
            'delete_menu',
            'list_order',
            'list_service'
        ];

        $staffPermissions = [
//            'list_product_category',
//            'add_product_category',
//            'edit_product_category',
//            'delete_product_category',
//            'list_product',
//            'add_product',
//            'edit_product',
//            'delete_product',
            'staff_list_order',
            'staff_list_all_order',
        ];

        foreach ($adminPermissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        foreach ($managerPermissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        foreach ($staffPermissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo($adminPermissions);

        $manager = Role::create(['name' => 'manager']);
        $managerPermissions[] = 'edit_restaurant';
        $manager->givePermissionTo($managerPermissions);

        $staff = Role::create(['name' => 'staff']);
        $staff->givePermissionTo($staffPermissions);

        $client = Role::create(['name' => 'client']);
        //$client->givePermissionTo($permissions);

    }
}
