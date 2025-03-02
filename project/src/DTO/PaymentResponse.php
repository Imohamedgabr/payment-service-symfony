<?php

namespace App\DTO;

use DateTimeInterface;

class PaymentResponse
{
    private string $transactionId;
    private DateTimeInterface $createdAt;
    private float $amount;
    private string $currency;
    private string $cardBin;
    private string $provider;
    private bool $success;
    private ?string $errorMessage;

    public function __construct(
        string $transactionId,
        DateTimeInterface $createdAt,
        float $amount,
        string $currency,
        string $cardBin,
        string $provider,
        bool $success = true,
        ?string $errorMessage = null
    ) {
        $this->transactionId = $transactionId;
        $this->createdAt = $createdAt;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->cardBin = $cardBin;
        $this->provider = $provider;
        $this->success = $success;
        $this->errorMessage = $errorMessage;
    }

    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getCardBin(): string
    {
        return $this->cardBin;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function toArray(): array
    {
        return [
            'transaction_id' => $this->transactionId,
            'created_at' => $this->createdAt->format(DateTimeInterface::ATOM),
            'amount' => $this->amount,
            'currency' => $this->currency,
            'card_bin' => $this->cardBin,
            'provider' => $this->provider,
            'success' => $this->success,
            'error_message' => $this->errorMessage,
        ];
    }
}