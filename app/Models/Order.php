<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class Order extends Model implements AuditableContract
{
    use Auditable;

    protected $fillable = [
        'uuid',
        'buyer_id',
        'total_amount',
        'status'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            $order->uuid = (string) Str::uuid();
        });

        static::creating(function ($order) {

            $order->uuid = (string) \Str::uuid();

            $lastId = self::max('id') + 1;

            $order->order_number = 'INV-' . date('Ymd') . '-' . str_pad($lastId, 5, '0', STR_PAD_LEFT);
        });
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

}
