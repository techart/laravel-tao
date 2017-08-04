<?php

namespace TAO\FSPages;

class Controller extends \TAO\Controller
{
    public function index()
    {
        $path = app()->tao->router->path;
        return $this->render($path);
    }

    public function index2()
    {
        return $this->render('home');
    }

    public function file()
    {
        $path = app()->tao->router->path;
        $view = 'tao::fspages.index';
        ob_start();
        include($path);
        $content = ob_get_clean();
        return $this->render($view, array('content' => $content));
    }
}
