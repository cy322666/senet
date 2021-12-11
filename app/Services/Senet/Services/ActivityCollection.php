<?php


namespace App\Services\Senet\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ActivityCollection extends Collection
{
    public function all()
    {
        $url = 'https://'.env('SENET_SUBDOMAIN').'.api.enes.tech/reports/user_activity/?format=json';//&from_date=2019-01-01T12:22:22-03&office_id=1&to_date='.date('Y-m-d').'T00:00:00';

        $response = Http::withHeaders(self::getHeaders())
            ->get($url, []);

        if($response->status() !== 200) {

            Log::error(__METHOD__ .json_encode($response->body()));

        } else
            return collect(json_decode($response->body(), true));
    }
}
