<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Čitateľný pohľad na priradenia projekt–používateľ (názov projektu, meno používateľa).
     */
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE VIEW project_user_readable AS
            SELECT
                pu.project_id,
                pu.user_id,
                p.name AS project_name,
                u.first_name AS user_first_name,
                u.last_name AS user_last_name
            FROM project_user pu
            JOIN projects p ON p.id = pu.project_id
            JOIN users u ON u.id = pu.user_id
        SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS project_user_readable');
    }
};
