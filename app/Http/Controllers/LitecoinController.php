<?php

namespace App\Http\Controllers;

use Exception;
use App\Enums\ActivityType;
use App\Traits\ManagesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\ManagesResponse;
use App\Jobs\UserActivityJob;
use App\Models\LitecoinPrivatekey;
use App\Models\LitecoinTransaction;
use App\Models\LitecoinWallet;
use App\Models\LitecoinWalletAddress;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class LitecoinController extends Controller
{
    use  ManagesResponse, ManagesUsers;

    public function LitecoinGenerateWallet(){

        $user = Auth::user();

        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/litecoin/wallet",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "GET",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return response()->json($error);
        } else {
            $checkUser = LitecoinWallet::where('user_id', auth()->user()->id)->first();

            if ($checkUser) {
                return Response::json([
                    'status' => false,
                    'message' => 'You already have an account created!'
                ], 419);
            }
            else {
             $checkUser = LitecoinWallet::on('mysql::write')->create([
                 'user_id' => auth()->user()->id,
                 'response' => $response
             ]);

            $this->saveUserActivity(ActivityType::CREATE_LITECOIN_WALLET, '', $user->id);

            return response()->json([ 'status' => true, 'message' => 'wallet created successfully', 'response' => $response ], 200);
        }
    }
    }

    public function LitecoinGenerateAddress($xpub){

        $user = Auth::user();

        $otp = 0;
        for ($i = 0; $i < 3; $i++)
        {
            $otp .= mt_rand(0,9);
        }

        $xpub = $xpub;
        $index = $otp;

        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/litecoin/address/" . $xpub . "/" . $index,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "GET",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return $error;
        } else {
            $checkRef = LitecoinWalletAddress::where('user_id', $response)->first();

            if($checkRef && $checkRef->status == 0){
                return response()->json(['message'=>'Address already exist.'], 413);
            }elseif
            (!$checkRef){
                LitecoinWalletAddress::on('mysql::write')->create([
                    'user_id' => auth()->user()->id,
                    'index' => $index,
                    'pub_key' => $xpub,
                    'address' => $response
                ]);
            }
            $this->saveUserActivity(ActivityType::CREATE_LITECOIN_ADDRESS, '', $user->id);

            return response()->json([ 'status' => true, 'message' => 'address generated successfully', 'response' => $response ], 200);
        }
    }

    public function LtcGetBlockChainInfo(){

        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/litecoin/info",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "GET",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return response()->json($error);
        } else {
            return response()->json([ 'status' => true, 'message' => 'blockchain info fetched successfully', 'response' => $response ], 200);

    }
}

    public function LtcGetBlockHash($i){

        $i = $i;
        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/litecoin/block/hash/" . $i,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "GET",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return response()->json($error);
        } else {
            return response()->json([ 'status' => true, 'message' => 'data fetched successfully', 'response' => $response ], 200);
        }
    }

    public function LtcGetBlockyHash($hash){

        $hash = $hash;

        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/litecoin/block/" . $hash,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "GET",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return $error;
        } else {
            return response()->json([ 'status' => true, 'message' => 'litecoin blocked fetched successfully', 'response' => $response ], 200);
        }
    }

    public function LtcGetRawTransaction($hash){

        $hash = $hash;
        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/litecoin/transaction/" . $hash,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "GET",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return $error;
        } else {
            return response()->json([ 'status' => true, 'message' => 'transaction by hash fetched successfully', 'response' => $response ], 200);
        }

    }

    public function LtcGetTxByAddress($address){

        $address = $address;
        $query = array(
        "pageSize" => "10"
        );

        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/litecoin/account/transaction/" . $address . "?" . http_build_query($query),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "GET",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return $error;
        } else {
            return response()->json([ 'status' => true, 'message' => 'transaction by address fetched successfully', 'response' => $response ], 200);
        }
    }

    public function LtcGetBalanceOfAddress($address){

        $address = $address;
        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/litecoin/address/balance/" . $address,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "GET",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return $error;
        } else {
            return response()->json([ 'status' => true, 'message' => 'balance fetched successfully', 'response' => $response ], 200);
        }
    }

    public function LtcGetUTXO($address){

        $otp = 0;
        for ($i = 0; $i < 3; $i++)
        {
            $otp .= mt_rand(0,9);
        }

        $address = $address;
        $index = $otp;

        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/litecoin/utxo/" . $address . "/" . $index,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "GET",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return $error;
        } else {
            return response()->json([ 'status' => true, 'message' => 'utxo fetched Successfully', 'response' => $response ], 200);
        }
    }

    public function LtcGenerateAddressPrivateKey(Request $request){

        $user = Auth::user();

        $otp = 0;
        for ($i = 0; $i < 3; $i++)
        {
            $otp .= mt_rand(0,9);
        }

        $validator = Validator::make($request->all(), [
            'mnemonic'=>'required|string|min:5'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $curl = curl_init();

        $payload = array(

            'mnemonic'     =>  $request->mnemonic,
            'index'     =>  0
        );

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_URL => "https://api.tatum.io/v3/ethereum/wallet/priv",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return response()->json($error);
        } else {

            $checkRef = LitecoinPrivatekey::where('user_id', auth()->user()->id)->first();

            if($checkRef){
                return response()->json(['message'=>'Private Key already exist.'], 413);
            }else{
                LitecoinPrivatekey::on('mysql::write')->create([
                    'user_id' => auth()->user()->id,
                    'response' => $response
                ]);
            }
            $this->saveUserActivity(ActivityType::LitecoinAddressPrivateKey, '', $user->id);

            return response()->json([ 'status' => true, 'message' => 'address created Successfully', 'response' => $response ], 201);
        }
    }

    public function LtcTransferBlockchain(Request $request,$address, $privateKey, $receiveradd, $value){

        $user = Auth::user();

        $ref = '51' . substr(uniqid(mt_rand(), true), 0, 8);


        $curl = curl_init();

        $payload = array(
        "fromAddress" => array(
            array(
            "address" =>  $address,
            "privateKey" => $privateKey
            )
        ),
        "to" => array(
            array(
            "address" => $receiveradd,
            "value" => 0.02969944
            )
        )
        );

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_URL => "https://api.tatum.io/v3/litecoin/transaction",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return $error;
        } else {
            LitecoinTransaction::on('mysql::write')->create([
                'user_id' => auth()->user()->id,
                'sender_address' => $address,
                'sender_private_key' => $privateKey,
                'receiver_address' => $receiveradd,
                'response' => $response,
                'ref' =>  'TXC_' . $ref,
                'value' => $value
            ], 201);

            $this->saveUserActivity(ActivityType::SEND_LITECOIN, '', $user->id);
            return response()->json([ 'status' => true, 'message' => 'litecoin sent  Successful', 'response' => $response ], 200);
        }
    }

    public function LtcBroadcast(Request $request){

        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'txData'=>'required|string|min:5'

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }



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
        CURLOPT_URL => "https://api.tatum.io/v3/litecoin/broadcast",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return $error;
        } else {
        $this->saveUserActivity(ActivityType::BROADCAST_LITECOIN, '', $user->id);
        return response()->json([ 'status' => true, 'message' => 'broadcast Successful', 'response' => $response ], 201);
        }

    }

    public function LtcEstimateGas(Request $request){


        $curl = curl_init();

        $payload = array(
        "chain" =>  $request->chain,
        "type" =>  $request->type,
        );

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_URL => "https://api.tatum.io/v3/blockchain/estimate",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return $error;
        } else {
            return response()->json([ 'status' => true, 'message' => 'Gas fee fetched Successfully', 'response' => $response ], 200);
        }
    }

    public function GetWalletDeatils()
    {
        try {
            $data = LitecoinWallet::on('mysql::write')->where('user_id', auth()->user()->id)->first();
            $message = 'data successfully fetched';

            return $this->sendResponse($data,$message);
        }catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()],404);
        } catch(\Exception $e) {
            return response()->json(['message' => $e->getMessage()],500);
        }
    }


}
