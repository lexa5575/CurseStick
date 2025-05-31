<?php

namespace App\Filament\Resources\ZelleAddressResource\Pages;

use App\Filament\Resources\ZelleAddressResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListZelleAddresses extends ListRecords
{
    protected static string $resource = ZelleAddressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Добавить Zelle адрес'),
        ];
    }
}
