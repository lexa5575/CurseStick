<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ZelleAddressResource\Pages;
use App\Models\ZelleAddress;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ZelleAddressResource extends Resource
{
    protected static ?string $model = ZelleAddress::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    
    protected static ?string $navigationGroup = 'Платежные системы';
    
    protected static ?string $navigationLabel = 'Zelle адреса';
    
    protected static ?string $modelLabel = 'Zelle адрес';
    
    protected static ?string $pluralModelLabel = 'Zelle адреса';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Информация о Zelle адресе')
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                            
                        Forms\Components\TextInput::make('address')
                            ->label('Zelle адрес')
                            ->required()
                            ->maxLength(255),
                            
                        Forms\Components\Textarea::make('note')
                            ->label('Примечание')
                            ->nullable()
                            ->maxLength(65535)
                            ->columnSpan('full'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('address')
                    ->label('Zelle адрес')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('note')
                    ->label('Примечание')
                    ->limit(50),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListZelleAddresses::route('/'),
            'create' => Pages\CreateZelleAddress::route('/create'),
            'edit' => Pages\EditZelleAddress::route('/{record}/edit'),
        ];
    }    
}
