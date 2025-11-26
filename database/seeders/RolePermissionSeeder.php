<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $schema = DB::getSchemaBuilder();
        if (!$schema->hasTable('roles') || !$schema->hasTable('permissions') || !$schema->hasTable('permission_role')) {
            $this->command->warn("Skipping RolePermissionSeeder: missing tables.");
            return;
        }

        $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');
        if (!$adminRoleId) {
            $this->command->warn("Admin role not found, skipping RolePermissionSeeder.");
            return;
        }

        $permIds = DB::table('permissions')->pluck('id');
        foreach ($permIds as $pid) {
            DB::table('permission_role')->updateOrInsert(
                ['role_id' => $adminRoleId, 'permission_id' => $pid],
                []
            );
        }
    }
}
