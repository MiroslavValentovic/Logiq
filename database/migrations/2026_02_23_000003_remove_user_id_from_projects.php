<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Odstráni user_id z projects (existujúce inštalácie po úprave štruktúry).
     */
    public function up(): void
    {
        if (! Schema::hasColumn('projects', 'user_id')) {
            return;
        }

        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('projects', 'user_id')) {
            return;
        }

        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
        });
    }
};
