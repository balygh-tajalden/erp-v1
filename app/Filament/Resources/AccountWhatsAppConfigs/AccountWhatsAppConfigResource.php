<?php

namespace App\Filament\Resources\AccountWhatsAppConfigs;

use App\Filament\Resources\AccountWhatsAppConfigs\Pages\ManageAccountWhatsAppConfigs;
use App\Models\AccountWhatsAppConfig;
use App\Models\Account;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\CheckboxList;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Forms\Components\TagsInput;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Action;
use App\Services\System\WhatsAppService;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Validation\Rule;

class AccountWhatsAppConfigResource extends Resource
{
    protected static ?string $model = AccountWhatsAppConfig::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'إعدادات الواتساب';
    protected static ?string $modelLabel = 'إعداد واتساب';
    protected static ?string $pluralModelLabel = 'إعدادات الواتساب';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('الربط مع الحساب')
                    ->schema([
                        Select::make('AccountID')
                            ->label('الحساب المحاسبي')
                            ->options(Account::where('AccountTypeID', 2)->pluck('AccountName', 'ID'))
                            ->searchable()
                            ->required()
                            ->unique(
                                table: 'tblAccountWhatsAppConfig',
                                column: 'AccountID',
                                ignoreRecord: true
                            )
                            ->validationMessages([
                                'unique' => 'هذا الحساب مربوط بالفعل بإعدادات واتساب أخرى.',
                            ]),
                        Toggle::make('IsActive')
                            ->label('تفعيل الخدمة عامة')
                            ->default(true)
                            ->reactive(),
                    ])->columns(2),

                Section::make('إعدادات الإشعارات')
                    ->description('سيتم تطبيق هذه الإعدادات على جميع الأرقام المضافة أدناه.')
                    ->schema([
                        TagsInput::make('Settings.numbers')
                            ->label('أرقام الواتساب المستلمة')
                            ->placeholder('ادخل الرقم هنا مثال 967771234567')
                            ->required()
                            ->nestedRecursiveRules([
                                'regex:/^[0-9]+$/',
                                'min:9',
                                'max:15',
                            ])
                            ->validationMessages([
                                'Settings.numbers.*.regex' => 'الرقم يجب أن يحتوي على أرقام فقط بدون مسافات أو حروف.',
                                'Settings.numbers.*.min' => 'الرقم يجب أن يكون 9 أرقام على الأقل.',
                                'Settings.numbers.*.max' => 'الرقم يجب ألا يتجاوز 15 رقماً.',
                            ])
                            ->suffixIcon('heroicon-o-plus-circle')
                            ->suffixIconColor('primary'),


                        CheckboxList::make('Settings.events')
                            ->label('أنواع العمليات التي يتم الإشعار بها')
                            ->options([
                                'receipt'              => 'سندات القبض',
                                'payment'              => 'سندات الصرف',
                                'simple_entry'         => 'القيود البسيطة',
                                'currency_buy'         => 'شراء عملة',
                                'currency_sell'        => 'بيع عملة',
                                'wallet_transaction'   => 'محافظ رقمية (USDT)',
                            ])
                            ->columns(3)
                            ->required(fn (Get $get): bool => $get('IsActive') === true)
                            ->validationMessages([
                                'required' => 'يجب اختيار نوع عملية واحد على الأقل عند تفعيل الخدمة.',
                            ]),
                    ]),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('account.AccountName')
                    ->label('الحساب المحاسبي')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('IsActive')
                    ->label('الحالة العامة')
                    ->boolean(),
                TextColumn::make('Settings')
                    ->label('الأرقام المرتبطة')
                    ->formatStateUsing(fn($state) => count($state ?? []) . ' أرقام'),
                TextColumn::make('CreatedDate')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->recordActions([
                Action::make('notify_added')
                    ->label('إرسال تنبيه تفعيل')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد إرسال رسالة الترحيب')
                    ->modalDescription('سيتم إرسال رسالة "تم ربط الحساب" لجميع الأرقام المحددة في هذا الإعداد.')
                    ->action(function (AccountWhatsAppConfig $record) {
                        $whatsappService = new WhatsAppService();
                        $response = $whatsappService->notifyAccountAdded($record);

                        if ($response['success']) {
                            Notification::make()
                                ->title('تم الإرسال بنجاح')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('فشل الإرسال')
                                ->body($response['error'])
                                ->danger()
                                ->send();
                        }
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAccountWhatsAppConfigs::route('/'),
        ];
    }
}
