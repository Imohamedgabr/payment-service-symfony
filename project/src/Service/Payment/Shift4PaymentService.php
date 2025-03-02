<?php

namespace App\Service\Payment;

use App\DTO\PaymentRequest;
use App\DTO\PaymentResponse;
use App\Service\Retry\RetryService;
use DateTime;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Shift4PaymentService implements PaymentServiceInterface
{
    private const PROVIDER_NAME = 'shift4';
    private string $apiKey;
    private HttpClientInterface $httpClient;
    private RetryService $retryService;

    public function __construct(
        HttpClientInterface $httpClient,
        ParameterBagInterface $parameterBag,
        RetryService $retryService
    ) {
        $this->httpClient = $httpClient;
        $this->apiKey = $parameterBag->get('app.payment.shift4.api_key');
        $this->retryService = $retryService;
    }

    public function processPayment(PaymentRequest $request): PaymentResponse
    {
        return $this->retryService->execute(function () use ($request) {
            try {
                $response = $this->httpClient->request('POST', 'https://api.shift4.com/charges', [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode($this->apiKey . ':'),
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'amount' => $request->getAmount() * 100,
                        'currency' => $request->getCurrency(),
                        'card' => [
                            'number' => $request->getCardNumber(),
                            'exp_month' => $request->getCardExpMonth(),
                            'exp_year' => $request->getCardExpYear(),
                            'cvc' => $request->getCardCvv(),
                        ],
                        'metadata' => [
                            'provider' => self::PROVIDER_NAME,
                        ],
                    ],
                ]);

                $data = json_decode($response->getContent(), true);

                if ($response->getStatusCode() >= 400) {
                    throw new Exception($data['error']['message'] ?? 'Unknown error from Shift4');
                }

                return new PaymentResponse(
                    $data['id'],
                    new DateTime('@' . $data['created']),
                    $data['amount'] / 100,
                    $data['currency'],
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