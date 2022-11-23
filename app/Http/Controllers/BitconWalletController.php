<?php

namespace App\Http\Controllers;

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

            return response()->json([
                'status' => true,
                'message' => 'wallet created successfully ',
                'data' => $response
            ]);

        }
    }


    public function CreateBitcoinPrivateKey(Request $request){

        // $payload = array(
        //     'index'      =>  0,
        //     'bank_code'     =>  $request->mnemonic,
        // );

        $curl = curl_init();

        $fields = [
            'index'      =>  0,
            'bank_code'     =>  $request->mnemonic,
        ];
        $payload = http_build_query($fields);

        curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "x-api-key: ". env('TATUM_TEST_KEY')
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_URL => env('TATUM_BASE_URL')."bitcoin/wallet/priv",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return $error;
        } else {
            return response()->json($response);
        }
    }

    public function CreateBitcoinAddress(Request $request) {

        $xpub = "tpubDF7sz32kcKjWTuL2RL29LdroDtJdTX4tEq6FzJU44FM39BNhL2KP9MV7d4WpR4X8jU6KeKxwfsVm6UiCkivyqWZqPRzb9UBQx5aiL5BBJKG";
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
        echo "cURL Error #:" . $error;
        return response()->json($error);
        } else {
            $checkRef = BitconWallet::where('pub_key', $xpub)->first();

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

            return response()->json([
                'status' => true,
                'message' => 'bitcoin address created successfully ',
                $response
            ]);

        }
    }



}
