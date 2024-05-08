<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerOrder extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'partner_id',
        'order_id',
        'job_id',
        'account',
        'pickup_time',
        'transport_type',
        'channel_order_display_id',
        'delivery_time',
        'package_size',
        'order_description',
        'order_is_already_paid',
        'driver_tip',
        'amount',
        'payment_type'
    ];
}
