<?php

namespace App\Filament\Resources\Branches\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('اطلاعات شعبه')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('نام شعبه')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('پاسداران'),

                        TextInput::make('code')
                            ->label('کد شعبه')
                            ->maxLength(50)
                            ->helperText('کد کوتاه برای استفاده در گزارش‌ها.'),

                        TextInput::make('phone')
                            ->label('تلفن')
                            ->tel()
                            ->maxLength(50),

                        Toggle::make('is_active')
                            ->label('فعال')
                            ->default(true),

                        Textarea::make('address')
                            ->label('آدرس')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
