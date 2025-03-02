<?php

namespace App\Tests\Service\Payment;

use App\Service\Payment\AciPaymentService;
use App\Service\Payment\PaymentServiceFactory;
use App\Service\Payment\Shift4PaymentService;
use App\Service\Retry\RetryService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PaymentServiceFactoryTest extends TestCase
{
    private MockContainer $container;
    private PaymentServiceFactory $factory;
    private Shift4PaymentService $shift4Service;
    private AciPaymentService $aciService;
    private RetryService $retryService;

    protected function setUp(): void
    {
        // Use the custom MockContainer
        $this->container = new MockContainer();

        // Mock the RetryService
        $this->retryService = $this->createMock(RetryService::class);

        // Mock the HTTP client and parameter bag
        $httpClient = $this->createMock(HttpClientInterface::class);
        $parameterBag = $this->createMock(ParameterBagInterface::class);
        $parameterBag->method('get')
            ->willReturnMap([
                ['app.payment.shift4.api_key', 'dummy_shift4_api_key'],
                ['app.payment.aci.api_key', 'dummy_aci_api_key'],
                ['app.payment.aci.entity_id', 'dummy_aci_entity_id'],
            ]);

        // Create instances of the payment services with mocked dependencies
        $this->shift4Service = new Shift4PaymentService($httpClient, $parameterBag, $this->retryService);
        $this->aciService = new AciPaymentService($httpClient, $parameterBag, $this->retryService);

        // Register the services in the container
        $this->container->set(Shift4PaymentService::class, $this->shift4Service);
        $this->container->set(AciPaymentService::class, $this->aciService);

        // Initialize the factory
        $this->factory = new PaymentServiceFactory($this->container);
    }

    public function testGetShift4PaymentService(): void
    {
        // Call the factory method
        $service = $this->factory->getPaymentService('shift4');

        // Assert that the returned service is an instance of Shift4PaymentService
        $this->assertInstanceOf(Shift4PaymentService::class, $service);
    }

    public function testGetAciPaymentService(): void
    {
        // Call the factory method
        $service = $this->factory->getPaymentService('aci');

        // Assert that the returned service is an instance of AciPaymentService
        $this->assertInstanceOf(AciPaymentService::class, $service);
    }

    public function testGetUnknownPaymentService(): void
    {
        // Expect an InvalidArgumentException when an unknown provider is requested
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown payment provider: unknown');

        // Call the factory method with an unknown provider
        $this->factory->getPaymentService('unknown');
    }

    public function testGetSubscribedServices(): void
    {
        // Get the subscribed services from the factory
        $subscribedServices = PaymentServiceFactory::getSubscribedServices();

        // Assert that the result is an array
        $this->assertIsArray($subscribedServices);

        // Assert that the array contains exactly 2 services
        $this->assertCount(2, $subscribedServices);

        // Assert that the array contains the expected service classes
        $this->assertContains(Shift4PaymentService::class, $subscribedServices);
        $this->assertContains(AciPaymentService::class, $subscribedServices);
    }
}