<?php

namespace App\Filament\Resources\CouponResource\Pages;

use App\Filament\Resources\CouponResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCoupons extends ListRecords
{
    protected static string $resource = CouponResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create New Coupon')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Coupons'),
            
            'active' => Tab::make('Active')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge($this->getModel()::where('is_active', true)->count()),
            
            'inactive' => Tab::make('Inactive')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->badge($this->getModel()::where('is_active', false)->count()),
            
            'expired' => Tab::make('Expired')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('valid_until', '<', now()))
                ->badge($this->getModel()::where('valid_until', '<', now())->count()),
            
            'permanent' => Tab::make('Permanent')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('valid_until'))
                ->badge($this->getModel()::whereNull('valid_until')->count()),
        ];
    }
}
