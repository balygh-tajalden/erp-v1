<?php

namespace App\DTOs\Accounting;

class SimpleEntryDTO
{
    public function __construct(
        public int $documentTypeId,
        public string $date,
        public string $notes,
        public int $toAccountId,
        public int $fromAccountId,
        public float $amount,
        public int $currencyId,
        public int $branchId,
        public int $theNumber,
        public ?string $referenceNumber = null,
        public bool $confirmedDuplicate = false,
        public ?int $rowVersion = null // <-- Added RowVersion
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            documentTypeId: 4,
            date: $data['TheDate'],
            notes: $data['Notes'] ?? '',
            toAccountId: $data['ToAccountID'], // إلى حساب = الطرف المدين (الآخذ)
            fromAccountId: $data['FromAccountID'], // من حساب = الطرف الدائن (العاطي)
            amount: (float) ($data['Amount']),
            currencyId: $data['CurrencyID'],
            branchId: $data['BranchID'],
            theNumber: (int) $data['TheNumber'],
            referenceNumber: (!empty($data['ReferenceNumber'])) ? $data['ReferenceNumber'] : null,
            confirmedDuplicate: $data['confirmedDuplicate'] ?? true,
            rowVersion: isset($data['RowVersion']) ? (int) $data['RowVersion'] : null
        );
    }
}
