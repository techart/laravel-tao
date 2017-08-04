<?php

namespace TAO\Fields\Controllers;

use TAO\Fields\Exception\UndefinedField;

class API extends \TAO\Controller
{
    public $datatypeCode;
    public $fieldName;
    public $datatype;
    public $item;
    public $id;
    public $field;
    public $action;

    public function index()
    {
        $this->datatypeCode = \Request::get('datatype');
        $this->fieldName = \Request::get('field');
        $this->action = \Request::get('action');
        if (!empty($this->datatypeCode) && !empty($this->fieldName) && !empty($this->action)) {
            $this->datatype = \TAO::datatype($this->datatypeCode);
            if ($this->datatype) {
                $this->item = $this->datatype;
                $this->id = \Request::get('id');
                if (!empty($this->id)) {
                    $this->item = $this->datatype->find($this->id);
                    if (!$this->item) {
                        return $this->error("Item {$this->id} not found");
                    }
                }
                try {
                    $this->field = $this->item->field($this->fieldName);
                } catch(UndefinedField $e)  {
                    return $this->error("Field {$this->field} not found");
                }

                if ($this->field->accessAPI()) {
                    $method = 'apiAction'.ucfirst(camel_case($this->action));
                    if (!method_exists($this->field, $method)) {
                        return $this->error("API action '{$this->action}' not found");
                    }
                    $rc = $this->field->$method($this);
                    if (is_string($rc)) {
                        return $this->error($rc);
                    }
                    return $this->response($rc);
                }
            }
        }
        return $this->pageNotFound();
    }

    protected function error($message = 'Error')
    {
        return $this->json(['error' => $message]);
    }

    protected function response($m)
    {
        $m['error'] = false;
        return $this->json($m);
    }
}