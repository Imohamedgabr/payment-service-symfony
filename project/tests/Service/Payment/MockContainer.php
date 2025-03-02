<?php

namespace App\Tests\Service\Payment;

use Psr\Container\ContainerInterface;

class MockContainer implements ContainerInterface
{
    private $services = [];

    public function set(string $id, object $service): void
    {
        $this->services[$id] = $service;
    }

    public function get(string $id): mixed
    {
        if (!$this->has($id)) {
            throw new \Exception("Service not found: $id");
        }
        return $this->services[$id];
    }

    public function has(string $id): bool
    {
        return isset($this->services[$id]);
    }
}