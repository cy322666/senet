<?php

namespace App\Services\Senet;

use Illuminate\Support\Facades\Http;

class Auth
{
    public static function auth()
    {
        $url = '';

        $response = Http::withHeaders(self::getHeaders())
            ->post($url, []);

        if($response->status() !== 200) {

            print_r($response->body());exit;

        } else {

            self::setBearerToken($response->body());
        }
    }

    private static function getHeaders() : array
    {
        return [
            'Authorization'  => 'Key '.env('SENET_APP_KEY'),
            'Content-Type' =>  'application/json',
        ];
    }

    private static function setBearerToken($token)
    {
        //local cache
    }
}
