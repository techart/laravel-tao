<?php

namespace TAO\Fields\Utils\Model;

/**
 * Class Admin
 * @package TAO\Fields\Utils\Model
 */
trait Admin
{
    /**
     * @return string
     */
    public function adminController()
    {
        return $this->adminType == 'tree' ? '\\TAO\\Admin\\TreeController' : '\\TAO\\Admin\\TableController';
    }

    public function adminMenuSection()
    {
        return false;
    }

    /**
     * @return array|bool
     */
    public function adminFormTabs()
    {
        $groups = $this->adminFormGroups();
        $tabs = array();
        if (is_array($groups)) {
            foreach ($groups as $code => $label) {
                if (strpos($code, '.') === false) {
                    $tabs[$code] = $label;
                }
            }
            return count($tabs) > 0 ? $tabs : false;
        }
        return false;
    }

    /**
     * @return array
     */
    public function adminFormFields()
    {
        $add = !$this->exists;
        $fields = array();
        foreach ($this->fieldsObjects() as $name => $field) {
            $method = $add ? 'inAdminAddForm' : 'inAdminEditForm';
            if ($field->$method()) {
                $fields[$name] = $field;
            }
        }
        uasort($fields, function ($f1, $f2) {
            $w1 = $f1->weightInAdminForm();
            $w2 = $f2->weightInAdminForm();
            if ($w1 > $w2) {
                return 1;
            }
            if ($w1 < $w2) {
                return -1;
            }
            return 0;
        });
        return $fields;
    }

    /**
     * @return bool
     */
    public function adminFormGroups()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function adminViewsPath()
    {
        return false;
    }

    /**
     * @return int
     */
    public function adminPerPage()
    {
        return 20;
    }

    /**
     * @return string
     */
    public function adminTitle()
    {
        return get_class($this);
    }

    /**
     * @return string
     */
    public function adminMenuTitle()
    {
        return $this->adminTitle();
    }

    /**
     * @return string
     */
    public function adminTitleList()
    {
        return $this->adminTitle();
    }

    /**
     * @return string
     */
    public function adminTitleEdit()
    {
        return $this->adminTitle() . ': Редактирование';
    }

    /**
     * @return string
     */
    public function adminTitleAdd()
    {
        return $this->adminTitle() . ': Добавление';
    }

    /**
     *
     */
    public function prepareForAdminList()
    {
    }

    /**
     * @return string
     */
    public function adminAddButtonText()
    {
        return 'Добавить';
    }

    /**
     * @return string
     */
    public function adminAddSubmitText()
    {
        return $this->adminAddButtonText();
    }

    /**
     * @return string
     */
    public function adminEditSubmitText()
    {
        return 'Изменить';
    }

    /**
     * @return string
     */
    public function adminReturnToListText()
    {
        return 'Вернуться к списку';
    }

    /**
     * @return string
     */
    public function adminEmptyListText()
    {
        return 'Нет ни одной записи';
    }
}