<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $slug = 'sv23810310303-products';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name', fn ($query) => $query->orderBy('name'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                                'out_of_stock' => 'Out of stock',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (?string $state, Set $set) => $set('slug', Str::slug((string) $state))),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('price')
                            ->label('Price')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('VND'),
                        Forms\Components\TextInput::make('stock_quantity')
                            ->label('Stock quantity')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->default(0),
                        Forms\Components\TextInput::make('discount_percent')
                            ->label('Discount (%)')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(90)
                            ->default(0)
                            ->suffix('%')
                            ->helperText('Creative field: maximum discount is 90%.'),
                        Forms\Components\Placeholder::make('discounted_preview')
                            ->label('Discounted price preview')
                            ->content(function (Get $get): string {
                                $price = (float) ($get('price') ?? 0);
                                $discount = (int) ($get('discount_percent') ?? 0);
                                $discount = max(0, min(90, $discount));
                                $final = $price - ($price * $discount / 100);

                                return number_format($final, 0, ',', '.') . ' VND';
                            }),
                        Forms\Components\FileUpload::make('image_path')
                            ->label('Cover image')
                            ->image()
                            ->disk('public')
                            ->directory('products')
                            ->maxFiles(1)
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('description')
                            ->label('Description')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Image'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('VND', locale: 'vi_VN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_percent')
                    ->label('Discount')
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('discounted_price')
                    ->label('Discounted price')
                    ->money('VND', locale: 'vi_VN'),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'gray' => 'draft',
                        'success' => 'published',
                        'danger' => 'out_of_stock',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->options(Category::query()->pluck('name', 'id')->all())
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
