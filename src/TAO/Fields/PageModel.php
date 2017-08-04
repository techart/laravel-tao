<?php

namespace TAO\Fields;

abstract class PageModel extends Model
{
    public function calculatedFields()
    {
        $fields = $this->fields();
        $extra = array(
            'isactive' => array(
                'type' => 'checkbox index',
                'label' => 'Включено к показу',
                'label_int_list' => 'Вкл',
                'default' => 1,
                'weight' => -900,
                'in_list' => true,
                'in_form' => true,
                'group' => 'common',
            ),
            'url' => array(
                'type' => 'string(250) index',
                'label' => 'URL',
                'style' => 'width:70%;',
                'weight' => -800,
                'in_list' => false,
                'in_form' => true,
                'group' => 'common',
            ),
            'title' => array(
                'type' => 'string(250)',
                'label' => 'Заголовок',
                'style' => 'width:90%;',
                'weight' => -700,
                'in_list' => true,
                'in_form' => true,
                'group' => 'common',
            ),
            'meta_title' => array(
                'type' => 'string(250)',
                'label' => 'Title',
                'style' => 'width:90%;',
                'weight' => 900100,
                'in_list' => false,
                'in_form' => true,
                'group' => 'common.meta',
            ),
            'meta_description' => array(
                'type' => 'text',
                'label' => 'Description',
                'weight' => 900200,
                'in_list' => false,
                'in_form' => true,
                'style' => 'width: 90%; height:100px;',
                'group' => 'common.meta',
            ),
            'meta_keywords' => array(
                'type' => 'text',
                'label' => 'Keywords',
                'weight' => 900300,
                'in_list' => false,
                'in_form' => true,
                'style' => 'width: 90%; height:100px;',
                'group' => 'common.meta',
            ),
        );
        foreach($extra as $field => $data) {
            if (isset($fields[$field])) {
                if ($fields[$field] === false) {
                    unset($fields[$field]);
                }
            } else {
                $fields[$field] = $data;
            }
        }
        return $fields;
    }

    public function adminFormGroups()
    {
        return array(
            'common' => 'Основные параметры',
            'common.meta' => 'SEO-информация',
            'content' => 'Контент',
            'params' => 'Доп.параметры',
        );
    }

    public function viewController()
    {
        return \TAO\Fields\Controllers\View::class;
    }

    public function templateFor($mode)
    {
        $code = $this->getDatatype();
        return "datatype ~ {$code}.{$mode}";
    }

    public function templateForPage()
    {
        return $this->templateFor('full');
    }

    public function route($request)
    {
        $url = $request->getPathInfo();
        $item = $this->where('url', $url)->where('isactive', 1)->first();
        if ($item) {
            return array(
                'controller' => $this->viewController(),
                'item' => $item,
            );
        }
    }

}