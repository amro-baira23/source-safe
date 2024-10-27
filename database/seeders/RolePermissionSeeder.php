<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Role::create(['name' => 'admin']) ;
        $user = Role::create(['name' => 'user']);


        $adminUser1 = User::factory()->create([
            'username'=>'admin1',
            'email'=> 'admin1@gmail.com',
            'password'=> bcrypt('12345678')
        ]);
        $adminUser2 = User::factory()->create([
            'username'=>'admin2',
            'email'=> 'admin2@gmail.com',
            'password'=> bcrypt('12345678')
        ]);
        $adminUser3 = User::factory()->create([
            'username'=>'admin3',
            'email'=> 'admin3@gmail.com',
            'password'=> bcrypt('12345678')
        ]);

        $adminUser1->assignRole($admin);
        $adminUser2->assignRole($admin);
        $adminUser3->assignRole($admin);

    }
}
