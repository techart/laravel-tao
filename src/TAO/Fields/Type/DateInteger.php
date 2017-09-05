<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;
use TAO\Fields\Field;

class DateInteger extends Field
{
    public function createField(Blueprint $table)
    {
        return $table->integer($this->name, false, true);
    }

    public function defaultValue()
    {
        return 0;
    }

    public function withTime()
    {
        return $this->param('with_time', false);
    }

    public function withSeconds()
    {
        if (!$this->withTime()) {
            return false;
        }
        return $this->param('with_seconds', false);
    }

    public function styleForAdminInput()
    {
        $style = parent::styleForAdminInput();
        return empty($style) ? 'width:200px' : $style;
    }

    public function render($format = 'd.m.Y')
    {
        return date($format, $this->value());
    }

    public function generateFormat()
    {
        if ($this->withTime()) {
            if ($this->withSeconds()) {
                return 'd.m.Y - H:i:s';
            }
            return 'd.m.Y - H:i';
        }
        return 'd.m.Y';
    }

    public function renderForAdminList()
    {
        return $this->render();
    }

    public function set($value)
    {
        if ($m = \TAO::regexp('{^(\d+)\.(\d+)\.(\d+)$}', $value)) {
            $value = mktime(0, 0, 0, $m[2], $m[1], $m[3]);
        } elseif ($m = \TAO::regexp('{^(\d+)\.(\d+)\.(\d+)\s*-\s*(\d+):(\d+)$}', $value)) {
            $value = mktime($m[4], $m[5], 0, $m[2], $m[1], $m[3]);
        } elseif ($m = \TAO::regexp('{^(\d+)\.(\d+)\.(\d+)\s*-\s*(\d+):(\d+):(\d+)$}', $value)) {
            $value = mktime($m[4], $m[5], $m[6], $m[2], $m[1], $m[3]);
        }
        $this->item[$this->name] = $value;
    }

    public function setFromRequest($request)
    {
        if ($request->has($this->name)) {
            $this->set($request->input($this->name));
        }
    }

    public function templateForInput()
    {
        return 'fields ~ date_integer';
    }
}
