<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Просмотр'),
            Actions\DeleteAction::make()
                ->label('Удалить'),
            Actions\Action::make('sendTrackingEmail')
                ->label('Send Tracking Email')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Send Tracking Email to Customer')
                ->modalDescription('This will send an email to the customer with tracking information. Make sure the tracking number is filled in.')
                ->modalSubmitActionLabel('Yes, Send Email')
                ->action(function () {
                    $order = $this->getRecord();
                    
                    // Проверяем наличие трекинг-номера
                    if (empty($order->tracking_number)) {
                        Notification::make()
                            ->title('No Tracking Number')
                            ->body('Please add a tracking number before sending the email.')
                            ->danger()
                            ->send();
                        return;
                    }
                    
                    // Отправляем email
                    $success = OrderResource::sendTrackingEmail($order);
                    
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
}
