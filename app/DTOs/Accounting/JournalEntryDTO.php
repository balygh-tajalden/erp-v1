<?php

namespace App\DTOs\Accounting;

class JournalEntryDTO
{
    public function __construct(
        public int $documentTypeId,
        public string $date,
        public string $notes,
        public int $branchId,
        public array $lines,
        public int $theNumber,
        public int $recordId = 0,
        public ?string $referenceNumber = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            documentTypeId: $data['documentTypeId'],
            date: $data['TheDate'],
            notes: $data['Notes'] ?? '',
            branchId: (int) ($data['BranchID']),
            lines: $data['entryLines'],
            theNumber: (int) ($data['TheNumber']),
            recordId: $data['recordId'] ?? 0,
            referenceNumber: $data['ReferenceNumber'] ?? '',
        );
    }
}
