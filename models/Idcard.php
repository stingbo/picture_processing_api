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

    /**
     * 更新用户的身份证图片
     *
     * @param   array    $data        要更新的数据
     */
    public static function updateIdcard($data) {
        self::where('idcard_no', '=', $data['idcard_no'])
            ->update([
                'idcard_front_img_false' => $data['front'],
                'idcard_back_img_false' => $data['back'],
                'idcard_both_img_false' => $data['both'],
                'issuing_authority' => $data['issuing_authority']
            ]);
    }

    /**
     * 查询身份证图片路径
     *
     * @param   string    $idcard_no        身份证号
     */
    public static function getIdcardImg($idcard_no) {
        $user = self::select([
            'gender',
            'idcard_front_img',
            'idcard_back_img',
            'idcard_both_img',
            'idcard_front_img_false',
            'idcard_back_img_false',
            'idcard_both_img_false'
        ])
            ->where('idcard_no', '=', $idcard_no)
            ->get()
            ->first();

        if ($user != null) {
            return $user->toArray();
        } else {
            return false;
        }
    }
}
