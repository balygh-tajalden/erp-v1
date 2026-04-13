<?php

namespace App\Filament\Resources\JournalEntries\Pages;

use App\Filament\Resources\JournalEntries\JournalEntryResource;
use App\Services\AccountingService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use App\DTOs\Accounting\JournalEntryDTO;

class CreateJournalEntry extends CreateRecord
{
    protected static string $resource = JournalEntryResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $dto = JournalEntryDTO::fromArray($data);

        return resolve(AccountingService::class)->createJournalEntry($dto);
    }
}
