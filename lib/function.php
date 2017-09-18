<?php
//注册函数
 function FUNC_function($params,$smarty)
{ 
	  global $config;
	  $siteconfig = include($config);
	  $returndata = '';
	  $mid = Mysite::$app->getMid();
	  if ($params['link']) {
	  	$params['link'] = '/'.$mid.$params['link'];
	  }
	  
	  switch($params['type'])
	  {
	  	 case 'url'://构造方式  /link/data
	  	   if(empty($params['link'])){
	  	   	$returndata = $siteconfig['siteurl']; 
	  	   }else{   
	  	   	   if($siteconfig['is_static'] == 1){//全静态
	  	   	 	       $returndata=$siteconfig['siteurl'].$params['link'];
	  	   	   }elseif($siteconfig['is_static'] == 2){//半静态
	  	   	   	    $returndata=$siteconfig['siteurl'].'/index.php'.$params['link'];
	  	   	   }else{//全动态
	  	   	   	   $dolink = explode('/',$params['link']);
	  	   	   	   $findkey = 0;
	  	   	 	    foreach($dolink as $key=>$value){ 
	  	   	 	  	 if(!empty($value)){
	  	   	 	  	 	  if($findkey == 0){
	  	   	 	  	 	  	$returndata=$siteconfig['siteurl'].'/index.php?ctrl='.$value;
	  	   	 	  	 	  }elseif($findkey == 1){
	  	   	 	  	 	  	$returndata .='&action='.$value;
	  	   	 	  	 	  }else{
	  	   	 	  	 	  	$returndata .= $findkey%2==0?'&'.$value:'='.$value;
	  	   	 	  	 	  }
	  	   	 	  	 	  $findkey++; 
	  	   	 	    	} 
	  	   	 	    } 
	  	   	 	
	  	   	   }
	  	   	 
	  	   }
	  	 break;
	  	 default:
	  	   $returndata = '调用你参数不足';
	  	 break;
	  	 
	  } 
		return $returndata;
}
//$smarty->registerPlugin("function","OFUC", "FUNC_function");
//$smarty->registerPlugin("block","OBLC", "FUNC_block");
//注册函数
function FUNC_block($params, $content, $smarty, &$repeat, $template)
{
		if (isset($content)) {   
			 $lang = $params["lang"];    
			 // do some translation with $content   
	      return $translation;
	  }
} 
	function getgoodscx($cost,$cxdata){
		$newarray = array('is_cx'=>0,'cost'=>$cost,'zhekou'=>10);
		if(!empty($cxdata)){
					//   	goodscx
			$nowdate = date('Y-m-d',time());
			$nowtime = time(); 
			if($nowtime > $cxdata['cxstarttime'] && $nowtime< $cxdata['ecxendttime']){
				//在促销时间段
				$checktime = $nowtime-strtotime($nowdate);
				if($checktime > $cxdata['cxstime1']  && $checktime < $cxdata['cxetime1']){
					$newarray['cost'] = $cost*$cxdata['cxzhe']*0.01;
					$newarray['is_cx'] = 1;
					$newarray['zhekou'] = $cxdata['cxzhe']*0.1;
					
					 //表示在促销
				}else{
					if($checktime > $cxdata['cxstime2']  && $checktime < $cxdata['cxetime2']){
						//表示在促销
						$newarray['cost'] = $cost*$cxdata['cxzhe']*0.01;
						$newarray['is_cx'] = 1;
						$newarray['zhekou'] = $cxdata['cxzhe']*0.1;
					} 
				}
			}
		} 
		return $newarray; 
	}
function is_mobile_request()   
{   
  $_SERVER['ALL_HTTP'] = isset($_SERVER['ALL_HTTP']) ? $_SERVER['ALL_HTTP'] : '';   
   
  if(preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i', strtolower($_SERVER['HTTP_USER_AGENT'])))   
  {
  	return true;
  }
  if((isset($_SERVER['HTTP_ACCEPT'])) and (strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') !== false)) {
    return true;
  } 
  
  if(isset($_SERVER['HTTP_X_WAP_PROFILE']))  
  {
  	return true;
  }  
  if(isset($_SERVER['HTTP_PROFILE']))   
  {
  	return true;
  }
  $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));   
  $mobile_agents = array(   
        'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',   
        'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',   
        'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',   
        'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',   
        'newt','noki','oper','palm','pana','pant','phil','play','port','prox',   
        'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',   
        'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',   
        'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',   
        'wapr','webc','winw','winw','xda','xda-'  
        );   
  if(in_array($mobile_ua, $mobile_agents))   
  {
  	return true;
  }
    
  if(strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false)   
  {
  	return true;
  }
  if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows phone') !== false)  {
  return true;
  }
  return false;
} 
function error($type,$msg){
	 
		logwrite($msg,1); 
 
}
function logwrite($msg,$checkflag = 1){
	/*写日志*/
	//时间   操作内容
	if($checkflag == 1){
	 
		$nowdate = date('Y-m-d',time());
		if(!file_exists(hopedir.'log/'.$nowdate.'.php'))//创建文件
	  { 
		if(!is_dir(hopedir.'log')){
			 mkdir(hopedir.'log', 0777);
		}
		$fp = @fopen(hopedir.'log/'.$nowdate.'.php', 'w');
		@fclose($fp); 
	  }
	  $file=fopen(hopedir.'log/'.$nowdate.'.php',"a+");
	  $linsk = $_SERVER['REQUEST_URI'];
	  $content = "时间:".date('Y-m-d H:i:s',time()).",".$linsk."描述:".$msg."\r\n";
	  fwrite($file, $content); 
	  fclose($file);
  }
   
   
}

function limitalert(){
  //$tmsg = '您暂无权限设置,如有疑问请联系QQ：375952873  Tel：18538930577';
    //return  $tmsg;  
 	}
function sendCustomMsg($mid, $msg,$useropenid){
 		$poststr = '{"touser":"' . $useropenid . '","msgtype":"text","text":{"content":"' . $msg . '"}}';
 		$params = array ();
 		$params ['mid'] = $mid;
 		$params ['data'] =  base64_encode($poststr);//array("touser"=>$useropenid,"msgtype"=>"text", "text"=>array("content"=>$msg));
 	
 		if (api_post_sendCustomMsg ( $params, $return_code, $return_msg, $return_data )) {
 			if ($return_code == 1) {
 				return true;
 			}
 		}
 		return false;
}
function getPayTypeAndName($uni_pay_type) {
	$retArray = array();
	$retArray['paytype'] = 0;
	$retArray['paytype_name'] = '';

	if ($uni_pay_type == 'W') {
		$retArray['paytype'] = 1;
		$retArray['paytype_name']=$uni_pay_type;
	}
	else if ($uni_pay_type == 'M') {
		$retArray['paytype'] = 2;
		$retArray['paytype_name']=$uni_pay_type;
	}
	else if ($uni_pay_type == 'S') {
		$retArray['paytype'] = 7;
		$retArray['paytype_name']=$uni_pay_type;
	}
	else if ($uni_pay_type == 'O') {
		$retArray['paytype'] = 4;
		$retArray['paytype_name']=$uni_pay_type;
	}
	
	return $retArray;
}
function api_post_low($url, $params, &$return_code, &$return_msg, &$return_data){
	//echo $url;
	//die;
 		$return_content = "";
 		$data_string = array("params" => $params);
 		$data_string = json_encode($params);
 		$ch = curl_init();
       // curl_setopt ($ch, CURLOPT_PROXY, "http://127.0.0.1:8888");
 		//curl_setopt ($ch, CURLOPT_PROXY, "http://127.0.0.1:8888");
 		//curl_setopt($ch, CURLOPT_COOKIE, "PHPSESSID=94cu843ig05b7dmrj8ab9fiuh7; XDEBUG_SESSION=ECLIPSE_DBGP");
 	
 	
 		curl_setopt($ch, CURLOPT_POST, 1);
 		curl_setopt($ch, CURLOPT_URL, $url);
 		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
 		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
 		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
 	
 		$header = array(
 				'Content-Type: application/json; charset=utf-8',
 				'Content-Length: ' . strlen($data_string) );
 		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
 	
 		ob_start();
 		$rc = curl_exec($ch);
 		$return_content = ob_get_contents();
 	
 		ob_end_clean();
 	
 		if ($rc === false) {
 			//\Think\Log::record($url."\n".$return_content);
 			return false;
 		}
 		$return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);	
 		if ($return_code == 200){
 			$data = json_decode($return_content, true);

 	 		if (!empty($data) && isset($data["code"])){
 				$return_code = $data["code"];
 				$return_msg = $data["msg"];
 				$return_data = $data["data"];
 				return true;
 			}
 			$return_data = $return_content;
 			//\Think\Log::record($url."\n".$return_content);
 			return false;
 		}
 		$return_data = $return_content;
 		//\Think\Log::record($url."\n".$return_content); 	
 		return false;
 	}

function doGet($url, $timeout=20) {
 		if (ini_get("allow_url_fopen") == "1") {
 			$result = file_get_contents($url);
 		} else {
 			if(function_exists('curl_init')){
 				$ch = curl_init();
 				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
 				curl_setopt($ch, CURLOPT_URL, $url);
 				curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
 				$result =  curl_exec($ch);
 				curl_close($ch);
 			}else{
 				$result = file_get_contents($url);
 			}
 		}
 	
 		return $result;
 }
 	
function api_url_base() {
	$tempinfo = include hopedir.'./config/autorun.php';
	return  $tempinfo['api_url_base'];
}
function api_oauth_url($mid,$returl) {
 	$url = api_url_base();
 	$url.="?c=Weixin&a=oauth&buztype=1&mid=".$mid."&returl=".$returl;
 	return $url;
 }
function api_card_oauth_url($mid,$returl) {
 	$url = api_url_base();
 	$url.="?c=WeixinACard&a=oauth&buztype=1&mid=".$mid."&returl=".$returl;
 	return $url;
} 

function api_post_oauth_cardopenid_bycode($params, &$return_code, &$return_msg, &$return_data) {
	$url = api_url_base().'/WeixinACard/getOpenidByCode';
	return api_post_low($url, $params, $return_code, $return_msg, $return_data);
}

function api_post_getUserAppidByMid($params, &$return_code, &$return_msg, &$return_data) {
	$url = api_url_base().'/Weixin/getUserAppidByMid';
	return api_post_low($url, $params, $return_code, $return_msg, $return_data);
}
 function api_post_oauth_user_bycode($params, &$return_code, &$return_msg, &$return_data) {
 	$url = api_url_base().'/Weixin/getUserInfoByCode';
 	return api_post_low($url, $params, $return_code, $return_msg, $return_data);
 }
 function api_post_createQrcode($params, &$return_code, &$return_msg, &$return_data) {
 	$url = api_url_base().'/Weixin/createQrcode';
 	return api_post_low($url, $params, $return_code, $return_msg, $return_data);
 }
 function api_post_isMemberOfMerchant($params, &$return_code, &$return_msg, &$return_data) {
 	$url = api_url_base().'/WeixinACard/isMemberOfMerchant';
 	return api_post_low($url, $params, $return_code, $return_msg, $return_data);
 }
 function api_post_getCouponListOfMerchant($params, &$return_code, &$return_msg, &$return_data) {
 	$url = api_url_base().'/WeixinACard/getCouponListOfMerchant';
 	return api_post_low($url, $params, $return_code, $return_msg, $return_data);
 }
 function api_post_getPreferentiaInfoOfCard($params, &$return_code, &$return_msg, &$return_data) {
 	$url = api_url_base().'/WeixinACard/getPreferentiaInfoOfCard';
 	return api_post_low($url, $params, $return_code, $return_msg, $return_data);
 }
 
 function api_post_userGetCoupon($params, &$return_code, &$return_msg, &$return_data) {
 	$url = api_url_base().'/WeixinACard/userGetCoupon';
 	return api_post_low($url, $params, $return_code, $return_msg, $return_data);
 }
 function api_post_uupdateUserGetCoupon($params, &$return_code, &$return_msg, &$return_data) {
 	$url = api_url_base().'/WeixinACard/uupdateUserGetCoupon';
 	return api_post_low($url, $params, $return_code, $return_msg, $return_data);
 }
 
 function api_post_getCouponListByMemberAndAmount($params, &$return_code, &$return_msg, &$return_data) {
 	$url = api_url_base().'/WeixinACard/getCouponListByMemberAndAmount';
 	return api_post_low($url, $params, $return_code, $return_msg, $return_data);
 }
 function api_post_getMemberCards($params, &$return_code, &$return_msg, &$return_data) {
 	$url = api_url_base().'/WeixinACard/getMemberCards';
 	return api_post_low($url, $params, $return_code, $return_msg, $return_data);
 }
  
 function api_post_sendCustomMsg($params, &$return_code, &$return_msg, &$return_data) {
 	$url = api_url_base().'/Weixin/sendCustomMsg';
 	return api_post_low($url, $params, $return_code, $return_msg, $return_data);
 }
 function api_post_getJSApiTicket($params, &$return_code, &$return_msg, &$return_data) {
 	$url = api_url_base().'/Weixin/getJSApiTicket';
 	return api_post_low($url, $params, $return_code, $return_msg, $return_data);
 }
 function api_post_getCardJSApiTicket($params, &$return_code, &$return_msg, &$return_data) {
 	$url = api_url_base().'/WeixinACard/getJSApiTicket';
 	return api_post_low($url, $params, $return_code, $return_msg, $return_data);
 }
 
 function api_post_cardConsume($params, &$return_code, &$return_msg, &$return_data) {
 	$url = api_url_base().'/WeixinACard/cardConsume';
 	return api_post_low($url, $params, $return_code, $return_msg, $return_data);
 }
 function api_post_memberCardPay($params, &$return_code, &$return_msg, &$return_data) {
 	$url = api_url_base().'/Pay/memberCardPay';
 	return api_post_low($url, $params, $return_code, $return_msg, $return_data);
 }
 function api_post_refund($params, &$return_code, &$return_msg, &$return_data) {
 	$url = api_url_base().'/Pay/refund';
 	return api_post_low($url, $params, $return_code, $return_msg, $return_data);
 }
 function api_post_qrcodePay($params, &$return_code, &$return_msg, &$return_data) {
 	$url = api_url_base().'/Pay/qrcodePay';
 	return api_post_low($url, $params, $return_code, $return_msg, $return_data);
 }
 function api_post_order_add($params, &$return_code, &$return_msg, &$return_data) {
 	$url = api_url_base().'/Order/addOrder';
 	return api_post_low($url, $params, $return_code, $return_msg, $return_data);
 }

 function getWxJSApiPayUrl($order_id,$ret_url) {
 	$url = api_url_base().'/Pay/wechatJSPay';
 	return $url.'?'.'order_id='.$order_id.'&ret_url='.$ret_url;
 }
 function queryOrder($params, &$return_code, &$return_msg, &$return_data) {
 	$url = api_url_base().'/Pay/queryOrder';
 	return api_post_low($url, $params, $return_code, $return_msg, $return_data);
 }
 function pushOneMessage($params, &$return_code, &$return_msg, &$return_data) {
 	$url = api_url_base().'/Push/pushOneMessage';
 	return api_post_low($url, $params, $return_code, $return_msg, $return_data);
 }
 function aliyunPushOneMessage($params, &$return_code, &$return_msg, &$return_data) {
 	$url = api_url_base().'/Push/aliyunPushOneMessage';
 	return api_post_low($url, $params, $return_code, $return_msg, $return_data);
 }
 
 function updateCallServiceStatus($params, &$return_code, &$return_msg, &$return_data) {
 	$url = api_url_base().'/Waimai/updateCallServiceStatus';
 	return api_post_low($url, $params, $return_code, $return_msg, $return_data);
 }
 
 function smallAppPrepay($params, &$return_code, &$return_msg, &$return_data) {
 	$url = api_url_base().'/WeixinSmallApp/prepay';
 	return api_post_low($url, $params, $return_code, $return_msg, $return_data);
 }
 //以post形式请求 lzm
 function https_request($url,$data = null){
 	$curl = curl_init();
 	curl_setopt($curl, CURLOPT_URL, $url);
 	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
 	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
 	if (!empty($data)){
 		curl_setopt($curl, CURLOPT_POST, 1);
 		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
 	}
 	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
 	$output = curl_exec($curl);
 	curl_close($curl);
 	return $output;
 }
 
 function doPost($url, $data, $timeout=10, $ca_path = false) {
     if(function_exists('curl_init')){
         if(is_array($data) == true && class_exists('\CURLFile')){
             foreach($data as $key => $value){
                 if(strpos($value, '@') === 0){
                     $filename = ltrim($value, '@');
                     $data[$key] = new \CURLFile($filename);
                 }
             }
         }
         $is_ssl = substr($url, 0, 8) == "https://" ? true : false;//是否ssl
    
         $ch= curl_init();
         curl_setopt($ch, CURLOPT_URL, $url);
         curl_setopt($ch, CURLOPT_POST, true);
         curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         curl_setopt($ch, CURLOPT_HEADER, false);
         curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

         if ($is_ssl && $ca_path) {
             curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);   // 只信任CA颁布的证书
             curl_setopt($ch, CURLOPT_CAINFO, $ca_path); // CA根证书（用来验证的网站证书是否是CA颁布）
             curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 检查证书中是否设置域名，并且是否与提供的主机名匹配
         } else if ($is_ssl && !$ca_path) {
             curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
             curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); // 检查证书中是否设置域名
         }
   
         $result = curl_exec($ch); # 得到的返回值

         if(!$result){
      
         }
  
         curl_close($ch);
     }else{
         //TODO token失效做retry
         $result = file_get_contents($url);
     }
     return $result;
 }
 

 
?>