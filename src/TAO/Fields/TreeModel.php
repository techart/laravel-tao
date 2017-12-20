<?php

namespace TAO\Fields;

use Illuminate\Database\Eloquent\Builder;
use TAO\Type\Collection;

class TreeModel extends Model
{
    use \TAO\Fields\Extra\Tree,
        \TAO\Fields\Extra\Title;

    protected function initExtraFields()
    {
        $this->initExtra('Tree', 'Title');
        $this->extraFields['title']['in_list'] = false;
    }

    public function fields()
    {
        return [];
    }

    /**
     * @return Builder
     */
    public function ordered()
    {
        return $this->orderBy('title');
    }

    public function adminMenuSection()
    {
        return 'Словари';
    }
}