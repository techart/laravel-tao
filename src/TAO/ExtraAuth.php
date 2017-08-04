<?php

namespace TAO;

class ExtraAuth {

    public function attempt($credentials)
    {
        $email = $credentials['email'];
        $password = $credentials['password'];
        $result = $this->process($email, $password);
        if (is_array($result) && isset($result['name'])) {
            $name = $result['name'];
            $user = app()->make(\TAO\Fields\Model\User::class);
            $users = $user->where('email', $email)->take(1)->get();
            if (count($users) == 0) {
                $user = app()->make(\TAO\Fields\Model\User::class);
                $user->field('name')->set($name);
                $user->field('email')->set($email);
                $user->field('password')->set(bcrypt('~'));
                $user->field('is_admin')->set(1);
                $user->save();
            }
            return true;
        }
        return false;
    }

    protected function emailToLogin($email)
    {
        $re = config('auth.extra.login', '{^(.+)@techart\.ru$}');
        if (!empty($re)) {
            if ($m = \TAO::regexp($re, $email)) {
                return $m[1];
            }
        }
        return false;
    }

    protected function authUrl()
    {
        return config('auth.extra.url');
    }

    protected function process($email, $password)
    {
        $login = $this->emailToLogin($email);
        if ($login) {
            $url = $this->authUrl();
            if (!empty($url)) {
                $curl = new \Curl\Curl();
                $curl->setBasicAuthentication($login, $password);
                $curl->get($url);
                if (!$curl->error) {
                    return array(
                        'name' => $login,
                    );
                }
            }
        }
        return false;
    }

}