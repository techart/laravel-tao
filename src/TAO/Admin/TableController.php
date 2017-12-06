<?php

namespace TAO\Admin;

use Illuminate\Support\Facades\Auth;
use TAO\Fields\Controllers\Actions;
use TAO\Fields\Controllers\Forms;
use TAO\Fields\Controllers\Table;

class TableController extends AdminController
{
    use Actions, Table, Forms;

    protected $datatype;
    protected $datatypeCode;

    protected function datatype()
    {
        if (is_null($this->datatype)) {
            $this->datatype = app()->tao->router('admin')->datatype;
            $this->datatypeCode = app()->tao->router('admin')->datatypeCode;
        }
        return $this->datatype;
    }

    protected function accessAction($method, $parameters)
    {
        $acc = parent::accessAction($method, $parameters);
        if ($acc === true) {
            if (!$this->datatype()->accessAdmin(Auth::user())) {
                return false;
            }
        }
        return $acc;
    }

    protected function initViews()
    {
        $path = $this->datatype()->adminViewsPath();
        if (!$path) {
            $path = "admin.table.{$this->datatypeCode}";
        }
        app()->taoAdmin->setTableViewsPath($path);
    }

    protected function titleList()
    {
        return $this->datatype()->adminTitleList();
    }

    protected function titleEdit()
    {
        $item = empty($this->editItem)? $this->datatype() : $this->editItem;
        return $item->adminTitleEdit();
    }

    protected function titleAdd()
    {
        return $this->datatype()->adminTitleAdd();
    }

    protected function perPage()
    {
        return $this->datatype()->adminPerPage();
    }

    protected function currentPage()
    {
        return $this->page;
    }

    protected function filtered()
    {
        $builder = $this->datatype()->ordered();
        return $this->datatype()->applyFilter($builder, $this->filterValues());
    }

    protected function countRows()
    {
        return $this->filtered()->count();
    }

    protected function canAdd()
    {
        return $this->datatype()->accessAdd();
    }

    protected function selectRows()
    {
        return $this->filtered()
            ->limit($this->perPage())
            ->offset(($this->currentPage()-1)*$this->perPage())
            ->get();
    }
}