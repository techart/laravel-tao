<?php

namespace TAO\Fields;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class PageModel
 * @package TAO\Fields
 *
 * Абстрактный класс для страничных моделей
 */
abstract class PageModel extends Model
{
    use \TAO\Fields\Extra\Switchable,
        \TAO\Fields\Extra\Addressable,
        \TAO\Fields\Extra\Title,
        \TAO\Fields\Extra\Metas;

    use \TAO\Fields\Extra\Addressable {
        getAccessibleItemByUrl as private parentAccessibleItemByUrl;
    }

    /**
     *
     */
    protected function initExtraFields()
    {
        $this->initExtra('Switchable', 'Addressable', 'Title', 'Metas');
    }

    /**
     * @return array
     */
    public function adminFormGroups()
    {
        return array(
            'common' => 'Основные параметры',
            'common.meta' => 'SEO-информация',
            'content' => 'Контент',
            'params' => 'Доп.параметры',
        );
    }

    /**
     * Переопределяем стандартные методы, т.к. нужно учитывать isactive
     *
     * @param $url
     * @return null|Model
     */
    public function getAccessibleItemByUrl($url)
    {
        $item = $this->parentAccessibleItemByUrl($url);
        return (is_object($item) && $item->isactive)? $item : null;
    }

    /**
     * @param $id
     * @return null|Model
     */
    public function getAccessibleItemById($id)
    {
        $item = parent::getAccessibleItemById($id);
        return $item->isactive? $item : null;
    }

    /**
     * @param array $data
     * @return Builder
     */
    public function getAccessibleItems($data = [])
    {
        return $this->ordered()->where('isactive', 1);
    }

}