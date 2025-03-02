<?php

namespace App\Repository;

use App\DTO\PaymentResponse;

interface PaymentRepositoryInterface
{
    public function save(PaymentResponse $response): void;
    public function findByTransactionId(string $transactionId): ?PaymentResponse;
}