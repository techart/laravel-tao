<?php

namespace TAO\View;

use Illuminate\View\FileViewFinder;
use Illuminate\Contracts\View\Factory as ViewFactory;

class Finder extends FileViewFinder
{
    /**
     * @param string $name
     * @return string
     */
    public function find($name)
    {
        if ($m = \TAO::regexp('{^table\s*~(.+)$}', $name)) {
            $name = app()->taoAdmin->tableView(trim($m[1]));
        } elseif ($m = \TAO::regexp('{^([a-z]+)\s*~(.+)$}', $name)) {
            $name = $this->findInTAO($m[1], trim($m[2]));
        } elseif ($m = \TAO::regexp('{^~\s*layout$}', $name)) {
            $name = app()->tao->layout;
        }
        return parent::find($name);
    }

    /**
     * @param $view
     * @return bool
     */

    public function exists($view)
    {
        $factory = app(ViewFactory::class);
        return $factory->exists($view) ? $view : false;
    }

    public function findInTAO($dir, $name)
    {
        $names = explode('|', $name);
        foreach ($names as $name) {
            if ($name = trim($name)) {
                if ($view = $this->exists("{$dir}.{$name}")) {
                    return $view;
                }
            }
        }
        return "tao::{$dir}.{$name}";
    }

}