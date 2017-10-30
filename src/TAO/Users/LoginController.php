<?php

namespace TAO\Users;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

//dd(2);

class LoginController extends \TAO\Controller
{

    use AuthenticatesUsers;
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    protected function redirectTo()
    {
        return '/users/home/';
    }

    public function showLoginForm()
    {
        return view('users ~ login');
    }

    /**
     * @param Request $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        $credentials = $this->credentials($request);
        if ($credentials['password'] == '~') {
            return false;
        }

        $auth = app()->make('TAO\ExtraAuth');
        $result = $auth->attempt($credentials);

        if ($result) {
            $credentials['password'] = '~';
        }

        return $this->guard()->attempt(
            $credentials, $request->has('remember')
        );
    }
}
