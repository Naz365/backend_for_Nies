<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FireSafetyAssetResource\Pages;
use App\Models\FireSafetyAsset;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FireSafetyAssetResource extends Resource
{
    protected static ?string $model = FireSafetyAsset::class;
    protected static ?string $navigationIcon = null;
    protected static ?string $navigationGroup = 'Field Engineering & Services';
    protected static ?string $navigationLabel = 'Equipment Asset Tracking';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Asset Identity')
                    ->schema([
                        Forms\Components\TextInput::make('asset_tag')->disabled()->required(),
                        Forms\Components\TextInput::make('serial_number')->label('Manufacturer Serial No.'),
                        Forms\Components\TextInput::make('asset_name')->required(),
                        Forms\Components\Select::make('equipment_type')
                            ->options([
                                'extinguisher' => 'Fire Extinguisher Cylinder',
                                'alarm_panel' => 'Fire Alarm Panel',
                                'smoke_detector' => 'Smoke / Heat Detector',
                                'hydrant_valve' => 'Hydrant Valve & Landing',
                                'pump' => 'Fire Jockey / Main Pump',
                                'fm200_cylinder' => 'FM-200 / Gas Suppression Cylinder',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('capacity_rating')->label('Capacity / Rating (e.g. 6kg, 45L)'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Operational & Valid',
                                'needs_refill' => '⚠️ Refilling Required',
                                'inspection_overdue' => '🚨 Inspection Overdue',
                                'decommissioned' => 'Decommissioned / Retired',
                            ])
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Customer & Installation Location')
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('location_in_building')
                            ->placeholder('e.g. 3rd Floor, Server Room North Wall, Zone B')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Maintenance & Refill Timeline')
                    ->schema([
                        Forms\Components\DatePicker::make('installed_date'),
                        Forms\Components\DatePicker::make('last_serviced_date'),
                        Forms\Components\DatePicker::make('next_service_due_date')->required(),
                        Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('asset_tag')->searchable()->sortable()->weight('bold'),
                Tables\Columns\TextColumn::make('asset_name')->searchable(),
                Tables\Columns\TextColumn::make('customer.name')->label('Customer / Client')->searchable(),
                Tables\Columns\TextColumn::make('equipment_type')->badge(),
                Tables\Columns\TextColumn::make('location_in_building')->searchable(),
                Tables\Columns\TextColumn::make('next_service_due_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'needs_refill' => 'warning',
                        'inspection_overdue' => 'danger',
                        'decommissioned' => 'gray',
                        default => 'secondary',
                    }),
            ])
            ->defaultSort('next_service_due_date', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Operational',
                        'needs_refill' => 'Needs Refill',
                        'inspection_overdue' => 'Inspection Overdue',
                        'decommissioned' => 'Decommissioned',
                    ]),
                Tables\Filters\SelectFilter::make('equipment_type')
                    ->options([
                        'extinguisher' => 'Fire Extinguisher',
                        'alarm_panel' => 'Alarm Panel',
                        'hydrant_valve' => 'Hydrant Valve',
                        'fm200_cylinder' => 'Gas Suppression',
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
            'index' => Pages\ManageFireSafetyAssets::route('/'),
        ];
    }
}
