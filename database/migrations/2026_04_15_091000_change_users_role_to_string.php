<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role VARCHAR(120) NOT NULL DEFAULT 'student'");

            return;
        }

        if ($driver === 'sqlite') {
            $this->rebuildSqliteUsersTable(asStringRole: true);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::table('users')
                ->whereRaw("LOWER(role) NOT IN ('admin', 'teacher', 'student')")
                ->update(['role' => 'student']);

            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'teacher', 'student') NOT NULL DEFAULT 'student'");

            return;
        }

        if ($driver === 'sqlite') {
            $this->rebuildSqliteUsersTable(asStringRole: false);
        }
    }

    private function rebuildSqliteUsersTable(bool $asStringRole): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        Schema::create('users_rebuild', function (Blueprint $table) use ($asStringRole): void {
            $table->id();
            $table->string('name');
            $table->string('identity_number')->unique();

            if ($asStringRole) {
                $table->string('role', 120)->default('student');
            } else {
                $table->enum('role', ['admin', 'teacher', 'student'])->default('student');
            }

            $table->string('kelas')->default('-');
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index('role');
            $table->index('kelas');
        });

        $roleSelectExpression = $asStringRole
            ? 'role'
            : "CASE WHEN LOWER(role) IN ('admin', 'teacher', 'student') THEN LOWER(role) ELSE 'student' END";

        DB::statement('INSERT INTO users_rebuild (id, name, identity_number, role, kelas, email, phone, is_active, email_verified_at, password, remember_token, created_at, updated_at) '
            . 'SELECT id, name, identity_number, ' . $roleSelectExpression . ', kelas, email, phone, is_active, email_verified_at, password, remember_token, created_at, updated_at FROM users');

        Schema::drop('users');
        Schema::rename('users_rebuild', 'users');

        DB::statement('PRAGMA foreign_keys = ON');
    }
};
