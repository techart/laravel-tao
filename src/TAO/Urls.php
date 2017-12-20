<?php

namespace TAO;

/**
 * Class Urls
 * @package TAO
 */
class Urls
{
    /**
     * @var array
     */
    static $currentUrls = array();

    /**
     * @var array
     */
    static $noSendVars = array();

    /**
     * @param $url
     * @return string
     */
    public static function clean($url)
    {
        $p = strpos($url, '?');
        if ($p > 0) {
            $url = substr($url, 0, $p);
        }
        return $url;
    }

    /**
     * @param $url
     */
    public static function addCurrentUrl($url)
    {
        $url = self::clean($url);
        self::$currentUrls[$url] = $url;
    }

    /**
     * @param $url
     * @return bool
     */
    public static function isCurrent($url)
    {
        $url = self::clean($url);
        if (self::clean($_SERVER['REQUEST_URI']) == $url) {
            return true;
        }
        return isset(self::$currentUrls[$url]);
    }

    /**
     * @param $url
     * @return bool
     */
    public static function isCurrentStartsWith($url)
    {
        $url = self::clean($url);
        if (strpos(self::clean($_SERVER['REQUEST_URI']),$url)===0) {
            return true;
        }

        foreach (self::$currentUrls as $curl) {
            if (strpos($curl, $url)===0) {
                return true;
            }
        }
    }

    /**
     * @return mixed
     */
    public static function cleanUri()
    {
        $uri = $_SERVER['REQUEST_URI'];
        if (preg_match('{^([^?]+)\?}', $uri, $m)) {
            $uri = $m[1];
        }
        return $uri;
    }
}
