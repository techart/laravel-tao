<?php

$nav = array(
    array(
        'url' => '/admin/',
        'title' => 'Главная',
    ),
    array(
        'access' => 'root',
        'url' => '/admin/vars/',
        'title' => 'Сайт',
        'sub' => array(
            array(
                'url' => '/admin/vars/',
                'title' => 'Настройки',
            ),
            array(
                'url' => '/admin/datatype/users/',
                'title' => 'Пользователи',
                'divider' => true,
            ),
            array(
                'url' => '/admin/datatype/roles/',
                'title' => 'Роли',
            ),
        ),
    ),
);

$nav = array_merge($nav, app()->taoAdmin->menu());

return $nav;