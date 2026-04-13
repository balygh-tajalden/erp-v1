<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Select;
use App\Models\UserGroup;
use App\Filament\Resources\Users\UserResource;
use App\Models\Branch;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('UserName')
                    ->label('اسم المستخدم')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('UserPassword')
                    ->label('كلمة المرور')
                    ->password()
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context): bool => $context === 'create')
                    ->maxLength(255),
                TextInput::make('Phone')
                    ->label('رقم الهاتف')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(12),
                Select::make('BranchID')
                    ->label('الفرع')
                    ->options(UserResource::getBranchOptions())
                    ->default(2)
                    ->required(),
                Select::make('UserGroupID')
                    ->label('المجموعة الأساسية')
                    ->options(UserResource::getUserGroupOptions())
                    ->required(),
                Toggle::make('IsActive')
                    ->label('حالة الحساب')
                    ->default(true),
                Textarea::make('Notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
                Hidden::make('CreatedBy')
                    ->default(Auth::id()),
                Hidden::make('ModifiedBy')
                    ->default(Auth::id())
                    ->dehydrateStateUsing(fn() => Auth::id()),
            ]);
    }
}
