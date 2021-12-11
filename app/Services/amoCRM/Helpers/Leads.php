<?php


namespace App\Services\amoCRM\Helpers;

use App\Services\amoCRM\Client;
use Illuminate\Support\Facades\Log;

abstract class Leads
{
    public static function searchByStatus($contact, $client, int $pipeline_id, int $status_id) : ?array
    {
        $leads = [];

        if($contact->leads) {

            foreach ($contact->leads as $lead) {

                if ($lead->status_id == $status_id && $lead->pipeline_id == $pipeline_id) {

                    $lead = $client->service
                        ->leads()
                        ->find($lead->id);

                    $leads = array_merge($leads, $lead);
                }
            }
        }
        return $leads;
    }

    //поиск активных в воронке
    public static function search($contact, $client, int $pipeline_id = null)
    {
        $leads = [];

        if($contact->leads) {

            foreach ($contact->leads as $lead) {

                if ($lead->status_id != 143 &&
                    $lead->status_id != 142) {

                    if($pipeline_id != null && $lead->pipeline_id == $pipeline_id) {

                        return $client->service
                            ->leads()
                            ->find($lead->id);
                    }
                }
            }
        }
    }

    public static function create($contact, array $params, array $fields)
    {
        $lead = $contact->createLead();

        if(count($params) > 0) {

            foreach ($params as $key => $param) {

                $lead->$key = $param;
            }
        }

        if(count($fields) > 0) {

            foreach ($params as $fieldsName => $fieldValue) {

                if(strpos($fieldsName, 'Дата') == true) {

                    $lead->cf($fieldsName)->setData($fieldValue);
                } else
                    $lead->cf($fieldsName)->setValue($fieldValue);
            }
        }

        $lead->save();

        return $lead;
    }

    public static function update($lead, array $params, array $fields)
    {
        try {

            if($fields) {

                foreach ($fields as $key => $field) {

                    if(strpos($key, 'Дата')  === false)

                        $lead->cf($key)->setValue($field);
                    else
                        $lead->cf($key)->setDate($field);
                }
            }

            if(count($params) > 0) {

                foreach ($params as $key => $param) {

                    $lead->$key = $param;
                }
            }

            $lead->save();

            return $lead;

        } catch (\Exception $exception) {

            Log::error(__METHOD__. ' : ошибка обновления '.$exception->getMessage(). ' , сделка : '.$lead->id);
        }
    }

    public static function get($client, $id)
    {
        try {

            $lead = $client->service->leads()->find($id);

            return $lead;

        } catch (\Exception $exception) {

            sleep(2);

            Log::error(__METHOD__. ' : '.$exception->getMessage(). ' , сделка : '.$id);
        }
    }
}
