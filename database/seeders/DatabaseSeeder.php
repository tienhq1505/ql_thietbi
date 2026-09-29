<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Chỉ chạy dữ liệu cơ bản (không thiết bị, không master data):
     *   php artisan migrate:fresh --seed
     *
     * Để seed thêm danh mục & thiết bị demo:
     *   php artisan db:seed --class=DemoMasterDataSeeder
     *   php artisan db:seed --class=DemoEquipmentSeeder
     */
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class,
        ]);
    }
}
