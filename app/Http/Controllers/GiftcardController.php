<?php

namespace App\Http\Controllers;

use App\Models\GiftCardCustomer;
use Illuminate\Http\Request;
use App\Enums\ActivityType;
use App\Models\GiftCard;
use App\Models\Location;
use App\Models\LocationResponse;
use App\Models\User;
use App\Traits\ManagesUsers;
use Illuminate\Support\Facades\Auth;
use App\Traits\ManagesResponse;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class GiftcardController extends Controller
{
    use  ManagesResponse, ManagesUsers;



    // public function upload(Request $request){
    //     $request->validate([
    //         'csv' => 'required|file'
    //     ]);

    //     $file = new \App\File();
    //     $file['original_name'] = $request['csv']->getClientOriginalName();
    //     $file['user_id'] = Auth::user()['id'];
    //     $file['path'] = 'files';
    //     $file['name'] = Str::random(32).'.'.\File::extension($file['original_name']);
    //     $request['csv']->storeAs($file['path'],$file['name']);
    //     $file->save();
    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Upload Success. File processing started.',
    //         'location' => '/wizard'
    //     ]);
    // }

    public function CreateCustomer (Request $request){

        $user = Auth::user();

        $ref = '51' . substr(uniqid(mt_rand(), true), 0, 8);

        $checkRef = GiftCardCustomer::where('user_id', auth()->user()->id)->first();

        if($checkRef){
            return Response::json([
                'status' => false,
                'message' => 'You already have an account created!'
            ], 419);
        }else{


        $data = array(
            'user_id' => auth()->user()->id,
            'given_name'       => $request->given_name,
            'family_name'      => $request->family_name,
            'email_address'  => $request->email_address,
            'address_line_1' => $request->address_line_1,
            'address_line_2' => $request->address_line_2,
            'locality' => $request->locality,
            'administrative_district_level_1' => $request->administrative_district_level_1,
            'postal_code' => $request->postal_code,
            "country" => $request->country,
            "phone_number" => $request->phone_number,
            "reference_id" => 'REF' . $ref,
            "note" => $request->note
        );

        $base_url = 'https://connect.squareupsandbox.com/v2/customers';

        $body = [
            "given_name" => $request->given_name,
            "family_name" => $request->family_name,
            "email_address" => $request->email_address,

                "address" => [
                    "address_line_1" => $request->address_line_1,
                    "address_line_2" => $request->address_line_2,
                    "locality" => $request->locality,
                    "administrative_district_level_1" => $request->administrative_district_level_1,
                    "postal_code" => $request->postal_code,
                    "country" => $request->country
                ],
                "phone_number" => $request->phone_number,
                "reference_id" => 'REF' . $ref,
                "note" => $request->note

            ];

            $response = Http::withHeaders([
                'Square-Version' => '2022-12-14',
                'Authorization' => 'Bearer '.env('SQAUREUP_SANDBOX_KEY'),
                'content-type' => 'application/json'
            ])->post($base_url, $body);

        $response = $response->getBody()->getContents();


        $checkUser = GiftCardCustomer::on('mysql::write')->create($data);

        $this->saveUserActivity(ActivityType::CREATEGIFTCARD_CUSTOMER, '', $user->id);
        return response()->json([
            "message" => "Giftcard Customer account created successfully",
            'data' => $response,
            'status' => 'success',
        ], 201);
    }

    }

    public function CustomerDetails ($id)
    {
        $base_url = 'https://connect.squareupsandbox.com/v2/customers/' .$id;
        try{

            $id = $id;

            $response = Http::withHeaders([
                'Square-Version' => '2022-12-14',
                'Authorization' => 'Bearer '.env('SQAUREUP_SANDBOX_KEY'),
                'content-type' => 'application/json'
            ])->get($base_url);
            return $response;

        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 422);
        }
    }

    public function UpdateCustomerDetails(Request $request, $id, $user_id){

        $user = Auth::user();

        $id = $id;

        $product = GiftCardCustomer::where('user_id', $user_id)->first();

        $product->user_id = auth()->user()->id;
        $product->phone_number = $user->phone;
        $product->email_address = $user->email;
        $product->note = $request->note;


        $base_url = 'https://connect.squareupsandbox.com/v2/customers/' .$id;

        $body = [
            "phone_number" => $request->phone_number,
            "email_address" => $request->email_address,
            "note" => $request->note,
            "version" => 2
        ];

            $response = Http::withHeaders([
                'Square-Version' => '2022-12-14',
                'Authorization' => 'Bearer '.env('SQAUREUP_SANDBOX_KEY'),
                'content-type' => 'application/json'
            ])->put($base_url, $body);

        $response = $response->getBody()->getContents();

        $product->save();

        $this->saveUserActivity(ActivityType::UPDATEGIFTCARD_CUSTOMER, '', $user->id);
        return response()->json([
            "message" => "Customer account updated successfully",
            'data' => $response,
            'status' => 'success',
        ], 201);

    }


    public function CreateGiftCard (Request $request, $location_id){

        $user = Auth::user();

        $checkRef = GiftCard::where('user_id', auth()->user()->id)->first();

        if($checkRef){
            return response()->json(['message'=>'giftcard already exist.'], 413);
        }else{

        $base_url = 'https://connect.squareupsandbox.com/v2/gift-cards';

        $body = [
            "idempotency_key" => Str::random(12),
            "location_id" => $location_id,

                "gift_card" => [
                    "type" => "DIGITAL"
                ],

            ];

            $response = Http::withHeaders([
                'Square-Version' => '2022-12-14',
                'Authorization' => 'Bearer '.env('SQAUREUP_SANDBOX_KEY'),
                'content-type' => 'application/json'
            ])->post($base_url, $body);

        $response = $response->getBody()->getContents();


        GiftCard::on('mysql::write')->create([
            'user_id' => auth()->user()->id,
            'response' => $response,
            "location_id" => $location_id,
            "idempotency_key" => Str::random(12)
        ]);
        $this->saveUserActivity(ActivityType::CREATEGIFTCARD, '', $user->id);
        return response()->json([
            "message" => "Giftcard created successfully",
            'data' => $response
        ], 201);
    }
}

    public function LINKGiftCard(Request $request, $id){

        $user = Auth::user();

        $id = $id;

        $base_url = 'https://connect.squareupsandbox.com/v2/gift-cards/'.$id.'/link-customer';

        $body = [
            "customer_id" => $request->customer_id
            ];

            $response = Http::withHeaders([
                'Square-Version' => '2022-12-14',
                'Authorization' => 'Bearer '.env('SQAUREUP_SANDBOX_KEY'),
                'content-type' => 'application/json'
            ])->post($base_url, $body);

        $response = $response->getBody()->getContents();

        $this->saveUserActivity(ActivityType::LINKGIFTCARD, '', $user->id);
        return response()->json([
            "message" => "Giftcard Linked to customer successfully",
            'data' => $response,
            'status' => 'success',
        ], 200);

    }

    public function RetrieveCardGAN (Request $request)
    {
        $base_url = 'https://connect.squareupsandbox.com/v2/gift-cards/from-gan';
        try{

            $body = [
                "gan" => $request->gan
            ];

            $response = Http::withHeaders([
                'Square-Version' => '2022-12-14',
                'Authorization' => 'Bearer '.env('SQAUREUP_SANDBOX_KEY'),
                'content-type' => 'application/json'
            ])->post($base_url, $body);

            return $response;

        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 422);
        }
    }

    public function RetrieveGIFTCard ($id)
    {
        $base_url = 'https://connect.squareupsandbox.com/v2/gift-cards/' .$id;
        try{

            $id = $id;

            $response = Http::withHeaders([
                'Square-Version' => '2022-12-14',
                'Authorization' => 'Bearer '.env('SQAUREUP_SANDBOX_KEY'),
                'content-type' => 'application/json'
            ])->get($base_url, $id);
            return $response;

        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 422);
        }
    }

    public function CreategiftCardActivity(Request $request){

        $user = Auth::user();

        $base_url = 'https://connect.squareupsandbox.com/v2/gift-cards/activities';

        $body = [
            "idempotency_key" => Str::random(12),
            "gift_card_activity" => [
                "gift_card_activity" => $request->gift_card_activity,
                "type" =>  "ACTIVATE",
                "location_id" => $request->location_id,

            "activate_activity_details" => [
                "order_id" => $request->order_id,
                "line_item_uid" => $request->line_item_uid
            ],
        ],
        ];

            $response = Http::withHeaders([
                'Square-Version' => '2022-12-14',
                'Authorization' => 'Bearer '.env('SQAUREUP_SANDBOX_KEY'),
                'content-type' => 'application/json'
            ])->post($base_url, $body);

        $response = $response->getBody()->getContents();

        $this->saveUserActivity(ActivityType::creategiftcardactivity, '', $user->id);
        return response()->json([
            "message" => "Giftcard activity changed successfully",
            'data' => $response,
            'status' => 'success',
        ], 200);

    }

    public function createLocation (Request $request){

        $user = Auth::user();

        $data = array(
            'user_id' => auth()->user()->id,
            'name'       => $request->name,
            'description'      => $request->description,
            'address_line_1' => $request->address_line_1,
            'locality' => $request->locality,
            'administrative_district_level_1' => $request->administrative_district_level_1,
            'postal_code' => $request->postal_code
        );

        $validator = Validator::make($data, [
            'name'       => 'required|unique:locations'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $base_url = 'https://connect.squareupsandbox.com/v2/locations';

        $body = [

            "location" => [
                "name" => $request->name,
                "description" => $request->description,
                "address" => [
                    "address_line_1" => $request->address_line_1,
                    "locality" => $request->locality,
                    "administrative_district_level_1" => $request->administrative_district_level_1,
                    "postal_code" => $request->postal_code
                ],
            ],

            ];

            $response = Http::withHeaders([
                'Square-Version' => '2022-12-14',
                'Authorization' => 'Bearer '.env('SQAUREUP_SANDBOX_KEY'),
                'content-type' => 'application/json'
            ])->post($base_url, $body);

        $response = $response->getBody()->getContents();

        $checkUser = Location::where('user_id', auth()->user()->id)->first();

        if ($checkUser) {
            return Response::json([
                'status' => false,
                'message' => 'You already have an account created!'
            ], 419);
        }
        else {
    $checkUser = Location::on('mysql::write')->create($data);

    LocationResponse::on('mysql::write')->create([
        'user_id' => auth()->user()->id,
        'response' => $response
    ], 201);

        $this->saveUserActivity(ActivityType::CREATEGIFTCARD_LOCATION, '', $user->id);
        return response()->json([
            "message" => "Location created successfully",
            'data' => $response,
            'status' => 'success',
        ], 201);
    }

    }

    public function detailsLocation ($id)
    {
        $base_url = 'https://connect.squareupsandbox.com/v2/locations/' .$id;
        try{

            $id = $id;

            $response = Http::withHeaders([
                'Square-Version' => '2022-12-14',
                'Authorization' => 'Bearer '.env('SQAUREUP_SANDBOX_KEY'),
                'content-type' => 'application/json'
            ])->get($base_url, $id);
            return $response;

        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()], 422);
        }
    }


}




