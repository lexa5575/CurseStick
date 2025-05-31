<?php

namespace App\Filament\Resources\ZelleAddressResource\Pages;

use App\Filament\Resources\ZelleAddressResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditZelleAddress extends EditRecord
{
    protected static string $resource = ZelleAddressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Удалить'),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
