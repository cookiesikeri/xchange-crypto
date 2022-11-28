<?php

namespace App\Http\Controllers;

use Exception;
use App\Enums\ActivityType;
use App\Traits\ManagesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\ManagesResponse;
use App\Jobs\UserActivityJob;
use App\Models\BinanceTransaction;
use App\Models\BinanceWallet;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class BinanceController extends Controller
{
    use  ManagesResponse, ManagesUsers;

    public function BnbGenerateWallet(){

        $user = Auth::user();

        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/bnb/account",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "GET",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return $error;
        } else {
            BinanceWallet::on('mysql::write')->create([
                'user_id' => auth()->user()->id,
                'response' => $response
            ]);

            $this->saveUserActivity(ActivityType::CREATE_POLYGON_WALLET, '', $user->id);

            return response()->json([ 'status' => true, 'message' => 'Wallet created Successfully', 'response' => $response ], 200);
        }
    }

        public function BnbGetCurrentBlock(){

            $curl = curl_init();

            curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => [
                "x-api-key: ". env('TATUM_TEST_KEY')
            ],
            CURLOPT_URL => "https://api.tatum.io/v3/bnb/block/current",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "GET",
            ]);

            $response = curl_exec($curl);
            $error = curl_error($curl);

            curl_close($curl);

            if ($error) {
                return response()->json($error);
            } else {
                return response()->json([ 'status' => true, 'message' => 'current block number fetched Successfully', 'response' => $response ], 200);

        }
    }

    public function BnbGetBlock($height){

        $height = $height;
        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/bnb/block/" . $height,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "GET",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return $error;
        } else {
            return response()->json([ 'status' => true, 'message' => 'Binance transaction fetched Successfully', 'response' => $response ], 200);
        }

    }

    public function BnbGetAccount($address){

        $address = $address;
        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/bnb/account/" . $address,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "GET",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return $error;
        } else {
            return response()->json([ 'status' => true, 'message' => 'Binance balance fetched Successfully', 'response' => $response ], 200);
        }

    }

    public function BnbGetTransaction($block){

        $block = $block;
        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/bnb/transaction/" . $block,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "GET",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return $error;
        } else {
            return response()->json([ 'status' => true, 'message' => 'Binance transaction fetched Successfully', 'response' => $response ], 200);
        }

    }

    public function BnbGetTxByAccount(Request $request, $address){

        // $validator = Validator::make($request->all(), [
        //     'startTime'=>'required',
        //     'endTime'=>'required'

        // ]);

        // if ($validator->fails()) {
        //     return response()->json($validator->errors(), 422);
        // }

        $address = $address;

        $query = array(
        "startTime" => $request->startTime,
        "endTime" => $request->endTime
        );

        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/bnb/account/transaction/" . $address . "?" . http_build_query($query),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "GET",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return $error;
        } else {
            return $response;
        }

    }

    public function BnbBlockchainTransfer(Request $request){

        $user = Auth::user();

        $ref = '51' . substr(uniqid(mt_rand(), true), 0, 8);

        $validator = Validator::make($request->all(), [
            'receiver_address'=>'required|string|min:5',
            'amount'=>'required|min:0',
            'sender_privkey'=>'required|string|min:5'

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }


    $curl = curl_init();

    $payload = array(
    "to" => $request->receiver_address,
    "currency" => "BNB",
    "amount" => $request->amount,
    "fromPrivateKey" => $request->sender_privkey,
    );

    curl_setopt_array($curl, [
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "x-api-key: ". env('TATUM_TEST_KEY')
    ],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_URL => "https://api.tatum.io/v3/bnb/transaction",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "POST",
    ]);

    $response = curl_exec($curl);
    $error = curl_error($curl);

    curl_close($curl);

    if ($error) {
        return $error;
    } else {
        BinanceTransaction::on('mysql::write')->create([
            'user_id' => auth()->user()->id,
            'to' => $request->receiver_address,
            'currency' => "MATIC",
            'amount' => $request->amount,
            'ref' =>  'TXC_' . $ref,
            'fromPrivateKey' => $request->sender_privkey,
            'response' => $response
        ], 201);

        $this->saveUserActivity(ActivityType::SEND_BINANCE, '', $user->id);

        return response()->json([ 'status' => true, 'message' => 'Binance sent Successfully', 'response' => $response ], 200);
    }

}

    public function BnbBroadcast(Request $request){

        $validator = Validator::make($request->all(), [
            'txData'=>'required|string|min:5'

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = Auth::user();

        $curl = curl_init();

        $payload = array(
        "txData" =>  $request->txData,
        );

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_URL => "https://api.tatum.io/v3/bnb/broadcast",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return $error;
        } else {
            $this->saveUserActivity(ActivityType::BROADCAST_BINANCE, '', $user->id);
            return response()->json([ 'status' => true, 'message' => 'Broadcasted Successful', 'response' => $response ], 200);
        }
    }

}
