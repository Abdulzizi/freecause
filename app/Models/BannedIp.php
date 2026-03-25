<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BannedIp extends Model
{
    public $timestamps = false;

    protected $fillable = ['ip', 'reason', 'created_at'];

    protected $table = 'banned_ips';
}
