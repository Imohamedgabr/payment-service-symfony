<?php

namespace App\MessageHandler;

use App\DTO\PaymentRequest;
use App\Message\ProcessPaymentMessage;
use App\Service\Payment\PaymentServiceFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ProcessPaymentMessageHandler
{
    private PaymentServiceFactory $paymentServiceFactory;
    private LoggerInterface $logger;

    public function __construct(
        PaymentServiceFactory $paymentServiceFactory,
        LoggerInterface $logger
    ) {
        $this->paymentServiceFactory = $paymentServiceFactory;
        $this->logger = $logger;
    }

    public function __invoke(ProcessPaymentMessage $message)
    {
        $data = $message->getPaymentData();
        $paymentRequest = new PaymentRequest(
            (float) $data['amount'],
            $data['currency'],
            $data['card_number'],
            (int) $data['card_exp_year'],
            (int) $data['card_exp_month'],
            $data['card_cvv']
        );

        $paymentService = $this->paymentServiceFactory->getPaymentService($message->getProvider());
        $response = $paymentService->processPayment($paymentRequest);

        if ($response->isSuccess()) {
            $this->logger->info('Payment processed successfully', [
                'transaction_id' => $response->getTransactionId(),
                'provider' => $response->getProvider()
            ]);
        } else {
            $this->logger->error('Payment processing failed', [
                'error' => $response->getErrorMessage(),
                'provider' => $response->getProvider()
            ]);
        }

        return $response;
    }
}