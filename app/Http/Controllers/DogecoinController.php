<?php

namespace App\Http\Controllers;

use App\Models\DogeCoinWallet;
use App\Models\DogeCoinWalletAddress;
use Exception;
use App\Enums\ActivityType;
use App\Traits\ManagesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\ManagesResponse;
use App\Jobs\UserActivityJob;
use App\Models\DodgecoinTransaction;
use App\Models\DogecoinPrivateKey;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DogecoinController extends Controller
{
    use  ManagesResponse, ManagesUsers;

    public function DogeGenerateWallet(){

        $user = Auth::user();

        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/dogecoin/wallet",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "GET",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return response()->json($error);
        } else {
            $checkUser = DogeCoinWallet::where('user_id', auth()->user()->id)->first();

            if ($checkUser) {
                return Response::json([
                    'status' => false,
                    'message' => 'You already have an account created!'
                ], 419);
            }
            else {
             $checkUser = DogeCoinWallet::on('mysql::write')->create([
                 'user_id' => auth()->user()->id,
                 'mnemonic' => $response
             ]);

            $this->saveUserActivity(ActivityType::CREATE_DOGECOIN_WALLET, '', $user->id);
            return response()->json([ 'status' => true, 'message' => 'Wallet created Successfully', 'response' => $response ], 201);
        }
    }
    }
    public function DogeGenerateAddress($xpub){

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
        CURLOPT_URL => "https://api.tatum.io/v3/dogecoin/address/" . $xpub . "/" . $index,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "GET",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return $error;
        } else {
            $checkRef = DogeCoinWalletAddress::where('user_id', $response)->first();

            if($checkRef && $checkRef->status == 0){
                return response()->json(['message'=>'Address already exist.'], 413);
            }elseif
            (!$checkRef){
                DogeCoinWalletAddress::on('mysql::write')->create([
                    'user_id' => auth()->user()->id,
                    'index' => $index,
                    'pub_key' => $xpub,
                    'address' => $response
                ]);
            }
            $this->saveUserActivity(ActivityType::CREATE_DOGECOIN_ADDRESS, '', $user->id);

            return $response;
        }
    }

    public function DogeGenerateAddressPrivateKey(Request $request){

        $user = Auth::user();

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
        CURLOPT_URL => "https://api.tatum.io/v3/dogecoin/wallet/priv",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return response()->json($error);
        } else {
            $checkRef = DogecoinPrivateKey::where('key', $response)->first();

            if($checkRef && $checkRef->status == 0){
                return response()->json(['message'=>'Private Key already exist.'], 413);
            }elseif (!$checkRef){
                DogecoinPrivateKey::on('mysql::write')->create([
                    'user_id' => auth()->user()->id,
                    'key' => $response
                ]);
            }
            $this->saveUserActivity(ActivityType::DogeGenerateAddressPrivateKey, '', $user->id);
            return $response;
            // return Response::json([
            //     'status' => 'success',
            //     'message' => 'Private Key created successfully!',
            //     'data' => $response
            // ], 201);
        }
    }

    public function DogeGetBlockChainInfo(){

            $curl = curl_init();

            curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => [
                "x-api-key: ". env('TATUM_TEST_KEY')
            ],
            CURLOPT_URL => "https://api.tatum.io/v3/dogecoin/info",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "GET",
            ]);

            $response = curl_exec($curl);
            $error = curl_error($curl);

            curl_close($curl);

            if ($error) {
                return response()->json($error);
            } else {
                return $response;

        }
    }
    public function DogeGetBlockHash($i){

        $i = $i;
        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/dogecoin/block/hash/" . $i,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "GET",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return response()->json($error);
        } else {
            return $response;
        }
    }
    public function DogeGetBlockByHash($hash){

        $hash = $hash;

        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/dogecoin/block/" . $hash,
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
    public function DogeGetRawTransaction($hash,){

        $hash = $hash;

        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/dogecoin/transaction/" . $hash,
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

    public function DogeGetUTXO($hash,){

        $otp = 0;
        for ($i = 0; $i < 3; $i++)
        {
            $otp .= mt_rand(0,9);
        }

        $hash = $hash;
        $index = $otp;
        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/dogecoin/utxo/" . $hash . "/" . $index,
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

    public function DogeTransferBlockchain($txHash, $value, $address,$signatureId,$receiveaddress){

        $user = Auth::user();

        $ref = '51' . substr(uniqid(mt_rand(), true), 0, 8);

        $curl = curl_init();

        $payload = array(
        "fromUTXO" => array(
            array(
            'txHash' => $txHash,
            "value" => $value,
            "address" => $address,
            "index" => 0,
            "signatureId" => $signatureId,
            )
        ),
        "to" => array(
            array(
            "address" => $receiveaddress,
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
        CURLOPT_URL => "https://api.tatum.io/v3/dogecoin/transaction",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return $error;
        } else {
            DodgecoinTransaction::on('mysql::write')->create([
                'user_id' => auth()->user()->id,
                'txHash' => $txHash,
                'value' => $value,
                'address' => $address,
                'ref' =>  'TXC_' . $ref,
                'index' => 0,
                'signatureId' => $signatureId,
                'recaddress' => $receiveaddress,
                'response' => $response
            ], 201);

            $this->saveUserActivity(ActivityType::SEND_DOGECOIN, '', $user->id);
            return $response;
        }

    }

    public function DogeBroadcast(Request $request){

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
        CURLOPT_URL => "https://api.tatum.io/v3/dogecoin/broadcast",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return $error;
        } else {
            $this->saveUserActivity(ActivityType::BROADCAST_DOGECOIN, '', $user->id);
            return $response;

        }

    }


    public function DogeEstimateGas(Request $request){

        $curl = curl_init();

        $validator = Validator::make($request->all(), [
            'senderAccountId'=>'required|string|min:5',
            'address'=>'required|string|min:5',
            'amount'=>'required|min:0'

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $curl = curl_init();

        $payload = array(
        "senderAccountId" =>  $request->senderAccountId,
        "address" =>  $request->address,
        "amount" =>  $request->amount,
        "xpub" =>  $request->xpub
        );

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_URL => "https://api.tatum.io/v3/offchain/blockchain/estimate",
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
            $data = DogeCoinWallet::on('mysql::write')->where('user_id', auth()->user()->id)->first();
            $message = 'data successfully fetched';

            return $this->sendResponse($data,$message);
        }catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()],404);
        } catch(\Exception $e) {
            return response()->json(['message' => $e->getMessage()],500);
        }
    }




}
