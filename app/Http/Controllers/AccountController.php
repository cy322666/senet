<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Helpers\Contacts;
use App\Services\amoCRM\Helpers\Leads;
use App\Services\Senet\Services\AccountCollection;
use App\Services\Senet\Services\ActivityCollection;
use Mockery\Exception;

class AccountController extends Controller
{
    public function account()
    {
        $accounts = (new AccountCollection())->all();

        foreach ($accounts as $account) {

            $model = Account::where('login', $account['login'])->first();

            if(!$model) $model = new Account();

            $model->birth_date = $account['birth_date'];
            $model->first_name  = $account['name'];
            $model->last_name  = $account['last_name'];
            $model->login      = $account['login'];
            $model->registration_date = $account['registration_date'];
            $model->sum_sale        = explode('.', $account['account_amount'])[0];
            $model->current_date = $account['last_login_date'];
            $model->count_session   = $account['number_of_visits'];

            $model->save();
        }
    }

    public function activity()
    {
        $activities = (new ActivityCollection())->all();

        foreach ($activities as $activity) {

            $model = Account::where('login', $activity['User login'])->first();

            if($model) {

                $model->spent_sale = $activity['Spent sum'];
                $model->avg_sale = $activity['Average check'];


                $model->avg_session = $activity['Average session time (hour)'];

                $model->monday = $activity['Monday'];
                $model->tuesday = $activity['Tuesday'];
                $model->wednesday = $activity['Wednesday'];
                $model->thursday = $activity['Thursday'];
                $model->friday = $activity['Friday'];
                $model->saturday = $activity['Saturday'];
                $model->sunday = $activity['Sunday'];

                $model->status = 'Обогащено';
                $model->save();
            }
        }
    }

    //отправка новых
    public function send_account()
    {
        $accounts = Account::where('status', 'Добавлено')->get();

        if($accounts->count() > 0) {

            $amocrm = (new Client())->init();

            foreach ($accounts as $account) {

                $contact = Contacts::search($account->phone, $amocrm) ?? Contacts::create($amocrm, $account->first_name.' '.$account->last_name);

                $contact = Contacts::update($contact, [
                    'Телефон'       => $account->login,
                    'Дата рождения' => $account->birth_date,

                ], ['name' => $account->first_name.' '.$account->last_name]);

                $lead = Leads::create($contact, [
                    'Дата регистрации' => $account->registration_date,
                ], [
                    'status_id'   => env('AMO_STATUS_DAY_1'),
                    'pipeline_id' => env('AMO_PIPELINE_ID'),
                    'name'        => 'Новый лид Senet',
                ]);

                $account->pipeline_id = $lead->pipeline_id;
                $account->lead_id = $lead->id;
                $account->contact_id = $contact->id;
                $account->status = 'Отработано';
                $account->save();
            }
        }
    }
}
