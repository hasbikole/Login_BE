<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->delete();
        $data = [
            [
                'name' => 'admin',
                'description' => 'admin'
            ],
            [
                'name' => 'company',
                'description' => 'company'
            ],
            [
                'name' => 'user',
                'description' => 'user'
            ],
        ];
        
        Role::insert($data);
    }
}
