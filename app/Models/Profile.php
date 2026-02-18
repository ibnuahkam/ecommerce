<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Profile extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'profile'; // atau 'profiles' kalau nama tabel plural

    protected $fillable = [
        'user_id',
        'address',
        'city',
        'province',
        'postal_code',
        'country',
        'avatar',
        'birth_date',
        'gender',
        'phone'
    ];

    protected $auditInclude = [
        'address',
        'city',
        'province',
        'postal_code',
        'country',
        'avatar',
        'phone'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
