<?php


namespace App\Console\Commands;


use App\Mail\ReminderDemoVersion;
use DateInterval;
use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendReminderMailDemoVersion
{
    /**
     * @throws Exception
     */
    public function __invoke()
    {
        $organisations = DB::table('organisations')->whereNotNull('demoPeriod')->get();

        foreach ($organisations as $organisation) {
            try {
                $startPeriod = new DateTime($organisation->demoPeriod);
                $endPeriod = $startPeriod->add(new DateInterval('P30D'));
                $today = new DateTime();
                $difference = (int) $endPeriod->diff($today)->format('%d');

                if ($difference == 7 || $difference == 3 || $difference == 1 || $difference == 0) {

                    $userAdmin = DB::table('users')
                        ->where('organisationId', $organisation->id)
                        ->where('role_id', '=', 1)
                        ->first();

                    switch ($difference) {
                        case 7:
                            $timeMessage = '<strong> over 7 </strong> dagen verloopt';
                            break;
                        case 3:
                            $timeMessage = '<strong> over 3 </strong> dagen verloopt';
                            break;
                        case 1:
                            $timeMessage = '<strong> over 1 </strong> dagen verloopt';
                            break;
                        default:
                            $timeMessage = '<strong> is verlopen </strong>';
                            break;
                    }

                    $message = 'Met dit bericht laten wij u weten dat uw proefperiode' . $timeMessage . ' . Wilt u gebruik blijven maken van het BIM ex-plan applicatie dan kunt hier uw acount verlengen.';
                    $mailSubject = 'Proefperiode';
                    $button = 'Account verlengen';


                    // sendMail
                    Mail::to($userAdmin->email)
                        ->send(new ReminderDemoVersion($userAdmin, $mailSubject, $message, $button));
                }

            } catch (\Exception $e) {
                return response($e->getMessage(), 400);
            }
        }
        return json_encode('success');
    }

}
