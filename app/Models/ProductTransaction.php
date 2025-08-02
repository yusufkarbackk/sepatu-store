<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'booking_trx_id',
        'city',
        'post_code',
        'address',
        'quantity',
        'sub_total_amount',             
        'grand_total_amount',
        'discount_amount',
        'is_paid',
        'shoe_id',
        'shoe_size',
        'promo_code_id',
        'proof',                                                                
    ];
    public static function generateUniqueTrxId()
    {
        $prefix = '55';
        do {
            $randomString = $prefix . mt_rand(1000, 9999);
        } while (self::where('booking_trx_id', $randomString)->exists());
        return $randomString;
    }

    public function shoe()
    {
        return $this->belongsTo(Shoe::class);
    }
    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }
}
