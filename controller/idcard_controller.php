<?php

require '../models/idcard.php';

class Idcard_controller {

    protected $idcard_obj;

    public function __construct() {
    }

    public function get() {
        $this->idcard_obj = new Idcard();
        $rst = $this->idcard_obj->getList();
        return $rst;
    }

    public function post() {

    }

    public function put() {

    }

    public function del() {

    }
}
