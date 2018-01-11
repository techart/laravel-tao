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
     * Проверка прав доступа на просмотр страницы записи
     *
     * @param bool $user
     * @return mixed
     */
    public function accessView($user = false)
    {
        return $this->isactive;
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