<?php

namespace App\Service;

use Symfony\Component\Cache\Adapter\AdapterInterface;

class IdempotencyService
{
    private AdapterInterface $cache;

    public function __construct(AdapterInterface $cache)
    {
        $this->cache = $cache;
    }

    public function execute(string $key, callable $callback)
    {
        $cacheItem = $this->cache->getItem('idempotency_' . md5($key));

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $result = $callback();
        
        $cacheItem->set($result);
        $cacheItem->expiresAfter(3600); // 1 hour
        $this->cache->save($cacheItem);

        return $result;
    }
}