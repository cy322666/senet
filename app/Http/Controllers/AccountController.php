<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Helpers\Contacts;
use App\Services\amoCRM\Helpers\Leads;
use App\Services\Senet\Services\AccountCollection;
use App\Services\Senet\Services\ActivityCollection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Mockery\Exception;

class AccountController extends Controller
{
    /**
     * подгрузка новых клиентов в бд
     */
    public function account()
    {
        $accounts = (new AccountCollection())->all();

        $accounts = array_slice((array)$accounts, -50);
        //echo '<pre>';print_r($accounts);echo '</pre>';exit;
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
     *
     * //TZ Asia/Yekaterinburg
     */
    public function activity()
    {
        $activities = (new ActivityCollection())->all();

        $activities = array_slice((array)$activities, -200);
       // echo '<pre>';print_r($activities);echo '</pre>';exit;
        foreach ($activities as $activity) {

            try {

                $model = Account::where('login', $activity['User login'])->first();

                if(empty($model) || $model->current_date == null) continue;

                $datetime_current = Carbon::parse($model->current_date)->format('Y-m-d H:i:s');

                $datetime_last = Carbon::parse($activity['Last visit date'])->format('Y-m-d H:i:s');

//                echo '<pre>';print_r($datetime_current.' < '.$datetime_last);echo '</pre>';

                if($datetime_current < $datetime_last) {

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
            } catch (\Exception $exception) {

                Log::warning(__METHOD__.' : '.$exception->getMessage().' '.$exception->getLine());
            }
        }
    }

    //отправка новых
    public function send()
    {
        $accounts = Account::where('updated_at', '>', Carbon::now()->subMinutes(15))->get();

        if($accounts->count() > 0) {

            $amocrm = (new Client())->init();

            foreach ($accounts as $account) {

                try {

                    if(strpos($account->login, '+7')  === false) continue;

                    if($account->count_session == 0 || $account->count_session == null)  {

                        $account->delete();

                        continue;
                    }

                    if($account->contact_id == null) {

                        $contact = Contacts::search(substr($account->login, -10), $amocrm);
                    } else
                        $contact = $amocrm->service->contacts()->find($account->contact_id);

                    if(empty($contact)) {

                        $contact = Contacts::create($amocrm, $account->first_name.' '.$account->last_name);
                    }

                    if($account->lead_id == null) {

                        $lead = Leads::search($contact, $amocrm);
                    } else {

                        $lead = $amocrm->service->leads()->find($account->lead_id);
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

                } catch (\Exception $exception) {

                    Log::warning(__METHOD__.' : '.$exception->getMessage().' '.$exception->getLine());
                }
            }
        }
    }
}
