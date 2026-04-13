<?php

namespace App\Filament\Resources\JournalEntries\Pages;

use App\Filament\Resources\JournalEntries\JournalEntryResource;
use App\Services\AccountingService;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use App\DTOs\Accounting\JournalEntryDTO;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use App\Models\EntryDetail;

class EditJournalEntry extends EditRecord
{
    protected static string $resource = JournalEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['entryLines'] = $this->record->details
            ->map(fn($line) => [
                'account_id'      => $line->AccountID,
                'display_amount'  => abs($line->Amount),
                'type'            => $line->Amount > 0 ? 'debit' : 'credit',
                'currency_id'     => $line->CurrencyID,
                'exchange_rate'   => abs($line->Amount) > 0 ? round(abs($line->MCAmount) / abs($line->Amount), 4) : 1,
                'line_notes'      => $line->Notes,
                'account_number'  => $line->account?->AccountNumber,
                'amount'          => $line->Amount,
                'mc_amount'       => $line->MCAmount,
            ])->toArray();

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $dto = JournalEntryDTO::fromArray($data);

        return resolve(AccountingService::class)->updateJournalEntry($record, $dto);
    }
}
