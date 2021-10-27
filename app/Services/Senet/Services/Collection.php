<?php


namespace App\Services\Senet\Services;


class Collection
{
    protected function getHeaders() : array
    {
        return [
            'Authorization' => 'Bearer ',//TODO get bearer cache
            'Content-Type'  =>  'application/json',
        ];
    }
}
