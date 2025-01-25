<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Transaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'listing_id',
        'start_date',
        'end_date',
        'price_per_day',
        'total_days',
        'fee',
        'total_price',
        'status',
    ];

    public function setListingIdAttribute($value)
    {
        $listing = Listing::find($value);
        $totalDays = Carbon::createFromDate($this->start_date)->diffInDays($this->attributes['end_date']) + 1;
        $totalPrice = $listing->price_per_day * $totalDays;
        $fee = $totalPrice * 0.1;

        $this->attributes['listing_id'] = $value;
        $this->attributes['total_days'] = $totalDays;
        $this->attributes['price_per_day'] = $listing->price_per_day;
        $this->attributes['total_price'] = $totalPrice + $fee;
        $this->attributes['fee'] = $fee;

    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }
}
