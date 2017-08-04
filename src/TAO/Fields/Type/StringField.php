<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;
use TAO\Fields\Field;

class StringField extends Field
{
    public function createField(Blueprint $table)
    {
        $len = $this->typeParamsIntArg(250);
        return $table->string($this->name, $len);
    }
    
    public function templateForInput()
    {
        return 'fields ~ string';
    }
}
