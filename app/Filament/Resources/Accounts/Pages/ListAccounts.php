<?php

namespace App\Filament\Resources\Accounts\Pages;

use App\Filament\Resources\Accounts\AccountResource;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use App\Models\Account;
use App\Models\AccountWallet;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;
use App\Models\AccountWhatsAppConfig;

class ListAccounts extends ListRecords
{
    protected static string $resource = AccountResource::class;

    protected static ?string $title = 'الحسابات';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('add_wallet')
                ->label('ربط حساب بمحفظة')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->schema([
                    Select::make('AccountID')
                        ->label('الحساب')
                        ->options(Account::where('AccountTypeID', 2)->pluck('AccountName', 'ID'))
                        ->searchable()
                        ->required(),
                    TextInput::make('WalletAddress')
                        ->label('عنوان المحفظة')
                        ->required()
                        ->unique('tblAccountWallets', 'WalletAddress'),
                    Select::make('Network')
                        ->label('الشبكة')
                        ->options([
                            'USDT-BEP20' => 'USDT (BEP20)',
                            'USDT-ERC20' => 'USDT (ERC20)',
                            'USDT-TRC20' => 'USDT (TRC20)',
                            'BTC' => 'Bitcoin',
                            'ETH' => 'Ethereum',
                        ])
                        ->default('USDT-BEP20')
                        ->required(),
                    TextInput::make('Label')
                        ->label('مسمى المحفظة'),
                    Toggle::make('IsActive')
                        ->label('نشطة')
                        ->default(true),
                ])
                ->action(function (array $data) {
                    AccountWallet::create($data);
                }),
            CreateAction::make()
                ->label('إضافة حساب')
                ->modalHeading('إضافة حساب جديد')
                ->modalWidth(Width::FitContent)
                ->createAnother(false),
        ];
    }

    protected function afterCreate(): void
    {
        $phone = $this->form->getState()['whatsapp_phone'] ?? null;

        if ($phone) {
            AccountWhatsAppConfig::create([
                'AccountID' => $this->record->ID,
                'IsActive'  => true,
                'Settings'  => [
                    'numbers' => [$phone],
                    'events'  => ['receipt', 'payment', 'simple_entry', 'currency_buy', 'currency_sell'],
                ],
            ]);
        }
    }
}
