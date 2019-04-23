<?php

namespace DromedarDesign\Prismic\Providers;

use DromedarDesign\Prismic\Cache;
use DromedarDesign\Prismic\Connection;
use DromedarDesign\Prismic\Connectors\Connector;
use Illuminate\Database\Connection as LaravelConnection;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Prismic\Api;

class ServiceProvider extends LaravelServiceProvider
{
    public function register()
    {
        $this->app->bind('dd.prismic.cache', Cache::class);

        $this->app->bind('db.connector.prismic', Connector::class);

        LaravelConnection::resolverFor('prismic', function ($connection, $database, $prefix, $config) {
            return new Connection($connection, $database, $prefix, $config);
        });
    }

    public function boot()
    {
        $this->app->singleton(Api::class, function () {
            return Api::get(
                config('database.connections.prismic.database'),
                config('database.connections.prismic.access_token', ''),
                null,
                config('database.connections.prismic.cache') ? resolve('dd.prismic.cache') : null,
                config('database.connections.prismic.cache.ttl', 5)
            );
        });
    }
}
