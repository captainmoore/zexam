<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
define('__JS__','http://zdy.com/static/js/');

/**
 * 日期时间控件
 ** @param $name 控件name，id
 * @param $value 选中值
 * @param $isdatetime 是否显示时间
 * @param $loadjs 是否重复加载js，防止页面程序加载不规则导致的控件无法显示
 * @param $showweek 是否显示周，使用，true | false
 */
function date_widget($name, $value = '', $isdatetime = 0, $loadjs = 0, $showweek = 'true', $timesystem = 1) {
    if ($value == '0000-00-00 00:00:00') $value = '';
    $id = preg_match("/\[(.*)\]/", $name, $m) ? $m[1] : $name;
    if ($isdatetime) {
        $size = 21;
        $format = '%Y-%m-%d %H:%M:%S';
        if ($timesystem) {
            $showsTime = 'true';
        } else {
            $showsTime = '12';
        }
    } else {
        $size = 10;
        $format = '%Y-%m-%d';
        $showsTime = 'false';
    }
    $str = '';
    if ($loadjs || !defined('CALENDAR_INIT')) {
        define('CALENDAR_INIT', 1);
        $str .= '<link rel="stylesheet" type="text/css" href="' . __JS__ . 'calendar/jscal2.css"/>
			<link rel="stylesheet" type="text/css" href="' . __JS__ . 'calendar/border-radius.css"/>
			<link rel="stylesheet" type="text/css" href="' . __JS__ . 'calendar/win2k.css"/>
			<script type="text/javascript" src="' . __JS__ . 'calendar/calendar.js"></script>
			<script type="text/javascript" src="' . __JS__ . 'calendar/lang/en.js"></script>';
    }
    $str .= '<input type="text"  name="' . $name . '" id="' . $id . '" value="' . $value . '" size="' . $size . '" class="date input-text" readonly>';
    $str .= '<script type="text/javascript">
			Calendar.setup({
			weekNumbers: ' . $showweek . ',
		    inputField : "' . $id . '",
		    trigger    : "' . $id . '",
		    dateFormat: "' . $format . '",
		    showTime: ' . $showsTime . ',
		    minuteStep: 1,
		    onSelect   : function() {this.hide();}
			});
        </script>';
    return $str;
}

function p($data){
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}