<?php

namespace TAO\Fields;

use Illuminate\Database\Schema\Blueprint;

/**
 *
 * Абстрактный класс, от которого пораждаются конкретные типы филдов
 *
 * Class Field
 * @package TAO\Fields
 */
abstract class Field
{
    /**
     *
     * Мнемокод типа (string, text, checkbox и пр.)
     * Переопределения не требует, заполняется автоматически
     *
     * @var string
     */
    public $type;

    /**
     *
     * Массив параметров поля как он описан в fields() модели
     * Переопределения не требует, заполняется автоматически
     *
     * @var array
     */
    public $data;


    /**
     *
     * Итем, к которому привязано поле
     * Переопределения не требует, заполняется автоматически
     *
     * @var \TAO\Fields\Model
     */
    public $item;

    /**
     *
     * Имя поля
     * Переопределения не требует, заполняется автоматически
     *
     * @var string
     */
    public $name;


    /**
     *
     * Расширяющие параметры типа - результат парсинга параметра type, т.е строки вида "string(200) index(f1, f2)"
     * Переопределения не требует, заполняется автоматически
     *
     * @var array
     */
    public $params;

    /**
     *
     * Проверка существования поля (полей) в таблице БД. Если поля нет, то оно создается
     * Чаще всего переопределение требуется в случае сложного поля (создание сопутствующих таблиц и т.п.)
     *
     * @param Blueprint $table
     */
    public function checkSchema(Blueprint $table)
    {
        if (!$this->item->hasColumn($this->name)) {
            $this->createField($table);
        } else {
            $f = $this->createField($table);
            if ($f) {
                $f->change();
            }
        }
        return $this;
    }

    /**
     *
     * Создание поля в таблице БД
     *
     * @param Blueprint $table
     */
    public function createField(Blueprint $table)
    {

    }

    /**
     *
     * Проверка индексов в таблице БД.
     * Как правило, переопределение не требуется. Описание индексов берется из параметра type - строки вида "string(200) index(f1, f2)"
     *
     * @param Blueprint $table
     */
    public function checkIndexes(Blueprint $table)
    {
        $index = false;
        foreach (['index', 'unique'] as $type) {
            if (isset($this->params[$type])) {
                $index = $this->params[$type];
            }
        }
        if ($index) {
            $type = $index['name'];
            $name = $index['extra'] ? $index['extra'] : ('idx_' . $this->item->getTable() . '_' . $this->name);
            $columns = $index['args'] ? $index['args'] : array($this->name);

            $info = $this->item->getIndexInfo($name);
            if (!$info) {
                $table->$type($columns, $name);
            } else {
                $currentType = $info->isUnique() ? 'Unique' : 'Index';
                $currentTypeString = strtolower($currentType) . ':' . implode(',', $info->getColumns());
                $newTypeString = $type . ':' . implode(',', $columns);
                if ($newTypeString != $currentTypeString) {
                    $dropMethod = "drop{$currentType}";
                    $table->$dropMethod($name);
                    $table->$type($columns, $name);
                }
            }
        }
        return $this;
    }

    /**
     *
     * Запись в итем значения.
     *
     * @param $value
     */
    public function set($value)
    {
        $this->item[$this->name] = $value;
    }

    /**
     * @param $request
     */
    public function setFromRequest($request)
    {
        if ($request->has($this->name)) {
            $this->item[$this->name] = is_null($request->input($this->name)) ? '' : $request->input($this->name);
        }
    }

    /**
     * @param $request
     */
    public function setFromRequestAfterSave($request)
    {
    }

    /**
     * @return string
     */
    public function defaultValue()
    {
        return '';
    }

    /**
     * @return $this
     */
    public function setupDefault()
    {
        $value = isset($this->data['default']) ? $this->data['default'] : $this->defaultValue();
        $this->set($value);
        return $this;
    }

    /**
     * @param $action
     * @param array $args
     * @return string
     */
    public function apiUrl($action, $args = array())
    {
        $args['action'] = trim($action);
        $args['datatype'] = $this->item->getDatatype();
        $args['field'] = $this->name;

        $id = $this->item->getKey();
        if (!empty($id)) {
            $args['id'] = $id;
        }
        return '/tao/fields/api?' . http_build_query($args);
    }

    /**
     * @param bool|false $user
     * @return mixed
     */
    public function accessAPI($user = false)
    {
        if (!$user) {
            $user = \Auth::user();
        }
        return $this->item->accessEdit($user);
    }

    /**
     * @return mixed
     */

    public function value()
    {
        return $this->prepareValue($this->rawValue());
    }

	public function rawValue()
	{
		return $this->item[$this->name];
	}

    protected function prepareValue($value)
    {
		if (isset($this->data['prepare_value']) && is_callable($this->data['prepare_value'])) {
			$value = call_user_func_array($this->data['prepare_value'], [$value, $this]);
		}
		return $value;
    }

    /**
     *
     * Имя дефолтного шаблона для рендера аутпута. Если возвращает false, то аутпут по умолчанию рендерится без шаблона
     *
     * @return string|bool
     */

    protected function defaultTemplate()
    {
        return false;
    }

    /**
     *
     * Дефолтный контекст, который передается в шаблон аутпута
     *
     * @return array
     */

    protected function defaultContext()
    {
        return [
            'field' => $this,
            'item' => $this->item,
            'settings' => $this->data,
        ];
    }

    /**
     *
     * Рендер значения
     *
     * @param $arg1 - имя шаблона или контекст (если шаблон стандартный)
     * $param $arg2 - контекст
     *
     * @return string
     */
    public function render($arg1 = false, $arg2 = false)
    {
        $template = $this->defaultTemplate();
        if (is_string($arg1)) {
            $template = $arg1;
        } elseif (isset($this->data['template'])) {
            $template = $this->data['template'];
        }
        if ($template) {
            $context = $this->defaultContext();
            if (is_array($arg1)) {
                $context = array_merge($context, $arg1);
            } elseif (is_array($arg2)) {
                $context = array_merge($context, $arg2);
            }
            return view($template, $context);
        } else {
            return $this->value();
        }
    }


    /**
     *
     * Имя дефолтного шаблона для рендера инпута в форме
     *
     * @return string
     */
    public function defaultInputTemplate()
    {
        if (isset($this->data['input_template'])) {
            return $this->data['input_template'];
        }
        return "fields ~ {$this->type}.input";
    }

    /**
     *
     * Дефолтный контекст, который передается в шаблон инпута
     *
     * @return array
     */
    public function defaultInputContext()
    {
        return $this->defaultContext();
    }

    /**
     *
     * Рендер инпута в форме
     *
     * @param $arg1 - имя шаблона или контекст (если шаблон стандартный)
     * $param $arg2 - контекст
     *
     * @return string
     */
    public function renderInput($arg1 = false, $arg2 = false)
    {
        $template = $this->defaultInputTemplate();
        if (is_string($arg1)) {
            $template = $arg1;
        } elseif (isset($this->data['input_template'])) {
            $template = $this->data['input_template'];
        }
        if ($template) {
            $context = $this->defaultInputContext();
            if (is_array($arg1)) {
                $context = array_merge($context, $arg1);
            } elseif (is_array($arg2)) {
                $context = array_merge($context, $arg2);
            }
            return view($template, $context);
        } else {
            return 'No input template for field ' . get_class($this);
        }
    }


    /**
     * @param $name
     * @param null $default
     * @return null
     */
    public function param($name, $default = null)
    {
        if (is_array($name)) {
            foreach ($name as $_name) {
                if (isset($this->data[$_name])) {
                    return $this->data[$_name];
                }
            }
            return $default;
        }
        return isset($this->data[$name]) ? $this->data[$name] : $default;
    }

    /**
     * @return mixed
     */
    public function typeParamsExtra()
    {
        return $this->params['type']['extra'];
    }

    /**
     * @return mixed
     */
    public function typeParamsArgs()
    {
        return $this->params['type']['args'];
    }

    /**
     * @param int $default
     * @return int
     */
    public function typeParamsIntArg($default = 0)
    {
        $args = $this->typeParamsArgs();
        if (is_array($args)) {
            foreach ($args as $arg) {
                if (preg_match('{^\d+$}', $arg)) {
                    return (int)$arg;
                }
            }
        }
        return $default;
    }

    /**
     * @param array $enum
     * @param bool|false $default
     * @return bool|string
     */
    public function typeParamsEnumArg(array $enum, $default = false)
    {
        $args = $this->typeParamsArgs();
        if (is_array($args)) {
            foreach ($args as $arg) {
                $arg = strtolower($arg);
                if (in_array($arg, $enum)) {
                    return $arg;
                }
            }
        }
        return $default;
    }

    /**
     * @param $message
     * @param null $column
     */
    public function error($message, $column = null)
    {
        $this->item->error($message, $column);
    }

    /**
     * @return mixed
     */
    public function renderForAdminList()
    {
        return $this->render();
    }

    /**
     * @return null
     */
    public function styleForInput()
    {
        if (\TAO::inAdmin()) {
            return $this->styleForAdminInput();
        }
        return $this->param(['form_style', 'style'], '');
    }

    /**
     * @return null
     */
    public function styleForAdminInput()
    {
        return $this->param(['admin_form_style', 'form_style', 'style'], '');
    }

    /**
     * @return null|string
     */
    public function classForInput()
    {
        if (\TAO::inAdmin()) {
            return $this->classForAdminInput();
        }
        $classes = $this->param(['form_class', 'class'], '');
        if (is_array($classes)) {
            $classes = implode(' ', $classes);
        }
        return $classes;
    }

    /**
     * @return null|string
     */
    public function classForAdminInput()
    {
        $classes = $this->param(['admin_form_class', 'form_class', 'class'], '');
        if (is_array($classes)) {
            $classes = implode(' ', $classes);
        }
        return $classes;
    }

    /**
     * @return null
     */
    public function inAdminList()
    {
        return $this->param(['in_admin_list', 'in_list'], false);
    }

    /**
     * @return null
     */
    public function inAdminEditForm()
    {
        return $this->param(['in_admin_edit_form', 'in_admin_form', 'in_form'], false);
    }

    /**
     * @return null
     */
    public function inAdminAddForm()
    {
        return $this->param(['in_admin_add_form', 'in_admin_form', 'in_form'], false);
    }

    /**
     * @return null
     */
    public function weightInAdminList()
    {
        return $this->param(['wight_in_admin_list', 'weight_in_list', 'weight'], 0);
    }

    /**
     * @return null
     */
    public function weightInAdminForm()
    {
        return $this->param(['wight_in_admin_form', 'weight_in_form', 'weight'], 0);
    }

    /**
     * @return null
     */
    public function labelInAdminList()
    {
        return $this->param(['label_in_admin_list', 'label_in_list', 'label'], $this->name);
    }

    /**
     * @return null
     */
    public function labelInAdminForm()
    {
        return $this->param(['label_in_admin_form', 'label_in_form', 'label'], $this->name);
    }

    /**
     * @return string
     */
    public function adminGroupLabel()
    {
        if (isset($this->data['group'])) {
            $group = trim($this->data['group']);
            if ($group == '') {
                return '#';
            }
            $groups = $this->item->adminFormGroups();
            if (isset($groups[$group])) {
                return $groups[$group];
            }
            return $group;
        }
        return '#';
    }

    /**
     * @return string
     */
    public function adminTab()
    {
        if (isset($this->data['group'])) {
            $group = trim($this->data['group']);
            if ($group) {
                list($tab) = explode('.', $group);
                $tab = trim($tab);
                if ($tab) {
                    return $tab;
                }
            }
        }
        return '#';
    }

    /**
     * @return mixed
     */
    public function __toString()
    {
        return $this->render();
    }

}