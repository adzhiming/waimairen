<?php
/*
 	手表接口 method 方法   
 */
class method   extends baseclass
{  	
//    function __construct(){ 	  
//	$this->rqueststr = "90001";
//    }
    
    /*
    * 我的订单查出该手表绑定的桌台未支付的订单
    * @para   id 编号      tablename 桌台名     dno 订单号    addtime下单时间   allcost应收金额
    * @return json 成功返回该手表绑定的桌台未支付的订单
    */
    function getmyorderlist(){
        
        //$shopid = intval(IReq::get('shopid'));
        $sn = IReq::get('sn');  //手表SN
        $mid = $this->mid;
        $timestamp = IReq::get('timestamp');
        $securitykey = IReq::get('securitykey');
        //私密钥判断
        $this->securitykeymd5($sn,$timestamp,$securitykey);
        
        $orderlist = $this->mysql->getarr("select c.id as orderid,c.tableid,c.dno,c.addtime,c.allcost,b.name as tablename,c.buyername as nickname,c.wx_card_downcost,c.wx_membercard_name,c.wx_card_name from ".Mysite::$app->config['tablepre']."table_qrcode_set a, ".Mysite::$app->config['tablepre']."table b, ".Mysite::$app->config['tablepre']."order c where a.tableid = b.id and b.mid = c.mid 
        and b.shopid = c.shopid and c.paystatus=0 and c.is_preeat=1 and b.id = c.tableid and c.addtime >= UNIX_TIMESTAMP(date_sub(now(),interval 1 day)) and a.sn = '".$sn."' order by c.id desc");
        
        foreach($orderlist as $key=>$value)
        {
           
//                $cx = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."table where id = ".$value['tableid']." limit 1 ");
//                $value['tablename']  = $cx["name"];
                $orderlist[$key]["addtime"]  = date("Y-m-d H:i",$value["addtime"]);
                $orderlist[$key]["allcost"]  = $value["allcost"]*100;
                $orderlist[$key]["wx_card_downcost"]  = $value["wx_card_downcost"]*100;    //优惠总额
                $orderlist[$key]["wx_membercard_name"]  = !$value["wx_membercard_name"]? "未使用会员卡" : $value["wx_membercard_name"] ;   //会员卡名
                $orderlist[$key]["wx_card_name"]  =  !$value["wx_card_name"]? "未使用优惠券" : $value["wx_card_name"] ;   //优惠券名
                //$order[] = $value;
        }

            if(empty($orderlist)){
                $message = "没有订单";
                $code = 0;
            }else{
                $code =0;//成功
                $message = "success";
            }
       
        
        $data = ['code' => $code,'message' =>$message , 'data' => $orderlist];
        print_r(json_encode($data));
	//return json_encode($data);
    }
    
    /*
    * 其他订单查出未绑定手表未支付的订单
    * @para   id 编号      tablename 桌台名     dno 订单号 
    * @return json 成功返回所有未支付的订单
    */
    function getotherorderlist(){
        
        //$shopid = intval(IReq::get('shopid'));
        $sn = IReq::get('sn');  //手表SN
        $mid = $this->mid;
        $timestamp = IReq::get('timestamp');
        $securitykey = IReq::get('securitykey');
        //私密钥判断
        $this->securitykeymd5($sn,$timestamp,$securitykey);
        
        $shopid = $this->getStoreidBySN($sn);//通过SN获取shopid
 
        $orderlist = $this->mysql->getarr("select c.id as orderid,c.tableid,c.dno,c.addtime,c.allcost,b.name as tablename,c.wx_card_downcost,c.wx_membercard_name,c.wx_card_name from ".Mysite::$app->config['tablepre']."table b, ".Mysite::$app->config['tablepre']."order c where b.mid = c.mid and b.shopid = c.shopid and b.id = c.tableid
        and c.paystatus=0 and c.is_preeat=1 and c.addtime >= UNIX_TIMESTAMP(date_sub(now(),interval 1 day)) and b.shopid = '".$shopid."' and b.id not in (select tableid from ".Mysite::$app->config['tablepre']."table_qrcode_set where sn = '".$sn."') order by c.id desc");
         
        foreach($orderlist as $key=>$value)
        {
           
                $orderlist[$key]["addtime"]  = date("Y-m-d H:i",$value["addtime"]);
                $orderlist[$key]["allcost"]  = $value["allcost"]*100;
                $orderlist[$key]["wx_card_downcost"]  = $value["wx_card_downcost"]*100;    //优惠总额
                $orderlist[$key]["wx_membercard_name"]  = empty($value["wx_membercard_name"])? "未使用会员卡" : $value["wx_membercard_name"] ;   //会员卡名
                $orderlist[$key]["wx_card_name"]  =  empty($value["wx_card_name"])? "未使用优惠券" : $value["wx_card_name"] ;   //优惠券名
        }
        //$orderlist = $order;
        
        if(empty($shopid)){
            $message = "手表没有绑定";
            $code = 1;//失败
        }else{
            $code =0;//成功
            $message = "success";
        }
        
        $data = ['code' => $code,'message' =>$message , 'data' => $orderlist];
         print_r(json_encode($data));
	//return json_encode($data);
    }
    
    /*
    *通过接口获取店铺id
   *@author lzm
   *add time 2017-05-26
   **/
   function getStoreidBySN($sn){
   	$sn = $sn;
   	$sid = "";
   	$url = Mysite::$app->config['banklay_url']."/getSidBySN";
   	$arr = array();
   	$arr['sn'] = $sn;
   	$rs = doPost($url,$arr);
   	if(!empty($rs)){
   		$sid_arr = json_decode($rs,true);
   		$storeid = $this->mysql->select_one("select id from ".Mysite::$app->config['tablepre']."shop where sid = '".$sid_arr['sid']."' ");
   		$sid = $storeid['id'];
   	}
   	return $sid;
   }
    
    /*
    * 更新订单接口 现金支付调用
    * @para
    * @return json 
    */
    function updateorderstatus(){
        $mid = $this->mid;
        $orderid = intval(IReq::get('orderid'));
        $paytype = IReq::get('paytype');
        $allcost = IReq::get('allcost');
        $timestamp = IReq::get('timestamp');
        $sn = IReq::get('sn');  //手表SN
        $securitykey = IReq::get('securitykey');
        //私密钥判断
        $this->securitykeymd5($sn,$timestamp,$securitykey);
        
        if($paytype=="X"){//现金支付
            $paytype = 5;
            $paytype_name = 'X';
        }
        
        $order = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where id = ".$orderid." ");
        
        if(!$order['id']){
            $code = 1;//失败
            $message = "没有查询到订单"; 
            $data = ['code' => $code,'message' =>$message,'data' =>''];
            print_r(json_encode($data));
            return;
        }elseif($order['paystatus']==1){
            $code = 2;//失败
            $message = "此订单已经支付过了";
            $data = ['code' => $code,'message' =>$message,'data' =>''];
            print_r(json_encode($data));
            return;
        }else{
            
            //$arr['allcost'] = $allcost/100;  //应收金额,转成元
            $arr['paytype'] = $paytype;         //0=>'货到支付',1=>'在线支付'
            $arr['paytype_name'] = $paytype_name;   //支付方式，W微信，Z支付宝，X现金
            $arr['paystatus'] = 1;        //表示已支付
            $arr['status'] = 2;        //已完成，用户已消费
            $re = $this->mysql->update(Mysite::$app->config['tablepre'].'order',$arr,"id='".$orderid."' and mid='".$mid."' ");

            //插入订单状态表
            $udata['mid'] = $mid; 
            $udata['orderid'] = $orderid; 
            $udata['statustitle'] = "现金支付成功"; 
            $udata['ststusdesc'] = "订单已现金支付成功"; 
            $udata['addtime'] = strtotime($timestamp); 
            $this->mysql->insert(Mysite::$app->config['tablepre']."orderstatus",$udata);

            if($re){
                
                //更新远程消息
                $url = Mysite::$app->config['banklay_url']."/updateCallServiceStatus";
                $arr = array();
                $arr['mid'] = $mid;
                $arr['orderid'] = $orderid;
                $arr['sn'] = $sn;
                $rs = doPost($url,$arr);
                if($rs){
                    $code =0;//成功
                    $message = "支付成功";
                }else{
                    $code =3;
                    $message = "更新失败";
                }
                
                
                $data = ['code' => $code,'message' =>$message,'data' =>''];
                print_r(json_encode($data));
            }
            
        }
        
    }
    function hutest() {
    	$orderid=1379;
    	$mid="90001";
    	$paytype='Z';
    	$sn='weilay008';
    	$order = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where mid='".$mid."' and id = '".$orderid."'  limit 1 ");
    	 
          $params = array();
        $params ['mid'] = $this->mid;
        $params ['order_id'] =$order['uni_order_id'];
        $params ['pay_type'] = $paytype;
        $params ['sn'] = $sn;
        $params ['ret_url'] = IUrl::creatUrlEx($this->mid,'wxsite/payOver/orderid/'.$orderid);
        $params ['body'] = "手表支付";
        if ($paytype == 'W') {
        	$params ['trade_type'] = 'JSAPI';
        }
        if (api_post_qrcodePay( $params, $return_code, $return_msg, $return_data )) {
        	if ($return_code==1 && $return_data) {
        	       $code =0;//成功
           			$message = "获取支付二维码成功";
            		$dataArr = array('pay_code'=>$return_data['url'],'tx_no'=>$orderid,'mid'=>$mid);
            		$data = ['code' => $code,'message' =>$message , 'data' => $dataArr];
            		print_r(json_encode($data));
            		return;
        	}
        }
    }
    
    //私密钥判断
    function securitykeymd5($sn,$timestamp,$securitykey){
        
        $securitykey_local = strtoupper(MD5($timestamp.'weilay'.$sn));  //本地组合
        $securitykey = strtoupper($securitykey);
//        echo $securitykey_local;die;
        if($securitykey!=$securitykey_local){
            $code = 5;
            $message =  "密钥码不对！";
            $data = ['code' => $code,'message' =>$message , 'data' => ''];
            print_r(json_encode($data));die;
             
        }
    }
    
    /*
    * 手表二维码支付接口
    * @para
    * @return json 
    */
    function watchqrpay(){
        $mid = $this->mid;
        $orderid = intval(IReq::get('orderid'));
        $paytype = IReq::get('paytype');
        $allcost = IReq::get('totalamount');
        $sn = IReq::get('sn');
        $timestamp = IReq::get('timestamp');
        $securitykey = IReq::get('securitykey');
        $allcost = $allcost/100;  //转成元
        
        //私密钥判断
        $this->securitykeymd5($sn,$timestamp,$securitykey);
        
//         $tableqr = $this->mysql->select_one("select * from " . Mysite::$app->config['tablepre'] . "table_qrcode_set where sn='".$sn."' order by id desc");
//         if(empty($tableqr)){
//         	$message = "手表没有绑定";
//         	$code = 1;//失败
//         	$data = ['code' => $code,'message' =>$message , 'data' => array()];
//         	print_r(json_encode($data));
//         	return;
//         }
        $order = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where mid='".$mid."' and id = '".$orderid."'  limit 1 ");

        $params = array();
        $params ['mid'] = $this->mid;
        $params ['order_id'] =$order['uni_order_id'];
        $params ['pay_type'] = $paytype;
        $params ['sn'] = $sn;
        $params ['ret_url'] = IUrl::creatUrlEx($this->mid,'wxsite/payOver/orderid/'.$orderid);
        $params ['body'] = "手表支付";
        if ($paytype == 'W') {
        	$params ['trade_type'] = 'JSAPI';
        }
        if (api_post_qrcodePay( $params, $return_code, $return_msg, $return_data )) {
        	if ($return_code==1 && $return_data) {
        	       $code =0;//成功
           			$message = "获取支付二维码成功";
            		$dataArr = array('pay_code'=>$return_data['url'],'tx_no'=>$orderid,'mid'=>$mid);
            		$data = ['code' => $code,'message' =>$message , 'data' => $dataArr];
            		print_r(json_encode($data));
            		return;
        	}
        }
        $message = empty($return_msg)?'失败':$return_msg;
  
        $data = ['code' => 3,'message' =>$message , 'data' => array()];
        print_r(json_encode($data));
    }
    
    /*
    * 查询支付结果接口
    * @para
    * @return json 
    */
    function payresult(){
        $orderid = intval(IReq::get('orderid'));
//        $paytype = IReq::get('paytype');
        $sn = IReq::get('sn');
        $timestamp = IReq::get('timestamp');
        $securitykey = IReq::get('securitykey');
         //私密钥判断
        $this->securitykeymd5($sn,$timestamp,$securitykey);
        
        $order = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where id = '".$orderid."' limit 1 ");
        
//        	        $return_msg='';
//			$return_data= null;
//			$params =array();
//			$params ['order_id'] = $order['dno'];
//                        if (queryOrder ( $params, $return_code, $return_msg, $return_data )) {
//                           echo $return_msg;die;
//                        }
                        
        if(!$order){    
                $code =2;
                $message = "没有查询到订单";
        }elseif($order['paystatus']==1){
        	$totalAmount = $order['allcost']*100;
        	$downcost = $order['wx_card_downcost'] *100;
        	$code =0;//成功
        	$message = "支付成功";
        	$dataarr = array("memberDiscountAmt"=>0,"totalAmount"=>$totalAmount,"couponDiscountAmt"=>$downcost,"tx_no"=>$orderid);
        }elseif($order['paystatus']==0){    
                $code =7;
                $message = "支付中";
        }else{
            $message = "支付失败";
            $code = 1;//失败
        }
        
        $data = ['code' => $code,'message' =>$message , 'data' => $dataarr];
        print_r(json_encode($data));
    }
    
    /*
    * 查询菜单中的菜品
    * @para
    * @return json 
    */
    function getmenuname(){
        $orderid = intval(IReq::get('orderid'));
        $sn = IReq::get('sn');
        $timestamp = IReq::get('timestamp');
        $securitykey = IReq::get('securitykey');
         //私密钥判断
        $this->securitykeymd5($sn,$timestamp,$securitykey);
        
        $orderdet = $this->mysql->getarr("select a.goodsname,a.goodscost,a.goodscount,a.is_yh,b.img from ".Mysite::$app->config['tablepre']."orderdet a,".Mysite::$app->config['tablepre']."goods b,".Mysite::$app->config['tablepre']."order c where a.order_id = '".$orderid."' and a.goodsid=b.id and c.id=a.order_id ");
      
        foreach($orderdet as $key=>$value)
        {
            $orderdet[$key]["goodsname"]  = $value["goodsname"];
            $orderdet[$key]["goodscost"]  = $value["goodscost"]*100;
            $orderdet[$key]["goodscount"]  = $value["goodscount"];
            $orderdet[$key]["img"]  = Mysite::$app->config['siteurl'].$value["img"];
//            $orderdet[$key]["wx_card_downcost"]  = $value["wx_card_downcost"]*100;//优惠总额
//            $orderdet[$key]["wx_membercard_name"]  = $value["wx_membercard_name"];//会员卡名
//            $orderdet[$key]["wx_card_name"]  = $value["wx_card_name"];//优惠券名
        	
        }
                        
        if($orderdet){
        	$code =0;//成功
        	$message = "成功";
        	$dataarr = $orderdet;
        }else{
            $code =1;
            $message = "没有查询到菜单";
        }
        
        $data = ['code' => $code,'message' =>$message , 'data' => $dataarr];
        print_r(json_encode($data));
    }
    
    /*
    * 获取店铺经纬度接口
    * @para
    * @return json 
    */
    function getdistance() {
        $mid = $this->mid;
        $sn = IReq::get('sn');
        $timestamp = IReq::get('timestamp');
        $securitykey = IReq::get('securitykey');
        //私密钥判断
        $this->securitykeymd5($sn,$timestamp,$securitykey);
        
        //获取店铺 lat经度   lng纬度
       $info = $this->mysql->select_one("select c.mid,c.sid,a.name,b.sn,c.hitcarddistance,c.lat,c.lng from ".Mysite::$app->config['tablepre']."staff a, ".Mysite::$app->config['tablepre']."staff_qrcode_set b, ".Mysite::$app->config['tablepre']."shop c where a.id = b.staffid and c.sid = a.sid
   and c.mid='".$mid."' and b. sn = '".$sn."'");

        if(empty($info['sn'])){
            $message = "手表没有绑定";
            $code = 1;//失败
        }elseif(empty($info)){
            $message = "获取失败";
            $code = 2;//失败
        }else{
            $code =0;//成功
            $message = "获取成功";
            $dataarr = array('lat'=>$info['lat'],'lng'=>$info['lng'],'distance'=>$info['hitcarddistance']);
        }
        
        $data = ['code' => $code,'message' =>$message , 'data' => $dataarr];
        print_r(json_encode($data));
    }
    

//    function staffhitcard(){
//        $mid = $this->mid;
//        $sn = IReq::get('sn');
//        $distance = intval(IReq::get('distance'));    //距离
//        $hitcard = date("H:i",time());
//        $year = date("Y",time());
//        $month = date("m",time());
//        
//        $info = $this->mysql->select_one("select c.mid,c.sid,a.name,b.sn,c.is_longhitcard,c.hitcarddistance,c.id,a.applypost,a.shift_id from ".Mysite::$app->config['tablepre']."staff a, ".Mysite::$app->config['tablepre']."staff_qrcode_set b, ".Mysite::$app->config['tablepre']."shop c where a.id = b.staffid and c.sid = a.sid
//   and c.mid='".$mid."' and b. sn = '".$sn."'");
//    
//        //签到距离必须小于后台设置的距离才能打卡成功
//        if(!$info){
//             $code = 2;//失败
//             $message = "没有绑定手表";
//                    
//        }elseif($info['is_longhitcard']!=1 || intval($distance)>intval($info['hitcarddistance'])){
//            $message = "员工没有规定在指定范围内打卡";
//            $code = 1;//失败
//        }else{
//            
//            $pun = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."punchrecording where shopid='".$info['id']."' and name='".$info['name']."' limit 1 ");
//            $lasttime = date('d',$pun['lasttime']) ;  //最后打卡时间
//            $lasttime = preg_replace('/^0*/', '', $lasttime);  //把天前面的0去掉
//            $jgtime = $pun['lasttime']+60*60 ;    //间隔一个小时
//            $nowtime = date('d',time());              //当前时间
//            $nowtime = preg_replace('/^0*/', '', $nowtime);  //把天前面的0去掉
//            $hitcardarr = explode('|',$pun['hitcard']);
//            //var_dump($nowtime);die;
//            
//                $aa = $nowtime - count($hitcardarr);
//                if(count($hitcardarr)==1){//第一次打卡
//                    $count = $nowtime-1;
//                }else{//第N次后打卡
//                    $count =  $nowtime-count($hitcardarr);
//                }
//                
//                for ($i = 1; $i <$count; $i++) {
//                    array_push($hitcardarr, ' ');
//                  
//                }
//               
//                //echo count($hitcardarr).'-----'.$nowtime;die;
//                if($nowtime>=count($hitcardarr) && $aa>0){
//                  //echo "1";die;
//                  array_push($hitcardarr, $hitcard);
//                  $hitcardstr = implode("|",$hitcardarr);
//                  $nrr['hitcard'] = $hitcardstr;
//                  $nrr['lasttime'] = time(); 
//                  //var_dump($hitcardstr);die;
//                  $nrr['shift_id'] = $info['shift_id']; 
//                  $this->mysql->update(Mysite::$app->config['tablepre'].'punchrecording',$nrr,"id='".$pun['id']."' ");
//                }elseif(time()>$jgtime){   //当前时间要大于一个小时后才能再次打卡
//                  //echo "2";die;
//                   //echo date('Y-m-d H:i',$jgtime)."--------".date('Y-m-d H:i',time());die;
//                    if($nowtime>$lasttime){//第二天打卡要加上|
//                        $nrr['hitcard'] = $pun['hitcard'].'|'.$hitcard;
//                    }else{
//                        $nrr['hitcard'] = $pun['hitcard'].'-'.$hitcard;
//                    }
//                   
//                   $nrr['lasttime'] = time(); 
//                   $nrr['shift_id'] = $info['shift_id']; 
//                   $this->mysql->update(Mysite::$app->config['tablepre'].'punchrecording',$nrr,"id='".$pun['id']."' ");
//                }
//          
//               
//            
//            if(!$pun){
//                
//                array_push($hitcardarr, $hitcard);
//                $hitcardstr = implode("|",$hitcardarr);
//                $nrr['hitcard'] = $hitcardstr;
//                
//                //插入打卡记录
//                $udata['mid'] = $mid; 
//                $udata['sid'] = $info['sid']; 
//                $udata['name'] = $info['name']; 
//                $udata['shopid'] = $info['id']; 
//                $udata['shift_id'] = $info['shift_id']; 
//                $udata['applypost'] = $info['applypost']; 
//                $udata['hitcard'] = $nowtime==1 ? $hitcard:$hitcardstr; 
//                $udata['sn'] = $sn;
//                $udata['year'] = $year;
//                $udata['month'] = $month;
//                $udata['addtime'] = date('Y-m-d H:i',time());
//                $udata['lasttime'] = time(); 
//                $udata['status'] = 1; 
//                $this->mysql->insert(Mysite::$app->config['tablepre']."punchrecording",$udata);
//            }
//
//            $code =0;//成功
//            $message = "签到成功";
//        }
//        
//        //$dataarr = array('lat'=>$shop['lat'],'lng'=>$shop['lng']);
//        $data = ['code' => $code,'message' =>$message,'data'=>'' ];
//        print_r(json_encode($data));
//    }
    
    /*
    * 员工打卡接口
    * @para
    * @return json 
    */
    function staffhitcard(){
        $sn = IReq::get('sn');
        $timestamp = IReq::get('timestamp');
        $securitykey = IReq::get('securitykey');
        //私密钥判断
        //$this->securitykeymd5($sn,$timestamp,$securitykey);
        
        $distance = IReq::get('distance');    //距离
        $hitcard = date("H:i",time());
       
        $info = $this->mysql->select_one("select c.mid,c.sid,a.name,b.sn,c.is_longhitcard,c.hitcarddistance,c.id,a.applypost,a.shift_id from ".Mysite::$app->config['tablepre']."staff a, ".Mysite::$app->config['tablepre']."staff_qrcode_set b, ".Mysite::$app->config['tablepre']."shop c where a.id = b.staffid and c.sid = a.sid
    and b. sn = '".$sn."'");
        
        $pun = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."punchrecording where shopid='".$info['id']."' and sn='".$info['sn']."' order by id desc ");
        $jgtime = $pun['hitcard']+60*1 ;    //间隔一分钟后才能再次打卡
   
        //签到距离必须小于后台设置的距离才能打卡成功
        if(!$info){
             $code = 2;//失败
             $message = "没有绑定手表";
                    
        }elseif($info['is_longhitcard']!=1 || intval($distance)>intval($info['hitcarddistance'])){
            $message = "员工没有规定在指定范围内打卡";
            $code = 1;//失败
        }elseif(time()>$jgtime){
            
                
            //插入打卡记录
            $udata['mid'] = $info['mid'];
            $udata['sid'] = $info['sid']; 
            $udata['name'] = $info['name']; 
            $udata['shopid'] = $info['id']; 
            $udata['shift_id'] = $info['shift_id']; 
            $udata['applypost'] = $info['applypost']; 
            $udata['hitcard'] = time(); 
            $udata['sn'] = $sn;
            $udata['year'] = date('Y',time());
            $udata['month'] = date('m',time());
            $udata['addtime'] = date('Y-m-d H:i',time());
            $udata['status'] = 1; 
   
             $this->mysql->insert(Mysite::$app->config['tablepre']."punchrecording",$udata);

            $code =0;//成功
            $message = "签到成功";
        }else{
            $code =3;//成功
            $message = "你刚已经打过卡了！";
        }

     
        //$dataarr = array('lat'=>$shop['lat'],'lng'=>$shop['lng']);
        $data = ['code' => $code,'message' =>$message,'data'=>'' ];
        print_r(json_encode($data));

    }

    /**
    * 获取打赏页面二维码
    *@return  return_type
    *@author yanyh
    *@date 2017年6月26日
    */
    public  function createRewardQcode(){
        $sn = IReq::get('sn');
        $mid = $this->mid;
        
        //私密钥判断
        $timestamp = IReq::get('timestamp');
        $securitykey = IReq::get('securitykey');
        $this->securitykeymd5($sn,$timestamp,$securitykey);
        if(!empty($sn)){
            $info = $this->mysql->select_one("select  * from ".Mysite::$app->config['tablepre']."staff_qrcode_set  where sn = '".$sn."' ");

            if(true==$info && $info !=null && !empty($info)){
                $payment_no = $info['mid'].time() . rand(100, 999) . rand(10000, 99999);
                $data['pay_code'] = "http://".$_SERVER["SERVER_NAME"]."/index.php/".$mid."/wxsite/weixin_reward_index/sn/".$sn."/payment_no/".$payment_no;
                $data['payment_no'] = $payment_no;
                $data['mid'] =$mid;
                $code = 0;
                $message = 'OK';
            }else{
                $data['mid'] ="";
                $data['pay_code'] = "";
                $data['payment_no'] ="";
                $code = 1;
                $message = '设备未绑定员工';
            }
        }else{
            $data['mid'] ="";
            $data['pay_code'] ="";
            $data['payment_no'] ='';
            $code = 1;
            $message = '获取sn参数失败';
        }
        $data = ['code'=>$code,'message'=>$message,'data'=>$data];  
        print_r(json_encode($data));
 
     }

    /**
     * 获取打赏结果信息
     *
     *@return  return_type
     *@author yanyh
     *@date 2017年6月27日
     */
    public function getRewardResult()
    {
        $sn = IReq::get('sn');
        $payment_no = IReq::get('payment_no');
        $mid = $this->mid;
        
        //私密钥判断
        $timestamp = IReq::get('timestamp');
        $securitykey = IReq::get('securitykey');
        $this->securitykeymd5($sn,$timestamp,$securitykey);
        
        $rs = $this->mysql->select_one("select  * from ".Mysite::$app->config['tablepre']."weixin_reward  where SN = '".$sn."' and payment_time = '".$payment_no."' ");
 
        if (false !== $rs) {
            if ($rs['successed'] == 'Y') {
                $code = 0;
                $message = '已评价，已打赏';
                $data = array(
                    'evaluate' => true,
                    'totalAmount' => empty($rs['fee'])?0:$rs['fee']*100
                );
            } else {
                if ($rs['comment'] != '') {
                    $code = 0;
                    $message = '已评价，未打赏';
                    $data = array(
                        'evaluate' => true,
                        'totalAmount' => empty($rs['fee'])?0:$rs['fee']*100
                    );
                } else {
                    $code = 1;
                    $message = '打赏中';
                    $data = array(
                        'evaluate' => false,
                        'totalAmount' => empty($rs['fee'])?0:$rs['fee']*100
                    );
                }
            }
        } else {
            //没有数据可能是打赏中可能是参数传错
            $code = 1;
            $message = '打赏中';
            $data = array(
                'evaluate' => false,
                'totalAmount' => 0
            );
        }
        $data = ['code'=>$code,'message'=>$message,'data'=>$data];  
        print_r(json_encode($data));
 
    }


    /**
     * 拉取手表当天的打赏记录
     * 
     *@return  return_type
     *@author yanyh
     *@date 2017年06月28日
     */
    public function getWatchTodayTips() {
        $sn = IReq::get('sn');
        $start = IReq::get('start');
        $end = IReq::get('end');
        $mid = $this->mid;
        
        //私密钥判断
        $timestamp = IReq::get('timestamp');
        $securitykey = IReq::get('securitykey');
        $this->securitykeymd5($sn,$timestamp,$securitykey);

        if (!$start) $start = date('Y-m-d'). ' 00:00:00';        
        if (!$end)   $end   = date('Y-m-d'). ' 23:59:59';
 
        $tips = $this->mysql->getarr("select id, AID, SN, MID, Operator, SID, fee, fee as totalamount, if(comment='', 0, 1) as iscomment, start, comment, openid, successed, payment_no, Remarks, TimeEnd, TxTime,  DATE_FORMAT(time, '%H:%i') as thattime, Refno, time, nickname, sex, province, city, headimgurl from ".Mysite::$app->config['tablepre']."weixin_reward  where SN = '".$sn."' and MID = '".$mid."' and time between '".$start."' and '".$end."' ");

        if (false !== $tips) {

            foreach ($tips as $key => $value) {
                $tips[$key]['totalamount'] =  $value['totalamount']*100;
            }


            $code = 0;
            $message = 'OK';
            $data = $tips;
            $data = ['code'=>$code,'message'=>$message,'data'=>$data];  
            print_r(json_encode($data));die;
            
        }

        $code = 1;
        $message = '查询失败';
        $data = array();
        $data = ['code'=>$code,'message'=>$message,'data'=>$data];  
        print_r(json_encode($data));die;
    }
    

    /**
    * 手表今日营业额接口 
    * @para   int    $mid      商户id
    * @return json 成功返回商户列表，失败返回错误
    */
    function todayTotal(){ 

        //获取shopid
        $sn     = IReq::get('sn');  //手表SN
        $timestamp = IReq::get('timestamp');
        $securitykey = IReq::get('securitykey');
        //私密钥判断
        $this->securitykeymd5($sn,$timestamp,$securitykey);
        
        $shopid = $this->getStoreidBySN($sn);
        if(empty($sn)){
            $data = ['code' => '-1','message' =>'参数错误','data'=>'']; 
            print_r(json_encode($data));die;
        }
   

        $t = time();
        $stime = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t));
        $etime = mktime(23,59,59,date("m",$t),date("d",$t),date("Y",$t)); 

        $where2 = 'and addtime > '.$stime.' and addtime < '.$etime;
        //weixin
        $weixin = $this->mysql->select_one("select  count(id) as shuliang,sum(allcost) as allcost,sum(shopcost) as shopcost from ".Mysite::$app->config['tablepre']."order  where shopid = '".$shopid."' and paytype =1 and shopcost > 0 and status = 3 ".$where2." order by id asc  limit 0,1000");
        //cart
        $card   = $this->mysql->select_one("select count(id) as shuliang,sum(allcost) as allcost,sum(shopcost) as shopcost  from ".Mysite::$app->config['tablepre']."order  where shopid = '".$shopid."' and paytype =2  and shopcost > 0 and status = 3 ".$where2."   order by id asc  limit 0,1000");
        //alipay
        $alipay   = $this->mysql->select_one("select count(id) as shuliang,sum(allcost) as allcost,sum(shopcost) as shopcost  from ".Mysite::$app->config['tablepre']."order  where shopid = '".$shopid."' and paytype =6  and shopcost > 0 and status = 3 ".$where2."   order by id asc  limit 0,1000");
        //cash
        $cash   = $this->mysql->select_one("select count(id) as shuliang,sum(allcost) as allcost,sum(shopcost) as shopcost  from ".Mysite::$app->config['tablepre']."order  where shopid = '".$shopid."' and paytype =5  and shopcost > 0 and status = 3 ".$where2."   order by id asc  limit 0,1000");
        
        $total['wechat']      = $weixin['allcost']*100;
        $total['chuzhika']    = $card['allcost']*100;
        $total['alipay']      = $alipay['allcost']*100;
        $total['bask']        = 0; //打赏暂未有值
        $total['cash']        = $cash['allcost']*100;
        //$total['allsum']      = $weixin['allcost'] + $card['allcost'] + $alipay['allcost']+ $cash['allcost'];

        $data = ['code' => '0','message' =>'OK' , 'data'=>$total]; 
        print_r(json_encode($data));die;

    }

    /**
    * 手表一周营业额接口 
    * @para   int    $mid      商户id
    * @return json 成功返回商户列表，失败返回错误
    */
    function weekTotal(){

        //获取shopid
        $sn     = IReq::get('sn');  //手表SN
        $timestamp = IReq::get('timestamp');
        $securitykey = IReq::get('securitykey');
        //私密钥判断
        $this->securitykeymd5($sn,$timestamp,$securitykey);
        
        $shopid = $this->getStoreidBySN($sn);
        if(empty($sn)){
            $data = ['code' => '-1','message' =>'参数错误','data'=>'']; 
            print_r(json_encode($data));die;
        }
        
        $datelist = [];
        $weeklist = [];
        $stime    = [];
        for($i=7;$i>0;$i--){
            $day = -$i.' day';
            $datelist[] = date("Y-m-d",strtotime($day));   
            $weeklist[] = date("w",strtotime($day));
            $stime[]    = date(strtotime($day));
        }
        //返回星期处理
        foreach ($weeklist as $key => $value) {
            switch ($value) {
                case '0':
                    $weeklist[$key] = '周日';
                    break;
                 case '1':
                     $weeklist[$key] = '周一';
                    break; 
                case '2':
                     $weeklist[$key] = '周二';
                    break; 
                case '3':
                     $weeklist[$key] = '周三';
                    break; 
                case '4':
                     $weeklist[$key] = '周四';
                    break; 
                case '5';
                     $weeklist[$key] = '周五';
                    break;
                case '6';
                     $weeklist[$key] = '周六';
                    break;
               
                default:
                    # code...
                    break;
            }
        }

        //统计数据
        $weektotal = [];
        foreach ($stime as $key => $value) {
            $starttime = $value;
            $endtime   = $value+86400;

            $where2 = 'and addtime > '.$starttime.' and addtime < '.$endtime;
            //weixin
            $weixin = $this->mysql->select_one("select  count(id) as shuliang,sum(allcost) as allcost,sum(shopcost) as shopcost from ".Mysite::$app->config['tablepre']."order  where shopid = '".$shopid."' and paytype =1 and shopcost > 0 and status = 3 ".$where2." order by id asc  limit 0,1000");
            //cart
            $card   = $this->mysql->select_one("select count(id) as shuliang,sum(allcost) as allcost,sum(shopcost) as shopcost  from ".Mysite::$app->config['tablepre']."order  where shopid = '".$shopid."' and paytype =2  and shopcost > 0 and status = 3 ".$where2."   order by id asc  limit 0,1000");
            //alipay
            $alipay = $this->mysql->select_one("select count(id) as shuliang,sum(allcost) as allcost,sum(shopcost) as shopcost  from ".Mysite::$app->config['tablepre']."order  where shopid = '".$shopid."' and paytype =6  and shopcost > 0 and status = 3 ".$where2."   order by id asc  limit 0,1000");
            //cash
            $cash   = $this->mysql->select_one("select count(id) as shuliang,sum(allcost) as allcost,sum(shopcost) as shopcost  from ".Mysite::$app->config['tablepre']."order  where shopid = '".$shopid."' and paytype =5  and shopcost > 0 and status = 3 ".$where2."   order by id asc  limit 0,1000");

            $weektotal[]  = $weixin['allcost']*100 + $card['allcost']*100 + $alipay['allcost']*100+ $cash['allcost']*100;

        }
    

        $data = [$weeklist,$weektotal,$datelist];

        $data = ['code' => '0','message' =>"OK" , 'data'=>$data]; 
        print_r(json_encode($data));die;

    }


    /**
    * 手表今日明细接口 
    * @para   int    $mid      商户id
    * @return json 成功返回商户列表，失败返回错误
    */
    function todayTotalDetail(){ 

        //获取shopid
        $sn     = IReq::get('sn');  //手表SN
        $timestamp = IReq::get('timestamp');
        $securitykey = IReq::get('securitykey');
        //私密钥判断
        $this->securitykeymd5($sn,$timestamp,$securitykey);
        
        $shopid = $this->getStoreidBySN($sn);
        if(empty($sn)){
            $data = ['code' => '-1','message' =>'参数错误','data'=>'']; 
            print_r(json_encode($data));die;
        }

        $t = time();
        $stime = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t));
        $etime = mktime(23,59,59,date("m",$t),date("d",$t),date("Y",$t)); 

        $where2 = 'and addtime > '.$stime.' and addtime < '.$etime;
        //订单明细
        $orderList = $this->mysql->getarr("select allcost as totalamount,addtime as thattime,paytype_name as paytype,paystatus as paystate from ".Mysite::$app->config['tablepre']."order  where shopid = '".$shopid."' and paytype_name!='' and shopcost > 0 and status = 3 ".$where2." order by id asc  limit 0,1000");

        foreach ($orderList as $key => $value) {
            if($value['paystate'] == 1){
                $orderList[$key]['paystate'] = 0;
            }
            if($value['paytype'] == 'M'){
                $orderList[$key]['paytype'] = 'CHUZHIKA';
            }
            $orderList[$key]['thattime'] = date('H:i',$orderList[$key]['thattime']);
            $orderList[$key]['totalamount'] = $orderList[$key]['totalamount']*100;
        }
    
        $data = ['code' => '0','message' =>'OK' , 'data'=>$orderList]; 
        print_r(json_encode($data));die;
    }

    /**
    * 排号桌子类型列表
    * @para   
    * @return json 成功返回列表，失败返回错误
    */
    function LineupList(){ 
        $sn     = IReq::get('sn');  //手表SN
        
        //私密钥判断
        $timestamp = IReq::get('timestamp');
        $securitykey = IReq::get('securitykey');
        $this->securitykeymd5($sn,$timestamp,$securitykey);
        
        //获取shopid
        $shopid = $this->getStoreidBySN($sn);
        
        $phszlist = $this->mysql->getarr("select id as phsz_id,dlname from ".Mysite::$app->config['tablepre']."table_phsz  where shopid='".$shopid."'  and status=1 order by id desc");
        
        if(empty($phszlist)){
            $message = "获取失败";
            $code = 2;//失败
        }else{
            $code =0;//成功
            $message = "获取成功";
        }
        
        $data = ['code' => $code,'message' =>$message , 'data' => $phszlist];
        print_r(json_encode($data));
    }
    
    /**
    * 正在排号的客人队列列表
    * @para   
    * @return json 成功返回列表，失败返回错误
    */
    function KrdlList(){ 
        $sn     = IReq::get('sn');  //手表SN
        
        //私密钥判断
        $timestamp = IReq::get('timestamp');
        $securitykey = IReq::get('securitykey');
        $this->securitykeymd5($sn,$timestamp,$securitykey);
        
        //获取shopid
        $shopid = $this->getStoreidBySN($sn);
        $phsz_id = IReq::get('phsz_id');
        
        $info = $this->mysql->select_one("select a.name from ".Mysite::$app->config['tablepre']."staff a, ".Mysite::$app->config['tablepre']."staff_qrcode_set b where a.is_rownumber=1 and a.id = b.staffid and b. sn = '".$sn."'");
      
        if(!$info){
            $message = "此员工不能操作排号功能";
            $code = 3;//失败
        }else{
            
                $krdllist = $this->mysql->getarr("select id,qhms,wxname,start_queue_time,guest_numer,count(id) as total from ".Mysite::$app->config['tablepre']."table_krdl where phsz_id=".$phsz_id." and status=1 and shopid='".$shopid."' and to_days(addtime) = to_days(now()) order by id desc");
        
                if(empty($krdllist)){
                    $message = "当前队列为空";
                    $code = 2;//失败
                }else{
                    $code =0;//成功
                    $message = "获取成功";
                }

                foreach ($krdllist as $key => $value) {
                    $krdllist[$key]['start_queue_time'] = date('H:i',$value['start_queue_time']);
                    $krdllist[$key]['guest_numer'] = $value['guest_numer']."人";
                    
                    if($value['total']){
			$krdllist[$key]['total'] = $value['total'] - 1;
                    }else{
                        $krdllist[$key]['total'] = 0;
                    }
                }
            
        }
        
        $data = ['code' => $code,'message' =>$message , 'data' => $krdllist];
        print_r(json_encode($data));
    }
    
    /**
    * 正在排号的客人详情
    * @para   
    * @return json 成功返回列表，失败返回错误
    */
//    function KrdlDetail(){ 
//        $sn     = IReq::get('sn');  //手表SN
//        
//        //私密钥判断
//        $timestamp = IReq::get('timestamp');
//        $securitykey = IReq::get('securitykey');
//        $this->securitykeymd5($sn,$timestamp,$securitykey);
//        
//        //获取shopid
//        $shopid = $this->getStoreidBySN($sn);
//        $krdl_id = IReq::get('krdl_id');
//        
//        $dataone = $this->mysql->getarr("select id,qhms,wxname,start_queue_time,guest_numer from ".Mysite::$app->config['tablepre']."table_krdl where id=".$krdl_id);
//        
//        foreach ($dataone as $key => $value) {
//            $dataone[$key]['start_queue_time'] = date('Y-m-d H:i',$value['start_queue_time']);
//            $dataone[$key]['guest_numer'] = $value['guest_numer']."人";
//        }
//        
//        if(empty($dataone)){
//            $message = "获取失败";
//            $code = 2;//失败
//        }else{
//            $code =0;//成功
//            $message = "获取成功";
//        }
//        
//        $data = ['code' => $code,'message' =>$message , 'data' => $dataone];
//        print_r(json_encode($data));
//    }
    
    /**
    * 更新排队客人的排号状态
    * @para   
    * @return json 成功返回列表，失败返回错误
    */
    function UpdatekrdlStatus(){ 
        $sn     = IReq::get('sn');  //手表SN
        
        //私密钥判断
//        $timestamp = IReq::get('timestamp');
//        $securitykey = IReq::get('securitykey');
//        $this->securitykeymd5($sn,$timestamp,$securitykey);
        
        //获取shopid
        $shopid = $this->getStoreidBySN($sn);
        $id = IReq::get('id');
        $status = IReq::get('status');
        
        //通知
        if($status==5){
            $is_notice =1;
        }
        
        if($status){
             $data['status'] = $status;
        }elseif($is_notice){
             $data['is_notice'] = $is_notice; 
        }
	$re = $this->mysql->update(Mysite::$app->config['tablepre'].'table_krdl',$data,"id='".$id."'");
        
        if($re){
            if($status==2){
                $message = "入号成功";
                $code = 0;
                
                
				$phsz = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."table_krdl  where id='".$id."' ");
				$krdl_info = $this->mysql->select_one("select b.uid,b.openid,c.tqtzrs,a.id,a.is_notice from ".Mysite::$app->config['tablepre']."table_krdl a,".Mysite::$app->config['tablepre']."wxuser b,".Mysite::$app->config['tablepre']."table_phsz c where a.uid=b.uid and a.shopid='".$shopid."' and a.status=1 and a.phsz_id=".$phsz["phsz_id"]." and to_days(a.addtime) = to_days(now()) order by a.id asc");
				
                                //已入号的要通知下一位,先来的先通知
				if($krdl_info){
                                    sendCustomMsg($this->mid, $this->SendWxMsg($krdl_info['uid']),$krdl_info['openid']);
                                    //通知计数
                                    $tdata['is_notice'] = $krdl_info['is_notice'] +1;
				    $this->mysql->update(Mysite::$app->config['tablepre'].'table_krdl',$tdata,"id='".$krdl_info['id']."'");
                                }

//				//提前通知n位,先来的先通知
                                if($krdl_info['tqtzrs']){
                                    $new_krdl_id = $krdl_info['id'] + $krdl_info['tqtzrs'];  //当前ID加上提前通知N位就能得到那个ID
                                    $newkrdl = $this->mysql->select_one("select b.uid,b.openid,a.is_notice,a.id from ".Mysite::$app->config['tablepre']."table_krdl a,".Mysite::$app->config['tablepre']."wxuser b where a.id=".$new_krdl_id." and a.uid=b.uid and a.status=1 and  to_days(a.addtime) = to_days(now()) ");
                                    if($newkrdl){
                                       sendCustomMsg($this->mid, $this->SendWxMsg($newkrdl['uid']),$newkrdl['openid']);
                                       //通知计数
                                       $zdata['is_notice'] = $newkrdl['is_notice'] +1;
				       $this->mysql->update(Mysite::$app->config['tablepre'].'table_krdl',$zdata,"id='".$newkrdl['id']."'");
                                    }
                                }
                
                
            }elseif($status==3){
                $message = "取消成功";
                $code = 0;
            }elseif($status==4){
                $message = "过号成功";
                $code = 0;
            }elseif($is_notice==1){

                	//发送微信信息
                        $info = $this->mysql->select_one("select w.uid,w.openid,k.is_notice,k.id from ".Mysite::$app->config['tablepre']."table_krdl k,".Mysite::$app->config['tablepre']."wxuser w where k.id=".$id." and k.uid=w.uid  ");

			if(sendCustomMsg($this->mid, $this->SendWxMsg($info['uid']),$info['openid'])){
				$message = "通知成功";
                                $code = 0;
				//通知计数
				$data['is_notice'] = $info['is_notice'] +1;
				$this->mysql->update(Mysite::$app->config['tablepre'].'table_krdl',$data,"id='".$info['id']."'");
			}else{
				$message = "通知失败";
                                $code = 1;
			}
                
            }
        }else{
            $message = "更新失败";
            $code = 2;//失败
        }
        
        $data = ['code' => $code,'message' =>$message , 'data' => ''];
        print_r(json_encode($data));
    }
    
    function SendWxMsg($uid){

                $cxinfo = $this->mysql->select_one("select a.shopname,b.qhms,b.start_queue_time,count(b.id) as total from " . Mysite::$app->config['tablepre'] . "shop a,".Mysite::$app->config['tablepre']."table_krdl b," . Mysite::$app->config['tablepre'] . "table_phsz c where a.id=b.shopid and c.id=b.phsz_id and b.uid=".$uid." and to_days(b.addtime) = to_days(now())");
                
		if($cxinfo['total']){
			$count = $cxinfo['total'] - 1;
		}else{
			$count = 0;
		}

		$str = "排号提醒\n";
		$str .= "门店：".$cxinfo['shopname']."\n";
		$str .= "排号时间：".date('H:i:s',$cxinfo['start_queue_time']) ."\n";
		$str .= "排队号码：".$cxinfo['qhms']."\n";
		$str .= "前面等待：".$count."\n";
		$str .= "请留意微信平台排号变化通知若过号，在现场叫号基础上顺延三桌安排\n";
		$link = IUrl::creatUrlEx($this->mid,'wxsite/phdetails/shopid/'.$shopid);
		$str .= "<a href='".$link."'>查看详情</a>";

		return $str;

     }

 

}