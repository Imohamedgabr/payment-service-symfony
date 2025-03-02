<?php

namespace App\Service\Payment;

use App\DTO\PaymentRequest;
use App\DTO\PaymentResponse;

interface PaymentServiceInterface
{
    public function processPayment(PaymentRequest $request): PaymentResponse;
}