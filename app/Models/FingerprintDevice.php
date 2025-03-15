<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FingerprintDevice extends Model
{
    protected $table = 'fingerprint_devices';

    protected $fillable = [
        'name',
        'ip',
        'port',
        'user',
        'password',
    ];

}
