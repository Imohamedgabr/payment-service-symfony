<?php

namespace App\Command;

use App\DTO\PaymentRequest;
use App\Service\Payment\PaymentServiceFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:example',
    description: 'Process a payment using a specified provider (aci or shift4)',
)]
class ProcessPaymentCommand extends Command
{
    private PaymentServiceFactory $paymentServiceFactory;

    public function __construct(PaymentServiceFactory $paymentServiceFactory)
    {
        parent::__construct();
        $this->paymentServiceFactory = $paymentServiceFactory;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('provider', InputArgument::REQUIRED, 'Payment provider (aci or shift4)')
            ->addArgument('amount', InputArgument::REQUIRED, 'Payment amount')
            ->addArgument('currency', InputArgument::REQUIRED, 'Payment currency')
            ->addArgument('card_number', InputArgument::REQUIRED, 'Card number')
            ->addArgument('card_exp_year', InputArgument::REQUIRED, 'Card expiration year')
            ->addArgument('card_exp_month', InputArgument::REQUIRED, 'Card expiration month')
            ->addArgument('card_cvv', InputArgument::REQUIRED, 'Card CVV code')
            ->setHelp(<<<'EOF'
This command processes a payment using the specified provider (aci or shift4).

Example usage:
    bin/console app:example shift4 100.00 USD 4111111111111111 2025 12 123
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $provider = strtolower($input->getArgument('provider'));
        if (!in_array($provider, ['aci', 'shift4'])) {
            $io->error('Invalid provider. Must be "aci" or "shift4".');
            return Command::FAILURE;
        }

        try {
            $paymentRequest = new PaymentRequest(
                (float) $input->getArgument('amount'),
                $input->getArgument('currency'),
                $input->getArgument('card_number'),
                (int) $input->getArgument('card_exp_year'),
                (int) $input->getArgument('card_exp_month'),
                $input->getArgument('card_cvv')
            );

            $paymentService = $this->paymentServiceFactory->getPaymentService($provider);
            $response = $paymentService->processPayment($paymentRequest);

            if ($response->isSuccess()) {
                $io->success('Payment processed successfully');
                $io->table(
                    ['Field', 'Value'],
                    [
                        ['Transaction ID', $response->getTransactionId()],
                        ['Created At', $response->getCreatedAt()->format('Y-m-d H:i:s')],
                        ['Amount', $response->getAmount()],
                        ['Currency', $response->getCurrency()],
                        ['Card BIN', $response->getCardBin()],
                        ['Provider', $response->getProvider()],
                    ]
                );
                return Command::SUCCESS;
            } else {
                $io->error(sprintf('Payment failed: %s', $response->getErrorMessage()));
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $io->error(sprintf('An error occurred: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }
}