<?php

namespace TAO\Foundation;

class Request extends \Illuminate\Http\Request
{
    public function url()
    {
        return preg_replace('/\?.*/', '', $this->getUri());
    }
}