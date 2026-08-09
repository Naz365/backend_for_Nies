<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = null;
    protected static ?string $navigationGroup = 'Store & Equipment Catalog';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('sku')
                            ->label('SKU Code')
                            ->placeholder('e.g. EXT-ABC-6KG')
                            ->maxLength(100),
                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->required(),
                                Forms\Components\TextInput::make('slug')->required(),
                            ]),
                    ])->columns(2),

                Forms\Components\Section::make('Pricing & Inventory (BDT ৳)')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->label('Selling Price (৳ BDT)')
                            ->numeric()
                            ->prefix('৳')
                            ->required()
                            ->default(0.00),
                        Forms\Components\TextInput::make('compare_at_price')
                            ->label('Original Regular Price (৳ BDT)')
                            ->numeric()
                            ->prefix('৳'),
                        Forms\Components\TextInput::make('stock_quantity')
                            ->label('Stock Quantity')
                            ->numeric()
                            ->default(100)
                            ->required(),
                        Forms\Components\Toggle::make('track_inventory')
                            ->label('Track Inventory Availability')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Media & Details')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->directory('products')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('specifications')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Publishing & Visibility')
                    ->schema([
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Feature on Homepage Store')
                            ->default(false),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                            ])
                            ->default('published')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('SEO Settings')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title'),
                        Forms\Components\Textarea::make('meta_description')->rows(2),
                    ])->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('sku')->label('SKU')->searchable(),
                Tables\Columns\TextColumn::make('price')->money('BDT')->sortable(),
                Tables\Columns\TextColumn::make('stock_quantity')->label('Stock')->sortable(),
                Tables\Columns\IconColumn::make('is_featured')->boolean(),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageProducts::route('/'),
        ];
    }
}
