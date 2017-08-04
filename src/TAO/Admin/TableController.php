<?php

namespace TAO\Admin;

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
        return $this->datatype()->adminTitleEdit();
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

    protected function filteredDatatype()
    {
        return $this->datatype()->applyFilter($this->filter);
    }

    protected function countRows()
    {
        return $this->filteredDatatype()->count();
    }

    protected function selectRows()
    {
        return $this->filteredDatatype()
            ->limit($this->perPage())
            ->offset(($this->currentPage()-1)*$this->perPage())
            ->get();
    }
}