<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuoteRequestResource\Pages;
use App\Models\QuoteRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QuoteRequestResource extends Resource
{
    protected static ?string $model = QuoteRequest::class;
    protected static ?string $navigationIcon = null;
    protected static ?string $navigationGroup = 'B2B & Inquiries';
    protected static ?string $navigationLabel = 'Project Quotations';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Quotation Details')
                    ->schema([
                        Forms\Components\TextInput::make('request_number')->disabled()->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'new' => 'New Inbound',
                                'contacted' => 'Contacted / Meeting Scheduled',
                                'quoted' => 'Quotation Sent',
                                'approved' => 'Quotation Approved',
                                'closed' => 'Closed / Contract Signed',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('service_type')->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Client Information')
                    ->schema([
                        Forms\Components\TextInput::make('customer_name')->required(),
                        Forms\Components\TextInput::make('company_name'),
                        Forms\Components\TextInput::make('phone')->required(),
                        Forms\Components\TextInput::make('email'),
                    ])->columns(2),

                Forms\Components\Section::make('Scope & Specifications')
                    ->schema([
                        Forms\Components\Textarea::make('project_description')->rows(4)->required()->columnSpanFull(),
                        Forms\Components\Textarea::make('notes')->rows(3)->label('Internal Estimator Notes')->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('request_number')->searchable()->sortable()->weight('bold'),
                Tables\Columns\TextColumn::make('customer_name')->searchable(),
                Tables\Columns\TextColumn::make('company_name')->searchable(),
                Tables\Columns\TextColumn::make('phone')->searchable(),
                Tables\Columns\TextColumn::make('service_type')->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'danger',
                        'contacted' => 'warning',
                        'quoted' => 'info',
                        'approved' => 'primary',
                        'closed' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'new' => 'New Inbound',
                        'contacted' => 'Contacted',
                        'quoted' => 'Quoted',
                        'approved' => 'Approved',
                        'closed' => 'Closed',
                    ]),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageQuoteRequests::route('/'),
        ];
    }
}
