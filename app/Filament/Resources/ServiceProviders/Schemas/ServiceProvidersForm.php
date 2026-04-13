<?php

namespace App\Filament\Resources\ServiceProviders\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ServiceProvidersForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('إعدادات مزود الخدمة')
                ->description('إدارة بيانات الشبكة وأكواد الربط التقنية والارباح')
                ->aside()
                ->columns(2)
                ->schema([
                    TextInput::make('Name')
                        ->label('اسم الشبكة')
                        ->required()
                        ->placeholder('مثال: يمن موبايل'),

                    TextInput::make('Code')
                        ->label('كود الربط (Technical Code)')
                        ->helperText('يستخدم للربط مع الـ API الخارجي')
                        ->required()
                        ->unique(ignorable: fn ($record) => $record),

                    TextInput::make('Category')
                        ->label('فئة الخدمة')
                        ->default('Telecommunications')
                        ->placeholder('اتصالات، إنترنت، إلخ'),

                    TextInput::make('DefaultProfit')
                        ->label('هامش الربح الافتراضي')
                        ->numeric()
                        ->default(0)
                        ->suffix('YR'),

                    Toggle::make('IsActive')
                        ->label('حالة الخدمة')
                        ->default(true)
                        ->onColor('success')
                        ->offColor('danger'),

                    FileUpload::make('Logo')
                        ->label('شعار الشبكة')
                        ->image()
                        ->directory('service-providers')
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
