<?php

/**
 * Created by PhpStorm.
 * User: lipo
 * Date: 2019/2/14 0014
 * Time: 09:40
 */

namespace app\admin\controller;

use think\Controller;

class Analysis extends Controller
{
    private $candidatesModel;
    private $analysisDataModel;
    private $analysisRefineryModel;
    private $analysisRefineryAreaModel;

    public function _initialize()
    {
        parent::_initialize();
        $this->candidatesModel = model('Candidates');
        $this->analysisDataModel = model('analysisData');
        $this->analysisRefineryModel = model('analysisRefinery');
        $this->analysisRefineryAreaModel = model('analysisRefineryArea');
    }

    //成交数据列表
    public function dataList()
    {
        $sql = 'select data.id as id,trade_date,area,brand,name,trade_type,trade_mode,volume,trade_price,basis,contract,delivery_date_fixed_price,delivery_date_basis_start,delivery_date_basis_end from exam_analysis_data as data join exam_analysis_refinery as re join exam_analysis_refinery_area as area where data.refinery_id=re.id and re.area_id=area.id order by id DESC';

        $res = $this->analysisDataModel->query($sql);
        $count = $this->analysisDataModel->count();

        $this->assign([
            'res' => $res,
            'count' => $count,
        ]);
        return $this->fetch();
    }

    public function dataListByDay()
    {
        $sql = 'select trade.id as id,trade_date,sum(volume) as total_volume from exam_analysis_data as trade join exam_analysis_refinery as re join exam_analysis_refinery_area as area where trade.refinery_id=re.id and re.area_id=area.id group by trade.trade_date order by trade_date ASC';
        $res = $this->analysisDataModel->query($sql);

        $sql_spot='select trade_date,sum(volume) as spot_volume from exam_analysis_data as trade join exam_analysis_refinery as re where trade.refinery_id=re.id and trade.trade_type=\'现货\' group by trade.trade_date order by trade_date ASC';
        $spot_volume=$this->analysisDataModel->query($sql_spot);

        $sql_basis='select trade_date,sum(volume) as basis_volume from exam_analysis_data as trade join exam_analysis_refinery as re where trade.refinery_id=re.id and trade.trade_type=\'远月\' and trade.trade_mode=\'基差\' group by trade.trade_date order by trade_date ASC';
        $basis_volume=$this->analysisDataModel->query($sql_basis);

        //p($res);
        //p($spot_volume);
        //p($basis_volume);
        $day_arr=[];
        $volume_arr=[];
        $spot_volume_arr=[];
        $basis_volume_arr=[];


        $spot_temp=[];
        foreach($spot_volume as $k=>$v){
            $spot_temp[$v['trade_date']]=$v['spot_volume'];
        };
        $basis_temp=[];
        foreach($basis_volume as $k=>$v){
            $basis_temp[$v['trade_date']]=$v['basis_volume'];
        };

        foreach($res as $k=>$v){
            $day_arr[]=date('Y-m-d',$v['trade_date']);
            $volume_arr[]=$v['total_volume'];

            $spot_volume_arr[]=array_key_exists($v['trade_date'],$spot_temp)?$spot_temp[$v['trade_date']]:0;
            $basis_volume_arr[]=array_key_exists($v['trade_date'],$basis_temp)?$basis_temp[$v['trade_date']]:0;
        };

        $count = count($res);
        $this->assign([
            'res' => $res,
            'count' => $count,
            'day_arr'=>json_encode($day_arr),
            'max_volume'=>max($volume_arr),
            'volume'=>json_encode($volume_arr),
            'spot_volume'=>json_encode($spot_volume_arr),
            'basis_volume'=>json_encode($basis_volume_arr)
        ]);
        return $this->fetch();
    }

    //录入数据
    public function dataEntry()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['trade_date'] = strtotime($data['trade_date']);
            $data['delivery_date_fixed_price'] = strtotime($data['delivery_date_fixed_price']);
            $data['delivery_date_basis_start'] = strtotime($data['delivery_date_basis_start']);
            $data['delivery_date_basis_end'] = strtotime($data['delivery_date_basis_end']);

            $rst = $this->analysisDataModel->data($data)->save();
            if ($rst) {
                $this->success('添加成功', null, '', 1);
            } else {
                $this->error('添加失败');
            }
        } else {
            $areas = $this->analysisRefineryAreaModel->select();
            $this->assign([
                'areas' => $areas,
            ]);
            return $this->fetch();
        }
    }

    //修改交易数据
    public function editTrade()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['trade_date'] = strtotime($data['trade_date']);
            $data['delivery_date_fixed_price'] = strtotime($data['delivery_date_fixed_price']);
            $data['delivery_date_basis_start'] = strtotime($data['delivery_date_basis_start']);
            $data['delivery_date_basis_end'] = strtotime($data['delivery_date_basis_end']);

            $rst = $this->analysisDataModel->where('id', $data['id'])->update($data);
            if ($rst >= 0) {
                $this->success('修改成功', null, '', 1);
            } else {
                $this->error('修改失败', null, '', 1);
            }
        } else {
            if (input('get.id')) {
                $id = input('get.id');
            } else {
                echo '参数错误';
                return;
            }
            $sql = 'select data.id as id,trade_date,area,area_id,brand,name,refinery_id,trade_type,trade_mode,volume,trade_price,basis,contract,delivery_date_fixed_price,delivery_date_basis_start,delivery_date_basis_end from exam_analysis_data as data join exam_analysis_refinery as re join exam_analysis_refinery_area as area where data.refinery_id=re.id and re.area_id=area.id and data.id=' . $id . ' order by id DESC';

            $rst = $this->analysisDataModel->query($sql);
            if (count($rst) == 0) {
                echo '数据不存在';
                return;
            };
            $trade = $rst[0];
            $areas = $this->analysisRefineryAreaModel->select();
            $this->assign([
                'data' => $trade,
                'areas' => $areas,
                'id' => $id
            ]);

            return $this->fetch();
        }
    }

    //删除交易数据
    public function delTrade()
    {
        if (is_numeric(input('get.id'))) {
            $id = input('get.id');
        } else {
            echo '参数错误';
            return;
        }
        $rst = $this->analysisDataModel->destroy($id);
        if ($rst) {
            $this->success('删除成功', null, '', 1);
        } else {
            $this->error('删除失败', null, '', 1);
        }
    }

    //按地区分析
    public function analysisResultArea()
    {
        $sql_join = ($trade_date = input('get.date')) ? " and trade_date=$trade_date" : '';
        $sql = 'select data.id as id,trade_date,area,brand,name,trade_type,trade_mode,volume,trade_price,basis,contract,delivery_date_fixed_price,delivery_date_basis_start,delivery_date_basis_end from exam_analysis_data as data join exam_analysis_refinery as re join exam_analysis_refinery_area as area where data.refinery_id=re.id and re.area_id=area.id' . $sql_join . ' order by id';
        $rst = $this->analysisDataModel->query($sql);

        $count = count($rst);
        $areas_rst = $this->analysisRefineryAreaModel->select();
        $areas = collection($areas_rst)->toArray();

        $data = array();//当天各地区成交量（总量、现货、远月基差）以成交量排序；

        foreach ($areas as $k => $v) {
            $data[$k]['area'] = $v['area'];
            $data[$k]['amount_basis'] = 0;//当前地区基差成交量
            $data[$k]['amount_spot'] = 0;//当前地区现货成交量
            $data[$k]['amount'] = 0;//当前地区总成交量
            foreach ($rst as $i => $n) {
                if ($n['area'] == $v['area']) {
                    $data[$k]['amount'] += $n['volume'];
                }
                if ($n['area'] == $v['area'] && $n['trade_type'] == '现货') {
                    $data[$k]['amount_spot'] += $n['volume'];
                }
                if ($n['area'] == $v['area'] && $n['trade_mode'] == '基差' && $n['trade_type'] == '远月') {
                    $data[$k]['amount_basis'] += $n['volume'];
                }
            }
        }

        $temp = array_column($data, 'amount');
        array_multisort($temp, SORT_ASC, $data);

        $area_str = json_encode(array_column($data, 'area'));
        $volume_basis = json_encode(array_column($data, 'amount_basis'));
        $volume_spot = json_encode(array_column($data, 'amount_spot'));
        $volume_str = json_encode(array_column($data, 'amount'));

        $this->assign([
            'count' => $count,
            'areas' => $areas,
            'volume_str' => $volume_str,
            'volume_basis' => $volume_basis,
            'volume_spot' => $volume_spot,
            'area_str' => $area_str,
        ]);
        return $this->fetch();
    }

    //按品牌分析
    public function analysisResultBrand()
    {
        $sql_join = ($trade_date = input('get.date')) ? " and trade_date=$trade_date" : '';
        $sql = 'select data.id as id,trade_date,area,brand,name,trade_type,trade_mode,volume,trade_price,basis,contract,delivery_date_fixed_price,delivery_date_basis_start,delivery_date_basis_end from exam_analysis_data as data join exam_analysis_refinery as re join exam_analysis_refinery_area as area where data.refinery_id=re.id and re.area_id=area.id' . $sql_join . ' order by id';
        $rst = $this->analysisDataModel->query($sql);
        $sql_brand = 'select brand from exam_analysis_refinery group by brand';

        $brand_array = $this->analysisRefineryModel->query($sql_brand);//所有品牌
        $data = array();//当天各品牌成交量（总量、现货、远月基差）以成交量排序；

        foreach ($brand_array as $k => $v) {
            $data[$k]['brand'] = $v['brand'];
            $data[$k]['amount_basis'] = 0;
            $data[$k]['amount_spot'] = 0;
            $data[$k]['amount'] = 0;
            foreach ($rst as $i => $n) {
                if ($n['brand'] == $v['brand']) {
                    $data[$k]['amount'] += $n['volume'];
                }
                if ($n['brand'] == $v['brand'] && $n['trade_type'] == '现货') {
                    $data[$k]['amount_spot'] += $n['volume'];
                }
                if ($n['brand'] == $v['brand'] && $n['trade_mode'] == '基差' && $n['trade_type'] == '远月') {
                    $data[$k]['amount_basis'] += $n['volume'];
                }
            }
        }

        $temp = array_column($data, 'amount');
        array_multisort($temp, SORT_DESC, $data);
        $data = array_slice($data, 0, 10);

        $temp = array_column($data, 'amount');
        array_multisort($temp, SORT_ASC, $data);

        $brand = json_encode(array_column($data, 'brand'));
        $volume_basis = json_encode(array_column($data, 'amount_basis'));
        $volume_spot = json_encode(array_column($data, 'amount_spot'));
        $volume_str = json_encode(array_column($data, 'amount'));

        $this->assign([
            'volume_str' => $volume_str,
            'volume_basis' => $volume_basis,
            'volume_spot' => $volume_spot,
            'brand' => $brand,
        ]);
        return $this->fetch();
    }

    //按油厂性质分析
    public function analysisResultNature()
    {
        $sql_join = ($trade_date = input('get.date')) ? " and trade_date=$trade_date" : '';
        //$sql = 'select trade.id as id,trade_date,area,brand,name,trade_type,trade_mode,volume,trade_price,basis,contract,delivery_date_fixed_price,delivery_date_basis_start,delivery_date_basis_end from exam_analysis_data as trade join exam_analysis_refinery as re join exam_analysis_refinery_area as area where trade.refinery_id=re.id and re.area_id=area.id' . $sql_join . ' order by id';
        $sql = 'select nature,sum(volume) as sum_volume from exam_analysis_data as trade join exam_analysis_refinery as re where trade.refinery_id=re.id'.$sql_join.' group by nature';
        $rst = $this->analysisDataModel->query($sql);

        $nature = json_encode(array_column($rst, 'nature'));
        $volume = json_encode(array_column($rst, 'sum_volume'));

        $data = array();
        foreach ($rst as $k => $v) {
            $data[] = array('value' => $v['sum_volume'], 'name' => $v['nature']);
        }

        $this->assign([
            'nature' => $nature,
            'data' => json_encode($data)
        ]);
        return $this->fetch();
    }

    public function ajaxGetRefineryByAreaId()
    {
        $area_id = input('get.')['area_id'];
        $refinery = $this->analysisRefineryModel->where('area_id', $area_id)->select();
        echo json_encode($refinery);
    }

    public function ajaxGetTrade(){
        if(request()->isAjax()){
            $type=input('get.type');
            if($type=='day'){
                $sql = 'select DATE_FORMAT(FROM_UNIXTIME(trade_date), \'%Y-%m-%d\') AS time,sum(volume) as total_volume from exam_analysis_data group by time order by time ASC';
                $sql_spot='select DATE_FORMAT(FROM_UNIXTIME(trade_date), \'%Y-%m-%d\') AS time,sum(volume) as spot_volume from exam_analysis_data where trade_type=\'现货\' group by time order by time ASC';
                $sql_basis='select DATE_FORMAT(FROM_UNIXTIME(trade_date), \'%Y-%m-%d\') AS time,sum(volume) as basis_volume from exam_analysis_data where trade_type=\'远月\' and trade_mode=\'基差\' group by time order by time ASC';
            }else if($type=='month'){
                $sql = 'select DATE_FORMAT(FROM_UNIXTIME(trade_date), \'%Y-%m\') AS time,sum(volume) as total_volume from exam_analysis_data group by time order by time ASC';
                $sql_spot='select DATE_FORMAT(FROM_UNIXTIME(trade_date), \'%Y-%m\') AS time,sum(volume) as spot_volume from exam_analysis_data where trade_type=\'现货\' group by time order by time ASC';
                $sql_basis='select DATE_FORMAT(FROM_UNIXTIME(trade_date), \'%Y-%m\') AS time,sum(volume) as basis_volume from exam_analysis_data where trade_type=\'远月\' and trade_mode=\'基差\' group by time order by time ASC';
            }else{
                $this->result('',1);
            }

            $res = $this->analysisDataModel->query($sql);
            $spot_volume=$this->analysisDataModel->query($sql_spot);
            $basis_volume=$this->analysisDataModel->query($sql_basis);

            //p($res);
            //p($spot_volume);
            //p($basis_volume);

            $time_arr=[];
            $volume_arr=[];
            $spot_volume_arr=[];
            $basis_volume_arr=[];


            $spot_temp=[];
            foreach($spot_volume as $k=>$v){
                $spot_temp[$v['time']]=$v['spot_volume'];
            };

            $basis_temp=[];
            foreach($basis_volume as $k=>$v){
                $basis_temp[$v['time']]=$v['basis_volume'];
            };

            foreach($res as $k=>$v){
                $time_arr[]=$v['time'];
                $volume_arr[]=$v['total_volume'];
                $spot_volume_arr[]=array_key_exists($v['time'],$spot_temp)?$spot_temp[$v['time']]:0;
                $basis_volume_arr[]=array_key_exists($v['time'],$basis_temp)?$basis_temp[$v['time']]:0;
            };

            $data=array(
                'time_arr'=>$time_arr,
                'max_volume'=>max($volume_arr),
                'volume_arr'=>$volume_arr,
                'spot_volume_arr'=>$spot_volume_arr,
                'basis_volume_arr'=>$basis_volume_arr
            );
            $this->result($data,2);
        }else{
            $this->result('',0);
        }
    }

    //导入Excel数据
    public function excel()
    {
        die();
        vendor('PHPExcel');
        $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
        $inputFileName = ROOT_PATH . 'public' . DS . 'uploads' . DS . 'jc.xlsx';

        $phpexcel = $objReader->load($inputFileName);
        $sheetData = $phpexcel->getActiveSheet()->toArray(null, true, true, true);
        //p($sheetData);
        //exit;

        $data = array();
        foreach ($sheetData as $r) {

            if ($r['D'] != '' && $r['D'] != '均价/成交量' && $r['D'] != '油厂') {
                $data['area'] = $r['A'];
                $data['brand'] = $r['B'];
                $data['type'] = $r['C'];
                $data['name'] = $r['D'];
                //p($data);

                $rst = $this->analysisRefineryModel->isUpdate(false)->data($data)->save();

                if ($rst) {
                    echo $rst . "条数据添加成功！<br/>";
                    echo "第" . $this->analysisRefineryModel->id . "条！<br/>";
                } else {
                    echo "Failure! Retry!";
                }
            }
        }
    }

    public function temp()
    {
        die();
        $areas = $this->analysisRefineryAreaModel->select();
        foreach ($areas as $k => $v) {
            $area_id = $v['id'];
            $area_name = $v['area'];

            $rst = $this->analysisRefineryModel->where('area', $area_name)->update(['area' => $area_id]);
            p($rst . '-------');
        };
    }
}