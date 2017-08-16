<?php

namespace TAO\Fields\Utils\Model;
use Illuminate\Contracts\View\Factory as ViewFactory;

trait View
{
    protected $viewMode = false;
    protected $_views = [];

    public function viewMode($value)
    {
        $this->viewMode = $value;
        return $this;
    }

    public function perPage($value)
    {
        $this->perPage = $value;
        return $this;
    }

    protected function defaultPageViewMode()
    {
        return 'page';
    }

    public function findView($name)
    {
        $factory = app(ViewFactory::class);
        $code = $this->getDatatype();
        $this->_views = [];
        if ($this->viewMode) {
            $this->_views[] = "datatypes.{$code}.{$name}-{$this->viewMode}";
        }
        $this->_views[] = "datatypes.{$code}.{$name}";
        $this->_views[] = "{$name}";
        if ($this->viewMode) {
            $this->_views['tao1'] = "tao::{$name}-{$this->viewMode}";
        }
        $this->_views['tao2'] = "tao::{$name}";
        $data['_views'] = $this->_views;
        foreach($this->_views as $view) {
            if ($factory->exists($view)) {
                return $view;
            }
        }
        return 'tao::no-view-for';
    }

    public function renderListPage($page = 1, $data = [])
    {
        $this->viewMode = $this->viewMode? $this->viewMode : 'teaser';
        $view = $this->findView('list-page');

        $count = $this->count();
        $numPages = ceil($count/$this->perPage);
        $rows = [];
        foreach($this->limit($this->perPage)->offset(($page-1)*$this->perPage)->get() as $row) {
            $rows[] = $row->urlCode($this->getUrlCode())->pagerVar($this->pagerVar);
        }
        $data['_views'] = $this->_views;
        $data['mode'] = $this->viewMode;
        $data['count'] = $count;
        $data['numpages'] = $numPages;
        $data['page'] = $page;
        $data['rows'] = $rows;
        $data['model'] = $this;
        if (!isset($data['title'])) {
            $data['title'] = $this->adminMenuTitle();
        }
        if (!isset($data['pager_callback'])) {
            $data['pager_callback'] = [$this, 'listUrl'];
        }
        return view($view, $data);
    }

    public function renderItemPage($item, $data = [])
    {
        if (!is_object($item)) {
            $item = $this->find($item);
        }
        if (!$item) {
            return response(view('404'), 404);
        }
        $viewMode = $this->viewMode? $this->viewMode : $this->defaultPageViewMode();
        return $item->viewMode($viewMode)->render($data);
    }

    public function render($data = [])
    {
        $this->viewMode = $this->viewMode? $this->viewMode : 'teaser';
        $view = $this->findView('item');
        $data['_views'] = $this->_views;
        $data['item'] = $this;
        $data['mode'] = $this->viewMode;
        return view($view, $data);
    }
}