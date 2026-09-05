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
        Schema::table('users', function (Blueprint $table) {
            $table->string('nip', 30)->nullable()->after('email');
            $table->string('no_hp', 25)->nullable()->after('nip');
            $table->string('unit_kerja', 200)->nullable()->after('no_hp');
            $table->boolean('is_first_login')->default(true)->after('unit_kerja');
            $table->string('temp_password', 100)->nullable()->after('is_first_login');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nip', 'no_hp', 'unit_kerja', 'is_first_login', 'temp_password']);
        });
    }
};
