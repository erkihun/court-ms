<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
        ]);

        $this->assignAdminPermissions();
    }

    private function assignAdminPermissions(): void
    {
        $schema = DB::getSchemaBuilder();
        if (!$schema->hasTable('roles') || !$schema->hasTable('permissions') || !$schema->hasTable('permission_role')) {
            $this->command->warn("Skipping RolesAndPermissionsSeeder: required tables missing.");
            return;
        }

        $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');
        if (!$adminRoleId) {
            $this->command->warn("Skipping RolesAndPermissionsSeeder: 'admin' role not found.");
            return;
        }

        $permissionIds = DB::table('permissions')->pluck('id');

        foreach ($permissionIds as $permissionId) {
            DB::table('permission_role')->updateOrInsert(
                ['role_id' => $adminRoleId, 'permission_id' => $permissionId],
                []
            );
        }
    }
}
