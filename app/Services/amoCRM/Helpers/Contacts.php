<?php

namespace App\Services\amoCRM\Helpers;

use App\Models\Api\Viewer;
use App\Services\amoCRM\Client;

abstract class Contacts extends Client
{
    public static function search($phone, $client)
    {
        $contacts = $client->service
            ->contacts()
            ->searchByPhone(substr($phone, -10));

        return $contacts ?? null;
    }

    public static function update($contact, $arrayFields = [], $arrayParams = [])
    {
        if(count($arrayParams) > 0) {

            foreach ($arrayFields as $fieldsName => $fieldValue) {

                if(strpos($fieldsName, 'Дата') == true) {

                    $contact->cf($fieldsName)->setData($fieldValue);
                } else
                    $contact->cf($fieldsName)->setValue($fieldValue);
            }
        }
        return $contact->save();
    }

    public static function create(Client $amoapi, $name = 'Неизвестно')
    {
        $contact = $amoapi->service
            ->contacts()
            ->create();

        $contact->name = $name;
        $contact->save();

        return $contact;
    }

    public static function get($client, $id)
    {
        return $client->service->contacts()->find($id);
    }
}
