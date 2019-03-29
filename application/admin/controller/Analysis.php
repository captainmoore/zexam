<?php

/**
 * Created by PhpStorm.
 * User: lipo
 * Date: 2019/2/14 0014
 * Time: 09:40
 */

namespace app\admin\controller;

use think\Controller;
use think\paginator\driver\Bootstrap;

class Analysis extends Controller
{
    private $candidatesModel;
    private $analysisDataModel;
    private $analysisRefineryModel;
    private $analysisRefineryAreaModel;

    public function _initialize() {
        parent::_initialize();
        $this->candidatesModel = model('Candidates');
        $this->analysisDataModel = model('analysisData');
        $this->analysisRefineryModel = model('analysisRefinery');
        $this->analysisRefineryAreaModel = model('analysisRefineryArea');
    }

    //成交数据列表
    public function dataList() {
        $sql = 'select data.id as id,trade_date,area,brand,name,trade_type,trade_mode,volume,trade_price,basis,contract,delivery_date_fixed_price,delivery_date_basis_start,delivery_date_basis_end from exam_analysis_data as data join exam_analysis_refinery as re join exam_analysis_refinery_area as area where data.refinery_id=re.id and re.area_id=area.id order by id DESC';

        $res = $this->analysisDataModel->query($sql);
        $count = $this->analysisDataModel->count();

        $this->assign([
            'res' => $res,
            'count' => $count,
        ]);
        return $this->fetch();
    }

    //成交数据列表(根据条件筛选)
    public function dataListFilter() {
        $current_page=input('get.page')?input('get.page'):0;
        if($current_page<=0)$current_page=1;

        $trade_date=input('get.trade_date')?input('get.trade_date'):0;
        $trade_date_end=input('get.trade_date_end')?input('get.trade_date_end'):0;
        $area=input('get.area')?input('get.area'):0;
        $refinery=input('get.refinery')?input('get.refinery'):0;
        $brand=input('get.brand')?input('get.brand'):0;
        $trade_type=input('get.trade_type')?input('get.trade_type'):0;
        $trade_mode=input('get.trade_mode')?input('get.trade_mode'):0;

        //筛选条件
        $sql_condition="";
        $vars=[];
        if($area!='0'){
            $sql_condition.=" and area='".$area."'";
        }
        if($refinery!='0'){
            $sql_condition.=" and name='".$refinery."'";
        }
        if($brand!='0'){
            $sql_condition.=" and brand='".$brand."'";
        }
        if($trade_type!='0'){
            $sql_condition.=" and trade_type='".$trade_type."'";
        }
        if($trade_mode!='0'){
            $sql_condition.=" and trade_mode='".$trade_mode."'";
        }
        if($trade_date!='0'&&$trade_date_end!='0'){
            $sql_condition.=" and trade_date>='".strtotime($trade_date)."' and trade_date<='".strtotime($trade_date_end)."'";
        }

        //成交量折线图start1111
        $sql = 'select trade.id as id,trade_date,sum(volume) as total_volume from exam_analysis_data as trade join exam_analysis_refinery as re join exam_analysis_refinery_area as area where trade.refinery_id=re.id and re.area_id=area.id '.$sql_condition.' group by trade.trade_date order by trade_date ASC';
        $res = $this->analysisDataModel->query($sql);

        //现货
        $sql_spot = 'select trade_date,sum(volume) as spot_volume,area from exam_analysis_data as trade join exam_analysis_refinery as re join exam_analysis_refinery_area as area where trade.refinery_id=re.id and trade.trade_type=\'现货\' and re.area_id=area.id '.$sql_condition.' group by trade.trade_date order by trade_date ASC';
        $spot_volume = $this->analysisDataModel->query($sql_spot);

        //远月
        $sql_basis = 'select trade_date,sum(volume) as basis_volume from exam_analysis_data as trade join exam_analysis_refinery as re join exam_analysis_refinery_area as area where trade.refinery_id=re.id and trade.trade_type=\'远月\' and re.area_id=area.id '.$sql_condition.' group by trade.trade_date order by trade_date ASC';
        $basis_volume = $this->analysisDataModel->query($sql_basis);

        $day_arr = [];
        $volume_arr = [];
        $spot_volume_arr = [];
        $basis_volume_arr = [];

        $spot_temp = [];
        foreach ($spot_volume as $k => $v) {
            $spot_temp[$v['trade_date']] = $v['spot_volume'];
        };
        $basis_temp = [];
        foreach ($basis_volume as $k => $v) {
            $basis_temp[$v['trade_date']] = $v['basis_volume'];
        };

        foreach ($res as $k => $v) {
            $day_arr[] = date('Y-m-d', $v['trade_date']);
            $volume_arr[] = $v['total_volume'];

            $spot_volume_arr[] = array_key_exists($v['trade_date'], $spot_temp) ? $spot_temp[$v['trade_date']] : 0;
            $basis_volume_arr[] = array_key_exists($v['trade_date'], $basis_temp) ? $basis_temp[$v['trade_date']] : 0;
        };
        if(sizeof($volume_arr)==0)$volume_arr=[0];//$volume_arr不能为空数组

        $count = count($res);
        $this->assign([
            'res' => $res,
            'count' => $count,
            'day_arr' => json_encode($day_arr),
            'max_volume' => max($volume_arr),
            'volume' => json_encode($volume_arr),
            'spot_volume' => json_encode($spot_volume_arr),
            'basis_volume' => json_encode($basis_volume_arr)
        ]);
        //成交量折线图end

        $vars= array(
            'trade_date'=>$trade_date,
            'trade_date_end'=>$trade_date_end,
            'area'=>$area,
            'refinery'=>$refinery,
            'brand'=>$brand,
            'trade_type'=>$trade_type,
            'trade_mode'=>$trade_mode,
        );

        $sql = "select data.id as id,trade_date,area,brand,name,data.refinery_id as refinery_id,trade_type,trade_mode,volume,trade_price,basis,contract,delivery_date_fixed_price,delivery_date_basis_start,delivery_date_basis_end from exam_analysis_data as data join exam_analysis_refinery as re join exam_analysis_refinery_area as area where data.refinery_id=re.id and re.area_id=area.id ".$sql_condition." order by trade_date DESC,id DESC limit ".(($current_page-1)*10).",10";
        $sql2 = "select count(*) as count_num from exam_analysis_data as data join exam_analysis_refinery as re join exam_analysis_refinery_area as area where data.refinery_id=re.id and re.area_id=area.id ".$sql_condition;

        $res = $this->analysisDataModel->query($sql);
        $count = $this->analysisDataModel->query($sql2);
        $count=$count[0]['count_num'];
        $options = [
            'var_page' => 'page',
            'path'     => '',
            'query'=>$vars,
            'fragment' => '',
        ];
        $page=Bootstrap::make($res,10,$current_page,$count,false,$options);

        //获取所有品牌
        $sql_brand='select brand from exam_analysis_refinery group by brand order by brand asc';
        $brands_temp=$this->analysisRefineryModel->query($sql_brand);
        $brands=[];
        foreach ($brands_temp as $v){
            $brands[]=$v['brand'];
        }

        $areas = $this->analysisRefineryAreaModel->select();
        $this->assign([
            'res' => $res,
            'page'=>$page,
            'count' => $count,
            'brands'=>$brands,
            'areas'=>$areas,
            'vars'=>$vars
        ]);
        return $this->fetch();
    }

    public function dataListByDay() {
        $sql = 'select trade.id as id,trade_date,sum(volume) as total_volume from exam_analysis_data as trade join exam_analysis_refinery as re join exam_analysis_refinery_area as area where trade.refinery_id=re.id and re.area_id=area.id group by trade.trade_date order by trade_date ASC';
        $res = $this->analysisDataModel->query($sql);

        $sql_spot = 'select trade_date,sum(volume) as spot_volume from exam_analysis_data as trade join exam_analysis_refinery as re where trade.refinery_id=re.id and trade.trade_type=\'现货\' group by trade.trade_date order by trade_date ASC';
        $spot_volume = $this->analysisDataModel->query($sql_spot);

        $sql_basis = 'select trade_date,sum(volume) as basis_volume from exam_analysis_data as trade join exam_analysis_refinery as re where trade.refinery_id=re.id and trade.trade_type=\'远月\' and trade.trade_mode=\'基差\' group by trade.trade_date order by trade_date ASC';
        $basis_volume = $this->analysisDataModel->query($sql_basis);

        $day_arr = [];
        $volume_arr = [];
        $spot_volume_arr = [];
        $basis_volume_arr = [];


        $spot_temp = [];
        foreach ($spot_volume as $k => $v) {
            $spot_temp[$v['trade_date']] = $v['spot_volume'];
        };
        $basis_temp = [];
        foreach ($basis_volume as $k => $v) {
            $basis_temp[$v['trade_date']] = $v['basis_volume'];
        };

        foreach ($res as $k => $v) {
            $day_arr[] = date('Y-m-d', $v['trade_date']);
            $volume_arr[] = $v['total_volume'];

            $spot_volume_arr[] = array_key_exists($v['trade_date'], $spot_temp) ? $spot_temp[$v['trade_date']] : 0;
            $basis_volume_arr[] = array_key_exists($v['trade_date'], $basis_temp) ? $basis_temp[$v['trade_date']] : 0;
        };

        $count = count($res);
        $this->assign([
            'res' => $res,
            'count' => $count,
            'day_arr' => json_encode($day_arr),
            'max_volume' => max($volume_arr),
            'volume' => json_encode($volume_arr),
            'spot_volume' => json_encode($spot_volume_arr),
            'basis_volume' => json_encode($basis_volume_arr)
        ]);
        return $this->fetch();
    }

    //录入数据
    public function dataEntry() {
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
    public function editTrade() {
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
    public function delTrade() {
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
    public function analysisResultArea() {
        $sql_join = ($trade_date = input('get.date')) ? " and trade_date=$trade_date" : '';
        $sql = 'select data.id as id,trade_date,area,brand,name,trade_type,trade_mode,volume,trade_price,basis,contract,delivery_date_fixed_price,delivery_date_basis_start,delivery_date_basis_end from exam_analysis_data as data join exam_analysis_refinery as re join exam_analysis_refinery_area as area where data.refinery_id=re.id and re.area_id=area.id' . $sql_join . ' order by id';
        $rst = $this->analysisDataModel->query($sql);

        $count = count($rst);
        $areas_rst = $this->analysisRefineryAreaModel->select();
        $areas = collection($areas_rst)->toArray();

        $data = array();//当天各地区成交量（总量、现货、远月基差）以成交量排序；20190326修改远月基差为远月

        foreach ($areas as $k => $v) {
            $data[$k]['area'] = $v['area'];
            $data[$k]['amount_actuals'] = 0;//当前地区现货成交量
            $data[$k]['amount_actuals_fixed_price'] = 0;//当前地区现货成交量
            $data[$k]['amount_actuals_basis'] = 0;//当前地区现货成交量
            $data[$k]['amount_futures'] = 0;//当前地区远月成交量
            $data[$k]['amount_futures_fixed_price'] = 0;//当前地区远月一口价成交量
            $data[$k]['amount_futures_basis'] = 0;//当前地区远月基差成交量
            $data[$k]['amount_futures_transfer'] = 0;//当前地区远月移库成交量
            $data[$k]['amount'] = 0;//当前地区总成交量
            foreach ($rst as $i => $n) {
                if ($n['area'] == $v['area']) {
                    $data[$k]['amount'] += $n['volume'];
                }
                if ($n['area'] == $v['area'] && $n['trade_type'] == '现货') {
                    $data[$k]['amount_actuals'] += $n['volume'];
                }
                if ($n['area'] == $v['area'] && $n['trade_type'] == '现货'&&$n['trade_mode']=='一口价') {
                    $data[$k]['amount_actuals_fixed_price'] += $n['volume'];
                }
                if ($n['area'] == $v['area'] && $n['trade_type'] == '现货'&&$n['trade_mode']=='基差') {
                    $data[$k]['amount_actuals_basis'] += $n['volume'];
                }
                if ($n['area'] == $v['area'] && $n['trade_type'] == '远月') {
                    $data[$k]['amount_futures'] += $n['volume'];
                }
                if ($n['area'] == $v['area'] && $n['trade_type'] == '远月'&&$n['trade_mode']=='一口价') {
                    $data[$k]['amount_futures_fixed_price'] += $n['volume'];
                }
                if ($n['area'] == $v['area'] && $n['trade_type'] == '远月'&&$n['trade_mode']=='基差') {
                    $data[$k]['amount_futures_basis'] += $n['volume'];
                }
            }
        }

        //p($data);

        $temp = array_column($data, 'amount');
        array_multisort($temp, SORT_ASC, $data);

        $area_str = json_encode(array_column($data, 'area'));
        $volume_actuals = json_encode(array_column($data, 'amount_actuals'));
        $volume_actuals_fixed_price = json_encode(array_column($data, 'amount_actuals_fixed_price'));
        $volume_actuals_basis = json_encode(array_column($data, 'amount_actuals_basis'));
        $volume_futures = json_encode(array_column($data, 'amount_futures'));
        $volume_futures_fixed_price = json_encode(array_column($data, 'amount_futures_fixed_price'));
        $volume_futures_basis = json_encode(array_column($data, 'amount_futures_basis'));
        $volume_str = json_encode(array_column($data, 'amount'));

        $this->assign([
            'count' => $count,
            'areas' => $areas,
            'volume_str' => $volume_str,
            'volume_actuals' => $volume_actuals,
            'volume_actuals_fixed_price' => $volume_actuals_fixed_price,
            'volume_actuals_basis' => $volume_actuals_basis,
            'volume_futures' => $volume_futures,
            'volume_futures_fixed_price' => $volume_futures_fixed_price,
            'volume_futures_basis' => $volume_futures_basis,
            'area_str' => $area_str,
        ]);
        return $this->fetch();
    }

    /**
     * 按品牌分析
     */
    public function analysisResultBrand() {
        $sql_join = ($trade_date = input('get.date')) ? " and trade_date=$trade_date" : '';
        $sql = 'select data.id as id,trade_date,area,brand,name,trade_type,trade_mode,volume,trade_price,basis,contract,delivery_date_fixed_price,delivery_date_basis_start,delivery_date_basis_end from exam_analysis_data as data join exam_analysis_refinery as re join exam_analysis_refinery_area as area where data.refinery_id=re.id and re.area_id=area.id' . $sql_join . ' order by id';
        $rst = $this->analysisDataModel->query($sql);
        $sql_brand = 'select brand from exam_analysis_refinery group by brand';

        $brand_array = $this->analysisRefineryModel->query($sql_brand);//所有品牌
        $data = array();//当天各品牌成交量（总量、现货、远月基差）以成交量排序；20190326修改远月基差为远月

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
                if ($n['brand'] == $v['brand'] && $n['trade_type'] == '远月') {
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

    /**
     * 按油厂性质分析
     */
    public function analysisResultNature() {
        $sql_join = ($trade_date = input('get.date')) ? " and trade_date=$trade_date" : '';
        $sql = 'select nature,sum(volume) as sum_volume from exam_analysis_data as trade join exam_analysis_refinery as re where trade.refinery_id=re.id' . $sql_join . ' group by nature';
        $rst = $this->analysisDataModel->query($sql);

        $nature = json_encode(array_column($rst, 'nature'));
        $volume = json_encode(array_column($rst, 'sum_volume'));

        $data = array();
        //$nature=array();
        foreach ($rst as $k => $v) {
            $data[] = array('value' => $v['sum_volume'], 'name' => $v['nature']);
            //$nature[]=$v['nature'].'('.$v['sum_volume'].')';
        }

        //p($nature);
        //p($data);


        $this->assign([
            'nature' => $nature,
            'data' => json_encode($data)
        ]);
        return $this->fetch();
    }

    public function ajaxGetRefineryByAreaId() {
        $area_id = input('get.')['area_id'];
        $refinery = $this->analysisRefineryModel->where('area_id', $area_id)->select();
        echo json_encode($refinery);
    }

    public function ajaxGetTrade() {
        if (request()->isAjax()) {
            $type = input('get.type');
            if ($type == 'day') {
                $sql = 'select DATE_FORMAT(FROM_UNIXTIME(trade_date), \'%Y-%m-%d\') AS time,sum(volume) as total_volume from exam_analysis_data group by time order by time ASC';
                $sql_spot = 'select DATE_FORMAT(FROM_UNIXTIME(trade_date), \'%Y-%m-%d\') AS time,sum(volume) as spot_volume from exam_analysis_data where trade_type=\'现货\' group by time order by time ASC';
                $sql_basis = 'select DATE_FORMAT(FROM_UNIXTIME(trade_date), \'%Y-%m-%d\') AS time,sum(volume) as basis_volume from exam_analysis_data where trade_type=\'远月\' and trade_mode=\'基差\' group by time order by time ASC';
            } else if ($type == 'month') {
                $sql = 'select DATE_FORMAT(FROM_UNIXTIME(trade_date), \'%Y-%m\') AS time,sum(volume) as total_volume from exam_analysis_data group by time order by time ASC';
                $sql_spot = 'select DATE_FORMAT(FROM_UNIXTIME(trade_date), \'%Y-%m\') AS time,sum(volume) as spot_volume from exam_analysis_data where trade_type=\'现货\' group by time order by time ASC';
                $sql_basis = 'select DATE_FORMAT(FROM_UNIXTIME(trade_date), \'%Y-%m\') AS time,sum(volume) as basis_volume from exam_analysis_data where trade_type=\'远月\' and trade_mode=\'基差\' group by time order by time ASC';
            } else {
                $this->result('', 1);
            }

            $res = $this->analysisDataModel->query($sql);
            $spot_volume = $this->analysisDataModel->query($sql_spot);
            $basis_volume = $this->analysisDataModel->query($sql_basis);

            //p($res);
            //p($spot_volume);
            //p($basis_volume);

            $time_arr = [];
            $volume_arr = [];
            $spot_volume_arr = [];
            $basis_volume_arr = [];


            $spot_temp = [];
            foreach ($spot_volume as $k => $v) {
                $spot_temp[$v['time']] = $v['spot_volume'];
            };

            $basis_temp = [];
            foreach ($basis_volume as $k => $v) {
                $basis_temp[$v['time']] = $v['basis_volume'];
            };

            foreach ($res as $k => $v) {
                $time_arr[] = $v['time'];
                $volume_arr[] = $v['total_volume'];
                $spot_volume_arr[] = array_key_exists($v['time'], $spot_temp) ? $spot_temp[$v['time']] : 0;
                $basis_volume_arr[] = array_key_exists($v['time'], $basis_temp) ? $basis_temp[$v['time']] : 0;
            };

            $data = array(
                'time_arr' => $time_arr,
                'max_volume' => max($volume_arr),
                'volume_arr' => $volume_arr,
                'spot_volume_arr' => $spot_volume_arr,
                'basis_volume_arr' => $basis_volume_arr
            );
            $this->result($data, 2);
        } else {
            $this->result('', 0);
        }
    }

    //导入Excel数据
    public function excel() {
        $area_rst = $this->analysisRefineryModel->select();
        $area = collection($area_rst)->toArray();
        $area_use = array();
        foreach ($area as $k => $v) {
            $area_use[$v['name']] = $v['id'];
        };
        //die();
        vendor('PHPExcel');
        $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
        //$inputFileName = ROOT_PATH . 'public' . DS . 'uploads' . DS . 'jc.xlsx';
        $inputFileName = ROOT_PATH . 'public' . DS . 'uploads' . DS . 'jc2.xlsx';

        $phpexcel = $objReader->load($inputFileName);
        $sheetData = $phpexcel->getActiveSheet()->toArray(null, true, true, true);
        //p($sheetData);
        //die();
        $data = array();
        /*foreach ($sheetData as $r) {
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
        }*/

        $sheet = array_slice($sheetData, 2, 122);
        //p($sheet);
        $raw = array();
        foreach ($sheet as $r) {
            if ($r['A']) {
                $raw[] = $r;
            }
        }
        $sheet = $raw;
        $t_arr = [];
        foreach ($sheet as $r) {
            $t = [];
            foreach ($r as $k => $v) {
                $t[] = $v;
            }
            $t_arr[] = $t;
        }
        $sheet = $t_arr;

        foreach ($sheet as $k => $v) {
            $refinery_id = $area_use[trim($v['3'])];
            for ($i = 0; $i < 60; $i++) {
                $trade = array();
                if ($i > 6 && $i < 12) {
                    $n = 2 * ($i - 5);
                } elseif ($i == 14) {
                    $n = 14;
                } elseif ($i == 15) {
                    $n = 16;
                } elseif ($i > 20 && $i < 26) {
                    $n = 2 * ($i - 21) + 18;
                } elseif ($i == 28) {
                    $n = 28;
                } elseif ($i == 29) {
                    $n = 30;
                } elseif ($i == 31) {
                    $n = 32;
                } elseif ($i > 50 && $i < 56) {
                    $n = 2 * ($i - 51) + 34;
                } elseif ($i == 58) {
                    $n = 44;
                } else {
                    $n = 1000;
                }

                //判断当前交易量，如果为0或者空，则表示没有交易，跳出本次循环
                if (array_key_exists($n + 1, $v) && $v[$n + 1] && $v[$n + 1] > 0) {
                    if ($i < 50) {
                        $trade['trade_date'] = strtotime('2019-01-' . $i);
                        //$trade['trade_date'] = '2019-01-' . $i;
                    } else {
                        $trade['trade_date'] = strtotime('2019-02-' . ($i - 40));
                        //$trade['trade_date'] = '2019-02-' . ($i - 40);
                    }
                    $trade['refinery_id'] = $refinery_id;
                    $trade['volume'] = trim($v[$n + 1]);
                    $trade['trade_type'] = '现货';
                    $price = trim($v[$n]);

                    //有的报价为2700/20形式
                    if(strpos($price,'/')){
                        $arr=explode('/',$price);
                        $price=$arr[0];
                    }

                    if (strpos($price, '+')) {
                        $arr = explode('+', $price);
                        $trade['basis'] = $arr[1];
                        switch ($arr[0]) {
                            case '05':
                                $trade['contract'] = 'M1905';
                                break;
                            case '09':
                                $trade['contract'] = 'M1909';
                                break;
                            default:
                                $trade['contract'] = 'M2001';
                        }

                        $trade['trade_mode'] = '基差';
                        $trade['trade_price'] = 0;
                        $trade['delivery_date_fixed_price'] = 0;
                        $trade['delivery_date_basis_start'] = $trade['trade_date'];
                        $trade['delivery_date_basis_end'] = $trade['trade_date'];
                    } else {
                        $trade['trade_mode'] = '一口价';
                        $trade['trade_price'] = $price;
                        $trade['basis'] = 0;
                        $trade['delivery_date_fixed_price'] = $trade['trade_date'];
                        $trade['delivery_date_basis_start'] = 0;
                        $trade['delivery_date_basis_end'] = 0;
                    }
                    $data[] = $trade;
                } else {
                    continue;
                }
            }
        }
        //p($data);
        //die();

        $rst=$this->analysisDataModel->saveAll($data,false);
        p($rst);

    }

    public function temp() {
        die();
        $areas = $this->analysisRefineryAreaModel->select();
        foreach ($areas as $k => $v) {
            $area_id = $v['id'];
            $area_name = $v['area'];

            $rst = $this->analysisRefineryModel->where('area', $area_name)->update(['area' => $area_id]);
            p($rst . '-------');
        };
    }

    public function test() {
        $str = '05+140';
        $r = explode('+', $str);
        $r = strpos('2400', '+');
        dump($r);
    }
}