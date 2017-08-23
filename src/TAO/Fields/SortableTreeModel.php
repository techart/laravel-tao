<?php

namespace TAO\Fields;

class SortableTreeModel extends TreeModel
{

    public $isSortable = true;

    public function calculatedFields()
    {
        $fields = parent::calculatedFields();
        $extra = array(
            'weight' => array(
                'type' => 'integer index',
                'in_list' => false,
                'in_form' => false,
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
        return $this->orderBy('weight')->orderBy('id');
    }

    public function beforeSave()
    {
        if ((int)$this['weight']==0) {
            $this['weight'] = (int)$this->max('weight')+1;
        }
    }

}