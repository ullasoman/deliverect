<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerOrderDeliveryLocation extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'partner_id',
        'job_id',
        'order_id',
        'company',
        'name',
        'street',
        'street_number',
        'postal_code',
        'city',
        'delivery_remarks',
        'phone',
        'latitude',
        'longitude',
    ];
}
