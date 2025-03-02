<?php

namespace App\Controller;

use App\DTO\PaymentRequest;
use App\Service\Payment\PaymentServiceFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PaymentController extends AbstractController
{
    private PaymentServiceFactory $paymentServiceFactory;
    private ValidatorInterface $validator;

    public function __construct(
        PaymentServiceFactory $paymentServiceFactory,
        ValidatorInterface $validator
    ) {
        $this->paymentServiceFactory = $paymentServiceFactory;
        $this->validator = $validator;
    }

    /**
     * Process a payment using the specified provider
     *
     * @Route("/app/example/{provider}", name="app_payment_process", methods={"POST"})
     */
    public function processPayment(Request $request, string $provider): JsonResponse
    {
        // Validate provider parameter
        $providerConstraint = new Assert\Choice(['aci', 'shift4']);
        $providerErrors = $this->validator->validate($provider, $providerConstraint);

        if (count($providerErrors) > 0) {
            return $this->json([
                'error' => 'Invalid payment provider. Use "aci" or "shift4".',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Get request data
        $data = json_decode($request->getContent(), true);

        // Validate request data
        $constraints = new Assert\Collection([
            'amount' => [new Assert\NotBlank(), new Assert\Type('numeric'), new Assert\Positive()],
            'currency' => [new Assert\NotBlank(), new Assert\Currency()],
            'card_number' => [new Assert\NotBlank(), new Assert\Regex('/^\d{13,19}$/')],
            'card_exp_year' => [new Assert\NotBlank(), new Assert\Type('integer'), new Assert\Range(['min' => (int)date('Y'), 'max' => (int)date('Y') + 20])],
            'card_exp_month' => [new Assert\NotBlank(), new Assert\Type('integer'), new Assert\Range(['min' => 1, 'max' => 12])],
            'card_cvv' => [new Assert\NotBlank(), new Assert\Regex('/^\d{3,4}$/')],
        ]);

        $errors = $this->validator->validate($data, $constraints);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        // Create payment request
        $paymentRequest = new PaymentRequest(
            (float) $data['amount'],
            $data['currency'],
            $data['card_number'],
            (int) $data['card_exp_year'],
            (int) $data['card_exp_month'],
            $data['card_cvv']
        );

        // Get the appropriate payment service and process payment
        $paymentService = $this->paymentServiceFactory->getPaymentService($provider);
        $response = $paymentService->processPayment($paymentRequest);

        // Return unified response
        return $this->json($response->toArray(), $response->isSuccess() ? Response::HTTP_OK : Response::HTTP_PAYMENT_REQUIRED);
    }
}