<?php

namespace App\Command\Bus;

class ProcessPaymentCommand
{
    private string $provider;
    private float $amount;
    private string $currency;
    private string $cardNumber;
    private int $cardExpYear;
    private int $cardExpMonth;
    private string $cardCvv;

    public function __construct(
        string $provider,
        float $amount,
        string $currency,
        string $cardNumber,
        int $cardExpYear,
        int $cardExpMonth,
        string $cardCvv
    ) {
        $this->provider = $provider;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->cardNumber = $cardNumber;
        $this->cardExpYear = $cardExpYear;
        $this->cardExpMonth = $cardExpMonth;
        $this->cardCvv = $cardCvv;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getCardNumber(): string
    {
        return $this->cardNumber;
    }

    public function getCardExpYear(): int
    {
        return $this->cardExpYear;
    }

    public function getCardExpMonth(): int
    {
        return $this->cardExpMonth;
    }

    public function getCardCvv(): string
    {
        return $this->cardCvv;
    }
}