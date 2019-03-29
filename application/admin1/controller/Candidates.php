<?php
/**
 * Created by PhpStorm.
 * User: lipo
 * Date: 2019/1/9 0009
 * Time: 09:46
 */

namespace app\admin\controller;

use think\Controller;

class Candidates extends Controller
{
    private $candidatesModel;

    public function _initialize() {
        parent::_initialize();
        $this->candidatesModel = model('Candidates');
    }

    public function listAll() {
        $res = $this->candidatesModel->getAll();
        $count = $this->candidatesModel->countAll();
        $this->assign([
            'candidates' => $res,
            'count' => $count,
        ]);
        return $this->fetch('list');
    }

    public function add() {
        if (request()->isPost()) {
            $data = input('post.');
            $data['create_time'] = time();
            $data['update_time'] = time();

            $res = $this->candidatesModel->save($data);
            if ($res) {
                $this->success('添加成功', $_SERVER['HTTP_REFERER']);
            } else {
                $this->error('添加失败');
            }
        } else {
            return $this->fetch();
        }
    }

    public function edit() {
        if (request()->isPost()) {
            $data = input('post.');
            $res = $this->candidatesModel->save($data, ['id' => $data['id']]);
            if ($res >= 0) {
                echo '修改成功';
                echo '<script>setTimeout(function() {parent.location.reload()},1000)</script>';
            }
        } else {
            $id = intval(input('get.')['id']);
            $res = $this->candidatesModel->get($id);
            $this->assign([
                'candidate' => $res,
            ]);
            return $this->fetch();
        }
    }

    /**
     * 删除指定考生
     * @param $id
     */
    public function del($id){
        if(!empty($id)){
            $res=$this->candidatesModel->save(['status'=>-1],['id'=>$id]);
            if($res){
                $this->success('删除成功');
            }else{
                $this->error('删除失败');
            }
        }else{
            $this->error('非法提交');
        }
    }

    public function deletedCandidates(){
        $candidates=$this->candidatesModel->getDeletedCandidates();
        $count=$this->candidatesModel->where(['status'=>-1])->count();
        $this->assign([
            'candidates'=>$candidates,
            'count'=>$count
        ]);

        return $this->fetch('list_deleted');
    }

    /**
     * 恢复被删除的考生
     * @param $id
     */
    public function recover($id){
        if(!empty($id)){
            $res=$this->candidatesModel->save(['status'=>1],['id'=>$id]);
            if($res){
                $this->success('恢复成功');
            }else{
                $this->error('恢复失败');
            }
        }else{
            $this->error('非法提交');
        }
    }
}