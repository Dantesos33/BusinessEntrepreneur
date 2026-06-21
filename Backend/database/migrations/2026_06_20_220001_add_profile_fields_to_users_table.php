<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Extends the default Laravel "users" table (already created by
     * 0001_01_01_000000_create_users_table.php) with the fields the
     * React app's User type needs: role, avatarUrl, bio, isOnline.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['entrepreneur', 'investor'])->after('email');
            $table->string('avatar_url')->nullable()->after('role');
            $table->text('bio')->nullable()->after('avatar_url');
            $table->boolean('is_online')->default(false)->after('bio');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'avatar_url', 'bio', 'is_online']);
        });
    }
};
