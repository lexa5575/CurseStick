<?php

namespace App\Filament\Resources\ZelleAddressResource\Pages;

use App\Filament\Resources\ZelleAddressResource;
use Filament\Resources\Pages\CreateRecord;

class CreateZelleAddress extends CreateRecord
{
    protected static string $resource = ZelleAddressResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
