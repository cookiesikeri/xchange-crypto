<?php

namespace App\Repositories;

use App\Interfaces\UserInterface;
use Illuminate\Support\Facades\Log;

class UserRepository implements UserInterface {

    public function is_user($user_id) {
        $user = \App\Models\User::on('mysql::write')->find($user_id);
        if(!$user) {
            $user = 404;
        }
        return $user;
    }

    public function create_user_wallet($user_id) {
        $wallet_created = 0;
        $user = $this->is_user($user_id);
        if(!is_int($user)) {
            $user->wallet()->create(['balance' => 0.00]);
            $wallet_created = !$wallet_created;
        }
        return $wallet_created;
    }

    public function get_user_wallet_balance($user_id) {
        $wallet_balance = 0.00;
        $user = $this->is_user($user_id);
        if(!is_int($user)) {
            $wallet_balance = $user->wallet->balance;
        }
        return $wallet_balance;
    }

    public function get_user_btc_address($user_id) {

        $user = $this->is_user($user_id);
        if(!is_int($user)) {
            $btc_address = $user->bitcon_wallet->address;
            $btc_key = $user->bitcon_wallet->pub_key;
        }
        return response()->json([ 'status' => true, 'message' => 'data fetched successfully', 'address' => $btc_address, 'pubkey' => $btc_key ], 200);
    }
    public function get_user_eth_address($user_id) {
        $user = $this->is_user($user_id);
        if(!is_int($user)) {
            $btc_address = $user->etherum_wallet_adress->address;
            $btc_key = $user->etherum_wallet_adress->pub_key;
        }
        return response()->json([ 'status' => true, 'message' => 'data fetched successfully', 'address' => $btc_address, 'pubkey' => $btc_key ], 200);
    }
    public function get_user_litecoin_address($user_id) {

        $user = $this->is_user($user_id);
        if(!is_int($user)) {
            $btc_address = $user->litecoin_wallet_address->address;
            $btc_key = $user->litecoin_wallet_address->pub_key;
        }
        return response()->json([ 'status' => true, 'message' => 'data fetched successfully', 'address' => $btc_address, 'pubkey' => $btc_key ], 200);
    }
    public function get_user_dogecoin_address($user_id) {

        $user = $this->is_user($user_id);
        if(!is_int($user)) {
            $btc_address = $user->doge_coin_wallet_address->address;
            $btc_key = $user->litecoin_wallet_address->pub_key;
        }
        return response()->json([ 'status' => true, 'message' => 'data fetched successfully', 'address' => $btc_address, 'pubkey' => $btc_key ], 200);
    }
    public function get_user_polygon_address($user_id) {

        $user = $this->is_user($user_id);
        if(!is_int($user)) {
            $btc_address = $user->polygon_wallet_address->address;
            $btc_key = $user->litecoin_wallet_address->pub_key;
        }
        return response()->json([ 'status' => true, 'message' => 'data fetched successfully', 'address' => $btc_address, 'pubkey' => $btc_key ], 200);
    }
    public function get_user_bnb_address($user_id) {

        $user = $this->is_user($user_id);
        if(!is_int($user)) {
            $btc_address = $user->binance_wallet->response;
        }
        return response()->json([ 'status' => true, 'message' => 'data fetched successfully', 'data' => $btc_address ], 200);
    }

    public function user_has_sufficient_wallet_balance($user_id, $amount) {
        $has_sufficient_balance = false;
        $wallet_balance = $this->get_user_wallet_balance($user_id);
        if($wallet_balance > 0.00 && $wallet_balance > $amount) {
            $has_sufficient_balance = !$has_sufficient_balance;
        }
        return $has_sufficient_balance;
    }

    public function update_user_wallet_balance($user_id, $amount) {
        $update = false;
        $user = $this->is_user($user_id);
        if(!is_int($user)) {
            $user->wallet()->update(['balance' => $amount]);
            $update = !$update;
        }
        return $update;
    }

    public function debit_user_wallet($user_id, $amount) {
        $debit = false;
        $wallet_balance = $this->get_user_wallet_balance($user_id);
        $new_wallet_balance = $wallet_balance - $amount;
        if($new_wallet_balance > 0.00) {
            $debit = $new_wallet_balance;
        }
        return $debit;
    }



    public function get_user_airtime_transactions($user_id, $paginate = 20, $status = 'all') {
        $airtime_transactions;
        $user = $this->is_user($user_id);
        if(!is_int($user)) {
            switch($status) {
                case '0':
                case '1':
                case '2':
                case '3':
                case '4':
                    $airtime_transactions = \App\Models\AirtimeTransaction::on('mysql::read')->where('status', $status)->where('user_id', $user->id)->paginate($paginate);
                    break;
                case 'all':
                    $airtime_transactions = \App\Models\AirtimeTransaction::on('mysql::read')->where('user_id', $user->id)->paginate($paginate);
                    break;
            }
        }
        return $airtime_transactions;
    }

    public function get_user_all_airtime_transactions($user_id, $status) {
        $airtime_transactions;
        $user = $this->is_user($user_id);
        if(!is_int($user)) {
            switch($status) {
                case '0':
                case '1':
                case '2':
                case '3':
                case '4':
                    $airtime_transactions = \App\Models\AirtimeTransaction::on('mysql::read')->where('status', $status)->where('user_id', $user->id)->get();
                    break;
                case 'all':
                    $airtime_transactions = \App\Models\AirtimeTransaction::on('mysql::read')->where('user_id', $user->id)->get();
                    break;
            }
        }
        return $airtime_transactions;
    }

    public function get_user_data_transactions($user_id, $paginate = 20, $status = 'all') {
        $data_transactions;
        $user = $this->is_vendor($user_id);
        if(!is_int($user)) {
            switch($status) {
                case '0':
                case '1':
                case '2':
                case '3':
                case '4':
                    $data_transactions = \App\Models\DataTransaction::on('mysql::read')->where('status', $status)->where('user_id', $user->id)->paginate($paginate);
                    break;
                case 'all':
                    $data_transactions = \App\Models\DataTransaction::on('mysql::read')->where('user_id', $user->id)->paginate($paginate);
                    break;
            }
        }
        return $data_transactions;
    }

    public function get_user_all_data_transactions($user_id, $status) {
        $data_transactions;
        $user = $this->is_user($user_id);
        if(!is_int($user)) {
            switch($status) {
                case '0':
                case '1':
                case '2':
                case '3':
                case '4':
                    $data_transactions = \App\Models\DataTransaction::on('mysql::read')->where('status', $status)->where('user_id', $user->id)->get();
                    break;
                case 'all':
                    $data_transactions = \App\Models\DataTransaction::on('mysql::read')->where('user_id', $user->id)->get();
                    break;
            }
        }
        return $data_transactions;
    }



    public function log_wallet_transaction($user, $amount_entered, $new_balance, $transaction_type, $description, $transaction_status, $transaction_reference)
    {
        $user->wallet->transactions()->create([
            'transaction_amount'        => $amount_entered,
            'current_balance'           => $user->wallet->balance,
            'new_balance'               => $new_balance,
            'transaction_type'          => $transaction_type,
            'transaction_description'   => $description,
            'status'                    => $transaction_status,
            'transaction_reference'     => $transaction_reference
        ]);
        return 'ok';
    }

    public function generate_transaction_reference()
    {
        $random_string_length = 10;
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $string = '';
        $max = strlen($characters) - 1;
        for ($i = 0; $i < $random_string_length; $i++)
        {
            $string .= $characters[mt_rand(0, $max)];
        }
        return $string;
    }

    public function verify_wallet_funding_payment($paymentReference, $mode = 1) {

        Log::info('lets try to verify payment to fund wallet on paystack');
        $amount = 0.0;
        $verified = 0;
        $result = array();
        $key = env('PAYSTACK_TEST_PRIVATE_KEY');
        if($mode == 2) {
            $key = env('PAYSTACK_LIVE_PRIVATE_KEY');
        }
        $url = 'https://api.paystack.co/transaction/verify/' . $paymentReference;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt(
            $ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $key]
        );
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $request = curl_exec($ch);

        if(curl_errno($ch)) {
            $verified = curl_errno($ch);
            Log::info('cURL error occured while trying to verify payment.');
            Log::error(curl_error($ch));

            // verification failed
            $verified = -1;
        } else {
            if ($request) {
                $result = json_decode($request, true);
                Log::info('result from paystack');
                Log::info($result);
                if($result["data"] && $result["data"]["status"] == "success") {
                    // at this point, payment has been verified.
                    $verified = 100;
                    $amount = $result["data"]["amount"];
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
        if($request->ajax()) {
            return response()->json(array(['status' => $verified, 'amount' => $amount]));
        }
        return array(['status' => $verified, 'amount' => $amount]);
    }
}
