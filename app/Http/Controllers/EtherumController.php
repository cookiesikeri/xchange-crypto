<?php

namespace App\Http\Controllers;

use App\Models\EtherumPrivateKey;
use Exception;
use App\Models\EtherumWallet;
use App\Models\EtherumWalletAdress;
use App\Models\EthTransaction;
use Illuminate\Http\Request;
use App\Traits\ManagesResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class EtherumController extends Controller
{
    use  ManagesResponse;

    public function EthGenerateWallet(Request $request){

        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/ethereum/wallet",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "GET",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return $error;
        } else {
                EtherumWallet::on('mysql::write')->create([
                    'user_id' => auth()->user()->id,
                    'response' => $response
                ]);


            return $response;
        }
    }

    public function EthGenerateAddress($xpub){

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
        CURLOPT_URL => "https://api.tatum.io/v3/ethereum/address/" . $xpub . "/" . $index,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "GET",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return $error;
        } else {
            $checkRef = EtherumWalletAdress::where('pub_key', $response)->first();

            if($checkRef && $checkRef->status == 0){
                return response()->json(['message'=>'Address already exist.'], 413);
            }elseif (!$checkRef){
                EtherumWalletAdress::on('mysql::write')->create([
                    'user_id' => auth()->user()->id,
                    'index' => $index,
                    'pub_key' => $xpub,
                    'address' => $response
                ]);
            }
            return $response;
        }
    }

    public function EthGenerateAddressPrivateKey(Request $request){

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
            $checkRef = EtherumPrivateKey::where('key', $response)->first();

            if($checkRef && $checkRef->status == 0){
                return response()->json(['message'=>'Private Key already exist.'], 413);
            }elseif (!$checkRef){
                EtherumPrivateKey::on('mysql::write')->create([
                    'user_id' => auth()->user()->id,
                    'key' => $response
                ]);
            }
            return $response;
        }
    }

    public function EthGetCurrentBlock(){

        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/ethereum/block/current",
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


    public function EthGetBlockByHash($hash){

        $hash = $hash;

        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/ethereum/block/" . $hash,
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

    public function EthGetBalance($address){

        $address = $address;
        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/ethereum/account/balance/" . $address,
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

    public function EthGetTransaction($hash){

        $hash = $hash;
        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/ethereum/transaction/" . $hash,
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

    public function EthGetTransactionCount($address){

        $address = $address;

        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/ethereum/transaction/count/" . $address,
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
    public function EthGetTransactionByAddress($address){

        $address = $address;
        $query = array(
        "pageSize" => "10"
        );

        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/ethereum/account/transaction/" . $address . "?" . http_build_query($query),
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

    public function EthBlockchainTransfer(Request $request){

        $ref = '51' . substr(uniqid(mt_rand(), true), 0, 8);

        $validator = Validator::make($request->all(), [
            'to'=>'required|string|min:5',
            'amount'=>'required',
            'privkey'=>'required|string|min:5'

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $curl = curl_init();

        $payload = array(
        "to" => $request->to,
        "currency" => "ETH",
        "amount" =>  $request->amount,
        "fromPrivateKey" => $request->privkey
        );

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_URL => "https://api.tatum.io/v3/ethereum/transaction",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return $error;
        } else {
            EthTransaction::on('mysql::write')->create([
                'user_id' => auth()->user()->id,
                'to' => $request->to,
                'currency' => "ETH",
                'amount' => $request->amount,
                'ref' =>  'TXC_' . $ref,
                'fromPrivateKey' => $request->privkey,
                'response' => $response
            ], 201);
            return $response;
        }
    }

    public function EthBlockchainSmartContractInvocation(Request $request){

        $curl = curl_init();

        $payload = array(
        "contractAddress" => $request->contractAddress,
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
        CURLOPT_URL => "https://api.tatum.io/v3/ethereum/smartcontract",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
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

    public function EthGetInternalTransactionByAddress($address){

        $address = $address;
        $query = array(
        "pageSize" => "10"
        );

        $curl = curl_init();

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_URL => "https://api.tatum.io/v3/ethereum/account/transaction/erc20/internal/" . $address . "?" . http_build_query($query),
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

    public function EthBroadcast(Request $request){

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
        CURLOPT_URL => "https://api.tatum.io/v3/ethereum/broadcast",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
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

}