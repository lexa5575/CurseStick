<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    
    protected static ?string $navigationLabel = 'Товары';
    
    protected static ?string $modelLabel = 'Товар';
    
    protected static ?string $pluralModelLabel = 'Товары';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Основная информация')
                    ->schema([
                        Grid::make()
                            ->schema([
                                TextInput::make('name')
                                    ->label('Название товара')
                                    ->required()
                                    ->maxLength(255),
                                    
                                Select::make('category_id')
                                    ->label('Категория')
                                    ->options(Category::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->required(),
                            ])->columns(2),
                            
                        Textarea::make('description')
                            ->label('Описание')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
                    
                Section::make('Цены и наличие')
                    ->schema([
                        Grid::make()
                            ->schema([
                                TextInput::make('price')
                                    ->label('Цена')
                                    ->required()
                                    ->numeric()
                                    ->prefix('$'),
                                    
                                TextInput::make('discount')
                                    ->label('Скидка')
                                    ->numeric()
                                    ->prefix('$')
                                    ->default(null)
                                    ->hint('Оставьте пустым, если скидки нет')
                                    ->nullable()
                                    ->dehydrateStateUsing(fn ($state) => $state === '' || $state === null ? 0 : $state),
                                    
                                Toggle::make('is_active')
                                    ->label('Активен')
                                    ->default(true)
                                    ->helperText('Отображать товар на сайте'),
                                    
                                Toggle::make('is_featured')
                                    ->label('Рекомендуемый')
                                    ->default(false)
                                    ->helperText('Показывать в рекомендуемых товарах'),
                            ])->columns(2),
                    ]),
                    
                Section::make('Изображение')
                    ->schema([
                        FileUpload::make('image')
                            ->label('Изображение товара')
                            ->image()
                            ->directory('products')
                            ->visibility('public')
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('600')
                            ->imageResizeTargetHeight('600'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_url')
                    ->label('Фото')
                    ->circular()
                    ->width(50)
                    ->height(50),
                    
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('category.name')
                    ->label('Категория')
                    ->sortable(),
                    
                TextColumn::make('price')
                    ->label('Цена')
                    ->money('USD')
                    ->sortable(),
                    
                TextColumn::make('discount')
                    ->label('Скидка')
                    ->money('USD')
                    ->sortable()
                    ->getStateUsing(function (Product $record): string {
                        return $record->discount ? '$' . number_format($record->discount, 2) : '-';
                    }),
                    
                ToggleColumn::make('is_active')
                    ->label('Активен')
                    ->sortable(),
                    
                ToggleColumn::make('is_featured')
                    ->label('Рекомендуемый')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Категория')
                    ->relationship('category', 'name'),
                    
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Статус')
                    ->placeholder('Все товары')
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные'),
                    
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Рекомендуемые')
                    ->placeholder('Все товары')
                    ->trueLabel('Только рекомендуемые')
                    ->falseLabel('Только обычные'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус товара')
                    ->options([
                        'active' => 'Активные',
                        'trashed' => 'Удаленные',
                        'all' => 'Все товары',
                    ])
                    ->default('active')
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? 'active') {
                            'active' => $query->whereNull('deleted_at'),
                            'trashed' => $query->onlyTrashed(),
                            'all' => $query->withTrashed(),
                            default => $query->whereNull('deleted_at'),
                        };
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->label('Удалить'),
                Tables\Actions\RestoreAction::make()
                    ->label('Восстановить'),
                Tables\Actions\ForceDeleteAction::make()
                    ->label('Удалить навсегда'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('toggle_active')
                        ->label('Изменить статус')
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            foreach ($records as $record) {
                                $record->update(['is_active' => !$record->is_active]);
                            }
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // При необходимости здесь можно добавить связи с другими моделями,
            // например, с заказами или отзывами на товар
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->with(['category']); // Optimize queries
    }
    
    // Enable soft delete actions in admin
    public static function canDelete(Model $record): bool
    {
        return true;
    }
    
    public static function canRestore(Model $record): bool
    {
        return $record->trashed();
    }
    
    public static function canForceDelete(Model $record): bool
    {
        return $record->trashed();
    }
}
