<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [];

    public static function getLastDays(string $lastDays) : int
    {
        $last_date = date('Y-m-d', strtotime($lastDays));

        $seconds = abs(strtotime(date('Y-m-d') - $last_date));

        return round(floor($seconds / 86400));
    }
}
