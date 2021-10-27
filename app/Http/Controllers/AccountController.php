<?php

namespace App\Http\Controllers;

use App\Models\Account;
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

            $model = Account::updateOrCreate(
                //TODO сюда же?
            );

            $model->firstname = $account;
            $model->last_name = $account;
            $model->phone = $account;//login
            $model->registration_date = $account;//registration_date
            $model->sum_sale = $account;//account_amount
            $model->last_login_date = $account;//last_login_date
            $model->count_session = $account;//number_of_visits

            $model->save();

            if($model->isDirty()) {

                //измененная модель уже находившаяся
            }
        }
    }

    public function activity()
    {
        $activities = (new ActivityCollection())->all();

        foreach ($activities as $activity) {

            $model = Account::updateOrCreate([

            ]);

            $model->save();

            if($model->isDirty()) {

                //измененная модель уже находившаяся
            }
        }
    }

    public function send_activity()
    {

    }

    public function send_account()
    {
        $accounts = Account::where('status', 'Добавлено')->get();//TODO два статуса для измененных тоже

        if($accounts->count() > 0) {

            foreach ($accounts as $account) {

                try {
                    $contact = Contacts::search();

                    if(!$contact) {

                        $contact = Contacts::create();

                        $lead = Leads::create();

                    } else
                        $lead = Leads::search();

                    $account->pipeline_id = $lead->pipeline_id;
                    $account->status_id = $lead->status_id;
                    $account->contact_id = $contact->id;
                    $account->status = 'Отработан новый';
                    $account->save();

                } catch (Exception $exception) {

                    $account->status = $exception->getMessage();
                    $account->save();
                }
            }
        }
    }

    public function all()
    {
        \App\Services\Senet\Auth::auth();

        $accounts = (new AccountCollection())->all();

        foreach ($accounts as $account) {

            $model = Account::create();

            $model->firstname = $account;
            $model->last_name = $account;
            $model->phone = $account;//login
            $model->registration_date = $account;//registration_date
            $model->sum_sale = $account;//account_amount
            $model->last_login_date = $account;//last_login_date
            $model->count_session = $account;//number_of_visits

            $model->save();
        }
    }
}
