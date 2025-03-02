<?php

namespace App\Event;

use App\DTO\PaymentResponse;

class PaymentProcessedEvent
{
    private PaymentResponse $paymentResponse;

    public function __construct(PaymentResponse $paymentResponse)
    {
        $this->paymentResponse = $paymentResponse;
    }

    public function getPaymentResponse(): PaymentResponse
    {
        return $this->paymentResponse;
    }
}