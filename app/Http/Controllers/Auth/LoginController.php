<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Globals\Visitor;
class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function validateLogin(Request $request)
    {
        $validate[$this->username()] = 'required';
        $validate['password']        = 'required';

        $this->validate($request, $validate);
    }

    public function login(Request $request)
    {
        Visitor::use_the_counter();
        $this->validateLogin($request);

        if ($this->attemptLogin($request)) 
        {
            $user = $this->guard()->user();
            $user->generateToken();
            return response()->json($user->toArray());
        }

        return $this->sendFailedLoginResponse($request);
    }

    public function logout(Request $request)
    {
        $user = Auth::guard('api')->user();

        if ($user) 
        {
            $user->api_token = null;
            $user->save();
        }

        $return['message'] = 'User logged out.';

        return response()->json($return, 200);
    }
}
