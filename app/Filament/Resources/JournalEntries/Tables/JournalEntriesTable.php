<?php

namespace App\Filament\Resources\JournalEntries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class JournalEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('RecordNumber')->label('رقم القيد')->searchable(),
                TextColumn::make('TheDate')->label('التاريخ')->date()->sortable(),
                TextColumn::make('branch.BranchName')->label('الفرع'),
                TextColumn::make('Notes')->label('البيان')->limit(50),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(), // ← هذا الزر سيفتح صفحة التفاصيل (Infolist)
                EditAction::make()
                    ->mutateRecordDataUsing(function (array $data, Model $record): array {
                        $data['entryLines'] = $record->details
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
                    })
                    ->using(function (Model $record, array $data): Model {
                        $data['documentTypeId'] = 5;
                        $dto = \App\DTOs\Accounting\JournalEntryDTO::fromArray($data);
                        return resolve(\App\Services\AccountingService::class)->updateJournalEntry($record, $dto);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
