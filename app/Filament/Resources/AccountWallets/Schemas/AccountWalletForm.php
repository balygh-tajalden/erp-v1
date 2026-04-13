<?php

namespace App\Filament\Resources\AccountWallets\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use App\Models\Account;

class AccountWalletForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات المحفظة')
                    ->schema([
                        Select::make('AccountID')
                            ->label('الحساب')
                            ->relationship('account', 'AccountName')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('WalletAddress')
                            ->label('عنوان المحفظة')
                            ->required()
                            ->unique('tblAccountWallets', 'WalletAddress', ignorable: fn ($record) => $record)
                            ->placeholder('مثلاً: TR7NHqfZSS28e...'),

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
                            ->label('مسمى المحفظة')
                            ->placeholder('مثلاً: Binance, OKX...'),

                        Toggle::make('IsActive')
                            ->label('نشطة')
                            ->default(true),

                        Textarea::make('Notes')
                            ->label('ملاحظات')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2)->columnSpanFull(),
            ]);
    }
}
