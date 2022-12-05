<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enums\ActivityType;
use App\Models\User;
use App\Models\VirtualAccount;
use App\Traits\ManagesUsers;
use Illuminate\Support\Facades\Auth;
use App\Traits\ManagesResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
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
            "status" => 1

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
            "message" => "Customer created successfully",
            'data' => $response,
            'status' => 'success',
        ], 201);
    }
    }

    public function createCard ()
    {

    }





}

