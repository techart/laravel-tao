<?php

namespace TAO\Middleware;

class Urls
{
    public function handle($request, \Closure $next)
    {
        return $next($request);
    }
}