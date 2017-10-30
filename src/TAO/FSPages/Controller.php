<?php

namespace TAO\FSPages;

class Controller extends \TAO\Controller
{
    public function index()
    {
        $path = app()->tao->router->path;dd($path);
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
        $result = include($path);
        $content = ob_get_clean();
        if ($result instanceof \Symfony\Component\HttpFoundation\Response) {
            return $result;
        }
        return $this->render($view, array('content' => $content));
    }
}
