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
                'label_in_admin_list' => 'Вкл',
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
                } else {
                    $fields[$field] = \TAO::merge($data, $fields[$field]);
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

    public function url()
    {
        $url = trim($this->field('url')->value());
        if ($url=='') {
            return $this->itemUrl($this);
        }
        return $url;
    }

    public function itemUrl($item)
    {
        $id = is_object($item)? $item->getKey() : $item;
        $url =  '/'.$this->getDatatype()."/{$id}/";
        return $url;
    }

    public function itemRoutes()
    {
        $url = $this->itemUrl('{id}');
        \Route::any($url, function($id) {
            $item = $this->find($id);
            if ($item->isactive) {
                $itemUrl = $item->url();
                $request = app()->request();
                $url = $request->getPathInfo();
                if ($url != $itemUrl) {
                    return \Redirect::away($itemUrl, 301);
                }
                return $this->renderItemPage($item);
            }
        })->where('id', '^\d+$');
    }

}