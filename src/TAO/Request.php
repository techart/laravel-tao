<?php

namespace TAO;

class Request extends \Illuminate\Http\Request
{
    public function url()
    {
        return preg_replace('/\?.*/', '', $this->getUri());
    }
}