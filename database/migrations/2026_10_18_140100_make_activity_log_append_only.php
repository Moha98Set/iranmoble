<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Enforces the append-only rule at the database level, so a stray query,
 * a console command or a compromised account cannot rewrite history —
 * the ActivityPolicy only covers what goes through Eloquent.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->dropTriggers();

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER activity_log_no_update
            BEFORE UPDATE ON activity_log
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'activity_log is append-only: updates are not allowed';
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER activity_log_no_delete
            BEFORE DELETE ON activity_log
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'activity_log is append-only: deletes are not allowed';
            END
        SQL);
    }

    public function down(): void
    {
        $this->dropTriggers();
    }

    private function dropTriggers(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS activity_log_no_update');
        DB::unprepared('DROP TRIGGER IF EXISTS activity_log_no_delete');
    }
};
