<?php

namespace TAO\Fields\Model;
use TAO\Fields\Model as AbstractModel;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

abstract class AbstractUser extends AbstractModel implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    public $isBlocked = false;

    public function fields()
    {
        $fields = array(
            'remember_token' => array(
                'type' => 'remember_token',
                'in_list' => false,
                'in_form' => false,
            ),
            'is_admin' => array(
                'type' => 'checkbox',
                //'label' => false,
                'label' => 'Супер-администратор',
                'in_list' => false,
                'in_form' => true,
                'group' => 'access',
            ),
            'is_secondary_admin' => array(
                'type' => 'checkbox',
                //'label' => false,
                'label' => 'Администратор',
                'in_list' => false,
                'in_form' => true,
                'group' => 'access',
            ),
            'access_realm_admin' => array(
                'type' => 'checkbox',
                //'label' => false,
                'label' => 'Редактор',
                'in_list' => false,
                'in_form' => true,
                'group' => 'access',
            ),
            'name' => array(
                'type' => 'string(150)',
                'label' => 'Имя',
                'in_list' => true,
                'weight_in_list' => 100,
                'in_form' => true,
                'style' => 'width: 90%',
                'group' => 'common',
            ),
            'email' => array(
                'type' => 'string(50) index',
                'label' => 'E-Mail',
                'in_list' => true,
                'weight_in_list' => 200,
                'in_form' => true,
                'style' => 'width: 250px',
                'group' => 'common',
            ),
            'password' => array(
                'type' => 'password',
                'label' => 'Хеш пароля',
                'in_list' => false,
                'in_form' => true,
                'group' => 'common',
            ),
            'roles' => array(
                'type' => 'multilink',
                'model' => Role::class,
                'label' => false,
                'in_list' => false,
                'in_form' => true,
                'group' => 'access.roles',
            ),
        );
        return $fields;
    }

    public function accessToRealm($name)
    {
        if ($this['is_admin'] || $this['is_secondary_admin']) {
            return true;
        }
        return (int)$this["access_realm_{$name}"];
    }

    public function adminFormGroups()
    {
        return array(
            'common' => 'Основные параметры',
            'access' => 'Управление доступом',
            'access.roles' => 'Состоит в группах',
        );
    }

    public function adminTitleList()
    {
        return 'Зарегистрированные пользователи';
    }

    public function adminTitleEdit()
    {
        return 'Редактирование пользователя';
    }

    public function adminAddButtonText()
    {
        return 'Создать';
    }
}
