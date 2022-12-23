<?php

namespace App\Http\Controllers;

use Exception;
use App\Enums\ActivityType;
use App\Traits\ManagesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\ManagesResponse;
use App\Jobs\UserActivityJob;
use App\Models\PolygonPrivateKey;
use App\Models\PolygonTransaction;
use App\Models\PolygonWallet;
use App\Models\PolygonWalletAddress;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;

class PolygonController extends Controller
{
    use  ManagesResponse, ManagesUsers;

    public function PolygonGenerateWallet(){

        $user = Auth::user();

        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/polygon/wallet",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "GET",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return response()->json($error);
        } else {
            $checkUser = PolygonWallet::where('user_id', auth()->user()->id)->first();

            if ($checkUser) {
                return Response::json([
                    'status' => false,
                    'message' => 'You already have an account created!'
                ], 419);
            }
            else {
             $checkUser = PolygonWallet::on('mysql::write')->create([
                 'user_id' => auth()->user()->id,
                 'mnemonic' => $response
             ]);
            $this->saveUserActivity(ActivityType::CREATE_POLYGON_WALLET, '', $user->id);

            return response()->json([ 'status' => true, 'message' => 'Wallet created Successfully', 'response' => $response ], 200);
        }
    }
    }

    public function PolygonGenerateAddress($xpub){

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
        CURLOPT_URL => "https://api.tatum.io/v3/polygon/address/" . $xpub . "/" . $index,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "GET",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return $error;
        } else {
            $checkRef = PolygonWalletAddress::where('user_id', $response)->first();

            if($checkRef && $checkRef->status == 0){
                return response()->json(['message'=>'Address already exist.'], 413);
            }elseif
            (!$checkRef){
                PolygonWalletAddress::on('mysql::write')->create([
                    'user_id' => auth()->user()->id,
                    'index' => $index,
                    'pub_key' => $xpub,
                    'address' => $response
                ]);
            }
            $this->saveUserActivity(ActivityType::CREATE_POLYGON_ADDRESS, '', $user->id);

            return response()->json([ 'status' => true, 'message' => 'Address created Successfully', 'response' => $response ], 201);
        }
    }

    public function PolygonGenerateAddressPrivateKey(Request $request){

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
        CURLOPT_URL => "https://api.tatum.io/v3/polygon/wallet/priv",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return response()->json($error);
        } else {
            $checkRef = PolygonPrivateKey::where('key', $response)->first();

            if($checkRef && $checkRef->status == 0){
                return response()->json(['message'=>'Private Key already exist.'], 413);
            }elseif (!$checkRef){
                PolygonPrivateKey::on('mysql::write')->create([
                    'user_id' => auth()->user()->id,
                    'key' => $response
                ]);
            }
            $this->saveUserActivity(ActivityType::POLYGONAddressPrivateKey, '', $user->id);
            return response()->json([ 'status' => true, 'message' => 'PrivateKey created Successfully', 'response' => $response ], 201);
        }
    }

    public function PolygonGetCurrentBlock(){

        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/polygon/block/current",
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

public function PolygonGetBlockbyHash($block){

    $hash = $block;

    $curl = curl_init();

    curl_setopt_array($curl, [
    CURLOPT_HTTPHEADER => [
        "x-api-key: ". env('TATUM_TEST_KEY')
    ],
    CURLOPT_URL => "https://api.tatum.io/v3/polygon/block/" . $hash,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "GET",
    ]);

    $response = curl_exec($curl);
    $error = curl_error($curl);

    curl_close($curl);

    if ($error) {
        return $error;
    } else {
        return response()->json([ 'status' => true, 'message' => 'Polygon block by hash fetched Successfully', 'response' => $response ], 200);
    }
}


public function PolygonGetBalance($address){

    $address = $address;

    $curl = curl_init();

    curl_setopt_array($curl, [
    CURLOPT_HTTPHEADER => [
        "x-api-key: ". env('TATUM_TEST_KEY')
    ],
    CURLOPT_URL => "https://api.tatum.io/v3/polygon/account/balance/" . $address,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "GET",
    ]);

    $response = curl_exec($curl);
    $error = curl_error($curl);

    curl_close($curl);

    if ($error) {
        return $error;
    } else {
        return response()->json([ 'status' => true, 'message' => 'Polygon balanced fetched Successfully', 'response' => $response ], 200);
    }
}

public function PolygonGetTransactionByAddress($hash){

    $hash = $hash;
    $curl = curl_init();

    curl_setopt_array($curl, [
    CURLOPT_HTTPHEADER => [
        "x-api-key: ". env('TATUM_TEST_KEY')
    ],
    CURLOPT_URL => "https://api.tatum.io/v3/polygon/transaction/" . $hash,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "GET",
    ]);

    $response = curl_exec($curl);
    $error = curl_error($curl);

    curl_close($curl);

    if ($error) {
        return $error;
    } else {
        return response()->json([ 'status' => true, 'message' => 'Polygon transaction fetched created Successfully', 'response' => $response ], 200);
    }

}

public function PolygonGetTransactionCount($address){

    $address = $address;
    $curl = curl_init();

    curl_setopt_array($curl, [
    CURLOPT_HTTPHEADER => [
        "x-api-key: ". env('TATUM_TEST_KEY')
    ],
    CURLOPT_URL => "https://api.tatum.io/v3/polygon/transaction/count/" . $address,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "GET",
    ]);

    $response = curl_exec($curl);
    $error = curl_error($curl);

    curl_close($curl);

    if ($error) {
        return $error;
    } else {

        return response()->json([ 'status' => true, 'message' => 'Polygon transaction by count fetched Successfully', 'response' => $response ], 200);
    }

}

    public function PolygonBlockchainTransfer(Request $request){

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
    "currency" => "MATIC",
    "amount" => $request->amount,
    "fromPrivateKey" => $request->sender_privkey,
    );

    curl_setopt_array($curl, [
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "x-api-key: ". env('TATUM_TEST_KEY')
    ],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_URL => "https://api.tatum.io/v3/polygon/transaction",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "POST",
    ]);

    $response = curl_exec($curl);
    $error = curl_error($curl);

    curl_close($curl);

    if ($error) {
        return $error;
    } else {
        PolygonTransaction::on('mysql::write')->create([
            'user_id' => auth()->user()->id,
            'to' => $request->receiver_address,
            'currency' => "MATIC",
            'amount' => $request->amount,
            'ref' =>  'TXC_' . $ref,
            'fromPrivateKey' => $request->sender_privkey,
            'response' => $response
        ], 201);

        $this->saveUserActivity(ActivityType::SEND_POLYGON, '', $user->id);

        return response()->json([ 'status' => true, 'message' => 'Polygon matic sent Successfully', 'response' => $response ], 200);
    }

}

public function PolygonBlockchainSmartContractInvocation(Request $request){


        $curl = curl_init();

        $payload = array(
        "contractAddress" =>  $request->contractAddress,
        "methodName" => "transfer",
        "methodABI" => array(
            "inputs" => array(
            array(
                "internalType" => "uint256",
                "name" => "amount",
                "type" => "uint256"
            )
            ),
            "name" => "stake",
            "outputs" => array(),
            "stateMutability" => "nonpayable",
            "type" => "function"
        ),
        "params" => array(
            "0x632"
        ),
        "fromPrivateKey" => $request->fromPrivateKey
        );

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_URL => "https://api.tatum.io/v3/polygon/smartcontract",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return $error;
        } else {
            return response()->json([ 'status' => true, 'message' => 'smart contract invoked Successfully', 'response' => $response ], 200);
        }
}

        public function PolygonBroadcast(Request $request){

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
            CURLOPT_URL => "https://api.tatum.io/v3/polygon/broadcast",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            ]);

            $response = curl_exec($curl);
            $error = curl_error($curl);

            curl_close($curl);

            if ($error) {
                return $error;
            } else {
                $this->saveUserActivity(ActivityType::BROADCAST_POLYGON, '', $user->id);
                return response()->json([ 'status' => true, 'message' => 'Broadcasted Successful', 'response' => $response ], 200);
            }
        }

        public function PolygonEstimateGas(Request $request){

            $curl = curl_init();

            $validator = Validator::make($request->all(), [
                'from'=>'required|string|min:5',
                'to'=>'required|string|min:5',
                'amount'=>'required|min:0'

            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $curl = curl_init();

            $payload = array(
            "from" =>  $request->from,
            "to" =>  $request->to,
            "amount" =>  $request->amount,
            );

            curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "x-api-key: ". env('TATUM_TEST_KEY')
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_URL => "https://api.tatum.io/v3/polygon/gas",
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
                $data = PolygonWallet::on('mysql::write')->where('user_id', auth()->user()->id)->first();
                $message = 'data successfully fetched';

                return $this->sendResponse($data,$message);
            }catch (ModelNotFoundException $e) {
                return response()->json(['message' => $e->getMessage()],404);
            } catch(\Exception $e) {
                return response()->json(['message' => $e->getMessage()],500);
            }
        }


    }


