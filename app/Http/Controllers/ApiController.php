<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Country;
use App\Models\FAQ;
use App\Models\GeneralDetail;
use App\Models\LGA;
use App\Models\Settings;
use App\Models\SiteSetting;
use App\Models\State;
use App\Traits\ManagesResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ApiController extends Controller
{
    use ManagesResponse;

    public function Country()
    {
        try {

            $data = Country::orderBy('name')->get();
            $message = 'countries successfully fetched';

            return $this->sendResponse($data,$message);
        }catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()],404);
        } catch(\Exception $e) {
            return response()->json(['message' => $e->getMessage()],500);
        }
    }


    public function States()
    {
        try {

            $data = State::orderBy('state')->paginate(50);
            $message = 'states successfully fetched';

            return $this->sendResponse($data,$message);
        }catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()],404);
        } catch(\Exception $e) {
            return response()->json(['message' => $e->getMessage()],500);
        }
    }
    public function GeneralDetail()
    {
        try {

            $data = GeneralDetail::first();
            $message = 'General details successfully fetched';

            return $this->sendResponse($data,$message);
        }catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()],404);
        } catch(\Exception $e) {
            return response()->json(['message' => $e->getMessage()],500);
        }
    }

    public function LGA()
    {
        try {

            $data = LGA::orderBy('state')->paginate(50);
            $message = 'General details successfully fetched';

            return $this->sendResponse($data,$message);
        }catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()],404);
        } catch(\Exception $e) {
            return response()->json(['message' => $e->getMessage()],500);
        }
    }

    public function SiteSetting()
    {
        try {

            $data = SiteSetting::first();
            $message = 'site details successfully fetched';

            return $this->sendResponse($data,$message);
        }catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()],404);
        } catch(\Exception $e) {
            return response()->json(['message' => $e->getMessage()],500);
        }
    }
    public function FAQs()
    {
        try {

            $data = FAQ::orderBy('created_at','desc')->get();
            $message = 'FAQ  successfully fetched';

            return $this->sendResponse($data,$message);
        }catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()],404);
        } catch(\Exception $e) {
            return response()->json(['message' => $e->getMessage()],500);
        }
    }
    public function ContactUs(Request $request, $dev_key)
    {
        try {

        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:100',
            'email'         => 'required|string|email',
            'phone' => 'required|string|min:4',
            'subject' => 'required|string|max:191',
            'message'     => 'required'
        ]);
        if($validator->fails()) {
            return response()->json($validator->errors());
        }

            $user             = new ContactMessage();
            $user->name   = $request->input('name');
            $user->phone   = $request->input('phone');
            $user->email   = $request->input('email');
            $user->subject   = $request->input('subject');
            $user->message   = $request->input('message');
            $user->status   = 0;
            $user->is_treated   = 0;
            $user->save();


            Mail::to('support@edekee.com')->send(new \App\Mail\ContactUsRequest($user));
            Mail::to($request->email)->send(new \App\Mail\ContactUsUser($user));

            $message = 'message sent successfully';

            return $this->sendResponse($user,$message);

        }catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()],404);
        } catch(\Exception $e) {
            return response()->json(['message' => $e->getMessage()],500);
        }

        }
}
