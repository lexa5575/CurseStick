<?php

namespace App\Filament\Resources\CouponResource\Pages;

use App\Filament\Resources\CouponResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewCoupon extends ViewRecord
{
    protected static string $resource = CouponResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            
            Actions\Action::make('toggle_status')
                ->label(fn (): string => $this->record->is_active ? 'Deactivate' : 'Activate')
                ->icon(fn (): string => $this->record->is_active ? 'heroicon-o-pause' : 'heroicon-o-play')
                ->color(fn (): string => $this->record->is_active ? 'warning' : 'success')
                ->action(function (): void {
                    $this->record->update(['is_active' => !$this->record->is_active]);
                })
                ->requiresConfirmation(),
                
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Coupon Details')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('code')
                                    ->label('Coupon Code')
                                    ->weight('bold')
                                    ->copyable()
                                    ->copyMessage('Coupon code copied')
                                    ->copyMessageDuration(1500),
                                    
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Display Name'),
                            ]),
                            
                        Infolists\Components\TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->placeholder('No description provided'),
                    ]),

                Infolists\Components\Section::make('Discount Information')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('discount_type')
                                    ->label('Discount Type')
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'fixed' => 'Fixed Amount ($)',
                                        'percentage' => 'Percentage (%)',
                                        default => $state,
                                    })
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'fixed' => 'info',
                                        'percentage' => 'success',
                                        default => 'gray',
                                    }),
                                    
                                Infolists\Components\TextEntry::make('formatted_discount')
                                    ->label('Discount Value')
                                    ->weight('bold')
                                    ->badge()
                                    ->color('primary'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Status & Validity')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\IconEntry::make('is_active')
                                    ->label('Active Status')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger'),
                                    
                                Infolists\Components\TextEntry::make('status_badge')
                                    ->label('Current Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'active' => 'success',
                                        'inactive' => 'gray',
                                        'expired' => 'danger',
                                        'exhausted' => 'warning',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'active' => 'Active',
                                        'inactive' => 'Inactive',
                                        'expired' => 'Expired',
                                        'exhausted' => 'Exhausted',
                                        default => 'Unknown',
                                    }),
                                    
                                Infolists\Components\TextEntry::make('validity')
                                    ->label('Validity Period')
                                    ->getStateUsing(function (): string {
                                        if ($this->record->isPermanent()) {
                                            return 'Permanent';
                                        }
                                        
                                        $from = $this->record->valid_from 
                                            ? $this->record->valid_from->format('M j, Y H:i') 
                                            : 'Immediately';
                                        $until = $this->record->valid_until->format('M j, Y H:i');
                                        
                                        return "{$from} → {$until}";
                                    }),
                            ]),
                    ]),

                Infolists\Components\Section::make('Usage Information')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('usage_info')
                                    ->label('Usage Count')
                                    ->getStateUsing(function (): string {
                                        if ($this->record->usage_limit) {
                                            return "{$this->record->usage_count} / {$this->record->usage_limit} uses";
                                        }
                                        return "{$this->record->usage_count} uses (unlimited)";
                                    })
                                    ->weight('bold'),
                                    
                                Infolists\Components\TextEntry::make('usage_limit')
                                    ->label('Usage Limit')
                                    ->formatStateUsing(fn (?int $state): string => $state ? (string) $state : 'Unlimited')
                                    ->badge()
                                    ->color(fn (?int $state): string => $state ? 'warning' : 'success'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Category Restrictions')
                    ->schema([
                        Infolists\Components\TextEntry::make('category_info')
                            ->label('Applies To')
                            ->getStateUsing(function (): string {
                                if ($this->record->applies_to_all_categories) {
                                    return 'All Categories';
                                }
                                
                                $categories = $this->record->categories()->pluck('name')->toArray();
                                if (empty($categories)) {
                                    return 'No specific categories selected';
                                }
                                
                                return implode(', ', $categories);
                            })
                            ->badge()
                            ->color(fn (string $state): string => 
                                $state === 'All Categories' ? 'success' : 'info'
                            ),
                    ]),

                Infolists\Components\Section::make('Timestamps')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Created At')
                                    ->dateTime(),
                                    
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime(),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }
}