<?php

namespace App\Filament\Resources\BankAccounts\Tables;

use App\Models\BankAccount;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class BankAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('title')
            ->columns([
                TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('bank_name')
                    ->label('بانک')
                    ->searchable()
                    ->sortable()
                    ->badge(),

                TextColumn::make('account_holder')
                    ->label('صاحب حساب')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('card_number')
                    ->label('شماره کارت')
                    ->placeholder('—')
                    ->searchable()
                    // Full number is one click away rather than on screen for
                    // anyone walking past, but still copyable.
                    ->formatStateUsing(fn (?string $state, BankAccount $record): ?string => $record->formatted_card_number)
                    ->copyable()
                    ->copyableState(fn (?string $state): ?string => $state)
                    ->copyMessage('شماره کارت کپی شد'),

                TextColumn::make('iban')
                    ->label('شبا')
                    ->placeholder('—')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('شماره شبا کپی شد')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('account_number')
                    ->label('شماره حساب')
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاریخ ثبت')
                    ->jalaliDateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('bank_name')
                    ->label('بانک')
                    ->options(fn (): array => BankAccount::query()
                        ->distinct()
                        ->orderBy('bank_name')
                        ->pluck('bank_name', 'bank_name')
                        ->all())
                    ->searchable(),

                TernaryFilter::make('is_active')
                    ->label('وضعیت')
                    ->placeholder('همه')
                    ->trueLabel('فعال')
                    ->falseLabel('غیرفعال'),

                TrashedFilter::make()->label('حذف‌شده‌ها'),
            ])
            ->recordActions([
                EditAction::make()->label('ویرایش'),
                DeleteAction::make()->label('حذف'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('هنوز حسابی ثبت نشده')
            ->emptyStateDescription('حساب‌های بانکی شرکت برای دریافت اجاره و تسویه در اینجا نگهداری می‌شوند.');
    }
}
