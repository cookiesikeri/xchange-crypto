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
        }

        return $transactionID;
    }

    public function getTodaysPendingTransactionsCount($userID, $serviceID)
    {
        $pendingTransactions = $this->getTodaysPendingTransactions($userID, $serviceID);
        if ($pendingTransactions == "s404" || $pendingTransactions == "st404") {
            return $pendingTransactions;
        }
        return count($pendingTransactions->toArray());
    }

    public function getTodaysPendingTransactions($userID, $serviceID)
    {
        $transactionTable = $this->resolveTransactionTable($serviceID);

        if ($transactionTable == "s404" || $transactionTable == "st404") {
            return $transactionTable;
        }

        $today = date("Y-m-d");
        $pendingTransactions = $transactionTable::on('mysql::read')->where('user_id', $userID)
            ->where('service_id', $serviceID)
            ->where('status', '<>', 2)
            ->where('date_created', 'LIKE', '%' . $today . '%')
            ->where('date_modified', 'LIKE', '%' . $today . '%')
            ->get();
        return $pendingTransactions;
    }

    public function resolveTransactionTable($serviceID)
    {
        $transactionTable = 'unknown';
        $service = strtolower($this->resolveServiceFromID($serviceID));

        switch ($service) {
            case 'airtime':
                $transactionTable = '\App\Models\AirtimeTransaction';
                break;
            case 'data':
                $transactionTable = '\App\Models\DataTransaction';
                break;
            default:
                $transactionTable = $service;
        }

        return $transactionTable;
    }

    public function resolveServiceFromID($serviceID)
    {
        $service = \App\Models\Service::on('mysql::read')->find($serviceID);

        if (!$service) {
            return "s404";
        }

        $serviceType = \App\Models\ServiceType::on('mysql::read')->find($service->service_type_id);

        if (!$serviceType) {
            return "st404";
        }

        Log::info('Service name: ' . $serviceType->name);
        return $serviceType->name;
    }

    public function resolveServiceNameFromID($serviceID, $needsAPI = 0)
    {
        $service = \App\Models\Service::on('mysql::read')->find($serviceID);

        if (!$service) {
            return "s404";
        }

        if (!$needsAPI) {
            return $service->name == "9mobile" ? "Etisalat" : $service->name;
        }

        return $service->api_id;
    }

    public function getUserByGLocatorID($gLocatorID, $email)
    {
        $user = \App\Models\User::on('mysql::write')->where('gLocatorID', $gLocatorID)->orWhere('email', $email)->first();

        if ($user && $user->count() > 0) {
            return $user->id;
        }

        return -1;
    }

    public function getUserByID($user_id)
    {
        $user = \App\Models\User::on('mysql::read')->find($user_id);

        if ($user && $user->count() > 0) {
            return $user->id;
        }

        return -1;
    }

    public function createUser($data)
    {
        $str = $data['password'];
        $data['password'] = $this->hashPassword($data['password']);
        \DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        $user = \App\Models\User::on('mysql::write')->create($data);
        \DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        // fire event to send welcome e-mail to user.
        // \Artisan::call('user:sendmail', [
        //     'id'        => $user->id,
        //     'string'    => $str
        // ]);
        return $user;
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

    public function verifyRavePayment($ref, $transactionType, $transactionID, $mode = 1)
    {
        $amount = 0;
        $resp = 0;
        $transaction = null;
        switch ($transactionType) {
            case 'airtime':
                $transaction = \App\Models\AirtimeTransaction::on('mysql::write')->find($transactionID);
                $amount = intval($transaction->amount);
                break;
            case 'data':
                $transaction = \App\Models\DataTransaction::on('mysql::write')->find($transactionID);
                $amount = intval($transaction->amount);
                break;
        }

        $data = array(
            'txref' => $ref,
            'SECKEY' => env('FLUTTER_WAVE_SANDBOX_SECRET_KEY')
        );

        // make request to endpoint using unirest.
        \Unirest\Request::verifyPeer(false);
        $headers = array('Content-Type' => 'application/json');
        $body = \Unirest\Request\Body::json($data);
        $url = "https://ravesandboxapi.flutterwave.com/flwv3-pug/getpaidx/api/v2/verify"; //url to staging server. please make sure to change when in production.

        if ($mode == 2) {
            $url = "https://api.ravepay.co/flwv3-pug/getpaidx/api/v2/verify";
            $data['SECKEY'] = env('FLUTTER_WAVE_LIVE_SECRET_KEY');
        }
        // Make `POST` request and handle response with unirest
        try {
            $response = \Unirest\Request::post($url, $headers, $body);
        } catch (\Exception $ex) {
            \Log::info('error trying to verify payment...');
            \Log::info($ex->getMessage());
            $transaction->update(['status' => 3]);
            $resp = $ex->getCode();
        }

        //check the status is success
        // return $response->body;
        if ($response->body->data->status === "successful" && $response->body->data->chargecode === "00") {
            //confirm that the amount is the amount you wanted to charge
            if ($response->body->data->amount === $amount) {
                $resp = 100;
                $transaction->update(['amount_paid' => $amount]);
            } else {
                $transaction->update(['status' => 5]);
                return -2;
            }
        } else {
            $transaction->update(['status' => 3]);
            $resp = 404;
        }

        return $resp;
    }

    public function verifyPayment($paymentReference, $transactionType, $transactionID, $mode = 1)
    {
        Log::info('Lets try to verify payment from paystack.');
        $verified = 0;
        $result = array();
        $key = env('PAYSTACK_SECRET_KEY');
        /* if ($mode == 2) {
            $key = env('PAYSTACK_LIVE_PRIVATE_KEY');
        } */
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

    public function generateAirtimeAPIString($transactionID, $receiver, $serviceName, $amount, $mode = 1)
    {
        Log::info('generating api string for airtime purchase.');
        $key = env('TEST_PUBLIC_KEY');
        if ($mode == 2) {
            $key = env('LIVE_PUBLIC_KEY');
        }
        Log::info(env('VENDOR_CODE') . "|" . $transactionID . "|" . $receiver . "|" . $serviceName . "|" . $amount . "|" . $key);

        return env('VENDOR_CODE') . "|" . $transactionID . "|" . $receiver . "|" . $serviceName . "|" . $amount . "|" . $key;
    }

    public function generateDataAPIString($transactionID, $receiver, $serviceName, $data, $mode = 1)
    {
        Log::info('generating api string for data purchase.');
        $key = env('TEST_PUBLIC_KEY');
        if ($mode == 2) {
            $key = env('LIVE_PUBLIC_KEY');
        }
        Log::info(env('VENDOR_CODE') . "|" . $transactionID . "|" . $receiver . "|" . $serviceName . "|" . $data . "|" . $key);

        return env('VENDOR_CODE') . "|" . $transactionID . "|" . $receiver . "|" . $serviceName . "|" . $data . "|" . $key;
    }

    public function hashAPIString($apiString, $mode = 1)
    {
        Log::info('Hashing api string for request.');
        $key = env('TEST_PRIVATE_KEY');
        if ($mode == 2) {
            $key = env('LIVE_PRIVATE_KEY');
        }
        Log::info(hash_hmac("sha1", $apiString, $key));

        return hash_hmac("sha1", $apiString, $key);
    }

    public function purifyJSON($json)
    {
        if (substr($json, 0, 3) == pack("CCC", 0xEF, 0xBB, 0xBF)) {
            $json = substr($json, 3);
        }
        return $json;
    }

    public function getDataBundles($apiID, $networkID, $mode = 1)
    {
        $resp = array(
            'status'    =>  0,
            'msg'       => 'Pending.'
        );
        $vendorURL = env('VENDOR_TEST_URL');
        if ($mode == 2) {
            $vendorURL = env('VENDOR_LIVE_URL');
        }
        if ($apiID === 1) {
            $respFormat = "json";
            $url = $vendorURL . "/get_data_bundles.php";
            $url .= "?data_network=" . urlencode($networkID) . "&response_format=" . urlencode($respFormat);
            Log::info('requesting bundle for:');
            Log::info($url);
            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0
            ));
            $request = curl_exec($ch);

            if (curl_errno($ch)) {
                Log::error('Error while trying to get data.');
                Log::error(curl_errno($ch));
                Log::error(curl_error($ch));

                $resp['status'] = -1;
                $resp['msg'] = curl_error($ch);
            } else {
                if ($request) {
                    $result = json_decode($this->purifyJSON($request), true);
                    Log::info($result);

                    if ($result['status'] != '00') {
                        $resp['status'] = $result['status'];
                        $resp['msg'] = $result['message'];
                    } else {
                        $resp['status'] = 1;
                        $resp['msg'] = $result['bundles'];
                    }
                }
            }
            curl_error($ch);
        }

        return $resp;
    }

    public function checkAPITransactionStatus($apiID, $receiver)
    {
        $resp = array(
            'status'    => 0,
            'msg'       => 'Pending'
        );
        $timeStamp = date('Y-m-d');
        $statuses = \App\Models\ApiRequest::on('mysql::read')->where('api_id', $apiID)->where('receiver', $receiver)->where('request_timestamp', 'LIKE', '%' . $timeStamp . '%')->get();

        $objects = [];
        if (count($statuses) > 0) {
            $resp['status'] = 1;
            foreach ($statuses as $status) {
                if ($status->status === 0) {
                    $objects[] = $status->id;
                }
            }
        } else {
            $objects = 0;
        }
        $resp['msg'] = $objects;
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

    public function getStates()
    {
        return response()->json(\App\Models\State::all());
    }

    public function getLga()
    {
        return response()->json(\App\Models\LGA::all());
    }

    public function getService($typeID)
    {
        return response()->json(\App\Models\Service::on('mysql::read')->where('service_type_id', $typeID)->get());
    }
}
