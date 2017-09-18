<?php
/*
 	小程序接口 method 方法   
 */
class method   extends smallappbase
{  	
	/**
	 * 登录接口
	 * @para   int    $mid      商户id
	 * @para   appid          
	 * @para    $code      获取微信用户信息授权code
	 * @return json 
	 */
	public function login() {
		$mid 		= $this->checkMid();
		$code 		= $this->getParam("code",'');
		$appid 		= $this->getParam("appid",'');
		$nickName 	= $this->getParam("nickName",'');
		$avatarUrl 	= $this->getParam("avatarUrl",'');
		$gender 	= $this->getParam("gender",'');

		if (empty($code) || empty($appid)) {
			$this->return_json(self::RET_CODE_ERR_ARGUMENT,'');
		}
		// 这个写死的需要被修改
		$secret = 'a9532b3caf9bdb7b8dd5e8bae884390c';
		$openid='';

		$url = "https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$secret}&js_code={$code}&grant_type=authorization_code";
		$result = doGet($url);
		$result = json_decode($result, true);
		if (!$result || !isset($result['openid'])) {
			$this->return_json(self::RET_CODE_ERR_OPDATA,'获取openid失败');
		}
		$openid = $result['openid'];
		$uid = 0;


				$oauthinfo =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."wxuser where mid='".$mid."' and appid='".$appid."'  and openid='".$openid."'  ");

				if(empty($oauthinfo)){//写用户数据
					$arr = array();
					$arr['mid'] = $mid;
					$arr['username'] = $openid;
					$arr['phone'] = '';
					$arr['address'] = '';
					$arr['password'] = md5($openid);
					$arr['email'] = '';
					$arr['creattime'] = time();
					$arr['score']  =0;
					$arr['logintime'] = time();
					 
					$arr['loginip'] ='';
					$arr['group'] = 10;
					$arr['logo'] = $avatarUrl;
					$arr['sex'] = $gender;  //用户的性别，值为1时是男性，值为2时是女性，值为0时是未知
					$newusername = $nickName;
					$checkusername ='xxx';
					$k = 0;
					while(!empty($checkusername)){
						$newusername = $k==0? $newusername:$newusername.$k;
						$checkusername = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where mid='".$mid."' and username ='".$newusername."' ");
						$k = $k+1;
						if(empty($checkusername)){
							break;
						}
					}
					$arr['username'] = $newusername;
					$this->mysql->insert(Mysite::$app->config['tablepre']."member",$arr);
					$uid = $this->mysql->insertid();
					 
					$mbdata['uid'] = $uid;
					$mbdata['mid'] = $mid;
					$mbdata['appid'] = $appid;
					$mbdata['openid'] = $openid;
					$mbdata['is_bang'] = 0;

					$this->mysql->insert(Mysite::$app->config['tablepre'].'wxuser',$mbdata);
				}else{//更新用户数据
					$uid= $oauthinfo['uid'];
					$membercheck = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where uid ='".$oauthinfo['uid']."' ");
					if(!empty($membercheck)){
						if(empty($membercheck['username'])){
							$newusername = $nickName;
							$checkusername ='xxx';
							$k = 0;
							while(!empty($checkusername)){
								$newusername = $k==0? $newusername:$newusername.$k;
								$checkusername = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where mid='".$mid."'   and username ='".$newusername."' ");
								$k = $k+1;
								if(empty($checkusername)){
									break;
								}
							}
							$cnewdata['username'] = $newusername;
						}
						$cnewdata['logo'] = $avatarUrl;
						$cnewdata['sex'] = $gender;
						$cnewdata['logintime'] = time();
						$this->mysql->update(Mysite::$app->config['tablepre'].'member',$cnewdata,"uid='".$oauthinfo['uid']."'");
					}else{
						$arr['mid'] = $mid;
						$arr['username'] = $openid;
						$arr['phone'] = '';
						$arr['address'] = '';
						$arr['password'] = md5($openid);
						$arr['email'] = '';
						$arr['creattime'] = time();
						$arr['score']  =0;
						$arr['logintime'] = time();
						$arr['loginip'] ='';
						$arr['group'] = 10;
						$arr['logo'] = $avatarUrl;
						$arr['sex'] = $gender;  //用户的性别，值为1时是男性，值为2时是女性，值为0时是未知
						$arr['uid'] = $oauthinfo['uid'];
						$newusername = $nickName;
						$checkusername ='xxx';
						$k = 0;
						while(!empty($checkusername)){
							$newusername = $k==0? $newusername:$newusername.$k;
							$checkusername = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where mid='".$mid."'  and   username ='".$newusername."' ");
							$k = $k+1;
							if(empty($checkusername)){
								break;
							}
						}
						$arr['username'] = $newusername;
						$this->mysql->insert(Mysite::$app->config['tablepre']."member",$arr);
						$uid = $this->mysql->insertid();
					}
					 
				}

		$retData = array();
		$retData['openid'] = $openid;
		$retData['nickName'] = $nickName;
		$retData['avatarUrl'] = $avatarUrl;
		$retData['uid'] = $uid;
		$this->return_json(self::RET_CODE_OK,'', $retData);
	}
	
	public function prepay() {
		$mid 		= $this->checkMid();
		$openid 		= $this->getParam("openid",'');
		$appid 		= $this->getParam("appid",'');
		$order_id		= $this->getParam("orderid",'');
		
		if (empty($openid) || empty($appid)) {
			$this->return_json(self::RET_CODE_ERR_ARGUMENT,'');
		}
		$order = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where mid='".$mid."' and id = '".$order_id."'  limit 1 ");
		if (empty($order)) {
			$this->return_json(self::RET_CODE_ERR_ARGUMENT,'找不到订单');
		}		
		
		$params = array();
		$params ['mid'] = $this->mid;
		$params ['order_id'] =$order['uni_order_id'];
		$params ['pay_type'] = 'S';
		$params ['body'] = "小程序支付";
		$params ['trade_type'] = 'JSAPI';
		$params ['appid'] =$appid;
		$params ['openid'] =$openid;

		$return_data = array();
		if (smallAppPrepay( $params, $return_code, $return_msg, $return_data )) {
			if ($return_code==1 && $return_data) {
					$this->return_json(self::RET_CODE_OK,'', $return_data);
			}
		}
		$message = empty($return_msg)?'获取支付参数失败':$return_msg;

		$this->return_json(self::RET_CODE_ERR_GETDATA,$return_msg);
	}
	/**
    * 商店首页接口 
    * @para   int    $mid      商户id
    * @return json 成功返回商户列表，失败返回错误
    */
	function index(){  
		$pageinfo = new page();
	  	$pageinfo->setpage(intval(IReq::get('page')),5); 
	 	$mid = $this->mid; 
	 	$sql = "SELECT s.id,s.shopname,s.shoplogo,s.shophdimg,s.lat,s.lng,sf.limitcost,sf.pscost,count(o.id) AS shuliang FROM ".Mysite::$app->config['tablepre']."shop AS s 
	 			LEFT JOIN ".Mysite::$app->config['tablepre']."shopfast AS sf ON s.id = sf.shopid 
	 			LEFT JOIN ".Mysite::$app->config['tablepre']."order AS o ON s.id = o.shopid and o.status = 3 
	 			WHERE  s.mid='".$mid."' and s.is_pass = 1 and s.is_open = 1 
	 			GROUP BY s.id ORDER BY s.sort asc limit ".$pageinfo->startnum().", ".$pageinfo->getsize()."";
	 	$shoplist = $this->mysql->getarr($sql);

	 	$lng = IReq::get('lng');// 通过前台获取
		$lat = IReq::get('lat');// 通过前台获取

		//通过经纬定位地址
		$place_url	= 'http://api.map.baidu.com/geocoder?output=json&location='.$lat.','.$lng.'&key=ccea36ece20a7a6eb0666bc726957e85'; 
		$json_place	= file_get_contents($place_url); 
		$place_arr	= json_decode($json_place,true);  
		$address    = $place_arr['result']['formatted_address'] ? $place_arr['result']['formatted_address'] : '定位失败,请开启定位'; 

	 	foreach ($shoplist as $key => $value) {




	 		$shoplist[$key]['shoplogo']  =  $shoplist[$key]['shoplogo']?  $this->siteurl.$shoplist[$key]['shoplogo'] : '';
	 		$shoplist[$key]['shophdimg'] =  $shoplist[$key]['shophdimg']? $this->siteurl.$shoplist[$key]['shophdimg'] : '';

	 		$mi = $this->GetDistance($lat,$lng, $value['lat'],$value['lng'], 1); 
		  	$mi = $mi > 1000? round($mi/1000,2):$mi;
							  
			$shoplist[$key]['juli'] = $mi;
			//消除多余返回
			unset($shoplist[$key]['lat']);
			unset($shoplist[$key]['lng']);
	 	}
 
		// $data = ['code' => '1','msg' =>"success" , 'shoplist'=>$shoplist,'address'=>$address]; 
		// print_r(json_encode($data));die;
		$data = ['shoplist' => $shoplist,'address'=>$address];
		$this->return_json(self::RET_CODE_OK,'success', $data);
     
	}

	/**
    * 商店菜单展示接口 
    * @para   int    $mid      商户id
    * @para   int    $id       门店id
    * @return shopinfo  成功返回商店信息，失败返回错误
    * @return goodslist 成功返回商品，失败返回错误
    */
	function shopshow(){
	 	$mid = $this->mid; 
	 	$id  = intval(IReq::get('id'));

	 	//临时删除购物车文件
	 	$openid = IReq::get('openid'); //'oeOB9wLUShhTu3LlSEhIyFhOPLNY';//
		$file =  dirname(__FILE__).'/cart/'.$id.$openid.'.txt';
		if(file_exists($file)){
			unlink($file);  
		}

      	//商店基本信息
  		$shopinfoSql = "SELECT s.id,s.shoplogo,s.starttime,s.notice_info,sf.limitcost,sf.pscost,sf.arrivetime FROM ".Mysite::$app->config['tablepre']."shop AS s 
 			LEFT JOIN ".Mysite::$app->config['tablepre']."shopfast AS sf ON s.id = sf.shopid 
 			WHERE  s.mid='".$mid."' and s.id='".$id."' and s.is_pass = 1 and s.is_open = 1";
      	$shopinfo = $this->mysql->select_one($shopinfoSql);
      	$shopinfo['shoplogo'] = Mysite::$app->config['siteurl'].$shopinfo['shoplogo'];  

		$sellcountSql = "select sum(sellcount+virtualsellcount) count from  ".Mysite::$app->config['tablepre']."goods where shopid= ".$id."  ";
		$sellcount = $this->mysql->select_one($sellcountSql);
		$shopinfo['sellcount'] = $sellcount['count'];

      	//商品类型 
 		$goodstypeSql = "select id,name from ".Mysite::$app->config['tablepre']."goodstype  where shopid='".$id."' order by orderid asc  ";
      	$goodstype    = $this->mysql->getarr($goodstypeSql);
      	
  		//商品类型下的商品
  	 	foreach($goodstype as $key=>$value){
  	 	 	$goodsSql = "select id,name,cost,(sellcount+virtualsellcount) AS sellcount,img from ".Mysite::$app->config['tablepre']."goods where shopid='".$id."' and is_waisong = 1 and typeid =".$value['id']."  order by good_order asc";
  	 	 	$goods = $this->mysql->getarr($goodsSql);
  	 	 	foreach ($goods as $k => $v) {
  	 	 	 	$goods[$k]['img'] = $goods[$k]['img']? $this->siteurl.$v['img'] : '';
  	 	 	 	$goods[$k]['count'] = 0;//商品初始数量前台使用
  	 	 	} 	
  	 	 	$goodstype[$key]['goods'] = $goods;
  	 	 	$goodstype[$key]['tag']   = 'tag'.$key;//返回菜单标记前台使用
  	 	}

	 	$data = ['shopinfo'=>$shopinfo,'goodslist'=>$goodstype,'shopid'=>$id];
		$this->return_json(self::RET_CODE_OK,'success', $data);

     
	}

	/**
    * 加入购物车接口 
    * @para   int    $shopid      门店id
    * @para   int    $goodsid      
    * @return data 成功返回，失败返回错误
    */
	function addcart(){  
		$openid   = IReq::get('openid');
		$shopid   = intval(IReq::get('shopid'));
	    $goods_id = intval(IReq::get('goodsid')); 

		if($goods_id){
		    $file   =  dirname(__FILE__).'/cart/'.$shopid.$openid.'.txt';
            $fp     = fopen($file, 'a+');
            $str    = $goods_id.',';
            flock($fp, LOCK_EX);// 加锁  
            fwrite($fp, $str);  
            flock($fp, LOCK_UN);// 解锁
            fclose($fp);  
		}

	}
	/**
    * 减少购物车接口 
    * @para   int    $shopid      门店id
    * @para   int    $goodsid      
    * @return data 成功返回，失败返回错误
    */
	function downcart(){
		$openid   = IReq::get('openid'); 
		$shopid   = intval(IReq::get('shopid'));
		$goods_id = intval(IReq::get('goodsid'));
 
	    if($goods_id){
	    	$file   =  dirname(__FILE__).'/cart/'.$shopid.$openid.'.txt';
          	$con = file_get_contents($file);
 
          	$cons  = substr($con,0,-1);         
          	$goods = explode(',',$cons);
          	//删除
          	foreach ($goods as $key => $value) {
          		if($value == $goods_id){
          			$pass = $key;
          		}
          	}
          	unset($goods[$pass]);
          	//重组
          	$ids = '';
          	foreach ($goods as $key => $value) {	 
          			$ids .= $ids ? ','.$value : $value;
          	}
          	if($ids){
          		$ids = $ids.',';
          	}
          	file_put_contents($file,$ids);
	    }

	}

	/**
    * 清除购物车接口 
    * @para   int    $shopid      门店id
    * @para   int    $goodsid      
    * @return data 成功返回，失败返回错误
    */
	function clearcart(){
		$openid   = IReq::get('openid'); 
		$shopid   = intval(IReq::get('shopid'));
 
	    if($shopid){
		   	//删除购物车文件
			$file   =  dirname(__FILE__).'/cart/'.$shopid.$openid.'.txt';
			if(file_exists($file)){
				unlink($file); 
			}
	    }

	}

	/**
    * 购物车准备下单接口 
    * @para   int    $shopid      门店id
    * @para   int    $goodsid      
    * @return data 成功返回，失败返回错误
    */
	function shopcart(){

		$openid = IReq::get('openid');
    	$mid    = $this->mid;
    	$member = $this->getmember($mid,$openid);

   	 	$shopid = IFilter::act(IReq::get('shopid'));  //shopid
        //判断购物车是否为空(购物车商品)
        $file =  dirname(__FILE__).'/cart/'.$shopid.$openid.'.txt';
        $con  = file_get_contents($file);
        if($con == ''){
        	$this->return_json(self::RET_CODE_ERR_UNKNOWN,'当前购物车为空');
        } 	
 
      	$cons  = substr($con,0,-1); 
      	$goods = explode(',',$cons);
       	$arr   = array_count_values($goods);

       	$goodslist = [];
       	foreach ($arr as $key => $value) {
       		$goodsinfo = $this->mysql->select_one("select id,name,cost from ".Mysite::$app->config['tablepre']."goods where id=".$key."  "); 
       		$goodsinfo['shuliang'] = $value;
       		$goodsinfo['sum'] 	   = $value*$goodsinfo['cost'];
   			$goodslist[]  = $goodsinfo;
       	}
       	$sum = 0;
       	foreach ($goodslist as $key => $value) {
       		$sum += $value['sum'];
       	}
       	$data['sum']   	   = $sum;
   		$data['goodslist'] = $goodslist; 


 		//商店相关
   		$shopinfoSql = "select id,shopname,starttime,is_pass,is_open,postdate,befortime,is_onlinepay from ".Mysite::$app->config['tablepre']."shopfast as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id  where shopid = ".$shopid."   ";
   	 	$shopinfo   =  $this->mysql->select_one($shopinfoSql);  
   	 	$data['shopname'] 	  = $shopinfo['shopname'];
   	 	$data['shopid'] 	  = $shopinfo['id'];
   	 	$data['is_onlinepay'] = $shopinfo['is_onlinepay'];


		//收货地址
		if(empty($member['uid']) || $member['uid'] ==  0){	 
			$data['deaddress'] = array();
		}else{
			$deaddressSql = "select id,contactname,phone,address from ".Mysite::$app->config['tablepre']."address  where userid = ".$member['uid']." and `default`=1   ";
			$data['deaddress']    =  $this->mysql->select_one($deaddressSql); 
		} 
		if($data['deaddress']){
			$data['is_hasdefault'] = 1;
		}else{
			$data['is_hasdefault'] = 0;
		}

		$this->return_json(self::RET_CODE_OK,'success', $data);
 
    }
    /**
    * 立即下单接口 
    * @para   int    $shopid      门店id
    * @para   int    $goodsid      
    * @return data 成功返回，失败返回错误
    */
    function makeorder(){  
    	
    	$openid = IReq::get('openid');//
    	$shopid = intval(IReq::get('shopid'));
    	$mid    = $this->mid;
    	$member = $this->getmember($mid,$openid);
 
		$addressinfo =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."address  where userid = ".$member['uid']." and `default`=1   "); 
		if(empty($addressinfo)) $this->return_json(self::RET_CODE_ERR_UNKNOWN,'未设置默认地址');

		$info['mid'] = $mid;	
		$info['is_goshop'] = 0;
		$info['username'] 	= $addressinfo['contactname']; 
		$info['mobile'] 	= $addressinfo['phone'];
		$info['addressdet'] = $addressinfo['address'];
		$info['shopid'] 	= $shopid;//店铺ID
		$info['remark'] 	= IFilter::act(IReq::get('remark'));//备注
		$info['paytype'] 	= '1';//IFilter::act(IReq::get('paytype'));//支付方式
		$info['dikou'] 		= '';//intval(IReq::get('dikou'));//抵扣金额
	//	$info['senddate'] = date('Y-m-d',time());// IFilter::act(IReq::get('senddate'));
		$info['minit'] 		= '';//IFilter::act(IReq::get('minit')); 
		$info['juanid']  	= '';//intval(IReq::get('juanid'));//优惠劵ID
		$info['ordertype'] = 3;//订单类型 
		$info['othercontent'] ='';//empty($peopleNum)?'':serialize(array('人数'=>$peopleNum)); 
		if(empty($info['shopid'])) $this->return_json(self::RET_CODE_ERR_UNKNOWN,'店铺ID错误'); 
		
		//判断购物车是否为空(购物车商品)
        $file =  dirname(__FILE__).'/cart/'.$shopid.$openid.'.txt';
        $con  = file_get_contents($file);
        if($con == ''){
        	$this->return_json(self::RET_CODE_ERR_UNKNOWN,'当前购物车为空');
        } 	
 
      	$cons  = substr($con,0,-1); 
      	$goods = explode(',',$cons);
       	$arr   = array_count_values($goods);

       	$goodslist = [];
       	foreach ($arr as $key => $value) {
       		$goodsinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goods where id=".$key."  "); 
       		$goodsinfo['shuliang'] = $value;
       		$goodsinfo['count'] = $value;
       		$goodsinfo['sum'] 	   = $value*$goodsinfo['cost'];
   			$goodslist[]  = $goodsinfo;
       	}
       	$sum = 0;
       	foreach ($goodslist as $key => $value) {
       		$sum   += $value['sum'];
       		$count += $value['count'];
       	}
   		$carinfo = array('goodslist' => $goodslist,'count'=>$count,'sum' => $sum,'bagcost'=>'0');

 		//商店信息
	    $shopinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id where a.shopid = '".$info['shopid']."'    ");   
		if(empty($shopinfo))   	$this->return_json(self::RET_CODE_ERR_UNKNOWN,'店铺获取失败'); 
		 
		// $areaid  = ICookie::get('myaddress');
		// $checkps =  $this->pscost($shopinfo,$carinfo['count'],$areaid); 
		// $info['cattype'] = 0;
		// if($shopshowtype != 'dingtai'){
		//   	if(empty($info['username'])) 		  		$this->return_json(self::RET_CODE_ERR_UNKNOWN,'联系人不能为空'); 
		//   	if(!IValidate::suremobi($info['mobile']))   $this->return_json(self::RET_CODE_ERR_UNKNOWN,'请输入正确的手机号'); 
		//   	if(empty($info['addressdet'])) 				$this->return_json(self::RET_CODE_ERR_UNKNOWN,'详细地址为空');  
		// }
	  	
	   	$info['userid'] = !isset($member['score'])?'0':$member['uid'];

	  	//获取ip
	   	$info['ipaddress'] 	= "";
	   	$ip_l 				= new iplocation(); 
     	$ipaddress			= $ip_l->getaddress($ip_l->getIP());  
     	if(isset($ipaddress["area1"])){
		   	$info['ipaddress']  = $ipaddress['ip'].mb_convert_encoding($ipaddress["area1"],'UTF-8','GB2312');//('GB2312','ansi',);
	   	} 
	  //area1 二级地址名称	area2 三级地址名称	area3
	   
	  	$info['areaids'] = '';
	  
	    if($shopinfo['is_open'] != 1) 		$this->return_json(self::RET_CODE_ERR_UNKNOWN,'店铺暂停营业');  
	   	$tempdata = $this->getOpenPosttime($shopinfo['is_orderbefore'],$shopinfo['starttime'],$shopinfo['postdate'],$info['minit'],$shopinfo['befortime']); 
	   	// if($tempdata['is_opentime'] ==  2) 	$this->return_json(self::RET_CODE_ERR_UNKNOWN,'选择的配送时间段，店铺未设置');   
	   	// if($tempdata['is_opentime'] == 3) 	$this->return_json(self::RET_CODE_ERR_UNKNOWN,'选择的配送时间段已超时');    
	   	$info['sendtime'] 	= $tempdata['is_posttime'];
	   	$info['postdate'] 	= $tempdata['is_postdate'];
	    $info['addpscost'] 	= $tempdata['cost'];
	 
	  	$checksend = Mysite::$app->config['ordercheckphone'];
    	if($checksend == 1){
    	  	if(empty($member['uid'])){
    	  	  	$checkphone = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."mobile where phone ='".$info['mobile']."'   order 	by addtime desc limit 0,50");
    	  	  	if(empty($checkphone)) $this->message('member_emailyan');
    	  	  	if(empty($checkphone['is_send'])){ 
    	  	  	  	$mycode = IFilter::act(IReq::get('phonecode'));
    	  	  	  	if($mycode == $checkphone['code']){
    	  	  	     	$this->mysql->update(Mysite::$app->config['tablepre'].'mobile',array('is_send'=>1),"phone='".$info['mobile']."'");  
    	  	  	  	}else{
    	  	  	     	$this->message('member_emailyan');
    	  	  	  	} 
    	  	  	} 
    	  	}
    	}

    	$paytype = $info['paytype'] == 1?1:0;
	   
	   	$info['shopinfo'] 	= $shopinfo;
	   	$info['allcost'] 	= $carinfo['sum'];
	   	$info['bagcost'] 	= $carinfo['bagcost'];
	   	$info['allcount'] 	= $carinfo['count'];
	   	$info['shopps'] 	= $checkps['pscost']; 
	   	$info['goodslist']  = $carinfo['goodslist'];
	   	$info['pstype'] 	= $checkps['pstype'];
	   	$info['cattype'] 	= 0;//表示不是预订 
	   
	   
		//检测库存
/*		foreach($info['goodslist'] as $key=>$value){  
	        if($value['stock'] < $value['count']){
	        	$this->return_json(self::RET_CODE_ERR_UNKNOWN,'商品库存不足'); 
		 	}
	    }*/

    	if( !strpos($_SERVER["HTTP_USER_AGENT"],'MicroMessenger')    ){    //判断是微信浏览器不
        	$info['platform'] = 3;//触屏
    	}else{
        	$info['platform'] = 2;//微信
    	}

	    if($shopinfo['limitcost'] > $info['allcost']) $this->return_json(self::RET_CODE_ERR_UNKNOWN,'商品总价低于最小起送价');         
  
        $orderclass = new orderclass();
	   	$orderclass->makenormal($info);
	   	$orderid = $orderclass->getorder();
	   	
	   	// if($info['userid'] ==  0){ 
	  	 //   	ICookie::set('orderid',$orderid,86400);
	   	// }    

	   	//下单完成删除购物车文件
		$file   =  dirname(__FILE__).'/cart/'.$shopid.$openid.'.txt';
		if(file_exists($file)){
			unlink($file); 
		}
        	
		//$this->return_json(self::RET_CODE_OK,'success',$orderid); 
 
	   	//add by rain will ********************************************************************************************************
 	   	$paymethod 				 = 'W';
	   	$paymethod 				 = 'membercard';
	   	$params    				 = array();
	   	$params ['out_trade_no'] = ''.$orderid;
	   	$params['body'] 		 = "小程序下单";
	   	$params ['mid'] 		 = $this->mid;
	   	$params ['order_type'] 	 = 1;
	   	$params ['attach'] =  "2;";
// 	   	$params ['pay_type'] = $paymethod;
// 	   	$params ['trade_type'] = 'JSAPI';
	   	$params ['order_amount'] =  $info['allcost']*100;
       	    
   	    //重新查订单表拿需要支付总金额，因为之前只拿到商品价格没有包含包装费和快递费
       	$cxjrinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where id = ".$orderid."");
       	$params ['pay_amount'] =  $cxjrinfo['allcost']*100;

		$order_sn ='';
		if (api_post_order_add ( $params, $return_code, $return_msg, $return_data )) {
			if ($return_code==1 && $return_data) {
				$updateData = array();
				$updateData['uni_order_id'] = $return_data['order_id'];
				$order_sn =  $return_data['order_sn'];
				$this->mysql->update(Mysite::$app->config['tablepre'].'order',$updateData,"id='".$orderid."'");
   	
			 	$this->return_json(self::RET_CODE_OK,'success',$orderid); 
 				 
			}
		}
                
	   	$opMsg=empty($return_msg)?'统一支付下单失败':$return_msg;

	   	$this->return_json(self::RET_CODE_ERR_UNKNOWN,$opMsg);
		 
	}
 
   	/**
    * 删除收货地址接口 
    * @para   int    $shopid      门店id
    * @para   int    $goodsid      
    * @return data 成功返回，失败返回错误
    */
   	function deladdress(){
   		$openid = IReq::get('openid');//
    	$mid    = $this->mid;
    	$member = $this->getmember($mid,$openid);  

  	 	$addressid = intval(IReq::get('addressid'));
		if(empty($addressid)) $this->return_json(self::RET_CODE_ERR_UNKNOWN,'收货地址不存在'); 
	   	$this->mysql->delete(Mysite::$app->config['tablepre'].'address',"id = '$addressid' and  userid='".$member['uid']."'");
	   	$this->return_json(self::RET_CODE_OK,'success',$addressid); 
  	}

  	/**
    * 编辑收货地址接口（设置默认以及其他）
    * @para   int    $shopid      门店id
    * @para   int    $goodsid      
    * @return data 成功返回，失败返回错误
    */
  	function editaddress(){
    	$mid    = $this->mid;
		$addressid = intval(IReq::get('addressid'));
		$data['addressid'] = $addressid;
     
     	$address = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."address where mid='".$this->mid."' and id = '".$addressid."' ");
	  	
	  	if($address){
	  		$this->return_json(self::RET_CODE_OK,'success',$address); 
	  	}else{
	  		$this->return_json(self::RET_CODE_ERR_UNKNOWN,'获取地址失败');
	  	}
 		
  	}
  	/**
    * 设置默认收货地址接口（设置默认以及其他）
    * @para   int    $shopid      门店id
    * @para   int    $goodsid      
    * @return data 成功返回，失败返回错误
    */
  	function defaultaddress(){
 		$openid = IReq::get('openid');//
    	$mid    = $this->mid;
    	$member = $this->getmember($mid,$openid);  

  		$what 	   = trim(IFilter::act(IReq::get('adrdefault')));
		$addressid = intval(IReq::get('addressid'));
		if(empty($addressid)) $this->return_json(self::RET_CODE_ERR_UNKNOWN,'收货地址不存在'); 
	  	if($what == '1'){
	  		$arr['default'] = 0;
	  		$this->mysql->update(Mysite::$app->config['tablepre'].'address',$arr,"userid='".$member['uid']."'");
	  		$arr['default'] = 1;
	  		$this->mysql->update(Mysite::$app->config['tablepre'].'address',$arr,"id='".$addressid."' and userid='".$member['uid']."' ");
	  		$this->return_json(self::RET_CODE_OK,'success',$addressid); 
	  	}
  	}

   	/**
    * 保存收货地址接口 
    * @para   int    $shopid      门店id
    * @para   int    $goodsid      
    * @return data 成功返回，失败返回错误
    */
 	//id	userid	username	address 完整地址：选择地址与详细地址	phone	otherphone	contactname	default	
   	//areaid1 区域1ID	areaid2 区域2ID	areaid3 区域3ID	sex 1时是男性，值为2时是女性，值为0时是未知	bigadr 选择的地址	
   	//detailadr 详细地址	lat 地址坐标	lng 地址坐标	addtime
   	function saveaddress(){
   		$openid = IReq::get('openid');
    	$mid    = $this->mid;
    	$member = $this->getmember($mid,$openid);   
	   
   	 	$addressid = intval(IReq::get('addressid'));
   	 	$laiyuan   = '0';//intval(IReq::get('laiyuan'));  // 地址来源，  1为PC端，PC端无坐标，不在微信端显示     手机端不传此参数，默认为0
	   
		if(empty($addressid))
		{//新增地址
		 	$checknum = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."address where mid='".$this->mid."' and userid='".$member['uid']."' ");
			if(Mysite::$app->config['addresslimit'] < $checknum) 			$this->return_json(self::RET_CODE_ERR_UNKNOWN,'收货地址数量限制');     

			$arr['mid'] 		= $this->mid;
			$arr['userid'] 		= $member['uid'];
			$arr['username'] 	= $member['username']; 
		
			$arr['address'] 	= IFilter::act(IReq::get('add_new'));
			$arr['phone'] 		= IFilter::act(IReq::get('tel'));
			$arr['otherphone'] 	= '';
			$arr['contactname'] = IFilter::act(IReq::get('name'));
			$check_message 		= IFilter::act(IReq::get('check_message'));
			$arr['sex'] 		= IFilter::act(IReq::get('sex'));
			$arr['default'] 	= $checknum == 0?1:0;
			$arr['addtime'] 	= time();
			
			$arr['bigadr'] 		= IFilter::act(IReq::get("address"));
			$arr['lat'] 		= IFilter::act(IReq::get('lat'));
			$arr['lng'] 		= IFilter::act(IReq::get('lng'));
			$arr['detailadr'] 	= IFilter::act(IReq::get('readdress'));
			$arr['address']     = $arr['bigadr'].'  '.$arr['detailadr'];
			if($laiyuan == 0 &&  Mysite::$app->config['addAreaType'] == 0 ){	
				if( empty($arr['bigadr']) ||  $arr['bigadr'] == '点击选择地址' ) 	$this->return_json(self::RET_CODE_ERR_UNKNOWN,'请选择地址！');  
				if( empty($arr['detailadr'])  ) 									$this->return_json(self::RET_CODE_ERR_UNKNOWN,'请填写详细地址！');  
				if( empty($arr['lat'])  ) 							$this->return_json(self::RET_CODE_ERR_UNKNOWN,'获取地图坐标失败，请重新获取！');  
				if( empty($arr['lng'])  ) 							$this->return_json(self::RET_CODE_ERR_UNKNOWN,'获取地图坐标失败，请重新获取！');  
			
			 }
			
			if(!empty($arr['otherphone'])&&!(IValidate::phone($arr['otherphone'])))	$this->return_json(self::RET_CODE_ERR_UNKNOWN,'手机号码格式错误'); 
			$this->mysql->insert(Mysite::$app->config['tablepre'].'address',$arr);
			$addid = $this->mysql->insertid();
			$this->mysql->update(Mysite::$app->config['tablepre'].'address',array('default'=>0),'userid = '.$member['uid'].' ');
	        $this->mysql->update(Mysite::$app->config['tablepre'].'address',array('default'=>1),'userid = '.$member['uid'].' and id='.$addid.'');
			
	        $this->return_json(self::RET_CODE_OK,'success',$addid);  

		}else{//修改地址
		    
			$arr['address'] 		= IFilter::act(IReq::get('add_new'));
			$arr['phone'] 			= IFilter::act(IReq::get('tel'));
			$arr['otherphone'] 		= '';
			$arr['contactname'] 	= IFilter::act(IReq::get('name'));
			$arr['sex'] 			= IFilter::act(IReq::get('sex'));
			$arr['addtime'] 		= time();
	
			$arr['bigadr'] 		=  IFilter::act(IReq::get('address'));
			$arr['lat'] 		=  IFilter::act(IReq::get('lat'));
			$arr['lng'] 		=  IFilter::act(IReq::get('lng'));
			$arr['detailadr'] 	=  IFilter::act(IReq::get('readdress'));
			$arr['address']     =  $arr['bigadr'].'  '.$arr['detailadr'];
			if($laiyuan == 0 &&  Mysite::$app->config['addAreaType'] == 0 ){		 
				if( empty($arr['bigadr']) ||  $arr['bigadr'] == '点击选择地址' ) 	$this->return_json(self::RET_CODE_ERR_UNKNOWN,'请选择地址！');  
				if( empty($arr['detailadr'])  ) 									$this->return_json(self::RET_CODE_ERR_UNKNOWN,'请填写详细地址！');  
				if( empty($arr['lat'])  ) 							$this->return_json(self::RET_CODE_ERR_UNKNOWN,'获取地图坐标失败，请重新获取！');  
				if( empty($arr['lng'])  ) 							$this->return_json(self::RET_CODE_ERR_UNKNOWN,'获取地图坐标失败，请重新获取！');  
	  		}
			if(!empty($arr['otherphone'])&&!(IValidate::phone($arr['otherphone'])))	$this->return_json(self::RET_CODE_ERR_UNKNOWN,'手机号码格式错误');
			$this->mysql->update(Mysite::$app->config['tablepre'].'address',$arr,'userid = '.$member['uid'].' and id='.$addressid.'');
			$this->mysql->update(Mysite::$app->config['tablepre'].'address',array('default'=>0),'userid = '.$member['uid'].' ');
	        $this->mysql->update(Mysite::$app->config['tablepre'].'address',array('default'=>1),'userid = '.$member['uid'].' and id='.$addressid.'');

	        $this->return_json(self::RET_CODE_OK,'success',$addressid);  
		}

  	}


   	/**
    * 收货地址列表接口 
    * @para   int    $shopid      门店id
    * @para   int    $goodsid      
    * @return data 成功返回，失败返回错误
    */
  	function addresslist(){
 		$openid = IReq::get('openid');//
    	$mid    = $this->mid;
    	$member = $this->getmember($mid,$openid);   
	 	
	  	$tarelist = $this->mysql->getarr("select *  from ".Mysite::$app->config['tablepre']."address where userid='".$member['uid']."'   order by id asc limit 0,50");
 
	  	$data['arealist'] = $tarelist;
	  	$this->return_json(self::RET_CODE_OK,'success',$data);  
   	}

   	/**
    * 我的订单列表接口 
    * @para   int    $shopid      门店id
    * @para   int    $goodsid      
    * @return data 成功返回，失败返回错误
    */
    function userorder(){
		$openid = IReq::get('openid');//
    	$mid    = $this->mid;
    	$member = $this->getmember($mid,$openid);   
	 	
	  	$pageinfo = new page();
	  	$pageinfo->setpage(intval(IReq::get('page')),100);  
	  	// 
		$datalist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."order where buyeruid='".$member['uid']."' and is_goshop = 0 and shoptype != 100  order by id desc limit ".$pageinfo->startnum().", ".$pageinfo->getsize()."");   

	  	$backdata  = array();
	  	foreach($datalist as $key=>$value){  
			$shopinfo = $this->mysql->select_one("select shoplogo from ".Mysite::$app->config['tablepre']."shop where id='".$value['shopid']."'");  
			$value['shoplogo'] = $this->siteurl.$shopinfo['shoplogo'];
			$value['addtime'] 			= date('Y-m-d H:i',$value['addtime']);
			$backdata[] 				= $value;
		}
		$data['orderlist'] = $backdata;
		$this->return_json(self::RET_CODE_OK,'success',$data);  
 
	}

	   	/**
    * 删除订单接口 
    * @para   int    $shopid      门店id
    * @para   int    $goodsid      
    * @return data 成功返回，失败返回错误
    */
    function delorder(){

    	$mid    = $this->mid;
		$orderid = intval(IReq::get('orderid'));
		$orderinfo  = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where id='".$orderid."'  ");
		
		if($orderinfo['mid'] != $mid)  $this->return_json(self::RET_CODE_ERR_UNKNOWN,'不合法的访问');  
		//if($orderinfo['status'] < 4)   $this->return_json(self::RET_CODE_ERR_UNKNOWN,'请支付后再删除该订单');  
	   	$this->mysql->delete(Mysite::$app->config['tablepre'].'order',"id = ".$orderinfo['id']." ");
	   	$this->mysql->delete(Mysite::$app->config['tablepre'].'orderdet',"order_id = ".$orderinfo['id']." ");
		$this->mysql->delete(Mysite::$app->config['tablepre'].'orderps',"orderid = '$orderid' and status != 3");  //写配送订单  

		$this->return_json(self::RET_CODE_OK,'success',$orderid);  
	}

	/**
    * 取消订单接口 
    * @para   int    $shopid      门店id
    * @para   int    $goodsid      
    * @return data 成功返回，失败返回错误
    */
	function cancelorder(){
		$openid = IReq::get('openid');//
    	$mid    = $this->mid;
    	$member = $this->getmember($mid,$openid);  
   
		$orderid = intval(IReq::get('orderid'));  
		if(!empty($orderid)){ 
	       	$orderinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where  id = ".$orderid.""); 
	      	//取消订单修改状态(未付款状态)
	      	if($orderinfo['paytype'] == 0 || $order['paystatus'] == 0){ 
		       	$ordercontrol = new ordercontrol($orderid);
				if($ordercontrol->buyerunorder($member['uid'])){		
					$this->mysql->delete(Mysite::$app->config['tablepre'].'orderps',"orderid = '$orderid' and status != 3");  //写配送订单  
					    $ordCls = new orderclass();
						$ordCls->writewuliustatus($orderinfo['id'],12,$orderinfo['paytype']);  // 用户取消订单
	        			$this->return_json(self::RET_CODE_OK,'success',$orderid);  
				}else{
	        		$this->return_json(self::RET_CODE_OK,'订单退款信息不存在',$ordercontrol->Error());  
				}
			}
			//若付款则执行退款操作
			if($orderinfo['paytype'] != 0){ 
		        //插入退款信息状态	 	 
				$data['mid']  	  = $this->mid;
				$data['orderid']  = $orderinfo['id'];
				$data['shopid']   = $orderinfo['shopid'];
				$data['uid'] 	  = $member['uid'];
				$data['username'] = $member['username'];
				$data['status']   = 0;
				$data['addtime']  = time();
				$data['cost']     = $orderinfo['allcost'];
				$data['admin_id'] = $orderinfo['admin_id'];
				$data['type']     = 1;
			    $data['reason']   = '小程序退款'; //退款原因
			    $data['content']  = '小程序退款';//退款详细内容说明
				$this->mysql->insert(Mysite::$app->config['tablepre'].'drawbacklog',$data);   
				$udata['is_reback'] = 1;
				$this->mysql->update(Mysite::$app->config['tablepre'].'order',$udata,"id='".$orderinfo['id']."'"); 
				$ordCls = new orderclass();
				$ordCls->writewuliustatus($orderinfo['id'],13,$orderinfo['paytype']);  // 管理员 操作配送发货

				$orderinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where  id = ".$orderid.""); 
				//是否可以退款
				if($orderinfo['is_reback'] > 0){
				   	if(empty($orderinfo))  $this->return_json(self::RET_CODE_ERR_UNKNOWN,'订单不存在'); 
					$drawbacklog = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."drawbacklog where orderid=".$orderid." order by  id desc  limit 0,2");
					if(empty($drawbacklog)) 		$this->return_json(self::RET_CODE_ERR_UNKNOWN,'订单退款信息不存在');  
					if($drawbacklog['status'] != 0) $this->return_json(self::RET_CODE_ERR_UNKNOWN,'订单退款操作不能完成');  
					if($orderinfo['status'] > 2)    $this->return_json(self::RET_CODE_ERR_UNKNOWN,'订单不能退款');  
					
					$params = array();
					$params ['mid'] = $this->mid;
					$params ['prj_id'] = 1;
					$params ['order_id'] = $orderinfo['uni_order_id'];
					$params ['total_amount'] = $orderinfo['allcost'] *100;
					$params ['refund_amount'] = $orderinfo['allcost'] *100;
					$params ['remarks'] = "订餐系统退款";
					
					$refundOk = false;
					if (api_post_refund ( $params, $return_code, $return_msg, $return_data )) {
						if ($return_code==1) {
							$refundOk = true;
						}
					}
					if ($refundOk) {
						$data['type'] = 1;//
					    $this->mysql->update(Mysite::$app->config['tablepre'].'drawbacklog',$data,"id='".$drawbacklog['id']."'");
						  
						//超管退款功能移动至此by yanyh
			   	       	$drawbacklog = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."drawbacklog where orderid=".$orderid." order by  id desc  limit 0,2");
			   	       	if(empty($drawbacklog)) 		 $this->return_json(self::RET_CODE_ERR_UNKNOWN,'订单退款信息不存在');   
			   	       	if($drawbacklog['status'] != 0)  $this->return_json(self::RET_CODE_ERR_UNKNOWN,'订单退款操作不能完成');  
					   	if($orderinfo['is_reback'] == 2) $this->return_json(self::RET_CODE_ERR_UNKNOWN,'订单已退款成功不能重复操作');  
			   	       	$arr['is_reback'] = 2;//订单状态
			   	       	$arr['status']    = 4;
			   	        $this->mysql->update(Mysite::$app->config['tablepre'].'order',$arr,"id='".$orderid."'");
			   	       	//$data['bkcontent'] = IReq::get('reasons');
			   	       	$data['status']   = 1;//
					   	$data['admin_id'] = ICookie::get('adminuid') ;
			   	       	$this->mysql->update(Mysite::$app->config['tablepre'].'drawbacklog',$data,"id='".$drawbacklog['id']."'");   
					
					   	$this->mysql->delete(Mysite::$app->config['tablepre'].'orderps',"orderid = '$orderid' and status != 3");
			   	       	$ordCls = new orderclass();
					   	$ordCls->writewuliustatus($orderinfo['id'],14,$orderinfo['paytype']);  // 管理员退款给用户 物流信息
			             
						$this->return_json(self::RET_CODE_OK,'success',$orderid);  
					 
					}else {
						$this->return_json(self::RET_CODE_OK,'退款失败',$return_msg);  
					}
				}   
	        	$this->return_json(self::RET_CODE_OK,'success',$orderid); 
	        }

		}else{
			$this->return_json(self::RET_CODE_ERR_UNKNOWN,'订单不存在');   
		}
	}

	/**
    * 我的订单状态接口 
    * @para   int    $shopid      门店id
    * @para   int    $goodsid      
    * @return data 成功返回，失败返回错误
    */
    function orderstatus(){

    	$mid    = $this->mid;
		$orderid = intval(IReq::get('orderid'));
	
		$orderstatus = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."orderstatus where   orderid = ".$orderid." order by addtime asc limit 0,10 ");
		foreach ($orderstatus as $k=>$v){
			$orderstatus[$k]['addtime'] = date('Y-m-d H:i:s',$v['addtime']);
		}
		$data['orderstatus'] = $orderstatus;

		$this->return_json(self::RET_CODE_OK,'success',$data);  
	}


	/**
    * 我的订单详情接口 
    * @para   int    $shopid      门店id
    * @para   int    $goodsid      
    * @return data 成功返回，失败返回错误
    */
    function orderdetail(){
    	$openid = IReq::get('openid');//
    	$mid    = $this->mid;
    	$member = $this->getmember($mid,$openid);  
     
		$orderid = intval(IReq::get('orderid'));
	
		if(!empty($orderid)){
	  	 
	     	$order = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where buyeruid='".$member['uid']."' and id = ".$orderid."");   	     	
	     	if($order['paytype'] == 1 && $order['paystatus'] == 0 && $order['status'] < 3){
	     		//$this->syncOrderPayStatus($order);
	     		$order = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where buyeruid='".$member['uid']."' and id = ".$orderid."");
	     	}

	     	if(!empty($order)){ //有订单
                $scoretocost 		= Mysite::$app->config['scoretocost'];
                $order['scoredown'] = $order['scoredown']/$scoretocost;//抵扣积分
	     	    $order['ps'] 		= $order['shopps'];
	     	    // 超市商品总价	 超市配送配送	shopcost 店铺商品总价	shopps 店铺配送费	pstype 配送方式 0：平台1：个人	bagcost  
       	     	$orderdet = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."orderdet where order_id='".$order['id']."'");  
 
	     	    $order['addtime'] 			= date('Y-m-d H:i:s',$order['addtime']);
	     	    $order['posttime'] 			= date('Y-m-d H:i:s',$order['posttime']);
	     	  
	     	   	$data['order'] 		= $order;
	           	$data['goodslist'] 	= $orderdet;
 	
	     	 	$this->return_json(self::RET_CODE_OK,'success',$data);  
	       	}
	  	}

  		$data['order'] = '';
    	$this->return_json(self::RET_CODE_OK,'success',$data);  
	   
	}
	/**
    * 搜索接口 
    * @para   int    $shopid      门店id
    * @para   int    $goodsid      
    * @return data 成功返回，失败返回错误
    */
	function search(){  //搜索 商家和商品页面
    	$mid    = $this->mid;
        $search = $this->mysql->select_one("select searchwords from ".Mysite::$app->config['tablepre']."system_config where mid = ".$mid." ");
      	$data['searchwords'] =  unserialize($search['searchwords']); 
    	$this->return_json(self::RET_CODE_OK,'success',$data);  
	}

	/**
    * 搜索详情接口 
    * @para   int    $shopid      门店id
    * @para   int    $goodsid      
    * @return data 成功返回，失败返回错误
    */
	function searchresult(){ 	 	
		$openid = IReq::get('openid');//
    	$mid    = $this->mid;
    	$member = $this->getmember($mid,$openid);  

		$searchname = IFilter::act(IReq::get('searchname'));
		$uid 		= $member['uid'];
	
		$cxsignlist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodssign where type='cx' order by id desc limit 0, 100");
		$cxarray  =  array();
		foreach($cxsignlist as $key=>$value){
		   $cxarray[$value['id']] = $value['imgurl'];
		}
	
		/* 搜索店铺 结果  */
		$where = '';  
        $shopsearch = IFilter::act(IReq::get('searchname')); 
        $shopsearch = urldecode($shopsearch); 
        if(!empty($shopsearch)) $where=" and shopname like '%".$shopsearch."%' "; 
            	
	    $pageinfo = new page();
	    $pageinfo->setpage(intval(IReq::get('page'))); 
					  
		$tempdd = array();
		$tempdd[] =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shop where is_pass = 1  and is_open = 1 and endtime > ".time()."  ".$where." ");		
	 
	    $nowhour = strtotime(date('H:i:s',time())); 
     	$templist = array();
       	$cxclass = new sellrule();  
	   	foreach($tempdd as $key=>$list){	   
            if(is_array($list)){
				foreach($list as $keys=>$values){  
								 
					if($values['id'] > 0){
						$templist111 = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shoptype where cattype = ".$values['shoptype']." and  parent_id = 0    order by orderid asc limit 0,1000"); 
						
						$attra = array();
						$attra['input'] = 0;
						$attra['img'] = 0;
						$attra['checkbox'] = 0; 
						foreach($templist111 as $key=>$vall){
						  	if($vall['type'] == 'input'){
						   		$attra['input'] =  $attra['input'] > 0?$attra['input']:$vall['id'];
						  	}elseif($vall['type'] == 'img'){
							 	$attra['img'] =  $attra['img'] > 0?$attra['img']:$vall['id'];
						  	}elseif($vall['type'] == 'checkbox'){
								$attra['checkbox'] =  $attra['checkbox'] > 0?$attra['checkbox']:$vall['id'];
						  	}
						} 
						
						$shopdet = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast where  shopid = ".$values['id']."   ");
					 
						$values = array_merge($values,$shopdet);
					
						$values['shoplogo'] = empty($values['shoplogo'])? Mysite::$app->config['imgserver'].Mysite::$app->config['shoplogo']:Mysite::$app->config['imgserver'].$values['shoplogo'];
				  	/*	$checkinfo = $this->shopIsopen($values['is_open'],$values['starttime'],$values['is_orderbefore'],$nowhour); 
				  		$values['opentype'] = $checkinfo['opentype'];
				  		$values['newstartime']  =  $checkinfo['newstartime']; */ 
				  		$attrdet = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shopattr where  cattype = ".$values['shoptype']." and shopid = ".$values['id']."");
				  		$cxclass->setdata($values['id'],1000,$values['shoptype']); 
				  
				 
				  
				  		$checkps = 	 $this->pscost($values,1,$areaid); 
				  		$values['pscost'] = $checkps['pscost'];
				  
				/*  		$lat = 0;
				  		$lng = 0;
					  	$mi = $this->GetDistance($lat,$lng, $values['lat'],$values['lng'], 1); 
						$tempmi = $mi;
				  		$mi = $mi > 1000? round($mi/1000,2).'km':$mi.'m';			  
						$values['juli'] = 		$mi;*/
				  
						$shopcounts = $this->mysql->select_one( "select count(id) as shuliang  from ".Mysite::$app->config['tablepre']."order	 where status = 3 and  shopid = ".$values['id']."" );

						if(empty( $shopcounts['shuliang']  )){
							$values['ordercount'] = 0;
						}else{
							$values['ordercount']  = $shopcounts['shuliang'];
						} 
							  
					  	$cxinfo = $this->mysql->getarr("select name,id,signid from ".Mysite::$app->config['tablepre']."rule where   shopid = ".$values['id']." and status = 1 and starttime  < ".time()." and endtime > ".time()." ");
				  		$values['cxlist'] = array();
				  
						foreach($cxinfo as $k1=>$v1){
							if(isset($cxarray[$v1['signid']])){
								 $v1['imgurl'] = $cxarray[$v1['signid']];
								 $values['cxlist'][] = $v1;
							}
				  		}
		 
					 	$values['attrdet'] = array();
					  	foreach($attrdet as $k=>$v){
						   	if($v['firstattr'] == $attra['input']){
								$values['attrdet']['input'] = $v['value'];
						   	}elseif($v['firstattr'] == $attra['img']){
								$values['attrdet']['img'][] = $v['value'];
						   	}elseif($v['firstattr'] == $attra['checkbox']){
								$values['attrdet']['checkbox'][] = $v['value'];
						   	} 
					  	}
		 
				  	$templist[] = $values;
					}
	   			} 
			}

		}
		$data['shopsearchlist']   = $templist ? $templist : null;
 
					
		/* 搜索商品列表 */
		$weekji 	 = date('w');
		$goodwhere   = '';  
        $goodssearch = IFilter::act(IReq::get('searchname')); 
        $goodssearch = urldecode($goodssearch); 

	    if(!empty($goodssearch)) $goodlistwhere=" and name like '%".$goodssearch."%' "; 

    		/*获取店铺*/
    		$pageinfo = new page();
    		$pageinfo->setpage(intval(IReq::get('page'))); 

			$list =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shop where is_pass = 1  and is_open = 1  and endtime > ".time()."  ".$goodwhere." ");		
				 	 	      
            $nowhour = strtotime(date('H:i:s',time())); 
            $goodssearchlist = array();
            $cxclass = new sellrule();
				   
            if(is_array($list)){
            	foreach($list as $keys=>$vatt){  		     
	            	if($vatt['id'] > 0){
						$detaa = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goods where shopid='".$vatt['id']."'  and shoptype = ".$vatt['shoptype']."  and    FIND_IN_SET( ".$weekji." , `weeks` )  ".$goodlistwhere."   order by good_order asc ");

						if(!empty($detaa)){
								 
							foreach ( $detaa as $keyq=>$valq ){

								$valq['img'] = $this->siteurl.$valq['img']; 
								if($valq['is_cx'] == 1){
								//测算促销 重新设置金额
									$cxdata = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goodscx where goodsid=".$valq['id']."  ");
									$newdata = getgoodscx($valq['cost'],$cxdata);
									
									$valq['zhekou'] = $newdata['zhekou'];
									$valq['is_cx'] = $newdata['is_cx'];
									$valq['cost'] = $newdata['cost'];
								}
						 
								$shopdet = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast where  shopid = ".$valq['shopid']."   ");
						 
						/*		$checkinfo = $this->shopIsopen($vatt['is_open'],$vatt['starttime'],$shopdet['is_orderbefore'],$nowhour); 
	            			    $valq['opentype'] = $checkinfo['opentype'];*/
								$temparray[] =$valq; 
								$vakk = $temparray;
							}
							
						}	

						$goodssearchlist = $vakk ? $vakk : null;	 
					}
                } 
            }
					
			$data['goodssearchlist']   = $goodssearchlist;

    		$this->return_json(self::RET_CODE_OK,'success',$data);  
 			
 
	}

		/**
    * api接口 
    * @para   int    $shopid      门店id
    * @para   int    $goodsid      
    * @return data 成功返回，失败返回错误
    */
   	function api(){  
  		$post = $_POST;  
  		$this->mysql->insert(Mysite::$app->config['tablepre']."giftbb",$post);
  		$id = $this->mysql->insertid();
  
  		$resurl = $post['url'].'web.php?id='.$id; 
  		$resjg  = '成功';

  		$data = ['code' => '1','jg' => $resjg , 'url'=>$resurl]; 
		print_r(json_encode($data));die;
  	}
  	function web(){
  		$id   = $_GET['id'];
  		$data = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."giftbb where id = '".$id."'" );
  	 	
  		$data = ['code' => '1','msg' => 'success' , 'data'=>$data]; 
		print_r(json_encode($data));die;
  	}

 	










 




}