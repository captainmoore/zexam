<?php

namespace app\admin\controller;
use think\Controller;

class Paper extends Controller
{
    protected $questionCategoryModel;
    protected $questionChoiceModel;
    protected $paperModel;

    public function _initialize(){
        $this->questionCategoryModel=model('QuestionCategory');
        $this->questionChoiceModel=model('QuestionChoice');
        $this->paperModel=model('Paper');

    }

    public function paperList(){
        $res=$this->paperModel->where('status',1)->order('id','desc')->select();
        $count=$this->paperModel->where('status',1)->count();
        $this->assign([
            'res'=>$res,
            'count'=>$count,
        ]);
        return $this->fetch('paper_list');
    }

    /**
     * 添加试卷
     */
    public function add(){
        if(request()->isPost()){
            $data=input('post.');
            $data['question']=json_encode($data['question']);
            $data['create_time']=time();
            $data['update_time']=time();
            $res=$this->paperModel->data($data)->save();
            if($res){
                $this->success('试卷添加成功');
            }else{
                $this->error('试卷添加失败');
            }
        }else{
            return $this->fetch();
        }
    }

    /**
     * 编辑试卷
     * @return mixed
     */
    public function edit(){
        if(request()->isPost()){
            $data=input('post.');
            $data['question']=json_encode($data['question']);
            $data['update_time']=time();
            $res=$this->paperModel->where('id',$data['id'])->data($data)->update();
            if($res){
                $this->success('试卷修改成功');
            }else{
                $this->error('试卷修改失败');
            }
        }else{
            if($id=input('get.')['id']){
                $res=$this->paperModel->where('id',$id)->find()->toArray();
                $paper_id=$res['id'];
                $name=$res['name'];
                $remark=$res['remark'];
                $questions=json_decode($res['question'],true);
                $this->assign([
                    'paper_id'=>$paper_id,
                    'name'=>$name,
                    'remark'=>$remark,
                    'questions'=>$questions,
                ]);
                return $this->fetch();
            }else{
                $this->error('非法提交');
            }
        }
    }

    public function show() {
        $paper_id = input('id');
        if ($paper_id && $paper_id != 0) {
            $res = $this->paperModel->get($paper_id);

            //对问题按照list_order进行由低到高排序
            $questions=json_decode($res['question'],true);
            foreach($questions as $k=>$v){
                $list_order[$k]=$v['list_order'];
                $question=$this->questionChoiceModel->where('id',$v['id'])->find()->toArray();
                $questions[$k]['answer']=json_decode($question['answer'],true);
            }
            array_multisort($list_order,SORT_ASC,SORT_NUMERIC,$questions);

            $this->assign([
                'paper_id' => $paper_id,
                'name' => $res['name'],
                'remark' => $res['remark'],
                'create_time' => $res['create_time'],
                'update_time' => $res['update_time'],

                'questions'=>$questions,
                'question_num' => count($questions),
            ]);
            return $this->fetch();
        } else {
            $this->error('非法提交！');
        }
    }

    public function chooseQuestion(){
        $res=$this->questionChoiceModel->where('status',1)->order('id','asc')->select();
        $this->assign([
            'questions'=>$res,
        ]);
        return $this->fetch();
    }

    public function test(){



        //return $this->fetch();
    }
}