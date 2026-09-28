<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('employee'); // employee | manager | hr
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('calendar_token', 64)->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['calendar_token']);
            $table->dropConstrainedForeignId('manager_id');
            $table->dropColumn(['role', 'calendar_token']);
        });
    }
};
