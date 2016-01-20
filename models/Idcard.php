<?php

use Illuminate\Database\Eloquent\Model as Eloquent;

class Idcard extends Eloquent{

    protected $table = 'idcard';

    public $timestamps = false;

    //定义不允许批量插入的字段
    protected $guarded = [];

    //定义允许批量插入的字段
    //protected $fillable = ['name', 'gender', 'birthday', 'detail_address'];

    /**
     * 根据姓名查找用户信息
     *
     * @param    string    $name    姓名
     * @return   array
     */
    public static function getOneByName($name) {
        $user = self::where('name', '=', $name)
            ->get()
            ->first();

        if ($user != null) {
            return $user->toArray();
        } else {
            return false;
        }
    }

    /**
     * 保存验证的身份证信息
     *
     * @param   array    $data        要插入的数据
     */
    public static function createIdcard($data) {
        self::create($data);
    }
}
