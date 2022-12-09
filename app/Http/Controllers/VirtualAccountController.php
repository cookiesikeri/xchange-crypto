<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enums\ActivityType;
use App\Models\User;
use App\Models\VirtualAccount;
use App\Models\VirtualCard;
use App\Models\VirtualCardRequest;
use App\Traits\ManagesUsers;
use Illuminate\Support\Facades\Auth;
use App\Traits\ManagesResponse;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;

class VirtualAccountController extends Controller
{
    use  ManagesResponse, ManagesUsers;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        $response = Http::get('https://jsonplaceholder.typicode.com/todos');

        return $response->json();
    }

    public function createAccountIND(Request $request)
    {
        $user = Auth::user();

        $data = array(
            'user_id' => auth()->user()->id,
            'name'       => $request->name,
            'number'      => $request->number,
            'firstName'  => $request->firstName,
            'lastName' => $request->lastName,
            'otherNames' => $request->otherNames,
            'dob' => $request->dob,
            'line1' => $request->line1,
            'line2' => $request->line2,
            "city" => $request->city,
            "state" => $request->state,
            "postalCode" => $request->postalCode,
            "country" => $request->country,
            "phoneNumber" => $request->phoneNumber,
            "emailAddress" => $request->emailAddress,
            "user_type" => "individual",
            "status" => "active",
            'id_type'      => $request->type

        );


        $base_url = 'https://api.sandbox.sudo.cards/customers';

        $body = [
            "type" => "individual",
            "status" => "active",
            "name" => $request->name,

                "identity" => [
                    "type" => $request->type,
                    "number" => $request->number
                ],
                "individual" => [
                    "firstName" => $request->firstName,
                    "lastName" => $request->lastName,
                    "otherNames" => $request->otherNames ? $request->otherNames : "string",
                    "dob" => $request->dob
                ],
                "documents" => [
                    "idFrontUrl" => $request->idFrontUrl,
                    "idBackUrl" => $request->idBackUrl,
                    "incorporationCertificateUrl" => $request->incorporationCertificateUrl,
                    "addressVerificationUrl" => $request->addressVerificationUrl
                ],
                "billingAddress" => [
                    "line1" => $request->line1,
                    "line2" => $request->line2 ? $request->line2 : null,
                    "city" => $request->city,
                    "state" => $request->state,
                    "postalCode" => $request->postalCode,
                    "country" => $request->country,
                    "phoneNumber" => $request->phoneNumber,
                    "emailAddress" => $request->emailAddress
                ],

            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.env('SUDO_SANDBOX_KEY'),
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ])->post($base_url, $body);

        $response = $response->getBody()->getContents();

        $checkUser = VirtualAccount::where('phoneNumber', $request->phoneNumber)->first();

        if ($checkUser) {
            return Response::json([
                'status' => false,
                'message' => 'You already have an account created!'
            ], 419);
        }
        else {
    $checkUser = VirtualAccount::on('mysql::write')->create($data);
    $this->saveUserActivity(ActivityType::VIRTUALACCOUNT, '', $user->id);

        return response()->json([
            "message" => "Individual Virtual account created successfully",
            'data' => $response,
            'status' => 'success',
        ], 201);
    }
    }

    public function GetCustomer ($id)
    {
        $base_url = 'https://api.sandbox.sudo.cards/customers';
        try{

            $id = $id;

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.env('SUDO_SANDBOX_KEY'),
                'accept' => 'application/json'
            ])->get($base_url, $id);
            return response()->json([ 'status' => true, 'message' => 'customer details fetched Successfully', 'data' => $response ['data']], 200);

        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 422);
        }
    }

    public function GetCustomerLocal($id)
    {
        try {
            $data = VirtualAccount::on('mysql::write')->where('user_id', $id)->orWhere('id', $id)->first();
            $message = 'data successfully fetched';

            return $this->sendResponse($data,$message);
        }catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()],404);
        } catch(\Exception $e) {
            return response()->json(['message' => $e->getMessage()],500);
        }
    }

    public function UpdateAccountIND(Request $request, $id, $user)
    {
        $id = $id;

        $product = VirtualAccount::where('user_id', $user)->first();

        $product->user_id = auth()->user()->id;
        $product->name = $request->name;
        $product->number = $request->number;
        $product->firstName = $request->firstName;
        $product->otherNames = $request->otherNames;
        $product->dob = $request->dob;
        $product->line1 = $request->line1;
        $product->line2 = $request->line2;
        $product->city = $request->city;
        $product->state = $request->state;
        $product->postalCode = $request->postalCode;
        $product->country = $request->country;
        $product->phoneNumber = $request->phoneNumber;
        $product->emailAddress = $request->emailAddress;
        $product->status = "active";

        $base_url = 'https://api.sandbox.sudo.cards/customers/'. $id;

        $body = [
            "type" => "individual",
            "status" => "active",
            "name" => $request->name,

                "identity" => [
                    "type" => $request->type,
                    "number" => $request->number
                ],
                "individual" => [
                    "firstName" => $request->firstName,
                    "lastName" => $request->lastName,
                    "otherNames" => $request->otherNames ? $request->otherNames : "string",
                    "dob" => $request->dob
                ],
                "documents" => [
                    "idFrontUrl" => $request->idFrontUrl,
                    "idBackUrl" => $request->idBackUrl,
                    "incorporationCertificateUrl" => $request->incorporationCertificateUrl,
                    "addressVerificationUrl" => $request->addressVerificationUrl
                ],
                "billingAddress" => [
                    "line1" => $request->line1,
                    "line2" => $request->line2 ? $request->line2 : null,
                    "city" => $request->city,
                    "state" => $request->state,
                    "postalCode" => $request->postalCode,
                    "country" => $request->country,
                    "phoneNumber" => $request->phoneNumber,
                    "emailAddress" => $request->emailAddress
                ],

            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.env('SUDO_SANDBOX_KEY'),
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ])->put($base_url, $body);

        $response = $response->getBody()->getContents();

        $product->save();
    $this->saveUserActivity(ActivityType::UPDATE_VIRTUALACCOUNT, '', auth()->user()->id);

        return response()->json([
            "message" => "Customer profile updated successfully",
            'data' => $response,
            'status' => 'success',
        ], 200);

}

public function createAccountCOMP(Request $request)
{
    $user = Auth::user();

    $data = array(
        'user_id' => auth()->user()->id,
        'name'       => $request->name,
        'id_type'      => $request->type,
        'number'      => $request->number,
        'firstName'  => $request->firstName,
        'lastName' => $request->lastName,
        'otherNames' => $request->otherNames,
        'dob' => $request->dob,
        'line1' => $request->line1,
        'line2' => $request->line2,
        "city" => $request->city,
        "state" => $request->state,
        "postalCode" => $request->postalCode,
        "country" => $request->country,
        "phoneNumber" => $request->phoneNumber,
        "emailAddress" => $request->emailAddress,
        "user_type" => "company",
        "status" => "active"

    );


    $base_url = 'https://api.sandbox.sudo.cards/customers';

    $body = [
        "type" => "company",
        "status" => "active",
        "name" => $request->name,

            "identity" => [
                "type" => $request->type,
                "number" => $request->number
            ],
            "company" => [
                "name" => $request->name
            ],
            "officer" => [
                "firstName" => $request->firstName,
                "lastName" => $request->lastName,
                "otherNames" => $request->otherNames ? $request->otherNames : "string",
                "dob" => $request->dob
            ],
            "documents" => [
                "idFrontUrl" => $request->idFrontUrl,
                "idBackUrl" => $request->idBackUrl,
                "incorporationCertificateUrl" => $request->incorporationCertificateUrl,
                "addressVerificationUrl" => $request->addressVerificationUrl
            ],
            "billingAddress" => [
                "line1" => $request->line1,
                "line2" => $request->line2 ? $request->line2 : null,
                "city" => $request->city,
                "state" => $request->state,
                "postalCode" => $request->postalCode,
                "country" => $request->country,
                "phoneNumber" => $request->phoneNumber,
                "emailAddress" => $request->emailAddress
            ],

        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.env('SUDO_SANDBOX_KEY'),
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ])->post($base_url, $body);

    $response = $response->getBody()->getContents();

    $checkUser = VirtualAccount::where('phoneNumber', $request->phoneNumber)->first();

    if ($checkUser) {
        return Response::json([
            'status' => false,
            'message' => 'You already have an account created!'
        ], 419);
    }
    else {
$checkUser = VirtualAccount::on('mysql::write')->create($data);
$this->saveUserActivity(ActivityType::VIRTUALACCOUNT, '', $user->id);

    return response()->json([
        "message" => "Company Virtual account created successfully",
        'data' => $response,
        'status' => 'success',
    ], 201);
}
}

public function createCard (Request $request)
{
    $user = Auth::user();

    $data = array(
        'user_id' => auth()->user()->id,
        'brand'       => $request->brand,
        'currency'      => $request->currency,
        'issuerCountry'      => $request->issuerCountry,
        'customerId' => $request->customerId,
        'allowedCategories' => $request->allowedCategories,
        'blockedCategories' => $request->blockedCategories,
        'atm' => "true",
        'pos' => "true",
        "web" => "true",
        "mobile" =>"true",
        "interval" => $request->interval,
        "amount" => $request->amount,
        "sendPINSMS" => $request->sendPINSMS,
        "status" => "active"

    );


    $base_url = 'https://api.sandbox.sudo.cards/cards';

    $body = [
        "type"=> $request->type,
        "currency"=> $request->currency,
        "issuerCountry"=> $request->issuerCountry,
        "status"=>"active",
        "brand"=> $request->brand,
        "customerId"=> "$request->customerId",
        "spendingControls"=>[
            "allowedCategories"=> [$request->allowedCategories],
            "blockedCategories"=>[$request->blockedCategories],
            "channels"=>[
                "atm"=>true,
                "pos"=>true,
                "web"=>true,
                "mobile"=>true
            ],
            "spendingLimits"=> [
                ["interval" => "daily",
                "amount" => 5000]
            ],
            "sendPINSMS"=> $request->sendPINSMS


        ],

    ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.env('SUDO_SANDBOX_KEY'),
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ])->post($base_url, $body);

    $response = $response->getBody()->getContents();

    $checkUser = VirtualCardRequest::on('mysql::write')->create($data);
    $this->saveUserActivity(ActivityType::CARDREQUEST, '', $user->id);

    return response()->json([
        "message" => "card request sent successfully",
        'data' => $response,
        'status' => 'success',
    ], 201);

}

    public function GetCustomerCard ($id)
    {
        $base_url = 'https://api.sandbox.sudo.cards/cards/customer';
        try{

            $id = $id;

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.env('SUDO_SANDBOX_KEY'),
                'accept' => 'application/json'
            ])->get($base_url, $id);
            return response()->json([ 'status' => true, 'message' => 'customer card fetched Successfully', 'data' => $response], 200);

        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 422);
        }
    }


    public function GetCustomerCards ($id)
    {
        $base_url = 'https://api.sandbox.sudo.cards/cards';
        try{

            $id = $id;

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.env('SUDO_SANDBOX_KEY'),
                'accept' => 'application/json'
            ])->get($base_url, $id);
            return response()->json([ 'status' => true, 'message' => 'customer cards fetched Successfully', 'data' => $response], 200);

        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 422);
        }
    }

    public function SetCardpin ($id)
    {

        $base_url = 'https://api.sandbox.sudo.cards/cards/'. $id; 'send-pin';

        try{

            $id = $id;
            $body = [
                "status"=>"active"
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.env('SUDO_SANDBOX_KEY'),
                'accept' => 'application/json'
            ])->put($base_url, $body);

            $response = $response->getBody()->getContents();

            return response()->json([ 'status' => true, 'message' => 'Default Card PIN sent Successfully', 'data' => $response], 200);

        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 422);
        }
    }

    public function changeCardpin (Request $request, $id)
    {

        $base_url = 'https://api.sandbox.sudo.cards/cards/'. $id; 'pin';

        try{
            $validator = Validator::make($request->all(), [
                'oldPin'    =>  'required',
                'newPin' =>  'required'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }


            $id = $id;
            $body = [
                "oldPin"=> $request->oldPin,
                "status"=>"active",
                "newPin"=>$request->newPin
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.env('SUDO_SANDBOX_KEY'),
                'accept' => 'application/json'
            ])->put($base_url, $body);

            $response = $response->getBody()->getContents();


            return response()->json([ 'status' => true, 'data' => $response], 200);

        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 422);
        }
    }






}

