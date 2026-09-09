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
            $files = $migrator->getMigrationFiles(database_path('migrations'));
            $pending = array_diff(array_keys($files), $migrator->getRepository()->getRan());

            if ($pending !== []) {
                Artisan::call('migrate', ['--force' => true]);
            }
        } catch (\Throwable $e) {
            Log::warning('Local auto-migrate skipped.', ['error' => $e->getMessage()]);
        }
    }
}
