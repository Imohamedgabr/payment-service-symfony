<?php

namespace App\DTO;

class PaymentRequest
{
    private float $amount;
    private string $currency;
    private string $cardNumber;
    private int $cardExpYear;
    private int $cardExpMonth;
    private string $cardCvv;

    public function __construct(
        float $amount,
        string $currency,
        string $cardNumber,
        int $cardExpYear,
        int $cardExpMonth,
        string $cardCvv
    ) {
        $this->amount = $amount;
        $this->currency = $currency;
        $this->cardNumber = $cardNumber;
        $this->cardExpYear = $cardExpYear;
        $this->cardExpMonth = $cardExpMonth;
        $this->cardCvv = $cardCvv;
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

    public function getCardBin(): string
    {
        // Extract first 6 digits as BIN
        return substr($this->cardNumber, 0, 6);
    }
}