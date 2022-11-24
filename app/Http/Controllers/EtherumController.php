<?php

namespace App\Http\Controllers;


use Exception;
use App\Models\EtherumWallet;
use App\Models\EtherumWalletAdress;
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

}
