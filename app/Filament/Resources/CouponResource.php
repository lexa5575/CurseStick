<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Enums\FontWeight;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Coupons';

    protected static ?string $modelLabel = 'Coupon';

    protected static ?string $pluralModelLabel = 'Coupons';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Coupon Information')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('code')
                                    ->label('Coupon Code')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(50)
                                    ->placeholder('e.g., SAVE20')
                                    ->helperText('Code will be automatically converted to uppercase')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('name')
                                    ->label('Display Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('e.g., 20% Off Holiday Sale')
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->placeholder('Internal description for admin use')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Discount Settings')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('discount_type')
                                    ->label('Discount Type')
                                    ->required()
                                    ->options([
                                        'fixed' => 'Fixed Amount ($)',
                                        'percentage' => 'Percentage (%)',
                                    ])
                                    ->live()
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('discount_value')
                                    ->label('Discount Value')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(fn (Forms\Get $get) => $get('discount_type') === 'percentage' ? 100 : 9999.99)
                                    ->step(0.01)
                                    ->suffix(fn (Forms\Get $get) => $get('discount_type') === 'percentage' ? '%' : '$')
                                    ->placeholder(fn (Forms\Get $get) => $get('discount_type') === 'percentage' ? '10' : '25.00')
                                    ->columnSpan(1),
                            ]),
                    ]),

                Forms\Components\Section::make('Category Restrictions')
                    ->schema([
                        Forms\Components\Toggle::make('applies_to_all_categories')
                            ->label('Apply to All Categories')
                            ->helperText('If disabled, select specific categories below')
                            ->live()
                            ->default(true),

                        Forms\Components\Select::make('categories')
                            ->label('Specific Categories')
                            ->multiple()
                            ->relationship('categories', 'name')
                            ->options(Category::pluck('name', 'id'))
                            ->visible(fn (Forms\Get $get) => !$get('applies_to_all_categories'))
                            ->helperText('Leave empty to apply to all categories'),
                    ]),

                Forms\Components\Section::make('Validity & Usage')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('valid_from')
                                    ->label('Valid From')
                                    ->helperText('Leave empty for immediate activation')
                                    ->columnSpan(1),

                                Forms\Components\DateTimePicker::make('valid_until')
                                    ->label('Valid Until')
                                    ->helperText('Leave empty for permanent coupon')
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('usage_limit')
                                    ->label('Usage Limit')
                                    ->numeric()
                                    ->minValue(1)
                                    ->placeholder('Unlimited')
                                    ->helperText('Maximum number of times this coupon can be used')
                                    ->columnSpan(1),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active')
                                    ->helperText('Inactive coupons cannot be used')
                                    ->default(true)
                                    ->columnSpan(1),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->copyable()
                    ->copyMessage('Coupon code copied')
                    ->copyMessageDuration(1500),

                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('formatted_discount')
                    ->label('Discount')
                    ->sortable('discount_value')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, '%') => 'success',
                        str_contains($state, '$') => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status_badge')
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

                Tables\Columns\TextColumn::make('usage_info')
                    ->label('Usage')
                    ->getStateUsing(function (Coupon $record): string {
                        if ($record->usage_limit) {
                            return "{$record->usage_count} / {$record->usage_limit}";
                        }
                        return "{$record->usage_count} / ∞";
                    })
                    ->sortable('usage_count'),

                Tables\Columns\TextColumn::make('validity')
                    ->label('Validity')
                    ->getStateUsing(function (Coupon $record): string {
                        if ($record->isPermanent()) {
                            return 'Permanent';
                        }
                        
                        $from = $record->valid_from ? $record->valid_from->format('M j, Y') : 'Now';
                        $until = $record->valid_until->format('M j, Y');
                        
                        return "{$from} - {$until}";
                    }),

                Tables\Columns\TextColumn::make('categories_count')
                    ->label('Categories')
                    ->counts('categories')
                    ->getStateUsing(function (Coupon $record): string {
                        if ($record->applies_to_all_categories) {
                            return 'All Categories';
                        }
                        $count = $record->categories_count ?? $record->categories()->count();
                        return $count . ' ' . str('category')->plural($count);
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),

                Tables\Filters\SelectFilter::make('discount_type')
                    ->label('Discount Type')
                    ->options([
                        'fixed' => 'Fixed Amount ($)',
                        'percentage' => 'Percentage (%)',
                    ]),

                Tables\Filters\Filter::make('expired')
                    ->label('Expired')
                    ->query(fn (Builder $query): Builder => $query->where('valid_until', '<', now())),

                Tables\Filters\Filter::make('permanent')
                    ->label('Permanent')
                    ->query(fn (Builder $query): Builder => $query->whereNull('valid_until')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                
                Tables\Actions\Action::make('toggle_status')
                    ->label(fn (Coupon $record): string => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (Coupon $record): string => $record->is_active ? 'heroicon-o-pause' : 'heroicon-o-play')
                    ->color(fn (Coupon $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (Coupon $record): void {
                        $record->update(['is_active' => !$record->is_active]);
                    })
                    ->requiresConfirmation()
                    ->modalDescription(fn (Coupon $record): string => 
                        $record->is_active 
                            ? 'This will deactivate the coupon and prevent customers from using it.'
                            : 'This will activate the coupon and allow customers to use it.'
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),
                    
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-pause')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'view' => Pages\ViewCoupon::route('/{record}'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::active()->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'success';
    }
}