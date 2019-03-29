<?php
namespace app\admin\controller;
use think\Controller;

class Index extends Controller {
    public function welcome() {
        $this->assign([
            'test_num'=>model('Test')->count(),
            'candidate_num'=>model('Candidates')->count(),
            'score_num'=>model('Score')->count(),
            'info'=>$_SERVER,
        ]);
        return $this->fetch();
    }

    public function index(){
        return $this->fetch();
    }
}