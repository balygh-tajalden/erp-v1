<?php

namespace App\Filament\Resources\SimpleEntries\Pages;

use App\Filament\Resources\SimpleEntries\SimpleEntryResource;
use Filament\Resources\Pages\CreateRecord;
use App\Services\AccountingService;
use Illuminate\Database\Eloquent\Model;
use App\DTOs\Accounting\SimpleEntryDTO;

class CreateSimpleEntry extends CreateRecord
{

    protected static string $resource = SimpleEntryResource::class;

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->submit(null)
            ->action(function (AccountingService $service) {
                $data = $this->form->getState();
                
                if ($service->isSimpleEntryDuplicate($data)) {
                    $this->create();
                } else {
                    $this->create();
                }
            })
            ->requiresConfirmation(function (AccountingService $service) {
                return $service->isSimpleEntryDuplicate($this->form->getState());
            })
            ->modalHeading('تنبيه: قيد مكرر')
            ->modalDescription('تم اكتشاف قيد مكرر بنفس التاريخ والمبلغ والحساب الأول. هل تريد المتابعة؟')
            ->modalSubmitActionLabel('نعم، أكمل الحفظ')
            ->modalCancelActionLabel('إلغاء')
            ->color('primary');
    }

    protected function handleRecordCreation(array $data): Model
    {
        $dto = SimpleEntryDTO::fromArray($data);
        return app(AccountingService::class)->createSimpleEntry($dto);
    }
}
