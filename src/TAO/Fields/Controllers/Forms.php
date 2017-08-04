<?php

namespace TAO\Fields\Controllers;

trait Forms
{

    protected $editItem;

    public function editAction()
    {
        if (is_null($this->id)) {
            return \TAO::pageNotFound();
        }

        $item = $this->datatype()->find($this->id);
        if (!$item || !$item->accessEdit(\Auth::user())) {
            return \TAO::pageNotFound();
        }

        $this->editItem = $item;
        $fields = $item->adminFormFields();

        $errors = array();

        $request = \Request::getFacadeRoot();
        if ($request->method() == 'POST') {
            foreach($fields as $field) {
                $field->setFromRequest($request);
            }
            $errors = $item->errors();
            if (!is_array($errors)) {
                $errors = array();
            }
            if (count($errors)==0) {
                $item->save();
                foreach($fields as $field) {
                    $field->setFromRequestAfterSave($request);
                }
                return redirect($this->actionUrl('list'));
            }
        }

        return $this->render('table ~ form.edit', $this->formViewParams(array(
            'id' => $this->id,
            'item' => $item,
            'title' => $this->titleEdit(),
            'fields' => $fields,
            'action_url' => $this->actionUrl('edit'),
            'submit_text' => $item->adminEditSubmitText(),
            'errors' => $errors,
        )));
    }

    public function addAction()
    {
        $item = $this->datatype()->newInstance();
        if (!$item || !$item->accessEdit(\Auth::user())) {
            return \TAO::pageNotFound();
        }
        $this->editItem = $item;
        $fields = $item->adminFormFields();

        $errors = array();

        $request = \Request::getFacadeRoot();
        if ($request->method() == 'POST') {
            foreach($fields as $field) {
                $field->setFromRequest($request);
            }
            $errors = $item->errors();
            if (!is_array($errors)) {
                $errors = array();
            }
            if (count($errors)==0) {
                $item->save();
                foreach($fields as $field) {
                    $field->setFromRequestAfterSave($request);
                }
                return redirect($this->actionUrl('list', array('page' => 1)));
            }
        }

        return $this->render('table ~ form.add', $this->formViewParams(array(
            'id' => null,
            'title' => $this->titleAdd(),
            'fields' => $fields,
            'action_url' => $this->actionUrl('add'),
            'submit_text' => $item->adminAddSubmitText(),
            'errors' => $errors,
        )));
    }

    public function deleteAction()
    {
        if (is_null($this->id)) {
            return \TAO::pageNotFound();
        }

        $item = $this->datatype()->find($this->id);
        if (!$item || !$item->accessEdit(\Auth::user())) {
            return \TAO::pageNotFound();
        }
        $item->delete();
        return redirect($this->actionUrl('list'));
    }

    protected function formViewParams($params)
    {
        $firstTab = false;
        $tabs = $this->datatype()->adminFormTabs();
        $etabs = $tabs;
        foreach($params['fields'] as $field) {
            $etabs[$field->adminTab()] = true;
        }
        foreach($etabs as $tab => $v) {
            if ($v!==true && isset($tabs[$tab])) {
                unset($tabs[$tab]);
            }
        }

        if (is_array($tabs)) {
            foreach(array_keys($tabs) as $tab) {
                $firstTab = $tab;
                break;
            }
        }

        return array_merge(array(
            'item' => $this->editItem,
            'datatype' => $this->datatype(),
            'list_url' => $this->actionUrl('list'),
            'tabs' => $tabs,
            'first_tab' => $firstTab
        ), $params);
    }

}