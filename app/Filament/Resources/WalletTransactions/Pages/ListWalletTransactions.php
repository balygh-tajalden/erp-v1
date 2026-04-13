<?php

namespace App\Filament\Resources\WalletTransactions\Pages;

use App\Filament\Resources\WalletTransactions\WalletTransactionResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use App\Models\WalletTransaction;
use App\Models\Currency;
use Livewire\Attributes\Url;
use App\Filament\Resources\WalletTransactions\Widgets\WalletBalanceWidget;
use Filament\Forms\Components\TextInput;
use App\Services\System\MoralisSyncService;
use Filament\Notifications\Notification;

class ListWalletTransactions extends ListRecords
{
    protected static string $resource = WalletTransactionResource::class;

    public bool $isUnlocked = false;

    #[Url]
    public string $address = '0x4337232444a07Cfe58aF6258509C350663946cB1';

    protected static ?string $title = 'USDT (BEP20) Transactions';

    public function mount(): void
    {
        parent::mount();
        $this->isUnlocked = session()->get('wallet_unlocked', false);
        
        if (!$this->isUnlocked) {
            $this->mountAction('unlock_wallet');
        }
    }

    protected function getHeaderWidgets(): array
    {
        return $this->isUnlocked ? [WalletBalanceWidget::class] : [];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }

    protected function getHeaderWidgetsData(): array
    {
        return ['address' => $this->address];
    }

    protected function getHeaderActions(): array
    {
        if (!$this->isUnlocked) {
            return [$this->getUnlockWalletAction()];
        }

        return [
            $this->getUnlockWalletAction()
                ->label('قفل الواجهة')
                ->icon('heroicon-o-lock-closed')
                ->color('gray')
                ->action(function () {
                    session()->forget('wallet_unlocked');
                    $this->isUnlocked = false;
                    $this->mountAction('unlock_wallet');
                }),

            Action::make('settings')
                ->label('Track Address')
                ->icon('heroicon-o-cog')
                ->schema([
                    TextInput::make('address')
                        ->label('Wallet Address')
                        ->default($this->address)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->address = $data['address'];
                    $this->dispatch('refreshWalletStats');
                }),

            Action::make('fetchMore')
                ->label('Fetch History (Older)')
                ->color('gray')
                ->icon('heroicon-o-arrow-path')
                ->action(fn(MoralisSyncService $service) => $this->handleSync($service, 'syncOlderTransactions')),
                
            Action::make('fetchTransactions')
                ->label('Sync New')
                ->color('warning')
                ->icon('heroicon-o-arrow-path')
                ->action(fn(MoralisSyncService $service) => $this->handleSync($service, 'syncNewTransactions')),
        ];
    }

    protected function handleSync(MoralisSyncService $service, string $method)
    {
        $result = $service->$method($this->address);
        
        Notification::make()
            ->title("تمت المزامنة بنجاح")
            ->body("تم العثور على {$result['count']} حركات جديدة.")
            ->success()
            ->send();

        $this->dispatch('refreshWalletStats');
    }

    protected function getUnlockWalletAction(): Action
    {
        return Action::make('unlock_wallet')
            ->label('فتح الواجهة')
            ->color('cyan')
            ->icon('heroicon-o-lock-open')
            ->modalHeading('الواجهة محمية - حركات المحفظة')
            ->modalDescription('يرجى إدخال الرمز السري للوصول')
            ->modalSubmitActionLabel('فتح الآن')
            ->schema([
                TextInput::make('code')
                    ->label('الرمز السري')
                    ->password()
                    ->required()
                    ->placeholder('••••')
                    ->extraInputAttributes(['maxlength' => 4, 'style' => 'text-align: center; font-size: 2rem; letter-spacing: 0.5rem;']),
            ])
            ->action(function (array $data) {
                if ($data['code'] === '7755') {
                    $this->isUnlocked = true;
                    session(['wallet_unlocked' => true]);
                    $this->dispatch('refreshWalletStats');
                } else {
                    Notification::make()
                        ->title('الرمز السري غير صحيح')
                        ->danger()
                        ->send();
                    
                    $this->mountAction('unlock_wallet');
                }
            })
            ->modalCloseButton(false)
            ->closeModalByClickingAway(false);
    }

    protected function getTableQuery(): ?\Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getTableQuery();
        return $this->isUnlocked ? $query : $query->whereRaw('1=0');
    }
}
