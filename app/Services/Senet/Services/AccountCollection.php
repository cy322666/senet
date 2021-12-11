<?php


namespace App\Services\Senet\Services;


use App\Services\Senet\Services\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AccountCollection extends Collection
{
    public function all(string $filterdate = null)
    {
        $url = 'https://'.env('SENET_SUBDOMAIN').'.api.enes.tech/account/?format=json';

        $headers = self::getHeaders();

        $response = Http::withHeaders($headers)
            ->get($url, []);

        if($response->status() !== 200) {

            print_r($response->body());exit;

        } else {

            $response = json_decode($response->body(), true);

            if(!empty($response['message'])) {

                dd($response['message']);
            } else
                return $response;
        }
    }
}
