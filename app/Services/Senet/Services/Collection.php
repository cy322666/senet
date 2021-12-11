<?php


namespace App\Services\Senet\Services;


class Collection
{
    protected function getHeaders() : array
    {
        return [
            'Authorization' => 'Key '.env('SENET_APP_KEY'),
            'Content-Type'  =>  'application/json',
        ];
    }
}
