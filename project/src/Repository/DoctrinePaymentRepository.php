<?php

namespace App\Repository;

use App\DTO\PaymentResponse;
use Doctrine\DBAL\Connection;

class DoctrinePaymentRepository implements PaymentRepositoryInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function save(PaymentResponse $response): void
    {
        $this->connection->insert('payments', [
            'transaction_id' => $response->getTransactionId(),
            'created_at' => $response->getCreatedAt()->format('Y-m-d H:i:s'),
            'amount' => $response->getAmount(),
            'currency' => $response->getCurrency(),
            'card_bin' => $response->getCardBin(),
            'provider' => $response->getProvider(),
            'success' => $response->isSuccess() ? 1 : 0,
            'error_message' => $response->getErrorMessage(),
        ]);
    }

    public function findByTransactionId(string $transactionId): ?PaymentResponse
    {
        $data = $this->connection->fetchAssociative(
            'SELECT * FROM payments WHERE transaction_id = ?',
            [$transactionId]
        );

        if (!$data) {
            return null;
        }

        return new PaymentResponse(
            $data['transaction_id'],
            new \DateTime($data['created_at']),
            (float) $data['amount'],
            $data['currency'],
            $data['card_bin'],
            $data['provider'],
            (bool) $data['success'],
            $data['error_message']
        );
    }
}