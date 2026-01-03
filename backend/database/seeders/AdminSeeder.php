<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 创建默认超级管理员
        Admin::create([
            'username' => 'admin',
            'email' => 'admin@iptv.local',
            'password' => Hash::make('admin123'),
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        $this->command->info('Default admin created successfully!');
        $this->command->info('Username: admin');
        $this->command->info('Password: admin123');
        $this->command->warn('Please change the default password after first login!');
    }
}
