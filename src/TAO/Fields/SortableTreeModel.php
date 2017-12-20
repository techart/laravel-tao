<?php

namespace TAO\Fields;

use Illuminate\Database\Eloquent\Builder;

class SortableTreeModel extends TreeModel
{
    use \TAO\Fields\Extra\Sortable,
        \TAO\Fields\Extra\Tree,
        \TAO\Fields\Extra\Title;

    protected function initExtraFields()
    {
        $this->initExtra('Sortable', 'Tree', 'Title');
        $this->extraFields['title']['in_list'] = false;
    }

    /**
     * @return Builder
     */
    public function ordered()
    {
        return $this->orderBy('weight')->orderBy('id');
    }

    public function beforeSave()
    {
        parent::beforeSave();
        if ((int)$this['weight']==0) {
            $this['weight'] = (int)$this->max('weight')+1;
        }
    }

}