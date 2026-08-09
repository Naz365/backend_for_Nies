<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = null;
    protected static ?string $navigationGroup = 'E-Commerce & Orders';
    protected static ?string $navigationLabel = 'Customer Orders';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Order Information')
                    ->schema([
                        Forms\Components\TextInput::make('order_number')
                            ->disabled()
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending Review',
                                'confirmed' => 'Confirmed',
                                'processing' => 'Processing / Packaging',
                                'shipped' => 'Dispatched / In Transit',
                                'delivered' => 'Delivered',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required(),
                        Forms\Components\Select::make('payment_status')
                            ->options([
                                'unpaid' => 'Unpaid (Pending COD)',
                                'paid' => 'Paid in Full',
                                'failed' => 'Payment Failed',
                                'refunded' => 'Refunded',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('payment_method')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Customer & Delivery Snapshot')
                    ->schema([
                        Forms\Components\TextInput::make('customer_name')->required(),
                        Forms\Components\TextInput::make('customer_phone')->required(),
                        Forms\Components\TextInput::make('customer_email'),
                        Forms\Components\Textarea::make('shipping_address')->required()->columnSpanFull(),
                    ])->columns(3),

                Forms\Components\Section::make('Financial Breakdown (৳ BDT)')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')->numeric()->prefix('৳')->required(),
                        Forms\Components\TextInput::make('shipping_fee')->numeric()->prefix('৳')->default(0),
                        Forms\Components\TextInput::make('discount_amount')->numeric()->prefix('৳')->default(0),
                        Forms\Components\TextInput::make('total_amount')->numeric()->prefix('৳')->required(),
                    ])->columns(4),

                Forms\Components\Section::make('Internal Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')->rows(2)->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')->searchable()->sortable()->weight('bold'),
                Tables\Columns\TextColumn::make('customer_name')->searchable(),
                Tables\Columns\TextColumn::make('customer_phone')->searchable(),
                Tables\Columns\TextColumn::make('total_amount')->money('BDT')->sortable(),
                Tables\Columns\TextColumn::make('payment_method')->badge(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'unpaid' => 'warning',
                        'failed' => 'danger',
                        'refunded' => 'gray',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'delivered' => 'success',
                        'shipped' => 'info',
                        'processing' => 'primary',
                        'confirmed' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ManageOrders::route('/'),
        ];
    }
}
