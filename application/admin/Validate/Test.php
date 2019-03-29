<?php
/**
 * Created by PhpStorm.
 * User: lipo
 * Date: 2019/1/23 0023
 * Time: 17:40
 */
namespace app\admin\validate;
use think\Validate;

class Test extends Validate{
    protected $rule=[
      ['name','require|max:5','姓名不能为空|姓名不能超过10个字符'],
    ];
}