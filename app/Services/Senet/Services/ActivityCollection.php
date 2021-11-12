<?php


namespace App\Services\Senet\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ActivityCollection extends Collection
{
    public function all()
    {
        $url = 'https://'.env('SENET_SUBDOMAIN').'.api.enes.tech/reports/user_activity/?format=json&from_date=2020-01-01T12:22:22-03&office_id=1&to_date='.date('Y-m-d').'T12:22:22';

        $response = Http::withHeaders(self::getHeaders())
            ->get($url, []);

        if($response->status() !== 200) {

            print_r($response->body());exit;

        } else
            return collect(json_decode($response->body(), true));
    }
}
