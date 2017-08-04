<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;
use TAO\Fields\Field;

class Checkbox extends Field
{
    public function createField(Blueprint $table)
    {
        return $table->boolean($this->name);
    }
    
    public function defaultValue()
    {
        return 0;
    }
    
    public function templateForInput()
    {
        return 'fields ~ checkbox';
    }
    
    public function checked()
    {
        return $this->item[$this->name];
    }
}
