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
use Filament\Notifications\Notification;

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
                Tables\Actions\Action::make('send_payment_details')
                    ->label('Отправить реквизиты')
                    ->icon('heroicon-o-envelope')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Отправить платежные реквизиты')
                    ->modalDescription(fn (ZelleAddress $record) => "Отправить платежные реквизиты на email: {$record->email}?")
                    ->action(function (ZelleAddress $record) {
                        try {
                            // Отправляем запрос на маршрут для отправки email
                            $controller = app('App\Http\Controllers\Admin\ZellePaymentController');
                            $response = $controller->sendPaymentDetails($record);
                            
                            $responseData = $response->getData();
                            
                            if ($responseData->success) {
                                Notification::make()
                                    ->title('Платежные реквизиты отправлены!')
                                    ->success()
                                    ->body("Email с реквизитами отправлен на {$record->email}")
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Ошибка отправки')
                                    ->danger()
                                    ->body($responseData->message)
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Ошибка отправки')
                                ->danger()
                                ->body('Не удалось отправить email: ' . $e->getMessage())
                                ->send();
                        }
                    }),
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
