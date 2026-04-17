<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->longText('face_encoding')->nullable()->after('password');
            $table->timestamp('face_registered_at')->nullable()->after('face_encoding');
            $table->index('face_registered_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['face_registered_at']);
            $table->dropColumn(['face_encoding', 'face_registered_at']);
        });
    }
};
