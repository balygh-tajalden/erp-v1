<?php

namespace App\Filament\Resources\Currencies\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use App\Models\Currency;

class CurrencyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('TheNumber')
                    ->label('Number')
                    ->numeric()
                    ->default(fn() => (Currency::max('TheNumber') ?? 0) + 1),
                TextInput::make('CurrencyName')
                    ->label('Name')
                    ->required(),
                TextInput::make('ArabicCode')
                    ->label('الكود العربي')
                    ->required(),
                TextInput::make('EnglishCode')
                    ->label('الكود')
                    ->required(),
                Toggle::make('IsDefault')
                    ->label('افتراضي'),
            ]);
    }
}
