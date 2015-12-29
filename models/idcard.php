<?php

use Illuminate\Database\Eloquent\Model as Eloquent;

class Idcard extends Eloquent{

    protected $table = 'tiantian_idcard';

    public $timestamps = false;

    public function getList() {
        $all = self::all()->toArray();
        return $all;
    }

    public function createIdcard() {
    }

    public function updateIdcard() {
    }

    public function deleteIdcard($id) {
    }
}
