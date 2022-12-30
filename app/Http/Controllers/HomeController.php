<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
// use App\Http\Controllers\admin\AdminController;

class HomeController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    // protected $redirectTo = '/dashboard';



    public function __construct()
    {
        $this->middleware('guest:admin')->except('logout', 'index');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function login()
    {
        if (Auth::check()) {
            return redirect('dashboard');
        }else{

            return view('cms.login');
        }
    }

    public function index()
    {

            return "Welcome to Taheerxchange Nigeria System Api Live APP...";
    }


    public function loginSubmit(Request $request)
    {
        // Validate the form data
        $this->validate($request, [
            'email'   => 'required|email',
            'password' => 'required|min:6'
        ]);

        $credentials = [
            'email' => $request->email,
            'password' => $request->password
        ];

        // Attempt to log the user in
        if (Auth::guard('admin')->attempt($credentials)) {
            Session::flash('success', 'Login sucessful');
            return redirect()->intended(route('dashboard'));
        }
        // if unsuccessful, then redirect back to the login with the form data
        Session::flash('error', 'Your credentials do not match our records.');
        return redirect()->back()->withInput($request->only('email', 'remember'));
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('cms.login');
    }
}
