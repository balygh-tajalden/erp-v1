<?php

namespace App\Filament\Resources\ServiceRequests\Schemas;

use App\Filament\Resources\BaseResource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceRequestsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('تفاصيل طلب الخدمة')
                ->columns(2)
                ->schema([
                    Select::make('AccountID')
                        ->label('حساب العميل')
                        ->options(BaseResource::getAccountOptions(true))
                        ->searchable()
                        ->required(),

                    Select::make('ProviderID')
                        ->label('مزود الخدمة')
                        ->relationship('provider', 'Name')
                        ->required(),

                    TextInput::make('PhoneNumber')
                        ->label('رقم الهاتف')
                        ->tel()
                        ->required(),

                    TextInput::make('Amount')
                        ->label('المبلغ')
                        ->numeric()
                        ->required()
                        ->prefix('YR'),

                    Select::make('ServiceType')
                        ->label('نوع الخدمة')
                        ->options([
                            1 => 'رصيد',
                            2 => 'باقة',
                        ])
                        ->required(),

                    Select::make('Status')
                        ->label('الحالة')
                        ->options([
                            'Pending' => 'قيد الانتظار',
                            'Processing' => 'جاري المعالجة',
                            'Success' => 'ناجح',
                            'Failed' => 'فشل',
                        ])
                        ->default('Pending'),

                    TextInput::make('ReferenceNumber')
                        ->label('الرقم المرجعي')
                        ->disabled(),

                    Textarea::make('Notes')
                        ->label('ملاحظات')
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
