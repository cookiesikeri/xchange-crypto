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
use App\Models\PolygonWallet;
use App\Models\PolygonWalletAddress;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
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
            return $error;
        } else {
            PolygonWallet::on('mysql::write')->create([
                'user_id' => auth()->user()->id,
                'response' => $response
            ]);

            $this->saveUserActivity(ActivityType::CREATE_POLYGON_WALLET, '', $user->id);

            return response()->json([ 'status' => true, 'message' => 'Wallet created Successfully', 'response' => $response ], 200);
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
            return response()->json([ 'status' => true, 'message' => 'current block number fetched created Successfully', 'response' => $response ], 200);

    }
}

public function PolygonGetBlockbyHash($hash){

    $hash = $hash;

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
        return response()->json([ 'status' => true, 'message' => 'Polygon block by hash fetched created Successfully', 'response' => $response ], 200);
    }
}


}
