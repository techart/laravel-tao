<?php

namespace TAO\Fields;

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
                'items' => 'datatype:categories/0=Корень',
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
                }
            } else {
                $fields[$field] = $data;
            }
        }
        return $fields;
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

    public function adminMenuSection()
    {
        return 'Словари';
    }
}