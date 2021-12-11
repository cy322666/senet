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
    /**
     * подгрузка новых клиентов в бд
     */
    public function account()
    {
        $accounts = (new AccountCollection())->all();

        $accounts = array_slice((array)$accounts, -30);

        foreach ($accounts as $account) {

            $model = Account::where('login', $account['login'])->first();

            if(!$model) {

                $model = new Account();

                $model->birth_date = $account['birth_date'];
                $model->first_name = $account['name'];
                $model->last_name  = $account['last_name'];
                $model->login      = $account['login'];
                $model->registration_date = $account['registration_date'];
                $model->current_date = $account['last_login_date'];
                $model->count_session     = $account['number_of_visits'];

                $model->save();
            }
        }
    }

    /**
     * подгружаем активность за период
     */
    public function activity()
    {
        $activities = (new ActivityCollection())->all();

        foreach ($activities as $activity) {

            $model = Account::where('login', $activity['User login'])->first();

            if($model) {

                $model->spent_sale = $activity['Spent sum'];
                $model->avg_sale = $activity['Average check'];
                $model->sum_sale = explode('.', $activity['Amount of replenishment'])[0];

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
    public function send()
    {
        $accounts = Account::where('lead_id', null)->get();

        if($accounts->count() > 0) {

            $amocrm = (new Client())->init();

            foreach ($accounts as $account) {

                if(strpos($account->login, '+7')  === false) continue;

                if($account->count_session == 0 || $account->count_session == null)  {

                    $account->delete();

                    continue;
                }

                $contact = Contacts::search(substr($account->login, -10), $amocrm);

                if($contact == null) {

                    $contact = Contacts::create($amocrm, $account->first_name.' '.$account->last_name);
                } else {

                    $lead = Leads::search($contact, $amocrm);
                }

                $contact = Contacts::update($contact, [
                    'Телефон'       => $account->login,
                    'Дата рождения' => $account->birth_date,

                ], ['name' => $account->first_name.' '.$account->last_name]);

                if(empty($lead)) {

                    $lead = Leads ::create($contact, [
                        'status_id' => env('AMO_STATUS_DAY_1'),
                        'name'      => $account->first_name.' '.$account->last_name,
                    ], []);
                }
//dd($account->spent_sale.' => '.explode('.', $account->spent_sale)[0]);
                $lead = Leads::update($lead, [
                    'status_id' => Account::getStatusId($account->count_session),
                    'sale'      => $account->sum_sale,
                ], [
                    'Дата регистрации' => $account->registration_date,
                    'Средний чек' => $account->avg_sale,
                    'Сумма потраченных денег' => $account->spent_sale,
                    'Количество сессий' => $account->count_session,
                    'Дата последнего посещения' => $account->current_date,
                    'Среднее время сеанса (час)' => $account->avg_session,
                    'Понедельник' => $account->monday,
                    'Вторник' => $account->tuesday,
                    'Среда' => $account->wednesday,
                    'Четверг' => $account->thursday,
                    'Пятница' => $account->friday,
                    'Суббота' => $account->saturday,
                    'Воскресенье' => $account->sunday,
                ]);

                $account->lead_id = $lead->id;
                $account->status_id = $lead->status_id;
                $account->contact_id = $contact->id;
                $account->status = 'Отправлено';
                $account->save();

                unset($account); unset($contact); unset($lead);
            }
        }
    }
}
