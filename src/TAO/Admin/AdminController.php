<?php

namespace TAO\Admin;

class AdminController extends \TAO\Controller
{
    protected function realm()
    {
        return 'admin';
    }

    public function layout()
    {
        return app()->taoAdmin->layout();
    }

    protected function urlLogin()
    {
        return '/admin/login';
    }

    protected function accessAction($method, $parameters)
    {
        if (! \Auth::check()) {
            return false;
        }
        $rc = \Auth::user()->accessToRealm($this->realm());
        if (!$rc) {
            \Auth::user()->isBlocked = true;
            return response($this->render('404'), 404);
        }
        return true;
    }


}