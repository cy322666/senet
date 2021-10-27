<?php


namespace App\Services\Senet\Services;


use App\Services\Senet\Services\Collection;
use App\Services\Yandex\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AccountCollection extends Collection
{
    const URL = '';

    public function all(string $filterdate = null)
    {
        $headers = self::getHeaders();

        $response = Http::withHeaders($headers)
            ->post(self::URL, []);

        if($response->status() !== 200) {

            print_r($response->body());exit;

        } else {

            $response = json_decode($response->body(), true);

            if(!empty($response['message'])) {

                dd($response['message'].' park_id : '.$this->auth->park_id);
            } else
                return collect($response['driver_profiles']);
        }
    }
}
