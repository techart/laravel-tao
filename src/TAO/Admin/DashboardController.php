<?php

namespace TAO\Admin;

class DashboardController extends AdminController
{
    public function index()
    {
        return $this->render('tao::admin.dashboard');
    }
}