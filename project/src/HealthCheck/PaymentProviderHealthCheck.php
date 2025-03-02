<?php

namespace App\Service\Payment\HealthCheck;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class PaymentProviderHealthCheck
{
    private HttpClientInterface $httpClient;
    private array $providers;

    public function __construct(HttpClientInterface $httpClient, array $providers)
    {
        $this->httpClient = $httpClient;
        $this->providers = $providers;
    }

    public function checkAll(): array
    {
        $results = [];
        foreach ($this->providers as $name => $url) {
            $results[$name] = $this->check($url);
        }
        return $results;
    }

    private function check(string $url): bool
    {
        try {
            $response = $this->httpClient->request('GET', $url);
            return $response->getStatusCode() < 500;
        } catch (\Exception $e) {
            return false;
        }
    }
}