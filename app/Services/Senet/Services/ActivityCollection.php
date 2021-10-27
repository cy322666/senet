<?php


namespace App\Services\Senet\Services;


use App\Services\Yandex\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ActivityCollection extends Collection
{
    private $auth;

    const URL = 'https://fleet-api.taxi.yandex.net/v1/parks/driver-profiles/list';

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function all()
    {
        $url = '';

        $response = Http::withHeaders(self::getHeaders())
            ->post($url, []);

        if($response->status() !== 200) {

            print_r($response->body());exit;

        } else
            return collect($response);
    }
}
