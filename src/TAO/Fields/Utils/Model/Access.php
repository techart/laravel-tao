<?php

namespace TAO\Fields\Utils\Model;
use TAO\Fields\Model\User;
use TAO\Navigation;

/**
 * Class Access
 * @package TAO\Fields\Utils\Model
 */
trait Access
{

    protected $groupAdmin = false;
    protected $groupAdminEdit = false;

    /**
     *
     * Возвращает символьный код группы доступа к админке этого дататайпа
     *
     * @return bool|string
     */
    public function groupForAdmin()
    {
        if ($this->groupAdmin) {
            return $this->groupAdmin;
        }
        return 'admin_'.$this->getDatatype();
    }

    /**
     *
     * Может ли текущий или переданный юзер админить данный дататайп (входить в его админку)
     *
     * @param $user
     * @return mixed
     */
    public function accessAdmin($user = false)
    {
        if (!$user) {
            $user = \Auth::user();
        }
        if ($user['is_admin']||$user['is_secondary_admin']) {
            return true;
        }

        $group = $this->groupForAdmin();
        if ($group) {
            return $user->checkAccess($group);
        }
        return false;
    }

    /**
     * @param Navigation $menuItem
     * @param User $user
     * @return mixed
     */
    public function accessAdminMenuItem($menuItem, $user)
    {
        return $this->accessAdmin($user);
    }

    /**
     *
     * Может ли текущий или переданный юзер редактировать данную конкретную запись
     *
     * @param $user
     * @return mixed
     */
    public function accessEdit($user = false)
    {
        if (!$user) {
            $user = \Auth::user();
        }
        if (!$this->accessAdmin($user)) {
            return false;
        }
        if ($this->groupAdminEdit) {
            return $user->checkAccess($this->groupEdit);
        }
        return true;
    }

    /**
     *
     * Может ли текущий или переданный юзер удалять данную конкретную запись
     *
     * @param $user
     * @return mixed
     */
    public function accessDelete($user = false)
    {
        if (!$user) {
            $user = \Auth::user();
        }
        return $this->accessEdit($user);
    }

    /**
     *
     * Может ли текущий или переданный юзер добавлять записи в этом дататайпе
     *
     * @param $user
     * @return mixed
     */
    public function accessAdd($user = false)
    {
        if (!$user) {
            $user = \Auth::user();
        }
        return $this->accessEdit($user);
    }
}