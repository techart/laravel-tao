<?php

namespace TAO;

use Illuminate\Contracts\View\Factory as ViewFactory;

class Admin
{
    protected $tableViewsPath = false;

    public function init()
    {
    }

    public function setTableViewsPath($path)
    {
        $this->tableViewsPath = $path;
    }

    public function tableView($name)
    {
        $factory = app(ViewFactory::class);
        if ($this->tableViewsPath) {
            $view = "{$this->tableViewsPath}.{$name}";
            if ($factory->exists($view)) {
                return $view;
            }
        }
        $view = "admin.table.{$name}";
        if ($factory->exists($view)) {
            return $view;
        }
        return "tao::admin.table.{$name}";
    }

    public function layout()
    {
        $factory = app(ViewFactory::class);
        return $factory->exists('layouts.admin') ? 'layouts.admin' : 'tao::admin.layout';
    }

    public function menu()
    {
        $menu = array();
        foreach (\TAO::datatypes() as $code => $datatype) {
            $section = $datatype->adminMenuSection();
            $title = $datatype->adminMenuTitle();
            $url = '/admin/datatype/' . $code;
            if (is_string($section)) {
                if (!isset($menu[$section])) {
                    $menu[$section] = array(
                        'title' => $section,
                        'url' => $url,
                        'sub' => array(),
                    );
                }
                $menu[$section]['sub'][] = array(
                    'title' => $title,
                    'url' => $url,
                );
            }
        }
        return $menu;
    }
}