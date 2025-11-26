<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class SampleUsersSeeder extends Seeder
{
    public function run(): void
    {
        $schema = DB::getSchemaBuilder();
        foreach (['users', 'roles', 'role_user'] as $t) {
            if (!$schema->hasTable($t)) {
                $this->command->warn("Skipping SampleUsersSeeder: missing table '$t'.");
                return;
            }
        }

        // Ensure base roles exist
        foreach (['admin', 'staff', 'viewer'] as $name) {
            DB::table('roles')->updateOrInsert(['name' => $name], ['name' => $name]);
        }

        $roleId = fn($name) => DB::table('roles')->where('name', $name)->value('id');

        $users = [
            ['name' => 'Admin User', 'email' => 'admin@example.com',  'role' => 'admin'],
            ['name' => 'Staff One',  'email' => 'staff1@example.com', 'role' => 'staff'],
            ['name' => 'Viewer One', 'email' => 'viewer1@example.com', 'role' => 'viewer'],
        ];

        foreach ($users as $u) {
            $user = User::updateOrCreate(
                ['email' => $u['email']],
                ['name' => $u['name'], 'password' => Hash::make('password')]
            );

            $rid = $roleId($u['role']);
            if ($rid) {
                DB::table('role_user')->updateOrInsert(
                    ['user_id' => $user->id, 'role_id' => $rid],
                    []
                );
            }
        }

        $this->command?->info('Sample users seeded. (password = "password")');
    }
}
