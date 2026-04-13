<?php

namespace App\Filament\Resources\Accounts\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;



class WalletsRelationManager extends RelationManager
{
    protected static string $relationship = 'wallets';

    protected static ?string $title = 'محافظ العملات الرقمية';

    protected static ?string $modelLabel = 'محفظة';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('WalletAddress')
            ->columns([
                TextColumn::make('WalletAddress')
                    ->label('العنوان')
                    ->copyable()
                    ->fontFamily('mono')
                    ->searchable(),
                
                TextColumn::make('Network')
                    ->label('الشبكة')
                    ->badge(),

                TextColumn::make('Label')
                    ->label('المسمى'),

                IconColumn::make('IsActive')
                    ->label('الحالة')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->toolbarActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
