<?php

namespace App\Http\Controllers\Apis;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UtilityController extends Controller
{
    public function generateTransactionID($service)
    {
        $prefix = '';
        $transactionID = 0;
        switch ($service) {
            case 1:
                //airtime
                $id = random_int(1000, 999999999999);
                $length = strlen((string)$id);
                if ($length < 12 || $length > 12) {
                    $id = $this->generateTransactionID(1);
                }
                $transactionID = 'AIR-' . $id;
                break;
            case 2:
                // data
                $id = random_int(1000, 999999999999);
                $length = strlen((string)$id);
                if ($length < 12 || $length > 12) {
                    $id = $this->generateTransactionID(2);
                }
                $transactionID = 'DAT-' . $id;
                break;
            case 3:
                // Power
                $id = random_int(1000, 999999999999);
                $length = strlen((string)$id);
                if ($length < 12 || $length > 12) {
                    $id = $this->generateTransactionID(3);
                }
                $transactionID = 'POW-' . $id;
                break;
            case 4:
                // TV
                $id = random_int(1000, 999999999999);
                $length = strlen((string)$id);
                if ($length < 12 || $length > 12) {
                    $id = $this->generateTransactionID(4);
                }
                $transactionID = 'TV-' . $id;
                break;
        }

        return $transactionID;
    }

    public function generatePassword($length = 8)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        return $string;
    }

    public function hashPassword($string)
    {
        return bcrypt($string);
    }

    public function verifyPayment($paymentReference, $transactionType, $transactionID)
    {
        Log::info('Lets try to verify payment from paystack.');
        $verified = 0;
        $result = array();
        $key = env('PAYSTACK_SECRET_KEY');

        $url = 'https://api.paystack.co/transaction/verify/' . $paymentReference;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            ['Authorization: Bearer ' . $key]
        );
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $request = curl_exec($ch);

        if (curl_errno($ch)) {
            // $resp['msg'] = curl_error($ch);
            $verified = curl_errno($ch);
            Log::info('cURL error occured while trying to verify payment.');
            Log::error(curl_error($ch));
            // change status for the transaction to failed so in case the payment actually went through, it can be retried.
            switch ($transactionType) {
                case 'airtime':
                    $transaction = \App\Models\AirtimeTransaction::on('mysql::write')->find($transactionID);
                    $transaction->update(['status' => 3]);
                    break;
                case 'data':
                    $transaction = \App\Models\DataTransaction::on('mysql::write')->find($transactionID);
                    $transaction->update(['status' => 3]);
                    break;
                case 'power':
                    $transaction = \App\Models\PowerTransaction::on('mysql::write')->find($transactionID);
                    $transaction->update(['status' => 3]);
                    break;
                case 'tv':
                    $transaction = \App\Models\TVTransaction::on('mysql::write')->find($transactionID);
                    $transaction->update(['status' => 3]);
                    break;
            }
        } else {
            if ($request) {
                $result = json_decode($request, true);

                if (isset($result["data"]) && $result["data"]["status"] == "success") {
                    // at this point, payment has been verified.
                    // launch an event on a queue to send email of receipt to user.
                    $verified = 100;
                } else {
                    // $resp['msg'] = 'Transaction not found!';
                    $verified = 404;
                }
            } else {
                // $resp['msg'] = 'Unable to verify transaction!';
                $verified = 503;
            }
        }
        curl_close($ch);

        return $verified;
    }

    public function generateRandomUserID()
    {
        $digits_needed = 12;
        $random_number = ''; // set up a blank string
        $count = 0;
        while ($count < $digits_needed) {
            $random_digit = mt_rand(0, 13);
            $random_number .= $random_digit;
            $count++;
        }
        return $random_number;
    }


    public function getUserByID($user_id)
    {
        $user = \App\Models\User::on('mysql::read')->find($user_id);

        if ($user && $user->count() > 0) {
            return $user->id;
        }

        return -1;
    }

    public function getTodaysPendingTransactionsCount($userID, $serviceID)
    {
        $pendingTransactions = $this->getTodaysPendingTransactions($userID, $serviceID);
        if ($pendingTransactions == "s404" || $pendingTransactions == "st404") {
            return $pendingTransactions;
        }
        return count($pendingTransactions->toArray());
    }



}
