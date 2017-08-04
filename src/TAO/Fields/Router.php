<?php

namespace TAO\Fields;

class Router extends \TAO\Router
{
    public $item;

    public function route($request)
    {
        foreach(\TAO::datatypeCodes() as $code) {
            $datatype = \TAO::datatype($code);
            $r = $datatype->route($request);
            if ($r) {
                if (is_array($r)) {
                    if (isset($r['item'])) {
                        $this->item = $r['item'];
                    }
                    return $r;
                }
                break;
            }
        }

        return false;
    }
}
