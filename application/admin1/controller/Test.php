<?php
/**
 * Created by PhpStorm.
 * User: lipo
 * Date: 2019/1/3 0003
 * Time: 10:22
 */

namespace app\admin\controller;

use think\Controller;
use think\Model;

class Test extends Controller
{
    private $testModel;
    private $scoreModel;
    private $candidatesModel;

    public function _initialize() {
        $this->testModel = model('Test');
        $this->scoreModel = model('Score');
        $this->candidatesModel = model('Candidates');
    }

    public function add() {
        if (request()->isPost()) {
            $data = input('post.');
            $validate = validate('Test');
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }

            $data['start_time'] = strtotime($data['start_time']);
            $data['end_time'] = strtotime($data['end_time']);
            $data['create_time'] = time();
            $data['update_time'] = time();
            //dump($data);

            $res = model('Test')->save($data);
            if ($res) {
                $this->success('添加成功！');
            } else {
                $this->error('添加失败！');
            }
        } else {
            return $this->fetch();
        }
    }

    public function edit() {
        if (request()->isPost()) {
            $data = input('post.');
            $id = $data['id'];
            $data['update_time'] = time();
            $res = $this->testModel->save([
                'name' => $data['name'],
                'description' => $data['description'],
                'start_time' => strtotime($data['start_time']),
                'end_time' => strtotime($data['end_time']),
                'update_time' => time()
            ], ['id' => intval($id)]);

            if ($res) {
                //$this->success('修改成功！');
                echo '<h2>修改成功</h2>';
                echo '<script type="text/javascript">setTimeout(function() {parent.location.reload();},1000)</script>';
            } else {
                $this->error('修改失败！');
            }
        } else {
            $id = input('id');
            $res = $this->testModel->get($id);
            //dump($res);
            return $this->fetch('', [
                'test' => $res,
            ]);
        }
    }

    public function del($id) {
        $id = intval($id);
        $res = $this->testModel->get($id)->delete();
        if ($res) {
            $this->success('删除成功！');
        } else {
            $this->error('删除失败！');
        }
    }

    public function listAll() {
        $res = $this->testModel->getAll();
        $count = $this->testModel->countAll();
        $this->assign([
            'res' => $res,
            'count' => $count,
        ]);
        return $this->fetch('list');
    }

    public function inputScore() {
        if (request()->isPost()) {
            $data = input('post.');

            foreach ($data['info'] as $k => $v) {
                $score = array();
                $score['test_id'] = $data['test_id'];
                $score['candidate_id'] = $v['id'];
                $score['candidate_name'] = $v['name'];
                $score['remark'] = $v['remark'];
                $score['score'] = $v['score'];
                $score['list_order'] = $v['list_order'];

                if ($v['id'] > 0) {//考生id如果为0，则说明该考生不存在，需要在考生表中增加该考生
                    $id = $this->scoreModel->data($score)->save();
                    if ($id <= 0) {
                        $this->error('录入失败');
                    }
                } else if ($v['id'] == 0) {
                    $candidate = array();
                    $candidate['name'] = $v['name'];
                    $candidate['create_time'] = time();
                    $candidate['update_time'] = time();
                    $this->candidatesModel->data($candidate, true)->isUpdate(false)->save();
                    $candidate_id = $this->candidatesModel->id;
                    $score['candidate_id'] = $candidate_id;
                    $res = $this->scoreModel->data($score, true)->isUpdate(false)->save();

                    if ($res < 0) {
                        $this->error('录入失败');
                    }
                } else {
                    $this->error('参数非法！');
                }

                $res = $this->testModel->where('id', $data['test_id'])->update(['score_num' => count($data['info'])]);
                if ($res) {
                    $this->success('录入成功', url('admin/test/showScore', ['id' => $data['test_id']]), [], 2);
                } else {
                    $this->error('录入失败');
                }
            }
        } else {
            $test_id = input('id');
            $res = $this->testModel->where('id', $test_id)->find();

            $this->assign([
                'test_id' => $test_id,
                'test_name' => $res['name'],
                'test_description' => $res['description'],
                'test_start_time' => $res['start_time'],
                'test_end_time' => $res['end_time']
            ]);
            return $this->fetch();
        }
    }

    public function inquiryNameAjax() {
        if (request()->isPost()) {
            $key = input('post.')['key'];
            $res = $this->candidatesModel->where('name like "' . $key . '%" and status=1')->select();
            return $res;
        }
    }

    public function showScore() {
        $test_id = input('id');
        if ($test_id && $test_id != 0) {
            $res = $this->testModel->get($test_id);

            $this->assign([
                'test_id' => $test_id,
                'test_name' => $res['name'],
                'test_description' => $res['description'],
                'test_start_time' => $res['start_time'],
                'test_end_time' => $res['end_time'],

                'score_num' => $this->scoreModel->where('test_id', 'eq', $test_id)->count(),
                'score_max' => $this->scoreModel->where('test_id', 'eq', $test_id)->max('score'),
                'score_min' => $this->scoreModel->where('test_id', 'eq', $test_id)->min('score'),
                'score_avg' => round($this->scoreModel->where('test_id', 'eq', $test_id)->avg('score'), 2)
            ]);
            $scores = $this->scoreModel->where('test_id', $test_id)->order('id', 'desc')->select();
            $this->assign(['scores' => $scores]);
            return $this->fetch();
        } else {
            $this->error('非法提交！');
        }
    }

    /**
     * @param $id 考试id
     */
    public function editScore() {
        if (request()->isPost()) {
            $data = input('post.');
            foreach ($data['info'] as $k => $v) {
                $score = array();
                $score['test_id'] = $data['test_id'];
                $score['candidate_id'] = $v['candidate_id'];
                $score['candidate_name'] = $v['name'];
                $score['remark'] = $v['remark'];
                $score['score'] = $v['score'];
                $score['list_order'] = $v['list_order'];

                if($v['del']==0){
                    $this->scoreModel->get($v['id'])->delete();
                }else{
                    if ($v['id'] > 0) {
                        $id = $this->scoreModel->save($score, ['id' => $v['id']]);
                    } else if ($v['id'] == 0) {
                        $model = model('Score');
                        $res = $model->data($score, true)->isUpdate(false)->save();
                        if (!$res) {
                            $this->error('修改失败11！');
                        }
                    } else {
                        $this->error('非法的参数！');
                    }
                }
            }
            $res = $this->testModel->where('id', $data['test_id'])->update(['score_num' => count($data['info'])]);
            echo $this->testModel->getLastSql();
            if ($res>=0) {
                $this->success('修改成功', url('admin/test/showScore', ['id' => $data['test_id']]), [], 2);
            } else {
                $this->error('修改失败');
            }
        } else {
            $test_id = input('id');
            if ($test_id && $test_id != 0) {
                $res = $this->testModel->where('id', $test_id)->find();
                $this->assign([
                    'test_id' => $test_id,
                    'test_name' => $res['name'],
                    'test_description' => $res['description'],
                    'test_start_time' => $res['start_time'],
                    'test_end_time' => $res['end_time']
                ]);
                $scores = $this->scoreModel->where('test_id', $test_id)->order('id', 'desc')->select();
                $this->assign(['scores' => $scores]);
                return $this->fetch();
            } else {
                $this->error('非法提交！');
            }
        }
    }

}