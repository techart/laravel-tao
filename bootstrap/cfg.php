<?php

function tao_cfg($name, $values = [])
{
    $path = realpath(__DIR__.'/../config')."/{$name}.php";
    if (is_file($path)) {
        $out = include($path);
        $out = tao_cfg_merge($out, $values);
        return $out;
    }
    return $values;
}


function tao_cfg_merge($out, $values = [])
{
    foreach($values as $k => $v) {
         if (is_array($v) && is_array($out[$k])) {
           $out[$k] = tao_cfg_merge($out[$k], $v);
         } elseif (is_null($v)) {
           unset($out[$k]);
         } else {
           $out[$k] = $v;
         }
    }
    return $out;
}