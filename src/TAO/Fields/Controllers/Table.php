<?php

namespace TAO\Fields\Controllers;

/**
 * Trait Table
 * @package TAO\Fields\Controllers
 */
trait Table
{
    /**
     * @var bool
     */
    protected $canAdd = false;
    /**
     * @var bool
     */
    protected $canEdit = false;
    /**
     * @var bool
     */
    protected $canDelete = false;
    /**
     * @var bool
     */
    protected $canCopy = false;

    protected $filterFields = [];
    protected $filterValues = [];


    protected function filterFields()
    {
        $filter = $this->datatype()->filter();
        if (empty($filter) && !is_array($filter)) {
            return [];
        }
        $out = [];
        $this->filterValues = [];
        $model = new \TAO\Fields\Dummy\Model();
        $model['id'] = 1;
        $request = \Request::getFacadeRoot();
        $inputData = $request->has('filter')? $request->get('filter') : [];
        foreach ($filter as $field => $data) {
            $out[$field] = app()->taoFields->create($field, $data, $model);
            $out[$field]->setupDefault();
            $out[$field]->setFromRequest($inputData);
            $this->filterValues[$field] = $out[$field]->value();
        }
        $this->filterFields = $out;
        return $out;
    }

    public function filterAction()
    {
        $request = \Request::getFacadeRoot();
        if ($request->method() == 'POST') {
            $fields = $this->filterFields();
            $this->filter = [];
            foreach ($fields as $name => $field) {
                if ($request->has($name)) {
                    $this->filter[$name] = $request->get($name);
                }
            }
        }
        return redirect($this->actionUrl('list'));
    }

    /**
     * @return mixed
     */
    public function listAction()
    {
        $this->initViews();

        if ($this->datatype()->isTree) {
            return $this->treeAction();
        }

        $filter = $this->filterFields();

        $count = $this->countRows();
        $numPages = ceil($count / $this->perPage());
        $rows = $this->prepareRows();
        return $this->render('table ~ list.table', [
            'title' => $this->titleList(),
            'datatype' => $this->datatype(),
            'fields' => $this->listFields(),
            'count' => $count,
            'per_page' => $this->perPage(),
            'numpages' => $numPages,
            'rows' => $rows,
            'can_add' => $this->canAdd(),
            'can_edit' => $this->canEdit,
            'can_delete' => $this->canDelete,
            'can_copy' => $this->canCopy,
            'add_text' => $this->datatype()->adminAddButtonText(),
            'filter' => $filter,
            'with_filter' => !empty($filter),
            'filter_url' => $this->actionUrl('filter', ['__no_filter' => true, '__no_page' => true]),
            'reset_filter_url' => $this->actionUrl('list', ['__no_filter' => true, '__no_page' => true]),
            'sidebar_visible' => !empty($this->filter),
            'filter_empty' => empty($this->filter),
            //'sidebar_visible' => true,
            'with_row_actions' => ($this->canEdit || $this->canDelete || $this->canCopy),
            'pager_callback' => array($this, 'pageUrl'),
            'page' => $this->page,
        ]);
    }

    /**
     * @return mixed
     */
    public function treeAction()
    {
        $filter = $this->filter;
        $filter['max_depth'] = $this->datatype()->adminMaxDepth();
        $tree = $this->datatype()->buildTree($filter);
        $this->prepareTree($tree);

        return $this->render('table ~ list.tree', [
            'title' => $this->titleList(),
            'count' => count($tree),
            'datatype' => $this->datatype(),
            'fields' => $this->listFields(),
            'tree' => $tree,
            'can_add' => $this->canAdd(),
            'can_edit' => $this->canEdit,
            'can_delete' => $this->canDelete,
            'can_copy' => $this->canCopy,
            'add_text' => $this->datatype()->adminAddButtonText(),
            'with_filter' => false,
            'with_row_actions' => ($this->canEdit || $this->canDelete || $this->canCopy),
        ]);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function weightAction()
    {
        $with = app()->request()->get('with');
        if (is_null($this->id) || is_null($with)) {
            return \TAO::pageNotFound();
        }

        $item1 = $this->datatype()->find($this->id);
        $item2 = $this->datatype()->find($with);
        if (!$item1 || !$item1->accessEdit(\Auth::user()) || !$item2 || !$item2->accessEdit(\Auth::user())) {
            return \TAO::pageNotFound();
        }
        $v = $item1['weight'];
        $item1['weight'] = $item2['weight'];
        $item2['weight'] = $v;
        $item1->save();
        $item2->save();
        return redirect($this->actionUrl('list'));
    }

    /**
     * @param $page
     * @return mixed
     */
    public function pageUrl($page)
    {
        return $this->actionUrl('list', array('page' => $page));
    }

    /**
     * @return array
     */
    protected function prepareRows()
    {
        $rows = array();
        foreach ($this->selectRows() as $row) {
            $this->prepareRow($row);
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * @param $tree
     */
    protected function prepareTree($tree)
    {
        foreach ($tree as $row) {
            $this->prepareRow($row);
            if (isset($row->childs) && is_array($row->childs)) {
                $this->prepareTree($row->childs);
            }
        }
    }

    /**
     * @param $row
     */
    protected function prepareRow($row)
    {
        $row->prepareForAdminList();
        if ($row->accessEdit(\Auth::user())) {
            $this->canEdit = true;
        }
        if ($row->accessDelete(\Auth::user())) {
            $this->canDelete = true;
        }
    }

    /**
     * @return array
     */
    protected function listFields()
    {
        $fields = array();
        foreach ($this->datatype()->fieldsObjects() as $name => $field) {
            if ($field->inAdminList()) {
                $fields[$name] = $field;
            }
        }
        uasort($fields, function ($f1, $f2) {
            $w1 = $f1->weightInAdminList();
            $w2 = $f2->weightInAdminList();
            if ($w1 > $w2) {
                return 1;
            }
            if ($w1 < $w2) {
                return -1;
            }
            return 0;
        });
        return $fields;
    }
}