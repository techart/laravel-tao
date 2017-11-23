<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Schema\Blueprint;
use TAO\Fields\Field;
use TAO\Fields\Model;

class Multilink extends Field
{
    protected $attachedIds;
    protected $relatedItems;

    public function belongsToMany()
    {
        return $this->item->belongsToMany($this->relatedModelClass(), $this->tableRelations(), $this->thisKey(), $this->relatedKey());
    }

    public function attachedIds()
    {
        if (is_null($this->attachedIds)) {
            $this->attachedIds = array();
            foreach ($this->belongsToMany()->allRelatedIds() as $id) {
                $this->attachedIds[$id] = $id;
            }
        }
        return $this->attachedIds;
    }

    public function value()
    {
        return $this->attachedIds();
    }

    public function isAttached($id)
    {
        if (empty($this->item->getKey())) {
            return false;
        }
        $ids = $this->attachedIds();
        return isset($ids[$id]);
    }

    public function items()
    {
        $items = $this->param('items');
        if (!empty($items)) {
            $items = \TAO::itemsForSelect($items);
            return $items;
        }
        $model = $this->relatedModel();
        $items = $model->itemsForSelect([]);
        return $items;
    }

    public function checkSchema(Blueprint $table)
    {
        $relTable = $this->tableRelations();
        if (!$this->item->dbSchema()->hasTable($relTable)) {
            $this->item->dbSchema()->create($relTable, function (Blueprint $table) {
                $thisKey = $this->thisKey();
                $relatedKey = $this->relatedKey();
                $table->integer($thisKey)->unsigned();
                $table->index($thisKey);
                $table->integer($relatedKey)->unsigned();
                $table->index($relatedKey);
            });
        }
    }

    public function setupDefault()
    {
    }


    public function setFromRequest($request)
    {
        if (is_array($request) && isset($request[$this->name]) && is_array($request[$this->name])) {
            $values = $request[$this->name];
            foreach ($this->items() as $id => $title) {
                if (isset($values[$id]) && $values[$id]) {
                    $this->belongsToMany()->attach($id);
                } else {
                    $this->belongsToMany()->detach($id);
                }
            }
        }
    }

    public function setFromRequestAfterSave($request)
    {
        $values = $request->has($this->name)? $request->get($this->name) : [];
        foreach ($this->items() as $id => $title) {
            if (isset($values[$id]) && $values[$id]) {
                $this->belongsToMany()->attach($id);
            } else {
                $this->belongsToMany()->detach($id);
            }
        }
    }

    public function tableRelations()
    {
        if (isset($this->data['table_relations'])) {
            return $this->data['table_relations'];
        }
        return $this->item->getTable() . '_' . $this->relatedModel()->getTable() . '_relations';
    }

    public function thisKey()
    {
        if (isset($this->data['this_key'])) {
            return $this->data['this_key'];
        }
        return $this->item->getForeignKey();
    }

    public function relatedKey()
    {
        if (isset($this->data['related_key'])) {
            return $this->data['related_key'];
        }
        return $this->relatedModel()->getForeignKey();
    }

    public function relatedModelClass()
    {
        $model = $this->param('model');
        if (!$model) {
            $datatype = $this->param('datatype');
            if (!$datatype) {
                return \TAO\Fields\Dummy\Model::class;
            }
            $model = \TAO::datatypeClass($datatype);
        }
        return $model;
    }

    /**
     * @return Model
     */
    public function relatedModel()
    {
        $class = $this->relatedModelClass();
        if ($class == \TAO\Fields\Dummy\Model::class) {
            $model = new \TAO\Fields\Dummy\Model;
            $model->code = $this->name;
            return $model;
        }
        return app()->make($class);
    }

    /**
     * Возвращает коллекцию связанных объектов
     *
     * @return Collection
     */
    public function relatedItems()
    {
        if (is_null($this->relatedItems)) {
            $this->relatedItems = $this->relatedModel()->whereIn('id', $this->attachedIds())->get();
        }
        return $this->relatedItems;
    }
}
