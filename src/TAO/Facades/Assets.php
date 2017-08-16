<?php

namespace TAO\Facades;

use Illuminate\Support\Facades\Facade;

class Assets extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'taoAssets';
    }
}