<?php
namespace app\index\controller;

class Index extends \think\Controller
{
    public function index()
    {
        $url=url("admin/index/index");
        echo "<a href='$url'>Welcome to use Z-EXAM</a>";
        //return $this->fetch();
    }
}
