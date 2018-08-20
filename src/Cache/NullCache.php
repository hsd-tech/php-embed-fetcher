<?php declare(strict_types=1);

namespace Hsdt\EmbedFetcher\Cache;

use Psr\SimpleCache\CacheInterface;

class NullCache implements CacheInterface
{
    public function get($key, $default = null)
    {
        return $default;
    }

    public function set($key, $value, $ttl = null)
    {
        // noop
    }

    public function delete($key)
    {
        // noop
    }

    public function clear()
    {
        // noop
    }

    public function getMultiple($keys, $default = null)
    {
        return array_combine($keys, array_fill(0, count($keys), $default));
    }

    public function setMultiple($values, $ttl = null)
    {
        // noop
    }

    public function deleteMultiple($keys)
    {
        // noop
    }

    public function has($key)
    {
        return false;
    }
}
