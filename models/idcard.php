<?php

use Illuminate\Database\Capsule\Manager as Capsule;

class Idcard extends Capsule{

    //protected $table = 'tiantian_idcard';

    //public $timestamps = false;

    public function getList () {
        //$all = self::find(1);
        $users = self::table('tiantian_idcard')->where('id', '=', 1)->get();
        return $users;
        //print_r($all);
    }
}
