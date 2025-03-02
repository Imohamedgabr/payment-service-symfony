<?php

namespace App\Service\Payment;

use App\DTO\PaymentRequest;
use App\DTO\PaymentResponse;
use App\Service\Retry\RetryService;
use DateTime;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AciPaymentService implements PaymentServiceInterface
{
    private const PROVIDER_NAME = 'aci';
    private string $entityId;
    private string $apiKey;
    private HttpClientInterface $httpClient;
    private RetryService $retryService;

    public function __construct(
        HttpClientInterface $httpClient,
        ParameterBagInterface $parameterBag,
        RetryService $retryService
    ) {
        $this->httpClient = $httpClient;
        $this->apiKey = $parameterBag->get('app.payment.aci.api_key');
        $this->entityId = $parameterBag->get('app.payment.aci.entity_id');
        $this->retryService = $retryService;
    }

    public function processPayment(PaymentRequest $request): PaymentResponse
    {
        return $this->retryService->execute(function () use ($request) {
            try {
                $response = $this->httpClient->request('POST', 'https://test.oppwa.com/v1/payments', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Content-Type' => 'application/x-www-form-urlencoded',
                    ],
                    'body' => [
                        'entityId' => $this->entityId,
                        'amount' => $request->getAmount(),
                        'currency' => $request->getCurrency(),
                        'paymentBrand' => 'VISA',
                        'paymentType' => 'DB',
                        'card.number' => $request->getCardNumber(),
                        'card.expiryMonth' => $request->getCardExpMonth(),
                        'card.expiryYear' => $request->getCardExpYear(),
                        'card.cvv' => $request->getCardCvv(),
                    ],
                ]);

                $data = json_decode($response->getContent(), true);

                if ($data['result']['code'] !== '000.100.110') {
                    throw new Exception($data['result']['description'] ?? 'Unknown error from ACI');
                }

                return new PaymentResponse(
                    $data['id'],
                    new DateTime(),
                    $request->getAmount(),
                    $request->getCurrency(),
                    $request->getCardBin(),
                    self::PROVIDER_NAME
                );
            } catch (Exception $e) {
                return new PaymentResponse(
                    '',
                    new DateTime(),
                    $request->getAmount(),
                    $request->getCurrency(),
                    $request->getCardBin(),
                    self::PROVIDER_NAME,
                    false,
                    $e->getMessage()
                );
            }
        });
    }
}