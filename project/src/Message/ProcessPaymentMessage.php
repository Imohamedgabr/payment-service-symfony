<?php

namespace App\Message;

class ProcessPaymentMessage
{
    private string $provider;
    private array $paymentData;

    public function __construct(string $provider, array $paymentData)
    {
        $this->provider = $provider;
        $this->paymentData = $paymentData;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function getPaymentData(): array
    {
        return $this->paymentData;
    }
}