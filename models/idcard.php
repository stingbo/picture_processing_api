<?php

use Illuminate\Database\Capsule\Manager as Capsule;

class Idcard extends Capsule{

    protected $table = 'tiantian_idcard';

    public $timestamps = false;

    public function getList () {
        $all = Idcard::find(1);
        //print_r($all);
    }
}
