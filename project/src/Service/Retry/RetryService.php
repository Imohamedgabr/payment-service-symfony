<?php

namespace App\Service\Retry;

class RetryService
{
    public function execute(callable $callback, int $maxRetries = 3, int $delay = 1000): mixed
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $maxRetries) {
            try {
                return $callback();
            } catch (\Exception $e) {
                $lastException = $e;
                $attempt++;
                
                if ($attempt < $maxRetries) {
                    // Exponential backoff
                    usleep($delay * pow(2, $attempt - 1));
                }
            }
        }

        throw $lastException;
    }
}