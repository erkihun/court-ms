<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $schema = DB::getSchemaBuilder();
        if (!$schema->hasTable('permissions')) {
            $this->command->warn("Skipping PermissionSeeder: 'permissions' table not found.");
            return;
        }

        $perms = [
            // Cases
            ['name' => 'cases.view',     'label' => 'View cases',     'description' => null],
            ['name' => 'cases.create',   'label' => 'Create case',    'description' => null],
            ['name' => 'cases.edit',     'label' => 'Edit case',      'description' => null],
            ['name' => 'cases.delete',   'label' => 'Delete case',    'description' => null],
            ['name' => 'cases.assign',   'label' => 'Assign case',    'description' => null],
            ['name' => 'cases.assign.team', 'label' => 'Assign case to team leaders', 'description' => null],
            ['name' => 'cases.assign.member', 'label' => 'Assign case to team members', 'description' => null],
            ['name' => 'bench-notes.manage', 'label' => 'Manage bench notes', 'description' => null],
            ['name' => 'applicants.view', 'label' => 'View applicants', 'description' => null],
            ['name' => 'applicants.manage', 'label' => 'Manage applicants', 'description' => null],
            ['name' => 'teams.manage', 'label' => 'Manage teams', 'description' => null],

            // Appeals
            ['name' => 'appeals.view',   'label' => 'View appeals',   'description' => null],
            ['name' => 'appeals.create', 'label' => 'Create appeal',  'description' => null],
            ['name' => 'appeals.edit',   'label' => 'Edit appeal',    'description' => null],
            ['name' => 'appeals.decide', 'label' => 'Record decision', 'description' => null],
            ['name' => 'decision.view',   'label' => 'View decisions',   'description' => null],
            ['name' => 'decision.create', 'label' => 'Create decision', 'description' => null],
            ['name' => 'decision.update', 'label' => 'Update decision', 'description' => null],
            ['name' => 'decision.delete', 'label' => 'Delete decision', 'description' => null],

            // Users & roles
            ['name' => 'users.view',     'label' => 'View users',       'description' => null],
            ['name' => 'users.manage',   'label' => 'Manage users',     'description' => null],
            ['name' => 'roles.manage',   'label' => 'Manage roles',     'description' => null],
            ['name' => 'permissions.manage', 'label' => 'Manage permissions', 'description' => null],
            ['name' => 'templates.manage', 'label' => 'Manage letter templates', 'description' => null],

            // Reports
            ['name' => 'reports.view',   'label' => 'View reports',     'description' => null],
            ['name' => 'reports.export', 'label' => 'Export reports',   'description' => null],
        ];

        // Upsert by unique 'name'
        DB::table('permissions')->upsert(
            $perms,
            ['name'],
            ['label', 'description', 'updated_at']
        );
    }
}
