<?php
namespace app\admin\model;

use think\Model;

class QuestionCategory extends Model
{
    public function lists(){
        $res=$this->order('id','asc')->select();
        return $res;
    }
}