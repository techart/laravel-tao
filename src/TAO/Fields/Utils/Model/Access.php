<?php

namespace TAO\Fields\Utils\Model;

/**
 * Class Access
 * @package TAO\Fields\Utils\Model
 */
trait Access
{

    /**
     * @param $user
     * @return mixed
     */
    public function accessAdmin($user = false)
    {
        if (!$user) {
            $user = \Auth::user();
        }
        return $user['is_admin'];
    }

    /**
     * @param $user
     * @return mixed
     */
    public function accessEdit($user = false)
    {
        if (!$user) {
            $user = \Auth::user();
        }
        return $this->accessAdmin($user);
    }

    /**
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