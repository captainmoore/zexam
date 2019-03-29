<?php

/**
 * Created by PhpStorm.
 * User: lipo
 * Date: 2018/12/28 0028
 * Time: 15:29
 */

namespace app\admin\controller;

use think\Controller;

class QuestionChoice extends Controller
{
    protected $questionChoiceModel;
    protected $questionCategoryModel;

    public function _initialize() {
        $this->questionChoiceModel = model('QuestionChoice');
        $this->questionCategoryModel = model('QuestionCategory');
    }

    /**
     * 可用选择题列表
     * @return mixed
     */
    public function questionList() {

        $arr = $this->questionCategoryModel->lists();
        $category = array();
        foreach ($arr as $k => $v) {
            $category[$v['id']] = $v['category'];
        }

        $this->assign([
            'count' => $this->questionChoiceModel->where('status', 1)->count(),
            'res' => $this->questionChoiceModel->where('status', 1)->select(),
            'category' => $category,
        ]);
        return $this->fetch();
    }

    /**
     * 禁用选择题列表
     * @return mixed
     */
    public function disabledList() {
        $arr = $this->questionCategoryModel->lists();
        $category = array();
        foreach ($arr as $k => $v) {
            $category[$v['id']] = $v['category'];
        }

        $this->assign([
            'count' => $this->questionChoiceModel->where('status', 0)->count(),
            'res' => $this->questionChoiceModel->where('status', 0)->select(),
            'category' => $category,
        ]);
        return $this->fetch('question_list');
    }

    /**
     * 删除选择题列表
     * @return mixed
     */
    public function deletedList() {

        $arr = $this->questionCategoryModel->lists();
        $category = array();
        foreach ($arr as $k => $v) {
            $category[$v['id']] = $v['category'];
        }

        $this->assign([
            'count' => $this->questionChoiceModel->where('status', -1)->count(),
            'res' => $this->questionChoiceModel->where('status', -1)->select(),
            'category' => $category,
        ]);
        return $this->fetch('question_list');
    }

    /**
     * 添加选择题
     * @return mixed
     */
    public function add() {
        if (request()->isPost()) {
            $data = input('post.');
            $answer = [];
            $question = [
                'title' => $data['title'],
                'category_id' => $data['category_id'],
                'status' => $data['status'],
                'remark' => $data['remark'],
                'create_time' => time(),
                'update_time' => time()
            ];
            foreach ($data['answer'] as $k => $v) {
                if ($v['content'] != "") {
                    $answer[$k] = $v;
                }
            }
            $question['answer'] = serialize($answer);
            $question['answer'] = json_encode($answer);
            $res = $this->questionChoiceModel->data($question)->save();
            if ($res) {
                $this->success('添加成功', url('admin/QuestionChoice/jump'), '', 1);
            } else {
                $this->error('添加失败');
            }
        } else {
            $this->assign([
                'category' => $this->questionCategoryModel->lists(),
            ]);
            return $this->fetch();
        }
    }

    /**
     * 编辑选择题
     * @return mixed
     */
    public function edit() {
        if (request()->isPost()) {
            $data = input('post.');
            $answer = [];
            $question = [
                'title' => $data['title'],
                'category_id' => $data['category_id'],
                'status' => $data['status'],
                'remark' => $data['remark'],
                'update_time' => time(),
            ];
            foreach ($data['answer'] as $k => $v) {
                if ($v['content'] != "") {
                    $answer[$k] = $v;
                }
            }
            $question['answer'] = json_encode($answer);
            p($question);
            $res = $this->questionChoiceModel->where('id', $data['question_id'])->update($question);
            if ($res) {
                $this->success('修改成功', url('admin/questionChoice/questionList'));
            } else {
                $this->error('修改失败');
            }
        } else {
            $id = intval(input('get.')['id']);
            $question = $this->questionChoiceModel->where('id', $id)->find();
            //dump($question);
            //dump($id);
            $this->assign([
                'question' => $question,
                'answer' => json_decode($question['answer'], true),
                'category' => $this->questionCategoryModel->lists(),
            ]);
            return $this->fetch();
        }
    }

    /**
     * 删除选择题
     * @param $id 删除条目主键
     */
    public function del($id) {
        if ($id) {
            $res = $this->questionChoiceModel->where('id', intval($id))->update(['status' => -1]);
            if ($res) {
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }

        } else {
            $this->error('参数错误！');
        }
    }

    /**
     * 添加完成后跳转
     * @return mixed
     */
    public function jump() {
        return $this->fetch();
    }

    /**
     * API
     */
    public function getQuestionById(){
        if(request()->isPost()){
            $ids=implode(',',input('post.')['ids']);
            $data=$this->questionChoiceModel->where('id','in',$ids)->order('id','asc')->select();
            if($data){
                $this->result($data,1);
            }else{
                $this->result('',0,'读取数据库失败');
            }
        }else{
            $this->result('',0,'非法提交');
        }
    }


}