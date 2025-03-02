<?php

namespace App\Service\Payment;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class PaymentServiceFactory implements ServiceSubscriberInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getPaymentService(string $provider): PaymentServiceInterface
    {
        return match (strtolower($provider)) {
            'shift4' => $this->container->get(Shift4PaymentService::class),
            'aci' => $this->container->get(AciPaymentService::class),
            default => throw new InvalidArgumentException(sprintf('Unknown payment provider: %s', $provider)),
        };
    }

    public static function getSubscribedServices(): array
    {
        return [
            Shift4PaymentService::class => Shift4PaymentService::class,
            AciPaymentService::class => AciPaymentService::class,
        ];
    }
}