<?php

namespace TAO\Fields;

use TAO\Type\Collection;

class TreeModel extends Model
{

    public $isTree = true;
    public $isSortable = false;


    public function calculatedFields()
    {
        $fields = $this->fields();
        $extra = array(
            'parent_id' => array(
                'type' => 'select index',
                'items' => function() {
                    return $this->buildTreeForParentSelect();
                },
                'label' => 'Родитель',
                'weight' => 100,
                'in_list' => false,
                'in_form' => true,
                'group' => 'common',
            ),
            'title' => array(
                'type' => 'string(250) index',
                'label' => 'Заголовок',
                'style' => 'width:90%;',
                'weight' => 200,
                'in_list' => false,
                'in_form' => true,
                'group' => 'common',
            ),
        );
        foreach($extra as $field => $data) {
            if (isset($fields[$field])) {
                if ($fields[$field] === false) {
                    unset($fields[$field]);
                } else {
                    $fields[$field] = \TAO::merge($data, $fields[$field]);
                }
            } else {
                $fields[$field] = $data;
            }
        }
        return $fields;
    }

    public function buildTreeForParentSelect()
    {
        $args = [0 => 'Корень'];
        if (isset($_GET['filter']['root'])) {
            $rootId = (int)$_GET['filter']['root'];
            $rootItem = $this->find($rootId);
            if ($rootItem) {
                $args = [
                    'root' => $rootId,
                    $rootId => $rootItem->title(),
                ];
            }
        }
        return $this->treeForSelect($args);
    }

    public function ordered()
    {
        return $this->orderBy('title');
    }

    public function fields()
    {
        return array(
        );
    }

    public function adminMaxDepth()
    {
        return 10000;
    }

    public function adminTitleList()
    {
        $title = $this->adminTitle();
        if (isset($_GET['filter']['root'])) {
            $rootId = (int)$_GET['filter']['root'];
            $rootItem = $this->find($rootId);
            if ($rootItem) {
                $title .= ': '. $rootItem->title();
            }
        }
        return $title;
    }

    public function adminMenuSection()
    {
        return 'Словари';
    }
}