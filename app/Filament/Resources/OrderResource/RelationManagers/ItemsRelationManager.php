<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    
    protected static ?string $title = 'Товары в заказе';
    
    protected static ?string $recordTitleAttribute = 'product.name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('quantity')
                    ->label('Количество')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                    
                Forms\Components\TextInput::make('price')
                    ->label('Цена за единицу')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('product.image_url')
                    ->label('Изображение')
                    ->defaultImageUrl(fn ($record) => 'https://picsum.photos/80/80?random=' . $record->product->id)
                    ->circular(false)
                    ->width(80)
                    ->height(80),
                    
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Товар')
                    ->description(fn ($record) => $record->product->discount > 0 ? 'Скидка: $' . number_format($record->product->discount, 2) : null)
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Количество')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('price')
                    ->label('Цена за единицу')
                    ->money('USD')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('total')
                    ->label('Итого')
                    ->money('USD')
                    ->getStateUsing(fn ($record) => $record->price * $record->quantity)
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Действия добавления новых товаров в заказ
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
