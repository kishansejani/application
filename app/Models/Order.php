<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'invoice_number',
        'user_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'address_id',
        'delivery_address',
        'delivery_city',
        'delivery_pincode',
        'delivery_lat',
        'delivery_lng',
        'delivery_type',
        'delivery_slot',
        'estimated_delivery_at',
        'subtotal',
        'discount_amount',
        'coupon_code',
        'delivery_charge',
        'tax_amount',
        'total_amount',
        'payment_method',
        'payment_status',
        'transaction_id',
        'order_status',
        'notes',
        'cancellation_reason',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'estimated_delivery_at' => 'datetime',
        'delivery_lat' => 'decimal:8',
        'delivery_lng' => 'decimal:8',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Compute delivery timing logic:
     * If order time is before 12:00 PM -> Deliver within 2 hours
     * If order time is 12:00 PM or later -> Deliver next day morning (9 AM - 12 PM)
     */
    public static function determineDeliverySlot(?Carbon $time = null): array
    {
        $now = $time ?? Carbon::now();
        $cutoff = $now->copy()->setTime(12, 0, 0);

        if ($now->lt($cutoff)) {
            $estTime = $now->copy()->addHours(2);
            return [
                'type' => 'two_hours',
                'slot_en' => 'Today Express Delivery (Within 2 Hours - By ' . $estTime->format('h:i A') . ')',
                'slot_gu' => 'આજે ૨ કલાકમાં ડિલિવરી (' . $estTime->format('h:i A') . ' સુધીમાં)',
                'estimated_at' => $estTime,
            ];
        } else {
            $estTime = $now->copy()->addDay()->setTime(11, 0, 0);
            return [
                'type' => 'next_day',
                'slot_en' => 'Tomorrow Morning Delivery (' . $now->copy()->addDay()->format('d M') . ' Between 09:00 AM - 12:00 PM)',
                'slot_gu' => 'આવતીકાલે સવારે ડિલિવરી (' . $now->copy()->addDay()->format('d M') . ' સવારે ૦૯:૦૦ થી ૧૨:૦૦ વચ્ચે)',
                'estimated_at' => $estTime,
            ];
        }
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->order_status) {
            'pending' => 'bg-amber-100 text-amber-800 border-amber-300',
            'confirmed' => 'bg-blue-100 text-blue-800 border-blue-300',
            'processing' => 'bg-indigo-100 text-indigo-800 border-indigo-300',
            'out_for_delivery' => 'bg-purple-100 text-purple-800 border-purple-300',
            'delivered' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'cancelled' => 'bg-rose-100 text-rose-800 border-rose-300',
            default => 'bg-slate-100 text-slate-800 border-slate-300',
        };
    }

    public function getLocalizedStatusAttribute(): string
    {
        $locale = app()->getLocale();
        $statuses = [
            'pending' => ['en' => 'Pending', 'gu' => 'બાકી છે'],
            'confirmed' => ['en' => 'Confirmed', 'gu' => 'સ્વીકારાયેલ'],
            'processing' => ['en' => 'Processing / Packing', 'gu' => 'તૈયાર થઈ રહ્યું છે'],
            'out_for_delivery' => ['en' => 'Out for Delivery', 'gu' => 'ડિલિવરી માટે નીકળેલ'],
            'delivered' => ['en' => 'Delivered', 'gu' => 'ડિલિવર થઈ ગયું'],
            'cancelled' => ['en' => 'Cancelled', 'gu' => 'રદ થયેલ'],
        ];

        return $statuses[$this->order_status][$locale] ?? $this->order_status;
    }
}
