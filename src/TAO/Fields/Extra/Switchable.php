<?php

namespace TAO\Fields\Extra;

/**
 * Trait Switchable
 * @package TAO\Fields\Extra
 *
 * @property bool $isactive
 */
trait Switchable
{
    public function initExtraSwitchable()
    {
        $this->extraFields = \TAO::merge($this->extraFields, [
            'isactive' => array(
                'type' => 'checkbox index',
                'label' => 'Включено к показу',
                'label_in_admin_list' => 'Вкл',
                'default' => 1,
                'weight' => -900,
                'in_list' => true,
                'in_form' => true,
                'group' => 'common',
            ),
        ]);
    }
}