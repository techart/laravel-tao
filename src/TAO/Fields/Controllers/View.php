<?php

namespace TAO\Fields\Controllers;

class View extends \TAO\Controller
{
    public function index()
    {
        $item = app()->tao->router->item;
        $template = $item->templateForPage();
        return $this->render($template, ['item' => $item]);
    }
}