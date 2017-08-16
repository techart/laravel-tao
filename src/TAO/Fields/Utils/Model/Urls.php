<?php

namespace TAO\Fields\Utils\Model;

trait Urls
{
    protected $urlCode = false;
    protected $baseListUrl = false;
    protected $pagerVar = 'page';

    public function urlCode($value)
    {
        $this->urlCode = $value;
        return $this;
    }

    public function getUrlCode()
    {
        return $this->urlCode? $this->urlCode : $this->getDatatype();
    }

    public function baseListUrl($value)
    {
        $this->baseListUrl = $value;
        return $this;
    }

    public function getBaseListUrl()
    {
        $url = $this->baseListUrl;
        if (!$url) {
            $url = '/'.$this->getUrlCode().'/';
        }
        return $url;
    }

    public function pagerVar($value)
    {
        $this->pagerVar = $value;
        return $this;
    }

    public function listUrl($page = 1)
    {
        $url = $this->getBaseListUrl();
        if ($page==1) {
            return $url;
        }
        return rtrim($url,'/')."/{$this->pagerVar}-{$page}/";
    }

    public function itemUrl($id)
    {
        return false;
    }

}