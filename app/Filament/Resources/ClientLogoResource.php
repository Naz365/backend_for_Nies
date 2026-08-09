<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientLogoResource\Pages;
use App\Models\ClientLogo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ClientLogoResource extends Resource
{
    protected static ?string $model = ClientLogo::class;
    protected static ?string $navigationIcon = null;
    protected static ?string $navigationGroup = 'Brand & Partners';
    protected static ?string $navigationLabel = 'Client Partner Logos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Client / Partner Name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('logo_path')
                    ->label('Logo Image Path / URL')
                    ->required(),
                Forms\Components\TextInput::make('website_url')
                    ->label('Website URL')
                    ->url()
                    ->maxLength(255),
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->label('Display on Website')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo_path')->label('Logo'),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('website_url')->limit(30),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
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
            'index' => Pages\ManageClientLogos::route('/'),
        ];
    }
}
