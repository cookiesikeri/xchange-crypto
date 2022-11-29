<?php

namespace App\Http\Controllers;

use App\Models\BitcoinPrivateKey;
use App\Models\BitcoinTransaction;
use App\Models\BitcoinWalletPass;
use App\Models\BitconWallet;
use Exception;
use App\Enums\ActivityType;
use App\Traits\ManagesUsers;
use App\Traits\ManagesResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\CryptoWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class BitconWalletController extends Controller
{
    use  ManagesResponse, ManagesUsers;

    public function CreateBitcoinWallet(Request $request){

        $user = Auth::user();

        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => env('TATUM_BASE_URL')."bitcoin/wallet",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "GET",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
        // echo "cURL Error #:" . $error;
        return response()->json($error);
        } else {
            $checkRef = BitcoinWalletPass::where('mnemonic', $response)->exists();

            if($checkRef && $checkRef->status >= 0){
                return response()->json(['message'=>'Wallet already exist.'], 413);
            }elseif (!$checkRef){
                BitcoinWalletPass::on('mysql::write')->create([
                    'user_id' => auth()->user()->id,
                    'mnemonic' => $response
                ]);
            }
            $this->saveUserActivity(ActivityType::CREATE_BITCOIN_WALLET, '', $user->id);

            return response()->json([ 'status' => true, 'message' => 'Wallet created Successfully', 'response' => $response ], 201);

        }
    }


    public function CreateBitcoinPrivateKey(Request $request){

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
        CURLOPT_URL => "https://api.tatum.io/v3/bitcoin/wallet/priv",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return response()->json($error);
        } else {
            $checkRef = BitcoinPrivateKey::where('key', $response)->first();

            if($checkRef && $checkRef->status == 0){
                return response()->json(['message'=>'Private Key already exist.'], 413);
            }elseif (!$checkRef){
                BitcoinPrivateKey::on('mysql::write')->create([
                    'user_id' => auth()->user()->id,
                    'key' => $response
                ]);
            }
            $this->saveUserActivity(ActivityType::BitcoinGenerateAddressPrivateKey, '', $user->id);
           return response()->json([ 'status' => true, 'message' => 'private key created Successfully', 'response' => $response ], 201);
        }
    }

    public function CreateBitcoinAddress(Request $request, $xpub) {

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
        CURLOPT_URL => "https://api.tatum.io/v3/bitcoin/address/" . $xpub . "/" . $index,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "GET",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
        return response()->json($error);
        } else {
            $checkRef = BitconWallet::where('pub_key', $response)->first();

            if($checkRef && $checkRef->status == 0){
                return response()->json(['message'=>'Address already exist.'], 413);
            }elseif (!$checkRef){
                BitconWallet::on('mysql::write')->create([
                    'user_id' => auth()->user()->id,
                    'index' => $index,
                    'pub_key' => $xpub,
                    'address' => $response
                ]);
            }

            $this->saveUserActivity(ActivityType::CREATE_BITCOIN_ADDRESS, '', $user->id);
            return response()->json([ 'status' => true, 'message' => 'addreess created Successfully', 'response' => $response ], 201);
            // return response()->json([
            //     'status' => true,
            //     'message' => 'bitcoin address created successfully ',
            //     $response
            // ]);

        }
    }

    public function BtcGetBalanceOfAddress(Request $request, $address) {

        $address = $address;
        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/bitcoin/address/balance/" . $address,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "GET",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return response()->json($error);
        } else {

            return response()->json([ 'status' => true, 'message' => 'balance fetched successfully', 'response' => $response ], 200);
        }
    }
    public function BtcGetTxByAddress($address) {

        $address = $address;
        $query = array(
        "pageSize" => "10"
        );

        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/bitcoin/transaction/address/" . $address . "?" . http_build_query($query),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "GET",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return response()->json($error);
        } else {

            return response()->json([ 'status' => true, 'message' => 'Successful', 'response' => $response ], 200);
        }
    }

    public function BtcTransferBlockchain(Request $request,$privkey, $senderadd, $receiverAdd, $value){

        $user = Auth::user();

        $ref = '51' . substr(uniqid(mt_rand(), true), 0, 8);

        $curl = curl_init();

        $payload = array(
        "fromAddress" => array(
            array(
            "address" => $senderadd,
            "privateKey" => $privkey
            )
        ),
        "to" => array(
            array(
            "address" => $receiverAdd,
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
        CURLOPT_URL => "https://api.tatum.io/v3/bitcoin/transaction",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return response()->json($error);
        } else {
            BitcoinTransaction::on('mysql::write')->create([
                'user_id' => auth()->user()->id,
                'sender_private_key' => $privkey,
                'sender_address' => $senderadd,
                'receiver_address' => $receiverAdd,
                'value' => $value,
                'ref' =>  'TXC_' . $ref,
                'response' => $response
            ], 201);

            $this->saveUserActivity(ActivityType::SEND_BITCOIN, '', $user->id);
            return $response;
        }
    }

    public function BtcGetTransactionDetails(Request $request,$hash){

        $hash = $hash;

        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/bitcoin/transaction/" . $hash,
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

    public function BtcGetBlockChainInfo(){

        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/bitcoin/info",
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

    public function BtcGetBlockHash($i){

        $i = $i;

        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/bitcoin/block/hash/" . $i,
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

    public function BtcGetUTXODetails($hash, $index){

        $otp = 0;
        for ($i = 0; $i < 3; $i++)
        {
            $otp .= mt_rand(0,9);
        }

        $hash = $hash;
        $index= $otp;

        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/bitcoin/utxo/" . $hash . "/" . $index,
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

    public function BtcBroadcast(Request $request){

        $user = Auth::user();


        $validator = Validator::make($request->all(), [
            'txData'=>'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $curl = curl_init();

        $payload = array(

            'txData'     =>  $request->txData
        );

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_URL => "https://api.tatum.io/v3/bitcoin/broadcast",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return response()->json($error);
        } else {
            $this->saveUserActivity(ActivityType::BROADCAST_BITCOIN, '', $user->id);
            return $response;
    }

}

        public function BtcEstimateGas(Request $request){


            $curl = curl_init();

            $payload = array(
            "chain" =>  $request->chain,
            "type" =>  $request->type,
            );

            curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "x-api-key: 4dc3bcdb-3c8a-4ba9-98d5-92f1a98339aa"
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


}
