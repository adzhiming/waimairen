<?php
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);
define('hopedir',   '../../../');   // 系统运行时目录
if (!function_exists("fastcgi_finish_request")) {
	function fastcgi_finish_request() {
		@ob_flush(); flush();
	}
}
set_time_limit(0);
ignore_user_abort(true);
fastcgi_finish_request();

require_once 'log.php';
require_once hopedir."./lib/function.php";
//初始化日志
$logHandler= new CLogFileHandler(hopedir."/log/wxpay".date('Y-m-d').".log");
$log = Log::Init($logHandler, 15);

class PayNotifynewCallBack {
	//查询订单
	private function handleSub($cfg, $result){			 
			 $lnk = mysql_connect($cfg['dbhost'], $cfg['dbuser'], $cfg['dbpw']) or die ('Not connected : ' . mysql_error()); 
  			 $version = mysql_get_server_info(); 
  			 if($version > '4.1' && $cfg['dbcharset']) {
						  	mysql_query("SET NAMES '".$cfg['dbcharset']."'");
  			 } 
  			 if($version > '5.0') {
				     mysql_query("SET sql_mode=''");
  			 } 
 		    if(!@mysql_select_db($cfg['dbname'])){ 
				 	if(@mysql_error()) {
								echo '数据库连接失败';exit;
					 } else {
								mysql_select_db($cfg['dbname']);
	     			 }
  			 }
			 
  			 $attach=$result['attach'];
  			 $arrAttach = explode(";", $attach);
  			 if ($arrAttach&&$arrAttach[0]&&$arrAttach[0]==3) {
  			 	//打赏
  			 	$info =  mysql_query("SELECT * from `".$cfg['tablepre']."weixin_reward` where payment_no = '".$result['order_id']."' ");
  			 	$rewardInfo = mysql_fetch_assoc($info);
  			 	if ($rewardInfo&&$rewardInfo['successed'] == 'N'&&$result['pay_status']==1) {
  			 		mysql_query("UPDATE  `".$cfg['tablepre']."weixin_reward` SET  `successed` ='Y'   where `payment_no`='".$result['order_id']."' ");
  			 	}

  			 	//推送
		  		$params = array();
		  		$params ['mid'] = $rewardInfo['MID'];
		  		$params ['topic'] = $rewardInfo['SN'];//"banklay_watch_pay_".$result['sn'];
		  		$totalAmount = $rewardInfo['fee']*100;
		  		$downcost = '';
		  		$message = array(
		  				'code' => 0,
		  				'message' =>"支付成功",
		  				'data' => array('evaluate' => true, 'totalAmount' => $totalAmount),
		  		);
		  		$params ['body'] = $message;
		  		$params ['flag'] = 'ds';

		 		if (aliyunPushOneMessage( $params, $return_code, $return_msg, $return_data )) {
		  			if ($return_code==1 && $return_data) {

		  			}
	  			}

  			 	return true;
  			 }
  			 
  			 $result['attach']='';
  			 
			//$checked =  explode('_',$result['attach'] );
			$checked = array();
			if( count($checked) > 1 ){    // 微信支付  在线充值
				
				$uid = $checked[1];
				   Log::DEBUG("uid:".$uid);
				  $info =  mysql_query("SELECT * from `".$cfg['tablepre']."member` where uid = ".$uid." ");
				  
				 
				  $memberinfo = mysql_fetch_assoc($info);
				  Log::DEBUG("transaction_id:".$result['transaction_id']);
				  
				  $checkinfo =mysql_query("SELECT * from `".$cfg['tablepre']."onlinelog` where dno = '".$result['transaction_id']."' "); 
				   $checkinfo = mysql_fetch_assoc($checkinfo);
				   if(empty($checkinfo)){
					    $acountcost = $result['total_fee']*0.01;
							$dotime = time(); 
							 Log::DEBUG("acountcost:".$acountcost);
							mysql_query("INSERT INTO `".$cfg['tablepre']."onlinelog` (`id` ,`type` ,`upid` ,`dno` ,`cost` ,`status` ,`addtime` ,`source`   )VALUES (NULL , 'acount', '".$memberinfo['uid']."', '".$result['transaction_id']."', '".$acountcost."', '1' , '".$dotime."','0');");
					Log::DEBUG("1:");
						 mysql_query("UPDATE  `".$cfg['tablepre']."member` SET  `cost` =  `cost`+".$acountcost." where `uid`=".$memberinfo['uid']."");  
						 
					Log::DEBUG("2:");	
					
						mysql_query("INSERT INTO `".$cfg['tablepre']."memberlog` (`id` ,`userid` ,`type` ,`addtype` ,`result` ,`addtime` ,`content` ,`title` ,`acount` )VALUES (NULL , '".$memberinfo['uid']."', '2', '1', '".$acountcost."', '".$dotime."', '在线充值', '使用微信支付充值".$acountcost."元', '".$memberinfo['cost']."');");
						Log::DEBUG("3:");	
						
						$acountid = mysql_insert_id($lnk);
						Log::DEBUG("acountid:".$acountid);	
						$acountinfo = file_get_contents($cfg['siteurl']."/index.php?ctrl=site&action=acountpayaddlog&dno=".$result['transaction_id']);

						
				   }
				 
				
			}else{
			 
			 if($result['attach'] == 'a'){    // 微信支付  闪惠订单 
					$orderdno = substr($result['out_trade_no'] , 2);
 				    $info =  mysql_query("SELECT * from `".$cfg['tablepre']."shophuiorder` where id = ".$orderdno." ");
					$backinfog = mysql_fetch_assoc($info);
				    if(!empty($backinfog) && $backinfog['status'] == 0){ 
						mysql_query("UPDATE  `".$cfg['tablepre']."shophuiorder` SET  `paystatus` =  1,`status` =  1,`completetime` =  ".time()."  where `id`=".$orderdno.""); 


						if($backinfog['uid'] > 0 && $backinfog['givejifen'] > 0){
							$memberinfo =  mysql_query("SELECT * from `".$cfg['tablepre']."member` where uid = ".$backinfog['uid']." "); 
							$memberinfo = mysql_fetch_assoc($memberinfo);
							if(!empty($memberinfo)){
								$sorce = $backinfog['givejifen'];
								mysql_query("UPDATE  `".$cfg['tablepre']."member` SET  `score`=`score`+".$sorce." where `uid`=".$backinfog['uid']."");  
								$allscore = $memberinfo['score']+$sorce;  
								mysql_query("INSERT INTO `".$cfg['tablepre']."memberlog` (`id` ,`userid` ,`type` ,`addtype` ,`result` ,`addtime` ,`content` ,`title` ,`acount` )VALUES (NULL , '".$memberinfo['uid']."', '1', '1', '".$sorce."', '".time()."', '优惠买单', '赠送积分".$sorce."元', '".$memberinfo['cost']."');");
							}
							
						}  
				    }
			 }else{    // 微信支付  普通订单
			  $info =  mysql_query("SELECT * from `".$cfg['tablepre']."order` where id = ".$result['out_trade_no']." ");
              $backinfog = mysql_fetch_assoc($info);
			  $mid= $backinfog['mid'];
			  
			  if(!empty($backinfog) && $backinfog['paystatus'] == 0){ 
			  	$transaction_id = $result['transaction_id'];
			  	$pay_status= $result['pay_status'];
			  	$pay_type= $result['pay_type'];
				$payTypeAndName = getPayTypeAndName($pay_type);

			  	if (empty($transaction_id)) {
			  		$transaction_id='';
			  	}
				if( $backinfog['paystatus'] == 0){ 
				  	if (!empty($pay_status)) {			  		
					  	mysql_query("UPDATE  `".$cfg['tablepre']."order` SET  `paystatus` =".$pay_status.",`status` =  1 ,`trade_no` = '".$transaction_id."' ,`paytype_name` = '".$payTypeAndName['paytype_name'] ."' ,`paytype` = '".$payTypeAndName['paytype']."'  where `id`=".$result['out_trade_no'].""
					." and `uni_order_id`=".$result['order_id']."");  
				  	}
				 }
			  	if($pay_status==1 && isset($result['sn']) && !empty($result['sn'])) {
			  		//推送
			  		$params = array();
			  		$params ['mid'] = $mid;
			  		$params ['topic'] = $result['sn'];//"banklay_watch_pay_".$result['sn'];
			  		$totalAmount = $result['pay_amount'];
			  		$downcost = $backinfog['wx_card_downcost'] *100;
			  		$message = array(
			  				'code' => 0,
			  				'message' =>"支付成功",
			  				'data' => array("memberDiscountAmt"=>0,"totalAmount"=>$result['pay_amount'],"couponDiscountAmt"=>$downcost,"tx_no"=>$result['out_trade_no']),
			  		);
			  		$params ['body'] = $message;
			  					  		
			  	/*	if (pushOneMessage( $params, $return_code, $return_msg, $return_data )) {
			  			if ($return_code==1 && $return_data) {

			  			}
			  		}*/
			  		if (aliyunPushOneMessage( $params, $return_code, $return_msg, $return_data )) {
			  			if ($return_code==1 && $return_data) {

			  			}
			  		}
			  		if ($backinfog['is_preeat'] == 1) {
			  			$params = array();
			  			$params ['mid'] = $mid;
			  			$params ['orderid'] = $result['out_trade_no'];
			  			$params ['sn'] = $result['sn'];
			  			
			  			if (updateCallServiceStatus( $params, $return_code, $return_msg, $return_data )) {
			  				if ($return_code==1 && $return_data) {
			  			
			  				}
			  			}
			  		}
			  	}
			  	

			  	
			  	if( $backinfog['paystatus'] == 0){
				  	Log::DEBUG("end notify:".$cfg['siteurl']."/index.php/".$mid."?ctrl=site&action=postmsgbypay&orderid=".$result['out_trade_no']);
				
				  	$info = file_get_contents($cfg['siteurl']."/index.php/".$mid."?ctrl=site&action=postmsgbypay&orderid=".$result['out_trade_no']);
			  	}
			  }
			  
			  }
			  
		}
			  mysql_close($lnk);  
			 
			 
		
			return true;
	
	}
	//排号
	protected function doQrcodePaihao($cfg, $mid,$openid,$arrAttach){
		if (empty(mid) || empty($openid)) return false;
		if (count($arrAttach) <2) return false;
		$shopid = $arrAttach[1];
		$dolink = $cfg['siteurl'].'/index.php/'.$mid.'/wxsite/shopqueue/shopid/'.$shopid;
		$res =  mysql_query("SELECT * from `".$cfg['tablepre']."shop` where id = ".$shopid." ");
		$shopInfo = mysql_fetch_assoc($res);
		
		$contents = '店铺：'.$shopInfo['shopname'].'\n';
		$contents .= '<a href=\''.trim($dolink).'\'>排号请点击</a>';
		$poststr = '{"touser":"' . $openid . '","msgtype":"text","text":{"content":"' . $contents . '"}}';
		$params = array ();
		$params ['mid'] = $mid;
		$params ['data'] =  base64_encode($poststr);
		
		if (api_post_sendCustomMsg ( $params, $return_code, $return_msg, $return_data )) {
			if ($return_code == 1) {
				return true;
			}
		}
		//Log::DEBUG("end scan  ".$return_msg);
		
	}
	public function Handle() {
		$input = file_get_contents ( 'php://input' );
		Log::DEBUG("data: ".$input);
		//$input='{"prj_id":"1","event_type":"order_pay","order_id":"12059","pay_status":1,"attach":"3;","sn":"","pay_amount":"10"}';
		//$input='{"prj_id":"1","event_type":"order_pay","order_id":"11567","pay_status":1,"sn":"","pay_amount":"1"}';
		$data = json_decode ( $input, TRUE );
		if (empty($data)) return false;
// 		$data['prj_id']=1;
// 		$data['event_type'] = 'order_pay';
// 		$data['order_id'] = 11615;
		
		$prj_id = $data['prj_id'];
		$event_type = $data['event_type'];
		$mid = '';
		$attach = '';
		if(isset($data['mid'])) {
			$mid= $data['mid'];
		}
		if(isset($data['attach'])) {
			$attach= $data['attach'];
		}
		
		if ($prj_id !=1) return false;
		

		
		$config = hopedir."config/hopeconfig.php";
		$cfg = include($config);
		
		if ($event_type == 'order_pay') {
			$order_id = $data['order_id'];
			//Log::DEBUG("order_id: ".$order_id );
			$params =array();
			$params ['order_id'] = $order_id;
			
			if (empty($order_id)) {
				echo "INVALID";
				die;
			}
			$return_code='';
			$return_msg='';
			$return_data= null;
			if (queryOrder ( $params, $return_code, $return_msg, $return_data )) {
				if ($return_code==1 && $return_data) {
					if(!$this->handleSub($cfg,$return_data)){
						$msg = "订单查询失败";
						return false;
					}
				}
			}
		}
		else if ($event_type == 'subscribe' || $event_type == 'scan') {
			//Log::DEBUG("begin scan ".$attach);
			$arrAttach = explode(";", $attach);
			$openid = '';
			if(isset($data['openid'])) {
				$openid= $data['openid'];
			}
			Log::DEBUG("openid: ".$openid .' mid:'.$mid);
			if ($arrAttach &&count($arrAttach) > 0&&!empty($arrAttach[0])) {
				$buz_type= $arrAttach[0];
				if ($buz_type == 'paihao') {
						$this->doQrcodePaihao($cfg,$mid,$openid, $arrAttach);
				}
			}

		}
	} 
}

Log::DEBUG("begin notify");
$notify = new PayNotifynewCallBack();
$notify->Handle(false);
