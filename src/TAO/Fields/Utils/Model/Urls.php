<?php

namespace TAO\Fields\Utils\Model;

use Illuminate\Database\Eloquent\Builder;

trait Urls
{
    public function automaticRoutes()
    {
        return false;
    }

    public function routeSelector($data = [])
    {
        $this->selector()->route($data);
        return $this;
    }
}