<?php

namespace TAO\Fields\Utils\Model;

use Illuminate\Database\Eloquent\Builder;

trait Urls
{
    protected $baseListUrl = false;

    public function baseListUrl($value)
    {
        $this->baseListUrl = $value;
        return $this;
    }

    public function getBaseListUrl()
    {
        $url = $this->baseListUrl;
        if (!$url) {
            $url = '/'.$this->getDatatype().'/';
        }
        return $url;
    }

    public function listUrl($page = 1)
    {
        $url = $this->getBaseListUrl();
        if ($page==1) {
            return $url;
        }
        return rtrim($url,'/')."/page-{$page}/";
    }

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
        $data['base'] = $base = isset($data['base'])? $data['base'] : $this->getDatatype();
        \Route::any("/{$base}/{id}/", function($id) use($data) {
            $data['id'] = $id;
            return $this->renderItemPage($data);
        })->where('id','^\d+$');
    }

    public function routePageByUrl($data = [])
    {
        $request = app()->request();
        $url = $request->getPathInfo();
        $selector = isset($data['selector'])? $data['selector'] : 'getItemByUrl';
        $mode = isset($data['mode'])? $data['mode'] : 'full';
        $item = $this->$selector($url);
        if ($item instanceof Builder) {
            $item = $item->first();
        }
        if ($item) {
            $data['item'] = $item;
            $data['mode'] = $mode;
            \Route::any($url, function() use($item, $data) {
                return $this->renderItemPage($data);
            });
        }
        return $this;
    }

    public function routeListing($data = [])
    {
        $data['base'] = $base = isset($data['base'])? $data['base'] : $this->getDatatype();
        $pages = isset($data['pages'])? $data['pages'] : true;
        $data['selector'] = $selector = isset($data['selector'])? $data['selector'] : 'getItems';
        \Route::any("/{$base}/", function() use($data) {
            $data['page'] = 1;
            return $this->renderListPage($data);
        });
        if ($pages) {
            \Route::any("/{$base}/page-{page}/", function($page) use($data) {
                $data['page'] = $page;
                return $this->renderListPage($data);
            })->where('page','^\d+$');
        }
        return $this;
    }
}