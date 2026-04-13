<?php

namespace App\DTOs\Accounting;

class WalletTransactionDTO
{
    public function __construct(
        public int $accountId,
        public int $fundAccountId,
        public float $amount,
        public int $currencyId,
        public string $notes,
        public string $date,
        public int $branchId,
        public ?int $documentTypeId = 13,
        public ?string $transactionHash = null,
        public ?int $sessionId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            accountId: (int) $data['AccountID'],
            fundAccountId: (int) $data['FundAccountID'],
            amount: (float) $data['Amount'],
            currencyId: (int) ($data['CurrencyID']),
            notes: (string) $data['Notes'],
            date: (string) $data['Date'],
            branchId: (int) ($data['BranchID']),
            documentTypeId: (int) ($data['DocumentTypeID'] ?? 13),
            transactionHash: (string) ($data['TransactionHash'] ?? ''),
            sessionId: (int) ($data['SessionID']),
        );
    }
}
