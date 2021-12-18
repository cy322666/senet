<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'birth_date',
        'first_name',
        'last_name',
        'login',
        'registration_date',
        'sum_sale',
        'current_date',
        'count_session',
    ];

    public static function getLastDays(string $lastDays) : int
    {
        $last_date = date('Y-m-d', strtotime($lastDays));

        $seconds = abs(strtotime(date('Y-m-d') - $last_date));

        return round(floor($seconds / 86400));
    }

    public static function getStatusId(int $count_sessions)
    {
        return $count_sessions < 10 ? env('AMO_STATUS_DAY_'.$count_sessions) : env('AMO_STATUS_DAY_10');
    }

    public static function getPipelineId(int $count_sessions)
    {
        return $count_sessions < 10 ? env('AMO_PIPELINE_ID') : env('AMO_PIPELINE_ID_10');
    }
}
