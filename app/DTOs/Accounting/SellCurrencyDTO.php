<?php

namespace App\DTOs\Accounting;

class SellCurrencyDTO
{
    public function __construct(
        public int $documentTypeId,
        public string $date,
        public int $theNumber,
        public int $accountId,
        public int $fundAccountId,
        public int $currencyId,
        public int $exchangeCurrencyId,
        public float $amount,
        public float $price,
        public float $exchangeAmount,
        public int $purchaseMethod,
        public ?string $notes = null,
        public int $branchId = 2,
        public ?string $referenceNumber = null,
        public ?int $rowVersion = null // <-- Added RowVersion
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            purchaseMethod: $data['PurchaseMethod'],
            documentTypeId: 8,
            date: $data['TheDate'],
            theNumber: (int) $data['Thenumber'],
            accountId: (int) $data['AccountID'],
            fundAccountId: (int) $data['FundAccountID'],
            currencyId: (int) $data['CurrencyID'],
            exchangeCurrencyId: (int) $data['ExchangeCurrencyID'],
            amount: (float) $data['Amount'],
            price: (float) $data['Price'],
            exchangeAmount: (float) $data['ExchangeAmount'],
            notes: $data['Notes'] ?? '',
            branchId: 2,
            referenceNumber: $data['ReferenceNumber'] ?? null,
            rowVersion: isset($data['RowVersion']) ? (int) $data['RowVersion'] : null
        );
    }
}
