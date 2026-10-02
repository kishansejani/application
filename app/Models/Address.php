<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'recipient_name',
        'recipient_phone',
        'house_no',
        'street_address',
        'landmark',
        'city',
        'state',
        'pincode',
        'latitude',
        'longitude',
        'formatted_address',
        'is_default',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFullAddressAttribute(): string
    {
        if (!empty($this->formatted_address)) {
            return $this->formatted_address;
        }

        $parts = array_filter([
            $this->house_no,
            $this->street_address,
            $this->landmark ? 'Near ' . $this->landmark : null,
            $this->city,
            $this->state,
            $this->pincode,
        ]);

        return implode(', ', $parts);
    }
}
