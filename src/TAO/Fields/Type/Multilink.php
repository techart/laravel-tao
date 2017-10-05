<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;
use TAO\Fields\Field;

class Multilink extends Field
{
    protected $attachedIds;

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
        return $this->relatedModel()->itemsForSelect();
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
    }

    public function setFromRequestAfterSave($request)
    {
        if ($request->has($this->name)) {
            $values = $request->get($this->name);
            foreach ($this->items() as $id => $title) {
                if (isset($values[$id]) && $values[$id]) {
                    $this->belongsToMany()->attach($id);
                } else {
                    $this->belongsToMany()->detach($id);
                }
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
            $model = \TAO::datatypeClass($datatype);
        }
        return $model;
    }

    public function relatedModel()
    {
        return app()->make($this->relatedModelClass());
    }
}
