<?php

namespace App\Filament\Resources\CouponResource\Pages;

use App\Filament\Resources\CouponResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditCoupon extends EditRecord
{
    protected static string $resource = CouponResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            
            Actions\Action::make('toggle_status')
                ->label(fn (): string => $this->record->is_active ? 'Deactivate' : 'Activate')
                ->icon(fn (): string => $this->record->is_active ? 'heroicon-o-pause' : 'heroicon-o-play')
                ->color(fn (): string => $this->record->is_active ? 'warning' : 'success')
                ->action(function (): void {
                    $this->record->update(['is_active' => !$this->record->is_active]);
                    
                    Notification::make()
                        ->success()
                        ->title('Status updated')
                        ->body('Coupon has been ' . ($this->record->is_active ? 'activated' : 'deactivated'))
                        ->send();
                })
                ->requiresConfirmation(),
                
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Coupon updated successfully')
            ->body('The coupon "' . $this->record->code . '" has been updated.');
    }
}
