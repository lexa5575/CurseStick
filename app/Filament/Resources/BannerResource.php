<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Filament\Resources\BannerResource\RelationManagers;
use App\Models\Banner;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationLabel = 'Баннеры';
    protected static ?string $modelLabel = 'Баннер';
    protected static ?string $pluralModelLabel = 'Баннеры';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('image')
                    ->label('Изображение')
                    ->disk('public')
                    ->directory('images/banners')
                    ->visibility('public')
                    ->image()
                    ->imageResizeMode('cover')
                    ->imageCropAspectRatio('16:9')
                    ->imageEditor()
                    ->required(),
                
                Section::make('Текст и содержимое')
                    ->schema([
                        Textarea::make('text')
                            ->label('Заголовок баннера')
                            ->rows(2)
                            ->maxLength(255),
                            
                        Textarea::make('subtitle')
                            ->label('Подзаголовок')
                            ->rows(2)
                            ->maxLength(255),
                            
                        TextInput::make('button_text')
                            ->label('Текст кнопки')
                            ->default('Смотреть товары'),
                            
                        TextInput::make('url')
                            ->label('URL кнопки')
                            ->placeholder('/categories'),
                    ])
                    ->collapsible(),
                
                Section::make('Стилизация текста')
                    ->schema([
                        ColorPicker::make('text_color')
                            ->label('Цвет текста')
                            ->default('#FFFFFF'),
                            
                        Select::make('text_size')
                            ->label('Размер шрифта')
                            ->options([
                                'text-xl' => 'Маленький',
                                'text-2xl' => 'Средний',
                                'text-3xl' => 'Большой',
                                'text-4xl' => 'Очень большой',
                                'text-5xl' => 'Огромный'
                            ])
                            ->default('text-4xl'),
                            
                        Select::make('text_weight')
                            ->label('Толщина шрифта')
                            ->options([
                                'font-normal' => 'Обычный',
                                'font-medium' => 'Средний',
                                'font-semibold' => 'Полужирный',
                                'font-bold' => 'Жирный',
                                'font-extrabold' => 'Очень жирный'
                            ])
                            ->default('font-bold'),
                            
                        Select::make('text_shadow')
                            ->label('Тень текста')
                            ->options([
                                'shadow-none' => 'Без тени',
                                'shadow-sm' => 'Маленькая',
                                'shadow' => 'Средняя',
                                'shadow-md' => 'Большая',
                                'shadow-lg' => 'Очень большая'
                            ])
                            ->default('shadow-none'),
                            
                        Select::make('text_alignment')
                            ->label('Выравнивание текста')
                            ->options([
                                'text-left' => 'По левому краю',
                                'text-center' => 'По центру',
                                'text-right' => 'По правому краю'
                            ])
                            ->default('text-center'),
                            
                        ColorPicker::make('overlay_color')
                            ->label('Цвет наложения')
                            ->default('#00000066'),
                    ])
                    ->collapsible(),
                
                Section::make('Настройки отображения')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Активен')
                            ->default(true),
                        
                        TextInput::make('order')
                            ->label('Порядок отображения')
                            ->numeric()
                            ->default(0),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_url')
                    ->label('Изображение')
                    ->square()
                    ->size(100),
                TextColumn::make('text')
                    ->label('Текст')
                    ->limit(50),
                ToggleColumn::make('is_active')
                    ->label('Активен'),
                TextColumn::make('order')
                    ->label('Порядок')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('order')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Banner $record) {
                        // Удаляем файл изображения при удалении баннера
                        if ($record->image && Storage::disk('public')->exists($record->image)) {
                            Storage::disk('public')->delete($record->image);
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Collection $records) {
                            // Удаляем файлы изображений при массовом удалении
                            foreach ($records as $record) {
                                if ($record->image && Storage::disk('public')->exists($record->image)) {
                                    Storage::disk('public')->delete($record->image);
                                }
                            }
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
