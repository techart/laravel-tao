<?php

namespace TAO\Admin;

class Router extends \TAO\Router
{
    public $path;
    public $datatype;
    public $datatypeCode;

    public function route($request)
    {

        app()->router->get('admin/login', '\\TAO\\Admin\\LoginController@showLoginForm');
        app()->router->post('admin/login', '\\TAO\Admin\\LoginController@login');
        app()->router->post('admin/logout', '\\TAO\Admin\\LoginController@logout');
        app()->router->any('admin', '\\TAO\\Admin\\DashboardController@index');
        app()->router->any('tao/fields/api', '\\TAO\\Fields\\Controllers\\API@index');

        $path = $request->path();
        if ($m = app()->tao->regexp('{^admin/(.+)$}', $path)) {
            $path = $m[1];
            if ($m = app()->tao->regexp('{^datatype/([^/]+)$}', $path)) {
                $code = $m[1];
                $datatype = app()->tao->datatype($code, false);
                if ($datatype) {
                    $this->datatype = $datatype;
                    $this->datatypeCode = $code;
                    $controller = $datatype->adminController();
                    app()->router->any("/admin/datatype/{$code}", $controller);
                }
            }
        }

        return false;
    }
}
