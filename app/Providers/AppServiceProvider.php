<?php

namespace App\Providers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        $this->migrateDevDatabase();
    }

    /**
     * Local-only convenience: run pending migrations on the dev database
     * at boot so new tables exist before the first page that needs them.
     * Testing owns postgres_test via RefreshDatabase; production migrates
     * manually. Never fatal: a stopped DB degrades to the usual error.
     */
    private function migrateDevDatabase(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        try {
            $migrator = app('migrator');
            $pending = $this->pendingMigrations($migrator) ?? array_keys($migrator->getMigrationFiles(database_path('migrations')));

            if ($pending !== []) {
                Artisan::call('migrate', ['--force' => true]);
            }
        } catch (\Throwable $e) {
            Log::warning('Local auto-migrate skipped.', ['error' => $e->getMessage()]);
        }
    }

    private function pendingMigrations($migrator): ?array
    {
        $files = $migrator->getMigrationFiles(database_path('migrations'));

        try {
            $ran = $migrator->getRepository()->getRan();
        } catch (\Throwable) {
            return null;
        }

        return array_values(array_diff(array_keys($files), $ran));
    }
}
