<?php
class smallappbase {
	const RET_CODE_OK = 1;
	const RET_CODE_ERR_UNKNOWN = - 1;
	const RET_CODE_ERR_ARGUMENT = - 2;
	const RET_CODE_ERR_GETDATA = - 3;
	const RET_CODE_ERR_OPDATA = - 4;
	const RET_CODE_ERR_EXEPTION = - 5;
	const RET_CODE_ERR_PRIVATE = - 6;
	const RET_CODE_ERR_WXCONFIG = 1001;
	protected $arrErrorMsg = array (
			'1' => "操作成功",
			'-1' => '未知错误',
			'-2' => '参数不合格',
			'-3' => '获取数据失败',
			'-4' => '数据操作失败',
			'-5' => '数据操作异常',
			'-6' => '没有权限',
			'1001' => '获取微信配置失败' 
	);
	public $mysql;
	// public $memberCls;
	// public $member;
	// public $pageCls;
	public $admin;
	public $digui;
	protected $mid;
	protected $appid = '';
	protected function ajaxReturn($data, $type = '', $json_option = JSON_UNESCAPED_UNICODE) {
		if (empty ( $type ))
			$type = 'JSON';
		switch (strtoupper ( $type )) {
			case 'JSON' :
				// 返回JSON数据格式到客户端 包含状态信息
				header ( 'Content-Type:application/json; charset=utf-8' );
				exit ( json_encode ( $data, $json_option ) );
			// case 'XML' :
			// // 返回xml格式数据
			// header('Content-Type:text/xml; charset=utf-8');
			// exit(xml_encode($data));
			// case 'JSONP':
			// // 返回JSON数据格式到客户端 包含状态信息
			// header('Content-Type:application/json; charset=utf-8');
			// $handler = isset($_GET[C('VAR_JSONP_HANDLER')]) ? $_GET[C('VAR_JSONP_HANDLER')] : C('DEFAULT_JSONP_HANDLER');
			// exit($handler.'('.json_encode($data,$json_option).');');
			case 'EVAL' :
				// 返回可执行的js脚本
				header ( 'Content-Type:text/html; charset=utf-8' );
				exit ( $data );
			default :
				// 用于扩展其他返回格式数据
				exit ();
		}
	}
	public function return_json($retCode, $retMsg, $data = "") {
		if ($retMsg === '') {
			if (isset ( $this->arrErrorMsg ['' . $retCode] )) {
				$retMsg = $this->arrErrorMsg ['' . $retCode];
			}
		}
		
		$ajaxResult = array (
				"code" => $retCode,
				"msg" => $retMsg 
		);
		if (! empty ( $data )) {
			$ajaxResult ["data"] = $data;
		}
		
		$this->ajaxReturn ( $ajaxResult, "JSON" );
		die ();
	}
	public function trimEmptyStr($val) {
		$cleanVal = '';
		if (is_array ( $val )) {
			$cleanVal = array_map ( 'trim', $val );
			$cleanVal = array_map ( 'htmlspecialchars', $cleanVal );
		} else if (is_string ( $val )) {
			$cleanVal = trim ( $val );
			$cleanVal = htmlspecialchars ( $cleanVal );
		} else {
			$cleanVal = $val;
		}
		return $cleanVal;
	}
	protected function getParam($name, $value_while_empty = "") {
		$value = null;
		// if (isset( $this->params[$name])) {
		// $value = $this->trimEmptyStr($this->params[$name]);
		// }
		
		if ($value === null || $value === "") {
			if (isset ( $_GET [$name] )) {
				$value = $this->trimEmptyStr ( $_GET [$name] );
			}
		}
		
		if ($value === null || $value === "") {
			if (isset ( $_POST [$name] )) {
				$value = $this->trimEmptyStr ( $_POST [$name] );
			}
		}
		
		if ($value === null || $value === "") {
			$value = $value_while_empty;
		}
		return $value;
	}
	protected function checkMid() {
		if (empty ( $this->mid )) {
			$this->return_json ( self::RET_CODE_ERR_ARGUMENT, '' );
		}
		return $this->mid;
	}
	
	
	/////////////////////////////////////////////////////////////////////////
	function init() {
		// 主要是检测权限
		$mid = Mysite::$app->getMid ();
		$this->mid = $mid;
		$this->siteurl = 'https://eat.banklay.cn'; //https返回前台路径
	 
		$controller = Mysite::$app->getController ();
		$this->mysql = new mysql_class ();
		// $this->memberCls = new memberclass($this->mysql);
		// $this->member = $this->memberCls->getinfo($this->mid, $this->appid);
		// $this->pageCls = new page();
	}
	function getmember($mid,$openid){
		$member   = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."wxuser where mid='".$mid."' and openid='".$openid."'");
	 	$userinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where uid='".$member['uid']."' " ); 
	 	return $userinfo; 
	}

	function pscost($shopinfo,$goodsnum=0,$tareaid=0){
  		$backdata = array('pscost'=>0,'pstype'=>0,'canps'=>0);
  		$locationtype = Mysite::$app->config['locationtype'];
  		if($locationtype == 1){
	  	  //地图模式
	  	  $valuelist = empty($shopinfo['pradiusvalue'])? unserialize(Mysite::$app->config['radiusvalue']):unserialize($shopinfo['pradiusvalue']);
	  	   $lat = ICookie::get('lat');  
	  	  	if(!empty($lat)){ 
	  	       	  $lng = ICookie::get('lng');   
	  	       	  $lat = empty($lat)?0:$lat;
	  	       	  $lng = empty($lng)?0:$lng;
	  	       	  $shoplat = isset($shopinfo['lat'])?$shopinfo['lat']:0;
	  	       	  $shoplng = isset($shopinfo['lng'])?$shopinfo['lng']:0;
	  	       	  $juli =  $this->GetDistance($lat,$lng, $shoplat,$shoplng, 1); 
	  	       	  $pradiusvalue =  unserialize($shopinfo['pradiusvalue']);
	  	       	  $juliceshi = intval($juli/1000);
	  	       	  if(is_array($valuelist)){
	  	       	  foreach($valuelist as $key=>$value){
	  	       	    if($juliceshi == $key){
	  	       	        $backdata['pscost'] = $value;
	  	       	        $backdata['canps'] = 1;
	  	       	    }
	  	       	  }
	  	       	} 
	  	  	}
  	  
  		}else{
	  	  //区域模式
	  	  //pradiusvalue	pscost
	  	  $backdata['pscost'] = $shopinfo['pscost'];
	  	  $areaid = ICookie::get('myaddress');
		  $areaid = $tareaid==0?$areaid:$tareaid;
	  	  if(!empty($areaid)){  
	  	  	$checkareainfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."areashop where areaid = ".$areaid." and shopid = ".$shopinfo['id'].""); 
	  	  	if(!empty($checkareainfo)) $backdata['canps'] = 1;
	  	  }
  		}
  		$backdata['pstype'] = $shopinfo['sendtype']; 
  		return $backdata; 
  	}

  	 function  getOpenPosttime($is_before,$starttime,$postdate,$minit,$befortime=0){ 
	    $backarray = array('is_opentime'=>0,'is_posttime'=>'','is_postdate'=>'','cost'=>0);
		$maxnowdaytime = strtotime(date('Y-m-d',time()));
		$daynottime = 24*60*60 -1; 
		$findpostime = false; 
		$posttime = time();
  		$miniday = $maxnowdaytime;
  		$maxday = $miniday+$daynottime; 
  	 
  		 
  	    $findps = false;
		$timelist = !empty($postdate)?unserialize($postdate):array();
		$data['pstimelist'] = array();
		$checknow = time();
		 $whilestatic = $befortime;
		$nowwhiltcheck = 0; 
		while($whilestatic >= $nowwhiltcheck){ 
		
		 
		    $nowstartcheck = $nowwhiltcheck*86400;
			foreach($timelist as $key=>$value){
				$docheck = $nowstartcheck+$value['s'];  
				if($docheck== $minit){
					$findps = true;
					$tempt['s'] = date('H:i',$miniday+$value['s']);
					$tempt['e'] = date('H:i',$miniday+$value['e']);
					$backarray['is_posttime'] = $nowstartcheck+$miniday+$value['s'];
					$backarray['is_postdate'] =  $tempt['s'] .'-'.$tempt['e'];
					$checkdotime = $nowstartcheck+$miniday+$value['e'];
					$backarray['cost'] = isset($value['cost'])?$value['cost']:0;
					if($checkdotime < $posttime){
						$backarray['is_opentime'] = 3; 
					}
					break;
				} 
			}
			$nowwhiltcheck = $nowwhiltcheck+1;
		}
		if($findps ==  false){
			$backarray['is_opentime'] = 2; 
		}
		 
		 return $backarray;
		 
		 
	}

	function GetDistance($lat1, $lng1, $lat2, $lng2, $len_type = 1, $decimal = 2) {
		$earth = 6378.137;
		$pi = 3.1415926;
		$radLat1 = $lat1 * PI () / 180.0; // PI()圆周率
		$radLat2 = $lat2 * PI () / 180.0;
		$a = $radLat1 - $radLat2;
		$b = ($lng1 * PI () / 180.0) - ($lng2 * PI () / 180.0);
		$s = 2 * asin ( sqrt ( pow ( sin ( $a / 2 ), 2 ) + cos ( $radLat1 ) * cos ( $radLat2 ) * pow ( sin ( $b / 2 ), 2 ) ) );
		$s = $s * EARTH_RADIUS;
		$s = round ( $s * 1000 );
		if ($len_type > 1) {
			$s /= 1000;
		}
		
		return round ( $s, $decimal );
	}
}
?>