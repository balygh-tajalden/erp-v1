<?php

namespace App\Filament\Resources\SimpleEntries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Pixelworxio\FilamentAiAction\AiAction;
use App\Ai\Actions\SummarizeSimpleEntry;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use App\Services\AccountingService;
use App\DTOs\Accounting\SimpleEntryDTO;
use Illuminate\Database\Eloquent\Model;

class SimpleEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('TheNumber')
                    ->label('رقم السند')
                    ->searchable()
                    ->sortable()
                    ->extraAttributes(['class' => 'px-6']),

                TextColumn::make('ReferenceNumber')
                    ->label('رقم المرجع')
                    ->searchable()
                    ->sortable()
                    ->extraAttributes(['class' => 'px-6']),
                
                TextColumn::make('CreatedDate')
                    ->label('تاريخ الاضافة')
                    ->dateTime('Y-m-d h:i A')
                    ->sortable() 
                    ->extraAttributes(['class' => 'px-6']),

                TextColumn::make('fromAccount.AccountName')
                    ->label('من حساب')
                    ->extraAttributes(['class' => 'px-6'])
                    ->description(fn($record) => $record->debit_line?->notes),

                TextColumn::make('toAccount.AccountName')
                    ->label('الى حساب')
                    ->extraAttributes(['class' => 'px-6']) 
                    ->description(fn($record) => $record->credit_line?->notes),

                TextColumn::make('Amount')
                    ->label('المبلغ')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->extraAttributes(['class' => 'px-6']),

                TextColumn::make('currency.CurrencyName')
                    ->label('العملة')
                    ->sortable()
                    ->extraAttributes(['class' => 'px-6']),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make()
                ->iconButton(),
                EditAction::make()
                ->iconButton()
                ->using(function (Model $record, array $data): Model {
                    $data['documentTypeId'] = 4;
                    return app(AccountingService::class)->updateSimpleEntry($record, SimpleEntryDTO::fromArray($data));
                }),
                // AiAction::make('summarize')
                //     ->label('AI Summary')
                //     ->modalHeading('AI Entry Summary')
                //     ->agent(SummarizeSimpleEntry::class)
                //     ->usingProvider('groq', 'llama-3.3-70b-versatile')
                //     ->stream(),
                DeleteAction::make()
                ->iconButton()
                ->action(fn (Model $record) => app(AccountingService::class)->deleteSimpleEntry($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                 
                ]),
            ]);
    }
}
