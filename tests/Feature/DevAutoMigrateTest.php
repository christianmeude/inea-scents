<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Tests\TestCase;

class DevAutoMigrateTest extends TestCase
{
    public function test_pending_detection_treats_missing_table_as_all_pending(): void
    {
        $migrator = new class
        {
            public function getMigrationFiles($path): array
            {
                return ['a' => 'x', 'b' => 'y'];
            }

            public function getRepository(): object
            {
                return new class
                {
                    public function getRan(): array
                    {
                        throw new \RuntimeException('no migrations table');
                    }
                };
            }
        };

        $this->assertNull($this->pendingMigrations($migrator));
    }

    public function test_pending_detection_diffs_ran(): void
    {
        $migrator = new class
        {
            public function getMigrationFiles($path): array
            {
                return ['a' => 'x', 'b' => 'y'];
            }

            public function getRepository(): object
            {
                return new class
                {
                    public function getRan(): array
                    {
                        return ['a'];
                    }
                };
            }
        };

        $this->assertSame(['b'], $this->pendingMigrations($migrator));
    }

    private function pendingMigrations(object $migrator): ?array
    {
        $provider = new AppServiceProvider($this->app);
        $method = new \ReflectionMethod($provider, 'pendingMigrations');

        return $method->invoke($provider, $migrator);
    }
}
