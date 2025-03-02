<?php

namespace App\Service\Payment;

use App\DTO\PaymentRequest;
use App\DTO\PaymentResponse;

class FallbackPaymentService implements PaymentServiceInterface
{
    private PaymentServiceInterface $primaryService;
    private PaymentServiceInterface $fallbackService;

    public function __construct(
        PaymentServiceInterface $primaryService,
        PaymentServiceInterface $fallbackService
    ) {
        $this->primaryService = $primaryService;
        $this->fallbackService = $fallbackService;
    }

    public function processPayment(PaymentRequest $request): PaymentResponse
    {
        try {
            $response = $this->primaryService->processPayment($request);
            if ($response->isSuccess()) {
                return $response;
            }
        } catch (\Exception $e) {
            // Primary service failed, try fallback
        }

        return $this->fallbackService->processPayment($request);
    }
}