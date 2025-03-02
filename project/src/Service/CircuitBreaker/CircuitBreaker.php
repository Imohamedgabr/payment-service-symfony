<?php

namespace App\Service\CircuitBreaker;

class CircuitBreaker
{
    private const STATE_CLOSED = 'closed';
    private const STATE_OPEN = 'open';
    private const STATE_HALF_OPEN = 'half_open';

    private string $state = self::STATE_CLOSED;
    private int $failures = 0;
    private int $threshold;
    private int $timeout;
    private ?int $lastFailureTime = null;

    public function __construct(int $threshold = 5, int $timeout = 30)
    {
        $this->threshold = $threshold;
        $this->timeout = $timeout;
    }

    public function execute(callable $callback): mixed
    {
        $this->checkState();

        try {
            $result = $callback();
            $this->success();
            return $result;
        } catch (\Exception $e) {
            $this->failure();
            throw $e;
        }
    }

    private function checkState(): void
    {
        if ($this->state === self::STATE_OPEN) {
            if (time() - $this->lastFailureTime > $this->timeout) {
                $this->state = self::STATE_HALF_OPEN;
            } else {
                throw new \RuntimeException('Circuit breaker is open');
            }
        }
    }

    private function success(): void
    {
        $this->failures = 0;
        $this->state = self::STATE_CLOSED;
    }

    private function failure(): void
    {
        $this->failures++;
        $this->lastFailureTime = time();

        if ($this->state === self::STATE_HALF_OPEN || $this->failures >= $this->threshold) {
            $this->state = self::STATE_OPEN;
        }
    }
}