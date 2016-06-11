<?php

use Illuminate\Database\Eloquent\Model as Eloquent;

class Basic_Idcard extends Eloquent{

    protected $table = 'basic_idcard';

    public $timestamps = false;

    /**
     * 根据姓名查找用户信息
     *
     * @param    string    $name    姓名
     * @return   array / bool
     */
    public function getByName($name) {
        $users = self::where('name', '=', $name)
            ->get();

        if ($users != null) {
            return $users->toArray();
        } else {
            return false;
        }
    }
}
