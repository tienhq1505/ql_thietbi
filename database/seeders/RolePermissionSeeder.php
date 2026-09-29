<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create permissions
        $permissions = [
            // Equipment permissions
            ['name' => 'equipment.view', 'display_name' => 'Xem danh sách & hồ sơ thiết bị', 'group_name' => 'Thiết bị'],
            ['name' => 'equipment.create', 'display_name' => 'Thêm mới thiết bị', 'group_name' => 'Thiết bị'],
            ['name' => 'equipment.edit', 'display_name' => 'Chỉnh sửa thông tin thiết bị', 'group_name' => 'Thiết bị'],
            ['name' => 'equipment.delete', 'display_name' => 'Xóa (Soft delete) thiết bị', 'group_name' => 'Thiết bị'],
            ['name' => 'equipment.request_delete', 'display_name' => 'Đề nghị xóa thiết bị', 'group_name' => 'Thiết bị'],
            ['name' => 'equipment.update_condition', 'display_name' => 'Cập nhật tình trạng kỹ thuật thiết bị', 'group_name' => 'Thiết bị'],

            // Import/Export permissions
            ['name' => 'equipment.import', 'display_name' => 'Nhập dữ liệu Excel', 'group_name' => 'Nhập/Xuất dữ liệu'],
            ['name' => 'equipment.export', 'display_name' => 'Xuất Excel / PDF', 'group_name' => 'Nhập/Xuất dữ liệu'],

            // Inventory permissions
            ['name' => 'inventory.manage', 'display_name' => 'Quản lý & thực hiện kiểm kê', 'group_name' => 'Kiểm kê'],

            // Master data & Category permissions
            ['name' => 'master_data.manage', 'display_name' => 'Quản lý danh mục chung', 'group_name' => 'Danh mục'],

            // System & User management
            ['name' => 'users.manage', 'display_name' => 'Quản lý người dùng & phân quyền', 'group_name' => 'Hệ thống'],
            ['name' => 'audit_log.view', 'display_name' => 'Xem nhật ký hệ thống (Audit Log)', 'group_name' => 'Hệ thống'],
        ];

        foreach ($permissions as $perm) {
            Permission::findOrCreate($perm['name'], 'web');
            $p = Permission::where('name', $perm['name'])->first();
            if ($p) {
                $p->update([
                    'display_name' => $perm['display_name'],
                    'group_name' => $perm['group_name'],
                ]);
            }
        }

        // 2. Define Roles
        $roles = [
            'admin' => [
                'display_name' => 'Quản trị hệ thống',
                'description' => 'Toàn quyền quản trị hệ thống, quản lý người dùng và duyệt xóa thiết bị',
                'permissions' => [
                    'equipment.view', 'equipment.create', 'equipment.edit', 'equipment.delete',
                    'equipment.request_delete', 'equipment.update_condition', 'equipment.import',
                    'equipment.export', 'inventory.manage', 'master_data.manage', 'users.manage',
                    'audit_log.view',
                ],
            ],
            'equipment_dept' => [
                'display_name' => 'Phòng Vật tư-TBYT',
                'description' => 'Quản lý danh mục thiết bị toàn viện, nhập xuất dữ liệu, đề nghị xóa và kiểm kê',
                'permissions' => [
                    'equipment.view', 'equipment.create', 'equipment.edit', 'equipment.request_delete',
                    'equipment.update_condition', 'equipment.import', 'equipment.export',
                    'inventory.manage', 'master_data.manage', 'audit_log.view',
                ],
            ],
            'technician' => [
                'display_name' => 'Kỹ thuật viên',
                'description' => 'Xem thiết bị toàn viện, cập nhật tình trạng kỹ thuật và tham gia kiểm kê',
                'permissions' => [
                    'equipment.view', 'equipment.update_condition', 'inventory.manage',
                ],
            ],
            'using_dept' => [
                'display_name' => 'Khoa sử dụng',
                'description' => 'Xem và đề nghị về thiết bị trong khoa mình, tham gia kiểm kê khoa',
                'permissions' => [
                    'equipment.view', 'equipment.request_delete', 'inventory.manage',
                ],
            ],
            'directorate' => [
                'display_name' => 'Ban giám đốc',
                'description' => 'Xem báo cáo tổng hợp, xuất dữ liệu toàn viện (Read-only)',
                'permissions' => [
                    'equipment.view', 'equipment.export', 'audit_log.view',
                ],
            ],
        ];

        foreach ($roles as $roleName => $roleData) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->update([
                'display_name' => $roleData['display_name'],
                'description' => $roleData['description'],
            ]);
            $role->syncPermissions($roleData['permissions']);
        }
    }
}
