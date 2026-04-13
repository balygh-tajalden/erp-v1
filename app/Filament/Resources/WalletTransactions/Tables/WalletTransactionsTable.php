<?php

namespace App\Filament\Resources\WalletTransactions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Actions\BulkAction;
use Filament\Actions\Action;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Forms;
use App\Models\Account;
use App\Models\AccountWallet;
use App\DTOs\Accounting\WalletTransactionDTO;
use App\Actions\Accounting\WalletTransactions\PostWalletTransactionAction;
use App\Filament\Resources\BaseResource;
use Illuminate\Support\Facades\Auth;

class WalletTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('BlockTimestamp', 'desc')
            ->columns([
                TextColumn::make('IsIngoing')
                    ->label('Type')
                    ->badge()
                    ->colors([
                        'success' => true,
                        'warning' => false,
                    ])
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Received' : 'Sent'),
                TextColumn::make('ToAddress')
                    ->label('Receiver')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('تم نسخ العنوان')
                    ->fontFamily('mono'),
                TextColumn::make('BlockTimestamp')
                    ->label('Date')
                    ->dateTime('Y-m-d h:i:s A')
                    ->sortable(),
                TextColumn::make('currency.CurrencyName')
                    ->label('Currency')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('Amount')
                    ->label('Amount')
                    ->sortable()
                    ->colors([
                        'success' => fn($state, $record) => $record->IsIngoing,
                        'danger' => fn($state, $record) => !$record->IsIngoing,
                    ])
                    ->formatStateUsing(function ($record) {
                        return ($record->IsIngoing ? '+' : '-') . ((float) $record->Amount);
                    }),
                TextColumn::make('IsPosted')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => true,
                        'gray' => false,
                    ])
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Posted' : 'Pending'),
                TextColumn::make('account.AccountName')
                    ->label('Account')
                    ->placeholder('-'),
            ])
            ->filters([
                TernaryFilter::make('IsPosted')
                    ->label('Posting Status'),
                SelectFilter::make('IsIngoing')
                    ->label('Type')
                    ->options([
                        '1' => 'Received',
                        '0' => 'Sent',
                    ]),
                Filter::make('BlockTimestamp')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('to'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('BlockTimestamp', '>=', $data['from']))
                            ->when($data['to'], fn($q) => $q->whereDate('BlockTimestamp', '<=', $data['to']));
                    })
            ])
            ->recordActions([
                Action::make('post')
                    ->label('')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->hidden(fn($record) => $record->IsPosted)
                    ->mountUsing(function ($form, $record) {
                        // محاولة التعرف التلقائي على الحساب
                        $address = $record->IsIngoing ? $record->FromAddress : $record->ToAddress;
                        $detectedAccount = AccountWallet::where('WalletAddress', $address)->value('AccountID');

                        // جلب رصيد صندوق ترست كافتراضي
                        $trustFund = Account::where('AccountName', 'LIKE', '%صندوق ترست%')->first();

                        // جلب الفرع والجلسة من المستخدم الحالي
                        $user = Auth::user();
                        $activeSession = \App\Models\AccountingSession::where('user_id', $user->ID)
                            ->where('IsEnded', 0)
                            ->latest('StartTime')
                            ->first();

                        $form->fill([
                            'AccountID' => $detectedAccount,
                            'FundAccountID' => $trustFund?->ID,
                            'Notes' => "العنوان" . " - " . ($record->IsIngoing ? $record->FromAddress : $record->ToAddress),
                            'Date' => $record->BlockTimestamp?->format('Y-m-d'),
                            'Amount' => (float)$record->Amount,
                            'CurrencyID' => $record->CurrencyID,
                            'BranchID' => $user->BranchID,
                            'TransactionHash' => $record->TransactionHash,
                            'SessionID' => $activeSession?->legacy_id,
                        ]);
                    })
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('AccountID')
                                ->label('حساب العميل')
                                ->options(Account::where('AccountTypeID', 2)->pluck('AccountName', 'ID'))
                                ->searchable()
                                ->required(),
                            Select::make('FundAccountID')
                                ->label('الصندوق')
                                ->options(Account::where('AccountName', 'LIKE', '%صندوق%')->pluck('AccountName', 'ID'))
                                ->searchable()
                                ->required(),
                            TextInput::make('Amount')
                                ->label('المبلغ')
                                ->numeric()
                                ->readOnly(),
                            Select::make('CurrencyID')
                                ->label('العملة')
                                ->options(fn() => BaseResource::getCurrencyOptions())
                                ->disabled()
                                ->dehydrated(),

                            TextInput::make('Notes')
                                ->label('البيان')
                                ->columnSpanFull()
                                ->required(),
                            DatePicker::make('Date')
                                ->label('التاريخ')
                                ->required(),
                            Hidden::make('BranchID'),
                            Hidden::make('TransactionHash'),
                            Hidden::make('SessionID'),
                        ])
                    ])
                    ->action(function ($record, array $data) {
                        try {
                            $action = app(PostWalletTransactionAction::class);
                            $dto = WalletTransactionDTO::fromArray($data);
                            $action->execute($record, $dto);

                            Notification::make()
                                ->title('تم الترحيل بنجاح')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('فشل الترحيل')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('unpost')
                    ->label('')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد إلغاء الترحيل')
                    ->modalDescription('هل أنت متأكد من إلغاء ترحيل هذه العملية؟ سيتم حذف القيد المحاسبي المرتبط بها وعكس الأرصدة.')
                    ->modalSubmitActionLabel('نعم، قم بالإلغاء')
                    ->visible(fn($record) => $record->IsPosted)
                    ->action(function ($record) {
                        try {
                            $action = app(PostWalletTransactionAction::class);
                            $action->unpost($record);

                            Notification::make()
                                ->title('تم الغاء الترحيل بنجاح')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('فشل إلغاء الترحيل')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                ViewAction::make()
                    ->iconButton(),
                EditAction::make()
                    ->iconButton(),
            ])
            ->recordAction(null)
            ->recordUrl(null)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
