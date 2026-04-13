<?php

namespace App\Filament\Resources\AccountingSessions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AccountingSessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات الجلسة')
                    ->schema([
                        TextInput::make('legacy_id')
                            ->label('ID الجلسة')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn ($record) => $record !== null),

                        Select::make('user_id')
                            ->label('المستخدم')
                            ->relationship('user', 'UserName')
                            ->required()
                            ->searchable()
                            ->disabled(fn ($record) => $record !== null),

                        Select::make('BranchID')
                            ->label('الفرع')
                            ->relationship('branch', 'BranchName')
                            ->required()
                            ->searchable(),

                        DateTimePicker::make('StartTime')
                            ->label('وقت البدء')
                            ->default(now())
                            ->required(),

                        DateTimePicker::make('EndTime')
                            ->label('وقت الانتهاء')
                            ->disabled()
                            ->visible(fn ($record) => $record?->IsEnded),

                        Toggle::make('IsEnded')
                            ->label('منتهية')
                            ->disabled()
                            ->default(false),

                        Textarea::make('Notes')
                            ->label('ملاحظات')
                            ->columnSpanFull(),
                    ])->columns(2)
            ]);
    }
}
