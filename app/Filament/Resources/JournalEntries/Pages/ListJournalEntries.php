<?php

namespace App\Filament\Resources\JournalEntries\Pages;

use App\Filament\Resources\JournalEntries\JournalEntryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use App\Services\AccountingService;
use App\DTOs\Accounting\JournalEntryDTO;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;

class ListJournalEntries extends ListRecords
{
    protected static string $resource = JournalEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->using(function (array $data): Model {
                    $lines = $data['entryLines'];

                    // 1. Validate line count
                    if (count($lines) < 2) {
                        Notification::make()
                            ->title('خطأ في الإدخال')
                            ->body('يجب أن يحتوي القيد على خطين على الأقل (مدين ودائن).')
                            ->danger()
                            ->send();
                        throw new Halt();
                    }

                    // 2. Validate entry balance
                    $accountingService = resolve(AccountingService::class);
                    $diff = $accountingService->calculateBalanceDiff($lines);

                    if (round($diff, 2) !== 0.00) {
                        Notification::make()
                            ->title('القيد غير متزن')
                            ->body('إجمالي المدين لا يساوي إجمالي الدائن. الفارق: ' . number_format(abs($diff), 2))
                            ->danger()
                            ->send();
                        throw new Halt();
                    }

                    try {
                        $data['documentTypeId'] = 5;
                        $dto = JournalEntryDTO::fromArray($data);
                        return $accountingService->createJournalEntry($dto);
                    } catch (\Exception $e) {
                        // Existing error handling
                        Notification::make()
                            ->title('خطأ محاسبي')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();

                        throw new Halt();
                    }
                })

        ];
    }
}
