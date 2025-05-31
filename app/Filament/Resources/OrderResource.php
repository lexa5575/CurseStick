<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\ZelleAddress;
use App\Mail\OrderShippedMail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Actions\Action;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\IconPosition;
use Illuminate\Support\Facades\Mail;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    
    protected static ?string $navigationGroup = 'Продажи';
    
    protected static ?string $navigationLabel = 'Заказы';
    
    protected static ?string $modelLabel = 'Заказ';
    
    protected static ?string $pluralModelLabel = 'Заказы';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('id')
                            ->label('ID заказа')
                            ->disabled(),
                            
                        Forms\Components\TextInput::make('status')
                            ->label('Статус заказа')
                            ->required(),
                            
                        Forms\Components\Select::make('payment_method')
                            ->label('Способ оплаты')
                            ->options([
                                'cash' => 'Наличные',
                                'card' => 'Карта',
                                'online' => 'Онлайн',
                                'zelle' => 'Zelle',
                                'crypto' => 'Криптовалюта',
                            ])
                            ->required(),
                            
                        Forms\Components\Select::make('payment_status')
                            ->label('Статус оплаты')
                            ->options([
                                'pending' => 'Ожидает оплаты',
                                'processing' => 'Обрабатывается',
                                'completed' => 'Оплачен',
                                'cancelled' => 'Отменен',
                                'failed' => 'Ошибка',
                                'refunded' => 'Возвращен',
                            ])
                            ->required(),
                            
                        Forms\Components\TextInput::make('total')
                            ->label('Сумма заказа')
                            ->numeric()
                            ->prefix('$')
                            ->disabled(),
                            
                        Forms\Components\TextInput::make('tracking_number')
                            ->label('Трекинг-номер')
                            ->placeholder('Введите трекинг-номер отправления')
                            ->helperText('Номер для отслеживания посылки клиентом'),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Информация о клиенте')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Имя')
                            ->required(),
                            
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required(),
                            
                        Forms\Components\TextInput::make('phone')
                            ->label('Телефон')
                            ->tel(),
                            
                        Forms\Components\TextInput::make('company')
                            ->label('Компания'),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Адрес доставки')
                    ->schema([
                        Forms\Components\TextInput::make('street')
                            ->label('Улица')
                            ->required(),
                            
                        Forms\Components\TextInput::make('house')
                            ->label('Дом/квартира'),
                            
                        Forms\Components\TextInput::make('city')
                            ->label('Город')
                            ->required(),
                            
                        Forms\Components\TextInput::make('state')
                            ->label('Область/штат')
                            ->required(),
                            
                        Forms\Components\TextInput::make('postal_code')
                            ->label('Почтовый индекс')
                            ->required(),
                            
                        Forms\Components\TextInput::make('country')
                            ->label('Страна')
                            ->required(),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Дополнительная информация')
                    ->schema([
                        Forms\Components\Textarea::make('comment')
                            ->label('Комментарий к заказу')
                            ->columnSpan('full'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('Клиент')
                    ->sortable()
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->sortable()
                    ->searchable()
                    ->copyable() // Добавляем возможность копирования
                    ->copyMessage('Скопировано в буфер обмена!')
                    ->extraAttributes(['class' => 'select-all']) // Добавляем CSS класс для возможности выделения
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('total')
                    ->label('Сумма')
                    ->money('USD')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус заказа')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Новый' => 'info',
                        'Обработан' => 'success',
                        'Отправлен' => 'warning',
                        'Доставлен' => 'success',
                        'Отменен' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Способ оплаты')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash' => 'Наличные',
                        'card' => 'Карта',
                        'online' => 'Онлайн',
                        'zelle' => 'Zelle',
                        'crypto' => 'Криптовалюта',
                        default => $state,
                    })
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Статус оплаты')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'failed' => 'danger',
                        'refunded' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата заказа')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Статус оплаты')
                    ->options([
                        'pending' => 'Ожидает оплаты',
                        'processing' => 'Обрабатывается',
                        'completed' => 'Оплачен',
                        'cancelled' => 'Отменен',
                        'failed' => 'Ошибка',
                        'refunded' => 'Возвращен',
                    ])
                    ->multiple(),
                    
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Способ оплаты')
                    ->options([
                        'cash' => 'Наличные',
                        'card' => 'Карта',
                        'online' => 'Онлайн',
                        'zelle' => 'Zelle',
                        'crypto' => 'Криптовалюта',
                    ])
                    ->multiple(),
                    
                Tables\Filters\Filter::make('created_at')
                    ->label('Дата заказа')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('С'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('По'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('customer_orders_history')
                    ->label('История заказов клиента')
                    ->icon('heroicon-o-clock')
                    ->color('info')
                    ->modalHeading(fn (Order $record): string => "История заказов для {$record->name} ({$record->email})")
                    ->modalContent(fn (Order $record): string => static::getCustomerOrdersHistoryModalContent($record))
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false),
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
            RelationManagers\ItemsRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
    
    /**
     * Формирует содержимое модального окна с историей заказов клиента
     *
     * @param Order $order Текущий заказ
     * @return string HTML-код таблицы с историей заказов
     */
    /**
     * Отправляет email с информацией о трекинге заказа
     *
     * @param Order $order Заказ для отправки уведомления
     * @return bool Успешно ли отправлено уведомление
     */
    public static function sendTrackingEmail(Order $order): bool
    {
        // Проверяем, что у заказа есть трекинг-номер
        if (empty($order->tracking_number)) {
            logger()->error('Tracking number is empty', ['order_id' => $order->id]);
            return false;
        }

        // Записываем в лог информацию о заказе для отладки
        logger()->info('Attempting to send tracking email', [
            'order_id' => $order->id,
            'email' => $order->email,
            'tracking_number' => $order->tracking_number
        ]);

        // Загружаем заказ со всеми связанными данными, если они еще не загружены
        if (!$order->relationLoaded('items')) {
            $order->load('items.product');
        }

        try {
            // Проверяем email
            if (empty($order->email)) {
                logger()->error('Order email is empty', ['order_id' => $order->id]);
                return false;
            }
            
            // Отправляем email клиенту
            Mail::to($order->email)
                ->send(new OrderShippedMail($order));

            // Обновляем статус заказа на "Отправлен", если он еще не был отправлен
            if ($order->status !== 'Отправлен') {
                $order->status = 'Отправлен';
                $order->save();
            }
            
            logger()->info('Tracking email sent successfully', ['order_id' => $order->id]);
            return true;
        } catch (\Exception $e) {
            // Записываем подробную информацию об ошибке в лог
            logger()->error('Error sending tracking email: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    public static function getCustomerOrdersHistoryModalContent(Order $order): string
    {
        // Получаем все заказы с таким же email
        $customerOrders = Order::where('email', $order->email)
            ->orderBy('created_at', 'desc')
            ->get();
            
        if ($customerOrders->isEmpty()) {
            return '<p>Заказы не найдены.</p>';
        }
        
        $html = '<div class="space-y-4">';
        $html .= '<p>Всего заказов: ' . $customerOrders->count() . '</p>';
        
        // Создаем таблицу с историей заказов
        $html .= '<div class="overflow-x-auto">';
        $html .= '<table class="min-w-full divide-y divide-gray-200">';
        
        // Заголовок таблицы
        $html .= '<thead class="bg-gray-50">';
        $html .= '<tr>';
        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">№ заказа</th>';
        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>';
        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Сумма</th>';
        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Способ оплаты</th>';
        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус оплаты</th>';
        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Zelle адрес</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        
        // Тело таблицы
        $html .= '<tbody class="bg-white divide-y divide-gray-200">';
        
        foreach ($customerOrders as $customerOrder) {
            $html .= '<tr'.($customerOrder->id === $order->id ? ' class="bg-blue-50"' : '').'>';
            
            // Номер заказа
            $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">';
            $html .= $customerOrder->id . ($customerOrder->id === $order->id ? ' (текущий)' : '');
            $html .= '</td>';
            
            // Дата заказа
            $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">';
            $html .= $customerOrder->created_at->format('d.m.Y H:i');
            $html .= '</td>';
            
            // Сумма заказа
            $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">';
            $html .= '$' . number_format($customerOrder->total, 2);
            $html .= '</td>';
            
            // Способ оплаты
            $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">';
            $paymentMethod = match ($customerOrder->payment_method) {
                'cash' => 'Наличные',
                'card' => 'Карта',
                'online' => 'Онлайн',
                'zelle' => 'Zelle',
                'crypto' => 'Криптовалюта',
                default => $customerOrder->payment_method,
            };
            $html .= $paymentMethod;
            $html .= '</td>';
            
            // Статус оплаты
            $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">';
            $statusColor = match ($customerOrder->payment_status) {
                'pending' => 'text-yellow-600',
                'processing' => 'text-blue-600',
                'completed' => 'text-green-600',
                'cancelled' => 'text-red-600',
                'failed' => 'text-red-600',
                'refunded' => 'text-gray-600',
                default => '',
            };
            $paymentStatus = match ($customerOrder->payment_status) {
                'pending' => 'Ожидает оплаты',
                'processing' => 'Обрабатывается',
                'completed' => 'Оплачен',
                'cancelled' => 'Отменен',
                'failed' => 'Ошибка',
                'refunded' => 'Возвращен',
                default => $customerOrder->payment_status,
            };
            $html .= '<span class="' . $statusColor . '">' . $paymentStatus . '</span>';
            $html .= '</td>';
            
            // Zelle адрес
            $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">';
            if ($customerOrder->payment_method === 'zelle') {
                // Получаем Zelle адрес, связанный с email клиента
                $zelleAddress = ZelleAddress::where('email', $customerOrder->email)->first();
                
                // Если нет адреса, связанного с email, используем первый доступный
                if (!$zelleAddress) {
                    $zelleAddress = ZelleAddress::first();
                }
                
                if ($zelleAddress) {
                    $html .= $zelleAddress->address . ' (' . $zelleAddress->email . ')';
                } else {
                    $html .= 'Не указан';
                }
            } else {
                $html .= 'Не применимо';
            }
            $html .= '</td>';
            
            $html .= '</tr>';
        }
        
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
}
