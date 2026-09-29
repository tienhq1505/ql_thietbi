<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Gán khoa@mems.local vào "Khoa Nội" (code = KNOI)
        $knoiDepartment = Department::where('code', 'KNOI')->first();

        $users = [
            [
                'name' => 'Quản trị viên Hệ thống',
                'email' => 'admin@mems.local',
                'password' => Hash::make('password'),
                'department_id' => null,
                'status' => 'active',
                'role' => 'admin',
            ],
            [
                'name' => 'Cán bộ Phòng Vật tư - TBYT',
                'email' => 'vattu@mems.local',
                'password' => Hash::make('password'),
                'department_id' => null,
                'status' => 'active',
                'role' => 'equipment_dept',
            ],
            [
                'name' => 'Kỹ thuật viên Thiết bị',
                'email' => 'kythuat@mems.local',
                'password' => Hash::make('password'),
                'department_id' => null,
                'status' => 'active',
                'role' => 'technician',
            ],
            [
                'name' => 'Nhân viên Khoa Nội',
                'email' => 'khoa@mems.local',
                'password' => Hash::make('password'),
                'department_id' => $knoiDepartment?->id,
                'status' => 'active',
                'role' => 'using_dept',
            ],
            [
                'name' => 'Ban Giám đốc Bệnh viện',
                'email' => 'giamdoc@mems.local',
                'password' => Hash::make('password'),
                'department_id' => null,
                'status' => 'active',
                'role' => 'directorate',
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            $user->syncRoles([$role]);
        }
    }
}
