<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceRequestResource\Pages;
use App\Models\ServiceRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceRequestResource extends Resource
{
    protected static ?string $model = ServiceRequest::class;
    protected static ?string $navigationIcon = null;
    protected static ?string $navigationGroup = 'Field Engineering & Services';
    protected static ?string $navigationLabel = 'Service Requests';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Service Request Information')
                    ->schema([
                        Forms\Components\TextInput::make('request_number')->disabled()->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending_review' => 'Pending Review',
                                'assigned' => 'Technician Assigned',
                                'site_visit_scheduled' => 'Site Visit Scheduled',
                                'inspection_completed' => 'Inspection Completed',
                                'quoted' => 'Quoted for Refill / Parts',
                                'resolved' => 'Service Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required(),
                        Forms\Components\Select::make('service_category')
                            ->options([
                                'extinguisher_refill' => 'Fire Extinguisher Refilling & Hydro Testing',
                                'fire_safety_inspection' => 'Fire Safety Audit & Inspection',
                                'fire_alarm_servicing' => 'Fire Alarm System Servicing',
                                'fire_hydrant_pump' => 'Fire Hydrant & Pump Maintenance',
                                'cctv_servicing' => 'CCTV Surveillance Servicing',
                                'access_control_maintenance' => 'Access Control Maintenance',
                                'amc_service' => 'Annual Maintenance Contract (AMC)',
                            ])
                            ->required(),
                        Forms\Components\Select::make('urgency')
                            ->options([
                                'low' => 'Low',
                                'normal' => 'Normal',
                                'high' => 'High',
                                'emergency' => '🚨 Emergency Response',
                            ])
                            ->required(),
                    ])->columns(4),

                Forms\Components\Section::make('Customer & Location')
                    ->schema([
                        Forms\Components\TextInput::make('customer_name')->required(),
                        Forms\Components\TextInput::make('company_name'),
                        Forms\Components\TextInput::make('phone')->required(),
                        Forms\Components\TextInput::make('email'),
                        Forms\Components\Textarea::make('location_address')->required()->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Engineering Details & Assignment')
                    ->schema([
                        Forms\Components\Textarea::make('equipment_details')->rows(3)->columnSpanFull(),
                        Forms\Components\TextInput::make('technician_assigned')->label('Assigned Lead Engineer'),
                        Forms\Components\DatePicker::make('scheduled_visit_date'),
                        Forms\Components\Textarea::make('technician_notes')->rows(3)->columnSpanFull(),
                    ])->columns(2),
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
                Tables\Columns\TextColumn::make('service_category')->badge(),
                Tables\Columns\TextColumn::make('urgency')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'emergency' => 'danger',
                        'high' => 'warning',
                        'normal' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'resolved' => 'success',
                        'inspection_completed' => 'primary',
                        'site_visit_scheduled' => 'warning',
                        'assigned' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('technician_assigned')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending_review' => 'Pending Review',
                        'assigned' => 'Assigned',
                        'site_visit_scheduled' => 'Site Visit Scheduled',
                        'inspection_completed' => 'Inspection Completed',
                        'resolved' => 'Resolved',
                    ]),
                Tables\Filters\SelectFilter::make('urgency')
                    ->options([
                        'emergency' => 'Emergency',
                        'high' => 'High',
                        'normal' => 'Normal',
                        'low' => 'Low',
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
            'index' => Pages\ManageServiceRequests::route('/'),
        ];
    }
}
