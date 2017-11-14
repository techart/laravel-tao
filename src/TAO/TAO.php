<?php

namespace TAO;

use Techart\Frontend\PathResolver;

class TAO
{
    /**
     * @var
     */
    public $app;
    /**
     * @var
     */
    public $routers;
    /**
     * @var
     */
    public $router;
    /**
     * @var
     */
    public $routerName;

    public $layout = 'layouts.app';

    protected $datatypes = null;
    protected $controller;
    protected $inAdmin = false;

    public function useLayout($name)
    {
        $this->layout = $name;
    }

    public function getLayout()
    {
        return $this->layout;
    }

    public function setController($controller)
    {
        $this->controller = $controller;
    }

    public function controller()
    {
        return $this->controller;
    }

    public function setInAdmin($value)
    {
        $this->inAdmin = $value;
        return $this;
    }

    public function inAdmin()
    {
        return $this->inAdmin;
    }

    public function pageNotFound()
    {
        return response(view('404'), 404);
    }

    /**
     *
     */
    public function route()
    {
        if (!$this->isCLI()) {
            $request = \Request::getFacadeRoot();
            $found = false;
            foreach (array_keys($this->routers()) as $name) {
                $router = $this->router($name);
                if (method_exists($router, 'route')) {
                    $data = $router->route($request);
                    if ($data) {
                        $this->router = $router;
                        $this->routerName = $name;

                        if (is_string($data)) {
                            $controller = $data;
                            $action = false;
                            if (preg_match('{^(.+)@(.+)$}', $data, $m)) {
                                $controller = $m[1];
                                $action = $m[2];
                            }
                            $data = array(
                                'controller' => $controller,
                                'action' => $action,
                            );
                        }

                        $controller = isset($data['controller']) ? $data['controller'] : false;
                        $action = isset($data['action']) ? $data['action'] : 'index';

                        if ($controller && $action) {
                            $pattern = $request->getPathInfo();
                            $controller = '\\' . trim($controller, '\\');
                            app()->router->any($pattern, "{$controller}@{$action}");
                            break;
                        }
                    }
                }
            }

            //if (!$found) {
            //    app()->router->any(Request::getRequestUri(), function () {
            //        return response(view('404'), 404);
            //    });
            //}
        }
    }

    public function addRouter($name, $class)
    {
        $this->routers();
        if (!isset($this->routers[$name])) {
            $this->routers[$name] = $class;
        }
        return $this;
    }

    public function routes()
    {
        if (config('auth.public.login', false)) {
            $controller = '\\' . (\TAO::datatype('users')->loginController());
            $urlLogin = \TAO::datatype('users')->loginUrl();
            \Route::get($urlLogin, "{$controller}@showLoginForm");
            \Route::post($urlLogin, array('as' => 'login', 'uses' => "{$controller}@login"));
            \Route::get('/users/logout/', "{$controller}@logout");

        }
        /*
                $this->get('login', 'Auth\LoginController@showLoginForm')->name('login');
                $this->post('login', 'Auth\LoginController@login');
                $this->post('logout', 'Auth\LoginController@logout')->name('logout');

                // Registration Routes...
                $this->get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
                $this->post('register', 'Auth\RegisterController@register');

                // Password Reset Routes...
                $this->get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
                $this->post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
                $this->get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
                $this->post('password/reset', 'Auth\ResetPasswordController@reset');
        */

        foreach (array_keys($this->routers()) as $name) {
            $router = $this->router($name);
            if (method_exists($router, 'routes')) {
                $router->routes();
            }
        }
        $this->route();
    }

    /**
     * @return array
     */
    public function routers()
    {
        if (is_null($this->routers)) {
            $this->routers = config('tao.routers');
            if (!is_array($this->routers)) {
                $this->routers = array();
            }
        }
        return $this->routers;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function router($name)
    {
        $this->routers();
        if (isset($this->routers[$name])) {
            if (is_string($this->routers[$name])) {
                $this->routers[$name] = app($this->routers[$name]);
            }
            return $this->routers[$name];
        }
    }

    public function datatypeClasses()
    {
        if (!$this->datatypes) {
            $this->datatypes = config('tao.datatypes', array());
        }
        return $this->datatypes;
    }


    public function datatypeClass($name, $default = null)
    {
        $datatypes = $this->datatypeClasses();
        if (!isset($datatypes[$name]) && !is_null($default)) {
            return $default;
        }
        return $datatypes[$name];
    }

    public function datatype($name, $default = null)
    {
        $class = $this->datatypeClass($name);
        if (empty($class)) {
            return $default;
        }
        return app()->make($class);
    }

    public function addDatatype($name, $class)
    {
        $c = $this->datatypeClass($name);
        if (empty($c)) {
            $this->datatypes[$name] = $class;
        }
        return $this;
    }

    public function datatypeCodes()
    {
        return array_keys($this->datatypeClasses());
    }

    public function datatypes()
    {
        $datatypes = array();
        foreach ($this->datatypeCodes() as $code) {
            $datatypes[$code] = $this->datatype($code);
        }
        return $datatypes;
    }

    public function datatypeCodeByClass($class)
    {
        $class = ltrim($class, '/');
        $datatypes = $this->datatypeClasses();
        foreach ($datatypes as $code => $dclass) {
            $dclass = ltrim($dclass, '/');
            if ($class == $dclass) {
                return $code;
            }
        }
        return $class;
    }

    public function publicPath()
    {
        return rtrim(base_path('www'), '/');
    }

    /**
     * @return bool
     */
    public function isCLI()
    {
        return !isset($_SERVER['REQUEST_URI']);
    }

    public function regexp($regexp, $s)
    {
        return preg_match($regexp, $s, $m) ? $m : false;
    }

    public function connectionNameFor()
    {
        return false;
    }

    public function classModified($class)
    {
        $name = is_string($class) ? $class : get_class($class);
        $time = $this->getClassModifyTime($class);
        $key = 'class-modify-' . str_replace('\\', '-', $name);
        $cachedTime = \Cache::get($key, 0);
        if ($time > $cachedTime) {
            \Cache::put($key, $time, 500000);
            return true;
        }
        return false;
    }

    public function getClassModifyTime($class)
    {
        $ref = $class instanceof \ReflectionClass ? $class : new \ReflectionClass($class);
        $file = $ref->getFileName();
        $time = filemtime($file);
        $timeParent = 0;
        $refParent = $ref->getParentClass();
        if ($refParent) {
            $timeParent = $this->getClassModifyTime($refParent);
        }
        return $time > $timeParent ? $time : $timeParent;
    }

    public function path($extra = false)
    {
        $path = str_replace('/src/TAO', '', __DIR__);
        if ($extra) {
            $path .= "/$extra";
        }
        return $path;
    }

    public function isIterable(&$object)
    {
        return is_array($object) || $object instanceof Traversable;
    }

    public function navigation($name = 'site')
    {
        return \TAO\Navigation::instance($name);
    }

    public function setMeta($name, $value)
    {
        app()->taoAssets->setMeta($name, $value);
    }

    public function meta()
    {
        return app()->taoAssets->meta();
    }

    public function render($template, $context = array())
    {
        return app()->taoView->render($template, $context);
    }

    public function renderWithinLayout($template, $context = array())
    {
        return app()->taoView->renderWithinLayout($template, $context);
    }

    public function itemsForSelect($src)
    {
        if (is_array($src)) {
            return $src;
        }
        if (is_string($src)) {
            $args = '';
            if ($m = \TAO::regexp('{^(.+?)/(.+)$}', $src)) {
                $src = trim($m[1]);
                $args = trim($m[2]);
            }
            if ($m = \TAO::regexp('{^datatype:(.+)$}', $src)) {
                return \TAO::datatype(trim($m[1]))->itemsForSelect($args);
            }
        }
        if (is_callable($src)) {
            return call_user_func($src);
        }
        return array();
    }

    public function merge($a, $b)
    {
        foreach ($b as $k => $v) {
            if (isset($a[$k]) && is_array($a[$k]) && is_array($v)) {
                $a[$k] = $this->merge($a[$k], $v);
            } else {
                $a[$k] = $v;
            }
        }
        return $a;
    }

    public function vars($name = false)
    {
        static $storage = false;
        if (!$storage) {
            $storage = app()->make('\\TAO\\Vars\\Storage');
        }
        if ($name) {
            return $storage->get($name);
        }
        return $storage;
    }

    public function authorized($callback = false)
    {
        $user = \Auth::user();
        if (is_object($user)) {
            if (is_callable($callback)) {
                return call_user_func($callback);
            }
            return true;
        } else {
            if (is_callable($callback)) {
                return redirect('/users/login/');
            }
            return false;
        }
    }

    public function frontend($path = false, $options = [])
    {
        return Frontend\Manager::instanse($path, $options);
    }
}