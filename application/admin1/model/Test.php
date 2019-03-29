<?php
/**
 * Created by PhpStorm.
 * User: lipo
 * Date: 2019/1/4 0004
 * Time: 10:31
 */
namespace app\admin\model;
use think\Model;

class Test extends Model{
    public function getAll(){
        $data=[
            //'id'=>1,
        ];
        $order=[
            'id'=>'desc'
        ];

        return $this->where($data)->order($order)->select();
    }

    public function countAll(){
        return $this->count();
    }
}