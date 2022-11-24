<?php

namespace App\Http\Controllers;

use App\Models\BitcoinPrivateKey;
use App\Models\BitcoinTransaction;
use App\Models\BitcoinWalletPass;
use App\Models\BitconWallet;
use Exception;
use App\Models\CryptoWallet;
use Illuminate\Http\Request;
use App\Traits\ManagesResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class BitconWalletController extends Controller
{
    use  ManagesResponse;

    public function CreateBitcoinWallet(Request $request){

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
                return response()->json(['message'=>'Address already exist.'], 413);
            }elseif (!$checkRef){
                BitcoinWalletPass::on('mysql::write')->create([
                    'user_id' => auth()->user()->id,
                    'mnemonic' => $response
                ]);
            }

            return $response;

        }
    }


    public function CreateBitcoinPrivateKey(Request $request){

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
            return $response;
        }
    }

    public function CreateBitcoinAddress(Request $request, $xpub, $index) {

        $xpub = $xpub;
        $index = 0;

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
            return $response;
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

            return $response;
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

            return $response;
        }
    }

    public function BtcTransferBlockchain(Request $request,$privkey, $senderadd, $receiverAdd, $value){

        $validator = Validator::make($request->all(), [
            'address'=>'required|string|min:5',
            'privateKey'=>'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

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
                'response' => $response
            ], 201);
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

        $hash = $hash;
        $index= 0;

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
            return $response;
    }

}



}
