<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;
use Illuminate\Support\Str;

class Product extends Model implements AuditableContract
{
    use HasFactory, Auditable;

    protected $table = 'products';

    protected $fillable = [
        'name',
        'uuid',
        'description',
        'price',
        'stock',
        'thumbnail',
        'user_id',
        'role_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            $product->uuid = (string) Str::uuid();
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function images()
    {
        return $this->hasMany(ProductImages::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(Roles::class);
    }
}
