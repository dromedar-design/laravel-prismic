<?php

namespace DromedarDesign\Prismic;

use Prismic\Cache\CacheInterface;
use \Illuminate\Support\Facades\Cache as LaravelCache;

class Cache implements CacheInterface
{
    public function has($key)
    {
        return LaravelCache::has($this->prefix($key));
    }

    public function get($key)
    {
        return LaravelCache::get($this->prefix($key));
    }

    public function set($key, $value, $ttl = 0)
    {
        if ($ttl == 0) {
            return LaravelCache::forever($this->prefix($key), $value);
        } else {
            return LaravelCache::add($this->prefix($key), $value, $ttl);
        }
    }

    public function delete($key)
    {
        return LaravelCache::forget($this->prefix($key));
    }

    public function clear()
    {
        return LaravelCache::flush(config('database.connections.prismic.cache.prefix'));
    }

    protected function prefix($key)
    {
        return config('database.connections.prismic.cache.prefix') . '.' . $key;
    }
}
