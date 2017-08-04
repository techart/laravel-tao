<?php

namespace TAO\Fields\Controllers;

trait Table
{
    protected $canEdit = false;
    protected $canDelete = false;
    protected $canCopy = false;

    public function listAction()
    {
        $this->initViews();
        $count = $this->countRows();
        $rows = $this->prepareRows();
        return $this->render('table ~ list.table', [
            'title' => $this->titleList(),
            'datatype' => $this->datatype(),
            'fields' => $this->listFields(),
            'count' => $count,
            'rows' => $rows,
            'can_edit' => $this->canEdit,
            'can_delete' => $this->canDelete,
            'can_copy' => $this->canCopy,
            'can_add' => $this->datatype()->accessAdd(\Auth::user()),
            'add_text' => $this->datatype()->adminAddButtonText(),
            'with_filter' => true,
            'with_row_actions' => ($this->canEdit || $this->canDelete || $this->canCopy),
        ]);
    }

    protected function prepareRows()
    {
        $rows = array();
        foreach($this->selectRows() as $row) {
            $row->prepareForAdminList();
            if($row->accessEdit(\Auth::user())) {
                $this->canEdit = true;
            }
            if($row->accessDelete(\Auth::user())) {
                $this->canDelete = true;
            }
            $rows[] = $row;
        }
        return $rows;
    }

    protected function listFields()
    {
        $fields = array();
        foreach($this->datatype()->fieldsObjects() as $name => $field) {
            if ($field->inAdminList()) {
                $fields[$name] = $field;
            }
        }
        uasort($fields, function($f1, $f2) {
            $w1 = $f1->weightInAdminList();
            $w2 = $f2->weightInAdminList();
            if ($w1>$w2) {
                return 1;
            }
            if ($w1<$w2) {
                return -1;
            }
            return 0;
        });
        return $fields;
    }
}