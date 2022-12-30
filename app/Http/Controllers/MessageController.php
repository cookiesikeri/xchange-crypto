<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function MessageDetails($id)
    {
        $order = ContactMessage::where('id', $id)->first();


        return view('cms.message.message_item', compact('order'));
    }

    public function message()
    {
        $contacts = ContactMessage::orderBy('created_at', 'desc')->get();

        $pasengercnt = ContactMessage::count();
      
        return view('cms.message.all_messages', compact(['pasengercnt', 'contacts']));
    }

    public function MarkmessageunRead ($id)
    {
   
        $property = ContactMessage::where('id', $id)->first();
        
        $property->is_treated = 0;
        $property->status = 0;

        $property->save();

        Session::flash('success', 'Message marked as Unread.');

        return redirect()->route('cms.messages');
    }


    public function messageRead($id)
    {
 

        $property = ContactMessage::where('id', $id)->first();
        
        $property->is_treated = 1;
        $property->status = 1;

        $property->update();

        Session::flash('success', 'Message marked as read.');

        return redirect()->route('cms.messages');
    }



    public function messageDelete($id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->delete();

        Session::flash('success', 'Message Deleted successfully.');
        return back();
    }

    public function messageReadUnread()
    {

        $contacts = ContactMessage::where('is_treated',  0)->where('status', 0)->orderBy('created_at', 'desc')->get();
        $pasengercnt = ContactMessage::where('is_treated', 0)->where('status', 0)->count();
    

        return view('cms.message.unread_messages', compact(['pasengercnt', 'contacts']));

    }

    public function Readmessages()
    {

        $contacts = ContactMessage::where('is_treated',  1)->where('status', 1)->orderBy('created_at', 'desc')->get();
        $pasengercnt = ContactMessage::where('is_treated', 1)->where('status', 1)->count();
    

        return view('cms.message.read_messages', compact(['pasengercnt', 'contacts']));

    }



}
