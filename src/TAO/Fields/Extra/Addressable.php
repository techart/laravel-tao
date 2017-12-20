<?php

namespace TAO\Fields\Extra;
use Illuminate\Database\Query\Builder;

/**
 * Trait Addressable
 * @package TAO\Fields\Extra
 *
 * Трейт для моделей, могущих откликаться по урлу, заданному в админке, а также по дефолтному урлу
 *
 */
trait Addressable
{
    public function initExtraAddressable()
    {
        $this->extraFields = \TAO::merge($this->extraFields, [
            'url' => array(
                'type' => 'string(250) index',
                'label' => 'URL',
                'style' => 'width:70%;',
                'weight' => -800,
                'in_list' => false,
                'in_form' => true,
                'group' => 'common',
            ),
        ]);
    }

    /**
     * Возвращает урл записи. Если не задан в админке, то возвращается дефолтный
     *
     * @return string
     */
    public function url()
    {
        $url = trim($this->field('url')->value());
        if ($url == '') {
            return $this->defaultUrl($this);
        }
        return $url;
    }

    /**
     * Возвращает по урлу (заданнму в адмике) итем, доступный на чтение текущему пользователю
     * Если нужно разграничивать доступ, то следует переопределить в конкретной модели
     *
     * @param $url
     * @return mixed
     */
    public function getAccessibleItemByUrl($url)
    {
        return $this->where('url', $url)->first();
    }

    /**
     * Дефолтный урл итема (если урл не задан в админке)
     * В качестве параметра может передаваться как итем, так и id
     *
     * @param Model|string $item
     * @return string
     */
    public function defaultUrl($item)
    {
        $id = is_object($item) ? $item->getKey() : $item;
        $url = '/' . $this->getDatatype() . "/{$id}/";
        return $url;
    }

    /**
     * Роутинг дататайпа по урлу, заданному в админке
     *
     * $data - параметры роутинга: (в примере рассматриваем урл /russia/moscow/)
     * - finder:    имя метода, который будет искать итем по урлу (по умолчанию - getAccessibleItemByUrl)
     * - pages:     если задан, то урл может быть многостраничным (/russia/moscow/page-1/ и т.д.). Номер страницы передается в Model::renderItemPage
     * - prefix:    префикс урла. Например - если задан news, то сработает /news/russia/moscow/
     * - postfix:   постфикс урла. Например - если задан shops, то сработает /russia/moscow/shops/
     * - mode:      режим отображения (по умолчанию - full)
     *
     * @param array $data
     * @return $this
     */
    public function routePageByUrl($data = [])
    {
        $request = app()->request();
        $url = $urlSrc = $request->getPathInfo();

        $page = 1;
        if (isset($data['pages']) || isset($data['listing'])) {
            if ($m = \TAO::regexp("{^(.+)/page-(\d+)/$}", $url)) {
                $url = $m[1] . '/';
                $data['page'] = $page = (int)$m[2];
            }
        }

        if (isset($data['prefix'])) {
            $prefix = trim($data['prefix'], '/');
            if ($m = \TAO::regexp("{^/{$prefix}/(.+)$}", $url)) {
                $url = '/' . $m[1];
            } else {
                return $this;
            }
        }

        if (isset($data['postfix'])) {
            $postfix = trim($data['postfix'], '/');
            if ($m = \TAO::regexp("{^(.+)/{$postfix}/$}", $url)) {
                $url = $m[1] . '/';
            } else {
                return $this;
            }
        }

        $finder = isset($data['finder']) ? $data['finder'] : 'getAccessibleItemByUrl';
        $mode = isset($data['mode']) ? $data['mode'] : 'full';
        $item = $this->$finder($url);
        if ($item instanceof \Illuminate\Database\Eloquent\Builder) {
            $item = $item->first();
        }
        if ($item) {
            $data['item'] = $item;
            $data['mode'] = $mode;
            \Route::any($urlSrc, function () use ($item, $data) {
                return $this->renderItemPage($data);
            });
        }
        return $this;
    }


    /**
     * Роутинг по дефолтному урлу. Если итем найден по дефолтному урлу, а у него в админке задан другой урл, то произойдет редирект 301
     *
     * @return $this
     */
    public function routePageById()
    {
        $url = $this->defaultUrl('{id}');

        \Route::any($url, function ($id) {
            $item = $this->getAccessibleItemById($id);
            if (!$item) {
                return response(view('404', 404));
            }

            $itemUrl = $item->url();
            $request = app()->request();

            $url = $request->getPathInfo();
            if ($url != $itemUrl) {
                return \Redirect::away($itemUrl, 301);
            }

            return $this->renderItemPage($item);
        })->where('id', '^\d+$');

        return $this;
    }
}