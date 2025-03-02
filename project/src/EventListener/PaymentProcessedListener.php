<?php

namespace App\EventListener;

use App\Event\PaymentProcessedEvent;
use Psr\Log\LoggerInterface;

class PaymentProcessedListener
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onPaymentProcessed(PaymentProcessedEvent $event): void
    {
        $response = $event->getPaymentResponse();
        $context = [
            'transaction_id' => $response->getTransactionId(),
            'amount' => $response->getAmount(),
            'currency' => $response->getCurrency(),
            'provider' => $response->getProvider(),
        ];

        if ($response->isSuccess()) {
            $this->logger->info('Payment processed successfully', $context);
        } else {
            $context['error'] = $response->getErrorMessage();
            $this->logger->error('Payment processing failed', $context);
        }
    }
}