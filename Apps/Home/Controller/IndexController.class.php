<?php

namespace Home\Controller;

use Common\Controller\BaseController;

class IndexController extends BaseController
{

    public function _initialize(){
        parent:: _initialize();

    }

    public function index ()
    {
        $this->display();
    }

    public function paySuccess ()
    {
        redirect('/home#/order');
    }
}