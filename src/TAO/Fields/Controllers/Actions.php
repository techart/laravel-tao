<?php

namespace TAO\Fields\Controllers;

trait Actions
{
    protected $action;
    protected $filter;
    protected $order;
    protected $page;
    protected $id;

    public function entryPointAction()
    {
        $this->action = \Request::has('action')? \Request::input('action') : 'list';
        $this->filter = \Request::has('filter')? \Request::input('filter') : array();
        $this->order = \Request::has('order')? \Request::input('order') : array();
        $this->page = \Request::has('page')? (int)\Request::input('page') : 1;
        $this->id = \Request::has('id')? (int)\Request::input('id') : null;
        $method = "{$this->action}Action";

        return $this->$method();
    }

    public function actionUrl($action, $params = array())
    {
        $uri = \Request::getPathInfo();
        $data = array(
            'order' => $this->order,
            'filter' => $this->filter,
        );
        if ($action != 'list') {
            $data['action'] = $action;
            if ($this->id > 1) {
                $data['id'] = $this->id;
            }
        }
        if ($this->page > 1) {
            $data['page'] = $this->page;
        }
        $data = array_merge($data, $params);
        if (count($data)>1) {
            $q = trim(http_build_query($data));
            if ($q!='') {
                $uri .= '?'.$q;
            }
        }
        return $uri;
    }
}