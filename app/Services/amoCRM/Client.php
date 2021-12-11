<?php


namespace App\Services\amoCRM;

use App\Models\Account;
use App\Services\amoCRM\Helpers\Contacts;
use App\Services\amoCRM\Helpers\Leads;
use App\Services\amoCRM\Helpers\Notes;
use Illuminate\Support\Facades\Log;
use Ufee\Amo\Base\Storage\Oauth\AbstractStorage;
use Ufee\Amo\Base\Storage\Oauth\FileStorage;
use Ufee\Amo\Oauthapi;
use App\Services\AccountStorage;

class Client
{
    public $service;

    public function init(string $widget = null): Client
    {
        $this->service = Oauthapi::setInstance([
            'domain' => env('AMO_DOMAIN'),
            'client_id' => env('AMO_CLIENT_ID'),
            'client_secret' => env('AMO_CLIENT_SECRET'),
            'redirect_uri' => env('AMO_REDIRECT_URI'),
        ]);

        \Ufee\Amo\Services\Account::setCacheTime(3600);

        $this->service->queries->setDelay(1);
        $this->service->queries->cachePath(storage_path('amocrm/cache'));

        try {

            $this->service->account;

        } catch (\Exception $exception) {

            try {

                $this->service->fetchAccessToken(env('AMO_CODE'));

            } catch (\Exception $exception) {

                $this->service->refreshAccessToken();
            }
        }

        return $this;
    }
}
