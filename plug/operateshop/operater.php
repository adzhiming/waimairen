<?php
// if(!defined('IN_WaiMai')) {
// exit('Access Denied');
// }
ini_set ( 'date.timezone', 'Asia/Shanghai' );
error_reporting ( E_ERROR );
define ( 'hopedir', '../../' ); // 系统运行时目录
if (! function_exists ( "fastcgi_finish_request" )) {
	function fastcgi_finish_request() {
		@ob_flush ();
		flush ();
	}
}
fastcgi_finish_request ();
$config = hopedir . "config/hopeconfig.php";
$cfg = include ($config);
require_once hopedir . "lib/function.php";

$lnk = mysql_connect ( $cfg ['dbhost'], $cfg ['dbuser'], $cfg ['dbpw'] ) or die ( 'Not connected : ' . mysql_error () );
$version = mysql_get_server_info ();
if ($version > '4.1' && $cfg ['dbcharset']) {
	mysql_query ( "SET NAMES '" . $cfg ['dbcharset'] . "'" );
}
if ($version > '5.0') {
	mysql_query ( "SET sql_mode=''" );
}
if (! @mysql_select_db ( $cfg ['dbname'] )) {
	if (@mysql_error ()) {
		echo '数据库连接失败';
		exit ();
	} else {
		mysql_select_db ( $cfg ['dbname'] );
	}
}

$optype = trim ( $_REQUEST ['optype'] );
if ($optype == "getshopinfobyid") {
	$id = intval ( $_REQUEST ['id'] );
	if (empty ( $id )) {
		$arr = array (
				"return_code" => 0,
				"return_msg" => "参数id不能为空",
				"return_data" => "" 
		);
	}
	
	$res = mysql_query ( "select * from " . $cfg ['tablepre'] . "shop where sid='" . $id . "' limit 1 " );
	
	$shopinfo = mysql_fetch_assoc ( $res );
	if($shopinfo){
		$shopinfo = $shopinfo;
	}
	else{
		$shopinfo = array();
	}
	$arr = array (
			"return_code" => 1,
			"return_msg" => "获取成功",
			"return_data" => $shopinfo 
	);
	$arr2 = $arr["return_data"]; 
}
elseif ($optype == "openwaimai") {
	$arr = $_REQUEST ['data'];
	$arr2 = array();
	$param['mid'] = $arr['mid'];
	$param['username'] = $arr['username'];
	$param['password'] = $arr['password'];
	$param['logintime'] = $arr['logintime'];
	$param['time'] = $arr['time'];
	$param['limit'] = $arr['limit'];
	if (empty ( $param['mid'] )) {
		$arr2 = array (
				"return_code" => 0,
				"return_msg" => "参数mid不能为空",
				"return_data" => ""
		);
	}
	$f = mysql_query ( "select * from " . $cfg ['tablepre'] . "system_config  where mid ='{$param['mid']}' limit 1");
	$rows = mysql_num_rows($f);
	if($rows<1){
		$cnfres = mysql_query ( "insert into " . $cfg ['tablepre'] . "system_config (`mid`) values('{$param['mid']}') " ,$lnk);
	}
	$ad = mysql_query ( "select * from " . $cfg ['tablepre'] . "admin  where mid ='{$param['mid']}' limit 1");
	$ads = mysql_num_rows($ad);
	if($ads<1){
		$adminres = mysql_query ( "insert into " . $cfg ['tablepre'] . "admin
				(`mid`,`username`,`password`,`logintime`,`time`,`limit`)
				values('{$param['mid']}','{$param['username']}','{$param['password']}','{$param['logintime']}','{$param['time']}','{$param['limit']}') " ,$lnk);
	}
	if($cnfres && $adminres){
		$arr2 = array (
				"return_code" => 1,
				"return_msg" => "开通成功",
				"return_data" => ""
		);
	}
}
elseif ($optype == "openshop") {
	$member = $_REQUEST ['member'];
	$data = $_REQUEST ['data'];
	$arr2 = array();
	if (empty ( $member['mid'] )) {
		$arr2 = array (
				"return_code" => 0,
				"return_msg" => "参数mid不能为空",
				"return_data" => ""
		);
	}
	//判断该是否已经开通	
	mysql_query(" ALTER  TABLE  " . $cfg ['tablepre'] . "member  type  =  InnoDB");
	mysql_query("BEGIN");
	$fsql = mysql_query("select * from " . $cfg ['tablepre'] . "member where sid = '{$data['sid']}' limit 1");
	$mrs = mysql_fetch_assoc($fsql);
	if(empty($mrs['uid'])){
		$memberres = mysql_query ( "insert into " . $cfg ['tablepre'] . "member
				(`mid`,`sid`,`username`,`password`,`phone`,`address`,`creattime`,`logintime`,`usertype`)
				values('{$member['mid']}','{$data['sid']}','{$member['username']}','{$member['password']}','{$member['phone']}',
		        '{$member['address']}','{$member['creattime']}','{$member['logintime']}','1') " ,$lnk);
		$uid = mysql_insert_id();
	}
	else{
		$memberres= true;
		$uid = $mrs['uid'];
	}
	$fsql = mysql_query("select * from " . $cfg ['tablepre'] . "shop where sid = '{$data['sid']}' limit 1");
	$srs = mysql_fetch_assoc($fsql);
	if(empty($srs['sid'])){
		$addtime = $data['times'];
		$endtimes = strtotime("+{$addtime} day");
		$shopres = mysql_query ( "insert into " . $cfg ['tablepre'] . "shop
				(`mid`,`sid`,`uid`,`shopname`,`phone`,`address`,`lat`,`lng`,`starttime`,`endtime`
			     ,`is_open`,`is_daopay`,`is_onlinepay`,`is_pass`,`addtime`,`psservicepoint`,`psservicepointcount`,`goodattrdefault`
			     ,`shoplicense`,`qiyeimg`,`zmimg`,`fmimg`,`wxhui_ewmurl`)
				values('{$data['mid']}','{$data['sid']}','{$uid}','{$data['shopname']}',
		'{$data['phone']}','{$data['address']}','{$data['lat']}','{$data['lng']}','{$data['starttime']}'
		,'{$endtimes}','{$data['is_open']}','{$data['is_daopay']}','{$data['is_onlinepay']}','{$data['is_pass']}'
		,'{$data['addtime']}','{$data['psservicepoint']}','{$data['psservicepointcount']}','{$data['goodattrdefault']}','{$data['shoplicense']}'
		,'{$data['qiyeimg']}','{$data['zmimg']}','{$data['fmimg']}','{$data['wxhui_ewmurl']}') " ,$lnk);
	}
	else{
		if($srs['endtime']<time()){
			$addtime = $data['times'];
			$endtimes = strtotime("+{$addtime} day"); //如果过期，则从现在开始算
		}
		else
		{
			$a = (int)$data['times'];
			$datetime=strtotime(date("Y-m-d",$srs['endtime']));//获取当前日期并转换成时间戳
			$endtimes=strtotime("+{$a} days",$datetime);//如果还没过期，则从结束时间累加天(即60*60*24)
			
		}
		$sql ="update " . $cfg ['tablepre'] . "shop set `endtime` = '{$endtimes}' where sid = '{$data['sid']}'";
		mysql_query($sql);
		$shopres = true;
	}
   if($memberres && $shopres){
       mysql_query("COMMIT");
       $arr2 = array (
				"return_code" => 1,
				"return_msg" => "同步成功",
				"return_data" => ""
		);
   }
   else{
     mysql_query("ROLLBACK");
     $arr2 = array (
     		"return_code" => 0,
     		"return_msg" => "同步失败",
     		"return_data" => ""
     );
   }
   mysql_query("END");
}
elseif($optype =="operator_wm"){
	$sql = $_REQUEST ['sql'];
	$sql = str_replace("#pre#",$cfg ['tablepre'],$sql);
	$type = $_REQUEST ['type'];
	if($type == "select"){
	    $info = array();
		if(!empty($sql)){
			$res = mysql_query($sql);
			$info = mysql_fetch_assoc($res);
		}
		echo json_encode($info,true) ;die;
	}
	elseif($type == "insert"){
		$res = mysql_query($sql);
		
		if($res){
			$arr2 = array (
     		"return_code" => 1,
     		"return_msg" => "成功",
     		"return_data" => ""
           );
		}
		else{
			$arr2 = array (
					"return_code" => 0,
					"return_msg" => "失败",
					"return_data" => ""
			);
		}
	}
	elseif($type == "update"){
		$res = mysql_query($sql);
		if($res){
			return true;
		}
		return false;
	}
	else{
		return '';
	}
}
echo json_encode ( $arr2 );

?>
