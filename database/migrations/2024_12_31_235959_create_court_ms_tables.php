<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. First, modify users table if it exists
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'phone')) {
                    $table->string('phone')->nullable()->after('email');
                }
                if (!Schema::hasColumn('users', 'address')) {
                    $table->text('address')->nullable()->after('phone');
                }
                if (!Schema::hasColumn('users', 'status')) {
                    $table->enum('status', ['active', 'inactive'])->default('active')->after('address');
                }
                if (!Schema::hasColumn('users', 'user_type')) {
                    $table->enum('user_type', ['staff', 'judge', 'lawyer', 'client'])->default('staff')->after('status');
                }
                if (!Schema::hasColumn('users', 'deleted_at')) {
                    $table->softDeletes()->after('updated_at');
                }
            });
        }

        // 2. Create standalone tables first (no foreign keys)
        $this->createRolesTable();
        $this->createPermissionsTable();
        $this->createCaseTypesTable();
        $this->createCourtsTable();

        // 3. Create tables with foreign keys
        $this->createRoleUserTable();
        $this->createPermissionRoleTable();
        $this->createCourtCasesTable();
        $this->createHearingsTable();
        $this->createDocumentsTable();
        $this->createCasePartiesTable();
    }

    private function createRolesTable()
    {
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }
    }

    private function createPermissionsTable()
    {
        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }
    }

    private function createCaseTypesTable()
    {
        if (!Schema::hasTable('case_types')) {
            Schema::create('case_types', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }
    }

    private function createCourtsTable()
    {
        if (!Schema::hasTable('courts')) {
            Schema::create('courts', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('type');
                $table->string('location');
                $table->text('address')->nullable();
                $table->string('contact_info')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    private function createRoleUserTable()
    {
        if (!Schema::hasTable('role_user')) {
            Schema::create('role_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('role_id')->constrained()->onDelete('cascade');
                $table->timestamps();

                $table->unique(['user_id', 'role_id']);
            });
        }
    }

    private function createPermissionRoleTable()
    {
        if (!Schema::hasTable('permission_role')) {
            Schema::create('permission_role', function (Blueprint $table) {
                $table->id();
                $table->foreignId('permission_id')->constrained()->onDelete('cascade');
                $table->foreignId('role_id')->constrained()->onDelete('cascade');
                $table->timestamps();

                $table->unique(['permission_id', 'role_id']);
            });
        }
    }

    private function createCourtCasesTable()
    {
        if (!Schema::hasTable('court_cases')) {
            Schema::create('court_cases', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('applicant_id')->nullable();
                $table->string('case_number')->unique();
                $table->string('code', 8)->nullable()->unique();
                $table->string('title');
                $table->string('respondent_name')->nullable();
                $table->string('respondent_address')->nullable();
                $table->text('description')->nullable();
                $table->text('relief_requested')->nullable();
                $table->foreignId('case_type_id')->constrained();
                $table->foreignId('judge_id')->nullable()->constrained('users');
                $table->date('filing_date');
                $table->date('first_hearing_date')->nullable();
                $table->enum('status', ['pending', 'active', 'adjourned', 'dismissed', 'closed'])->default('pending');
                $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('assigned_at')->nullable();
                $table->text('notes')->nullable();
                $table->string('review_status', 30)->default('awaiting_review')->index();
                $table->text('review_note')->nullable();
                $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    private function createHearingsTable()
    {
        if (!Schema::hasTable('hearings')) {
            Schema::create('hearings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('court_case_id')->constrained()->onDelete('cascade');
                $table->date('hearing_date');
                $table->time('hearing_time');
                $table->string('location')->nullable();
                $table->text('purpose');
                $table->text('outcome')->nullable();
                $table->enum('status', ['scheduled', 'completed', 'adjourned', 'cancelled'])->default('scheduled');
                $table->timestamps();
            });
        }
    }

    private function createDocumentsTable()
    {
        if (!Schema::hasTable('documents')) {
            Schema::create('documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('court_case_id')->constrained()->onDelete('cascade');
                $table->string('title');
                $table->string('file_path');
                $table->string('file_type')->nullable();
                $table->integer('file_size')->nullable();
                $table->text('description')->nullable();
                $table->foreignId('uploaded_by')->constrained('users');
                $table->timestamps();
            });
        }
    }

    private function createCasePartiesTable()
    {
        if (!Schema::hasTable('case_parties')) {
            Schema::create('case_parties', function (Blueprint $table) {
                $table->id();
                $table->foreignId('court_case_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->enum('party_type', ['plaintiff', 'defendant', 'lawyer', 'witness', 'judge']);
                $table->text('representation')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        // Drop tables in reverse order (with foreign keys first)
        Schema::dropIfExists('case_parties');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('hearings');
        Schema::dropIfExists('court_cases');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('courts');
        Schema::dropIfExists('case_types');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');

        // Revert users table changes
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['phone', 'address', 'status', 'user_type', 'deleted_at']);
            });
        }
    }
};
