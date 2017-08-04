<?php

namespace TAO\Admin;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends \TAO\Controller
{
    use AuthenticatesUsers;

    public function showLoginForm()
    {
        return $this->render('tao::admin.login');
    }

    public function redirectPath()
    {
        return '/admin';
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->flush();
        $request->session()->regenerate();
        return redirect('/admin/login');
    }

    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

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
