<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'customer_name',
        'company_name',
        'phone',
        'email',
        'service_category',
        'location_address',
        'equipment_details',
        'urgency',
        'status',
        'technician_assigned',
        'technician_notes',
        'scheduled_visit_date',
    ];

    protected $casts = [
        'scheduled_visit_date' => 'date',
    ];

    public static function generateRequestNumber(): string
    {
        do {
            $suffix = strtoupper(Str::random(5));
            $candidate = 'SRV-' . date('Y') . '-' . $suffix;
        } while (static::where('request_number', $candidate)->exists());

        return $candidate;
    }
}
