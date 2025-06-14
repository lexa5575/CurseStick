<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Form;
use Filament\Forms\Components;
use Filament\Notifications\Notification;

class ViewOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    // Форма для редактирования
    public function form(Form $form): Form
    {
        
        return $form
            ->schema([
                Components\Section::make('Статус заказа')
                    ->schema([
                        Components\Select::make('status')
                            ->label('Статус заказа')
                            ->options([
                                'Новый' => 'Новый',
                                'Оплачен' => 'Оплачен',
                                'Обработан' => 'Обработан',
                                'Отправлен' => 'Отправлен',
                                'Доставлен' => 'Доставлен',
                                'Отменен' => 'Отменен',
                            ])
                            ->required(),
                            
                        Components\Select::make('payment_status')
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
                    ])
                    ->columns(2),
                    
                Components\Section::make('Информация о доставке и трекинг')
                    ->schema([
                        Components\Grid::make(2)
                            ->schema([
                                Components\Group::make()
                                    ->schema([
                                        Components\Placeholder::make('shipping_info')
                                            ->label('Данные о доставке')
                                            ->content(function ($record) {
                                                // Формируем текст для отображения
                                                $text = $record->name . "\n";
                                                $text .= $record->email . "\n";
                                                if (!empty($record->phone)) $text .= $record->phone . "\n";
                                                $text .= $record->street;
                                                if (!empty($record->house)) $text .= ", " . $record->house;
                                                $text .= "\n" . $record->city . ", " . $record->state . " " . $record->postal_code . "\n" . $record->country;
                                                
                                                // Создаем простой блок с текстом
                                                $html = '<div class="p-4 bg-gray-700 text-white rounded-lg">';
                                                $html .= '<pre class="whitespace-pre-wrap text-sm">' . htmlspecialchars($text) . '</pre>';
                                                $html .= '<div class="mt-2 text-xs text-gray-300">Выделите текст для копирования</div>';
                                                $html .= '</div>';
                                                
                                                return new \Illuminate\Support\HtmlString($html);
                                            })
                                    ]),
                                
                                Components\Group::make()
                                    ->schema([
                                        Components\TextInput::make('tracking_number')
                                            ->label('Трекинг-номер')
                                            ->placeholder('Введите трекинг-номер отправления')
                                            ->helperText('Номер для отслеживания посылки клиентом'),
                                    ]),
                            ])
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->url(route('filament.admin.resources.orders.index'))
                ->icon('heroicon-o-arrow-left')
                ->label('К списку заказов'),
                
            Actions\Action::make('sendTrackingEmail')
                ->label('Send Tracking Email')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Send Tracking Email to Customer')
                ->modalDescription('This will send an email to the customer with tracking information. Make sure the tracking number is filled in.')
                ->modalSubmitActionLabel('Yes, Send Email')
                ->action(function () {
                    // Сохраняем форму с помощью стандартного метода базового класса
                    // Это гарантирует, что изменения будут сохранены
                    $this->save();
                    
                    // Получаем свежие данные формы и заказа
                    $data = $this->form->getState();
                    $order = $this->getRecord()->fresh();
                    
                    // Проверяем наличие трекинг-номера до сохранения
                    // Используем данные напрямую из формы, а не из модели
                    if (empty($data['tracking_number'])) {
                        Notification::make()
                            ->title('No Tracking Number')
                            ->body('Please add a tracking number before sending the email.')
                            ->danger()
                            ->send();
                        return;
                    }
                    
                    // Сохраняем изменения в базе данных
                    $order->tracking_number = $data['tracking_number']; // Явно устанавливаем трекинг-номер
                    $order->save();
                    
                    // Проверяем, что трекинг-номер был сохранен
                    \Illuminate\Support\Facades\Log::info('Tracking number saved', [
                        'order_id' => $order->id,
                        'tracking_number' => $order->tracking_number,
                    ]);
                    
                    // Обновляем модель из базы данных
                    $order = $order->fresh();
                    
                    try {
                        // Отправляем email с информацией о трекинг-номере
                        \Mail::to($order->email)->send(new \App\Mail\OrderShippedMail($order));
                        
                        // Обновляем статус заказа
                        if ($order->status !== 'Отправлен') {
                            $order->status = 'Отправлен';
                            $order->save();
                        }
                        
                        $success = true;
                        

                    } catch (\Exception $e) {
                        // Записываем ошибку в лог
                        \Illuminate\Support\Facades\Log::error('Error sending tracking email: ' . $e->getMessage(), [
                            'order_id' => $order->id,
                            'exception' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        
                        $success = false;
                    }
                    
                    if ($success) {
                        Notification::make()
                            ->title('Email Sent Successfully')
                            ->body('The tracking email has been sent to ' . $order->email)
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Error Sending Email')
                            ->body('There was a problem sending the tracking email. Please check logs for details.')
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
    
    // Переопределяем заголовок страницы
    public function getHeading(): string
    {
        return "Заказ #{$this->record->id}";
    }
    
    // Скрываем стандартную кнопку сохранения
    public function getSaveFormAction(): Actions\Action
    {
        return parent::getSaveFormAction()
            ->label('Сохранить изменения')
            ->icon('heroicon-o-check');
    }
    
    // Скрываем стандартную кнопку отмены
    public function getCancelFormAction(): Actions\Action
    {
        return parent::getCancelFormAction()->hidden();
    }
}
