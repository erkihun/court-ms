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

        $timestamp = now()->toDateTimeString();

        $perms = [
            // Cases
            [
                'name' => 'cases.view',
                'label' => 'View cases',
                'description' => null,
            ],
            [
                'name' => 'cases.create',
                'label' => 'Create case',
                'description' => null,
            ],
            [
                'name' => 'cases.edit',
                'label' => 'Edit case',
                'description' => null,
            ],
            [
                'name' => 'cases.delete',
                'label' => 'Delete case',
                'description' => null,
            ],
            [
                'name' => 'cases.assign',
                'label' => 'Assign case',
                'description' => null,
            ],
            [
                'name' => 'cases.types',
                'label' => 'Create Case Type',
                'description' => 'Create Case Type',
            ],
            [
                'name' => 'cases.review',
                'label' => null,
                'description' => null,
            ],
            [
                'name' => 'cases.assign.team',
                'label' => 'Assign case to team leaders',
                'description' => null,
            ],
            [
                'name' => 'cases.assign.member',
                'label' => 'Assign case to team members',
                'description' => null,
            ],

            // Appeals
            [
                'name' => 'appeals.view',
                'label' => 'View appeals',
                'description' => null,
            ],
            [
                'name' => 'appeals.create',
                'label' => 'Create appeal',
                'description' => null,
            ],
            [
                'name' => 'appeals.edit',
                'label' => 'Edit appeal',
                'description' => null,
            ],
            [
                'name' => 'appeals.decide',
                'label' => 'Record decision',
                'description' => null,
            ],

            // Decisions
            [
                'name' => 'decision.view',
                'label' => 'View decisions',
                'description' => null,
            ],
            [
                'name' => 'decision.create',
                'label' => 'Create decision',
                'description' => null,
            ],
            [
                'name' => 'decision.update',
                'label' => 'Update decision',
                'description' => null,
            ],
            [
                'name' => 'decision.delete',
                'label' => 'Delete decision',
                'description' => null,
            ],
            [
                'name' => 'decisions.view',
                'label' => null,
                'description' => null,
            ],

            // User + roles
            [
                'name' => 'users.view',
                'label' => 'View users',
                'description' => null,
            ],
            [
                'name' => 'users.manage',
                'label' => 'Manage users',
                'description' => null,
            ],
            [
                'name' => 'roles.manage',
                'label' => 'Manage roles',
                'description' => null,
            ],
            [
                'name' => 'permissions.manage',
                'label' => 'Manage permissions',
                'description' => null,
            ],
            [
                'name' => 'templates.manage',
                'label' => 'Manage letter templates',
                'description' => null,
            ],

            // Reports
            [
                'name' => 'reports.view',
                'label' => 'View reports',
                'description' => null,
            ],
            [
                'name' => 'reports.export',
                'label' => 'Export reports',
                'description' => null,
            ],

            // System + teams
            [
                'name' => 'settings.manage',
                'label' => null,
                'description' => null,
            ],
            [
                'name' => 'teams.manage',
                'label' => 'Manage teams',
                'description' => null,
            ],
            [
                'name' => 'notes.manage',
                'label' => null,
                'description' => null,
            ],

            // Applicants
            [
                'name' => 'applicants.manage',
                'label' => 'Manage applicants',
                'description' => null,
            ],
            [
                'name' => 'applicants.view',
                'label' => 'View applicants',
                'description' => null,
            ],

            // Bench notes
            [
                'name' => 'bench-notes.manage',
                'label' => 'Manage bench notes',
                'description' => null,
            ],
            [
                'name' => 'bench-notes.create',
                'label' => null,
                'description' => null,
            ],
            [
                'name' => 'bench-notes.update',
                'label' => null,
                'description' => null,
            ],
            [
                'name' => 'bench-notes.delete',
                'label' => null,
                'description' => null,
            ],
            [
                'name' => 'bench-notes.view',
                'label' => null,
                'description' => null,
            ],

            // Letters
            [
                'name' => 'letters.templet.view',
                'label' => null,
                'description' => null,
            ],
            [
                'name' => 'letters.templet.create',
                'label' => null,
                'description' => null,
            ],
            [
                'name' => 'letters.templet.update',
                'label' => null,
                'description' => null,
            ],
            [
                'name' => 'letters.templet.delete',
                'label' => null,
                'description' => null,
            ],
            [
                'name' => 'letters.view',
                'label' => null,
                'description' => null,
            ],
            [
                'name' => 'letters.create',
                'label' => null,
                'description' => null,
            ],
            [
                'name' => 'letters.update',
                'label' => null,
                'description' => null,
            ],
            [
                'name' => 'letters.delete',
                'label' => null,
                'description' => null,
            ],
            [
                'name' => 'letters.approve',
                'label' => null,
                'description' => null,
            ],

            // Hearing
            [
                'name' => 'hearing.view',
                'label' => null,
                'description' => null,
            ],
            [
                'name' => 'hearing.create',
                'label' => null,
                'description' => null,
            ],
            [
                'name' => 'hearing.update',
                'label' => null,
                'description' => null,
            ],
            [
                'name' => 'hearing.delete',
                'label' => null,
                'description' => null,
            ],

            // Files
            [
                'name' => 'file.view',
                'label' => null,
                'description' => null,
            ],
            [
                'name' => 'file.create',
                'label' => null,
                'description' => null,
            ],
            [
                'name' => 'file.update',
                'label' => null,
                'description' => null,
            ],
            [
                'name' => 'file.delete',
                'label' => null,
                'description' => null,
            ],

            // Messaging
            [
                'name' => 'message.create',
                'label' => null,
                'description' => null,
            ],
        ];

        $perms = array_map(static function (array $permission) use ($timestamp): array {
            return [
                'name' => $permission['name'],
                'label' => $permission['label'],
                'description' => $permission['description'],
                'label_translations' => null,
                'description_translations' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }, $perms);

        DB::table('permissions')->upsert(
            $perms,
            ['name'],
            ['label', 'description', 'label_translations', 'description_translations', 'updated_at']
        );
    }
}
