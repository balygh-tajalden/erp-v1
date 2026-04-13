<?php

namespace App\Filament\Resources\SimpleEntries\Pages;

use App\Filament\Resources\SimpleEntries\SimpleEntryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Services\AccountingService;
use App\DTOs\Accounting\SimpleEntryDTO;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Filament\Support\Exceptions\Halt;

class ListSimpleEntries extends ListRecords
{
    protected static string $resource = SimpleEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->using(function (array $data, string $model): Model {
                    try {
                        $data['documentTypeId'] = 4;
                        $dto = SimpleEntryDTO::fromArray($data);
                        return resolve(AccountingService::class)->createSimpleEntry($dto);
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('خطأ محاسبي')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();

                        throw new Halt();
                    }
                }),
        ];
    }
}
