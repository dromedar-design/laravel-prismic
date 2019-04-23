<?php

namespace DromedarDesign\Prismic\Tests;

use DromedarDesign\Prismic\Providers\ServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'prismic');
        $app['config']->set('database.connections.prismic', [
            'driver' => 'prismic',
            'database' => 'https://prismic-laravel.cdn.prismic.io/api/v2',
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }
}
