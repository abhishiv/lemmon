<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     *
     * @return void
     */
    public function run()
    {
        if (env('APP_ENV') == 'production') {
            $users = [
                [
                    'name' => 'george',
                    'email' => 'george@geopress.ro',
                    'phone' => '+40751097307',
                    'password' => bcrypt('george2022!'),
                    'restaurant_id' => null,
                    'role' => 'admin',
                    'status' => User::ACTIVE,
                    'permissions' => [
                        'add_restaurant',
                        'edit_restaurant',
                        'list_restaurant',
                        'delete_restaurant',
                        'block_restaurant',
                        'activate_restaurant',
                    ]
                ],
                [
                    'name' => 'anamaria',
                    'email' => 'anabarbut@thelemmon.ch',
                    'phone' => '+407510973066',
                    'password' => bcrypt('anabarbut2022!'),
                    'restaurant_id' => null,
                    'role' => 'admin',
                    'status' => User::ACTIVE,
                    'permissions' => [
                        'add_restaurant',
                        'edit_restaurant',
                        'list_restaurant',
                        'delete_restaurant',
                        'block_restaurant',
                        'activate_restaurant',
                    ]
                ],
				
				[
                    'name' => 'guga',
                    'email' => 'vashakidze@gmail.com',
                    'phone' => '+407510973067',
                    'password' => bcrypt('guga2022!'),
                    'restaurant_id' => null,
                    'role' => 'admin',
                    'status' => User::ACTIVE,
                    'permissions' => [
                        'add_restaurant',
                        'edit_restaurant',
                        'list_restaurant',
                        'delete_restaurant',
                        'block_restaurant',
                        'activate_restaurant',
                    ]
                ],
            ];
        } else {
            $users = [
                [
                    'name' => 'admin',
                    'email' => 'admin@thelemmon.ch',
                    'phone' => '0789789789',
                    'password' => bcrypt(12345678),
                    'role' => 'admin',
                    'restaurant_id' => null,
                    'status' => User::ACTIVE,
                    'permissions' => [
                        'add_restaurant',
                        'edit_restaurant',
                        'list_restaurant',
                        'delete_restaurant',
                        'block_restaurant',
                        'activate_restaurant',
                    ]
                ],
                [
                    'name' => 'manager',
                    'email' => 'manager@thelemmon.ch',
                    'phone' => '0789789782',
                    'password' => bcrypt(12345678),
                    'role' => 'manager',
                    'status' => User::ACTIVE,
                    'restaurant_id' => 1,
                    'permissions' => [
                        'list_product_category',
                        'add_product_category',
                        'edit_product_category',
                        'delete_product_category',
                        'list_product',
                        'add_product',
                        'edit_product',
                        'delete_product',
                    ]
                ],
                [
                    'name' => 'staff',
                    'email' => 'staff@thelemmon.ch',
                    'phone' => '0789789781',
                    'password' => bcrypt(12345678),
                    'restaurant_id' => 1,
                    'staff_type' => 'waiter',
                    'role' => 'staff',
                    'status' => User::ACTIVE,
                    'permissions' => [
                        'delete_product'
                    ]
                ]

            ];
        }

        foreach ($users as $user) {
            $u = User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'restaurant_id' => $user['restaurant_id'],
                'status' => $user['status'],
                'staff_type' => $user['role'] == 'staff' ? 'waiter' : null,
                'phone' => $user['phone'],
                'password' => $user['password'],
                'remember_token' => Str::random(60),
                'email_verified_at' => Carbon::now()
            ]);

            $u->assignRole($user['role']);
            $u->givePermissionTo($user['permissions']);
        }
    }
}
