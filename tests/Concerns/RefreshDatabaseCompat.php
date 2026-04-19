<?php

namespace Tests\Concerns;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\DB;

/**
 * Drop-in replacement for RefreshDatabase that handles MySQL-specific
 * migrations (e.g. ALTER TABLE ... CHANGE) failing on SQLite :memory:.
 *
 * When migrate:fresh throws a QueryException containing "CHANGE",
 * the trait marks the failed migration as run and continues with the rest.
 */
trait RefreshDatabaseCompat
{
    use RefreshDatabase;

    protected function refreshTestDatabase(): void
    {
        if (! RefreshDatabaseState::$migrated) {
            try {
                $this->artisan('migrate:fresh', $this->migrateFreshUsing());
            } catch (\Illuminate\Database\QueryException $e) {
                if (str_contains($e->getMessage(), 'CHANGE') && config('database.default') === 'sqlite') {
                    // Mark the MySQL-specific migration as run so migrate can continue
                    $batch = DB::table('migrations')->max('batch') ?? 1;
                    DB::table('migrations')->insert([
                        'migration' => '2026_02_25_000001_update_complaint_follow_ups_work_order_enum',
                        'batch' => $batch,
                    ]);

                    // Run remaining migrations
                    $this->artisan('migrate');
                } else {
                    throw $e;
                }
            }

            RefreshDatabaseState::$migrated = true;
        }

        $this->beginDatabaseTransaction();
    }
}
