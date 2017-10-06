<?php

namespace TAO\Fields\Utils\Model;

use Illuminate\Database\Eloquent\Builder;

trait Urls
{
    public function itemUrl($id)
    {
        return false;
    }

    public function automaticRoutes()
    {
        return false;
    }

    public function routePageById($data = [])
    {
        $data['base'] = $base = isset($data['base']) ? $data['base'] : $this->getDatatype();
        \Route::any("/{$base}/{id}/", function ($id) use ($data) {
            $data['id'] = $id;
            return $this->renderItemPage($data);
        })->where('id', '^\d+$');
        return $this;
    }

    public function routePageByUrl($data = [])
    {
        $request = app()->request();
        $url = $urlSrc = $request->getPathInfo();

        $page = 1;
        if (isset($data['pages']) || isset($data['listing'])) {
            if ($m = \TAO::regexp("{^(.+)/page-(\d+)/$}", $url)) {
                $url = $m[1] . '/';
                $page = (int)$m[2];
            }
        }

        if (isset($data['prefix'])) {
            $prefix = trim($data['prefix'], '/');
            if ($m = \TAO::regexp("{^/{$prefix}/(.+)$}", $url)) {
                $url = '/' . $m[1];
            } else {
                return $this;
            }
        }

        if (isset($data['postfix'])) {
            $postfix = trim($data['postfix'], '/');
            if ($m = \TAO::regexp("{^(.+)/{$postfix}/$}", $url)) {
                $url = $m[1] . '/';
            } else {
                return $this;
            }
        }

        $finder = isset($data['finder']) ? $data['finder'] : 'getItemByUrl';
        $mode = isset($data['mode']) ? $data['mode'] : 'full';
        $item = $this->$finder($url);
        if ($item instanceof Builder) {
            $item = $item->first();
        }
        if ($item) {
            $data['item'] = $item;
            $data['mode'] = $mode;
            \Route::any($urlSrc, function () use ($item, $data) {
                return $this->renderItemPage($data);
            });
        }
        return $this;
    }

    public function routeSelector($data = [])
    {
        $this->selector()->route($data);
        return $this;
    }
}