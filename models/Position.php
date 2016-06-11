<?php

use Illuminate\Database\Eloquent\Model as Eloquent;

class Position extends Eloquent{

    protected $table = 'position';

    public $timestamps = false;

    /**
     * 根据county_id获取地址信息
     *
     * @param    string    $county_id    区县id
     * @return   array
     */
    public static function getPositionByCountyId($county_id) {
        $position = self::select(['province_name', 'city_name', 'county_name', 'town_name', 'village_name'])
            ->where('county_id', '=', $county_id)
            ->get();

        if ($position != null) {
            return $position->toArray();
        } else {
            return false;
        }
    }

    /**
     * 根据city_id获取地址信息
     *
     * @param    string    $city_id    城市id
     * @return   array
     */
    public static function getPositionByCityId($city_id) {
        $position = self::select(['province_name', 'city_name', 'county_name', 'town_name', 'village_name'])
            ->where('city_id', '=', $city_id)
            ->limit(20)
            ->get();

        if ($position != null) {
            return $position->toArray();
        } else {
            return false;
        }
    }

}
