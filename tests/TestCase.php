<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

use Spectator\Spectator;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (app()->environment('testing')) {
            $database = config('database.connections.'.config('database.default').'.database');

            throw_if($database !== 'postgres_test', new \RuntimeException(
                "Tests must run against the dedicated 'postgres_test' database, got '{$database}'. Refusing to risk wiping dev/prod data."
            ));
        }

        Spectator::using('api-docs.json');
    }
}
