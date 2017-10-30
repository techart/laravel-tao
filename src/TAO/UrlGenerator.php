<?php

namespace TAO;

class UrlGenerator extends \Illuminate\Routing\UrlGenerator
{
    public function to($path, $extra = [], $secure = null)
    {
        return parent::to($path, $extra, $secure);
    }

    public function isValidUrl($path)
    {
        $v = parse_url($path);
        if (isset($v['path']) && !empty($v['path'])) {
            $path = $v['path'];
            if ($path[0] == '/') {
                return true;
            }
        }
        return parent::isValidUrl($path);
    }

}