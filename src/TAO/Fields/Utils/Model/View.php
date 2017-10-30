<?php

namespace TAO\Fields\Utils\Model;

use Illuminate\Contracts\View\Factory as ViewFactory;

trait View
{
    public function findView($name, $default = 'tao::no-view-for')
    {
        $factory = app(ViewFactory::class);
        $code = $this->getDatatype();
        $views = [];
        $views[] = "datatypes.{$code}.{$name}";
        $views[] = "tao::datatypes.{$code}.{$name}";
        $views[] = "datatypes.{$name}";
        $views[] = "tao::datatypes.{$name}";
        foreach ($views as $view) {
            if ($factory->exists($view)) {
                return $view;
            }
        }
        return $default;
    }

    public function renderListPage($data = [])
    {
        $data['page'] = $page = isset($data['page']) ? $data['page'] : 1;
        $data['per_page'] = $perPage = isset($data['per_page']) ? $data['per_page'] : 10;
        $data['mode'] = $mode = isset($data['mode']) ? $data['mode'] : 'list';
        $data['listing'] = $selector = isset($data['listing']) ? $data['listing'] : 'getItems';
        $data['pager_callback'] = isset($data['pager_callback']) ? $data['pager_callback'] : [$this, 'listUrl'];
        if (isset($data['base'])) {
            $this->baseListUrl('/' . $data['base'] . '/');
        }
        $view = $this->findView($data['mode']);

        $select = $this->$selector();
        $count = $select->count();
        $numPages = ceil($count / $perPage);
        $rows = [];
        foreach ($select->limit($perPage)->offset(($page - 1) * $this->perPage)->get() as $row) {
            $rows[] = $row;
        }

        $data['count'] = $count;
        $data['numpages'] = $numPages;
        $data['rows'] = $rows;
        $data['model'] = $this;
        $data['item'] = $this;
        if (!isset($data['title'])) {
            $data['title'] = $this->adminMenuTitle();
        }
        return view($view, $data);
    }

    public function renderItemPage($data = [])
    {
        if (isset($data['item'])) {
            $item = $data['item'];
        } else {
            $selector = isset($data['selector']) ? $data['selector'] : 'getItemById';
            $id = isset($data['id']) ? $data['id'] : 0;
            $item = $this->$selector($id);
        }
        if ($item instanceof Builder) {
            $item = $item->first();
        }
        if (!$item) {
            return response(view('404'), 404);
        }
        $data['mode'] = isset($data['mode']) ? $data['mode'] : 'full';
        return $item->render($data);
    }

    public function render($data = [])
    {
        $data['mode'] = isset($data['mode']) ? $data['mode'] : 'teaser';
        $data['item'] = $this;
        $view = $this->findView($data['mode']);
        return view($view, $data);
    }
}