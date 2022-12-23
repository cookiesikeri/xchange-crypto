<?php

namespace App\Http\Controllers;

use App\Models\EtherumPrivateKey;
use Exception;
use App\Models\EtherumWallet;
use App\Models\EtherumWalletAdress;
use App\Models\EthTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\ActivityType;
use App\Traits\ManagesUsers;
use App\Traits\ManagesResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EtherumController extends Controller
{
    use  ManagesResponse, ManagesUsers;

    public function EthGenerateWallet(Request $request){
        $user = Auth::user();

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
            // return $error;
            return response()->json($error);
        } else {
            $checkUser = EtherumWallet::where('user_id', auth()->user()->id)->first();

            if ($checkUser) {
                return Response::json([
                    'status' => false,
                    'message' => 'You already have an account created!'
                ], 419);
            }
            else {
             $checkUser = EtherumWallet::on('mysql::write')->create([
                 'user_id' => auth()->user()->id,
                 'mnemonic' => $response
             ]);
                $this->saveUserActivity(ActivityType::CREATE_ETH_WALLET, '', $user->id);
                return response()->json([ 'status' => true, 'message' => 'Wallet created Successfully', 'response' => $response ], 201);
        }
    }
    }

    public function EthGenerateAddress($xpub){

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
            $this->saveUserActivity(ActivityType::CREATE_ETH_ADDRESS, '', $user->id);
            return response()->json([ 'status' => true, 'message' => 'private key created Successfully', 'response' => $response ], 201);
        }
    }

    public function EthGenerateAddressPrivateKey(Request $request){

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
            $checkRef = EtherumPrivateKey::where('key', $response)->first();

            if($checkRef && $checkRef->status == 0){
                return response()->json(['message'=>'Private Key already exist.'], 413);
            }elseif (!$checkRef){
                EtherumPrivateKey::on('mysql::write')->create([
                    'user_id' => auth()->user()->id,
                    'key' => $response
                ]);
            }
            $this->saveUserActivity(ActivityType::ETHGenerateAddressPrivateKey, '', $user->id);

            return response()->json([ 'status' => true, 'message' => 'private key created Successfully', 'response' => $response ], 201);
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

        $user = Auth::user();

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

            $this->saveUserActivity(ActivityType::SEND_ETH, '', $user->id);
            return response()->json([ 'status' => true, 'message' => 'Blockchain sent Successfully', 'response' => $response ], 201);
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
            $this->saveUserActivity(ActivityType::BROADCAST_ETH, '', $user->id);
            return response()->json([ 'status' => true, 'message' => 'Broadcast Successful', 'response' => $response ], 200);
        }

    }

    public function EthEstimateGas(Request $request){

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
        CURLOPT_URL => "https://api.tatum.io/v3/ethereum/gas",
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

    public function EthEstimateGasMultiple(Request $request){

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
        CURLOPT_URL => "https://api.tatum.io/v3/ethereum/gas",
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

    public function GetETHprivateKey($user_id)
    {
        try {
            $data = EtherumPrivateKey::on('mysql::write')->where('user_id', $user_id)->first();
            $message = 'data successfully fetched';

            return $this->sendResponse($data,$message);
        }catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()],404);
        } catch(\Exception $e) {
            return response()->json(['message' => $e->getMessage()],500);
        }
    }
    public function GetWalletDeatils()
    {
        try {
            $data = EtherumWallet::on('mysql::write')->where('user_id', auth()->user()->id)->first();
            $message = 'data successfully fetched';

            return $this->sendResponse($data,$message);
        }catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()],404);
        } catch(\Exception $e) {
            return response()->json(['message' => $e->getMessage()],500);
        }
    }

}
