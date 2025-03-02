<?php

namespace App\CommandHandler;

use App\Command\Bus\ProcessPaymentCommand;
use App\DTO\PaymentRequest;
use App\Service\Payment\PaymentServiceFactory;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ProcessPaymentCommandHandler
{
    private PaymentServiceFactory $paymentServiceFactory;

    public function __construct(PaymentServiceFactory $paymentServiceFactory)
    {
        $this->paymentServiceFactory = $paymentServiceFactory;
    }

    public function __invoke(ProcessPaymentCommand $command)
    {
        $paymentRequest = new PaymentRequest(
            $command->getAmount(),
            $command->getCurrency(),
            $command->getCardNumber(),
            $command->getCardExpYear(),
            $command->getCardExpMonth(),
            $command->getCardCvv()
        );

        $paymentService = $this->paymentServiceFactory->getPaymentService($command->getProvider());
        return $paymentService->processPayment($paymentRequest);
    }
}