<?php
class method   extends adminbaseclass
{ 
	function index(){ 

		$shopid 		= ICookie::get('adminshopid');
		$mid 			= $this->mid; 
 		$cha_starttime  = strtotime(IReq::get('starttime'));
		$cha_endtime    = (IReq::get('endtime')==" ")? time()+86400 :strtotime(IReq::get('endtime'))+86400;
	    $mftime 		= strtotime(date('Y-m',time()));
		$metime 		= time();//strtotime(date('Y-m',time()).'-'.date('t',time()).' 23:59:59 ');//,"lasttime"=>mktime(23,59,59,$m,$d,$y)); 
		$dftime 		= strtotime(date('Y-m-d',time())); 
		$detime 		= time();//今天订单将配送时间做为当前时间 
	    // 今日总订单	
	    $tjdata['dayallorder'] = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."order  where  status = 3 and addtime > $dftime and addtime < $detime and mid=".$mid." ");
	    //今日待审核订单	  
		$tjdata['dayworder']   = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."order  where addtime > $dftime and addtime < $detime  and status = 0 and mid=".$mid." ");
		//总待审核订单
		$tjdata['allworder']   = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."order  where  status = 0 and mid=".$mid." ");
		//总待审核百分比
		$data['today_worder_percent'] = sprintf("%01.2f",($tjdata['dayworder']/$tjdata['allworder'])*100).'%';
               

	    // 今日已审核订单
	    $tjdata['dayporder']   = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."order  where addtime > $dftime and addtime < $detime  and status > 0 and status < 4 and mid=".$mid." ");
	    // 总审核订单
	    $tjdata['allporder']   = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."order  where  status > 0 and status < 4 and mid=".$mid." ");
	  	//总审核百分比
		$data['today_porder_percent'] = sprintf("%01.2f",($tjdata['dayporder']/$tjdata['allporder'])*100).'%';

	    // 本月已完成订单	 
	    $tjdata['monthallorder']  = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."order  where addtime > $mftime and addtime < $metime  and status = 3 and mid=".$mid." ");//
	    // 已完成订单总量 
		$tjdata['allorder']     = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."order  where  status = 3 and mid=".$mid." "); 
		  //今日完成订单百分比
		  $tjdata['today_order_percent'] = sprintf("%01.2f",($tjdata['dayallorder']/$tjdata['allorder'])*100).'%';
 
	    //已上线商家
	    $tjdata['onlineshop'] = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."shop  where  is_pass = 1 and mid=".$mid." ");
	    //待审核商家
	    //$tjdata['wshop'] = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."shop  where  is_pass = 0 and mid=".$mid." ");
	    //普通会员
	    $tjdata['pmember'] = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."member  where mid=".$mid." ");
	    //商城订单
	    $tjdata['market'] = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."order where shoptype=1 and mid=".$mid."  ");
	    //商品数量
	    $tjdata['marketg'] = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."goods where mid=".$mid." ");
	    $data['tjdata'] = $tjdata; 
		$data['serverurl'] = Mysite::$app->config['serverurl']; 

		//订单类型(外卖，堂食，预定)
		$wmNum  = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."order where  paystatus =1 and shopcost > 0 and status = 3  and  is_goshop = 0 and mid=".$mid."  order by id asc");

		$dtNum  = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."order where  paystatus =1 and shopcost > 0 and status = 3  and  is_goshop = 1 and mid=".$mid."  order by id asc");

		$ydNum  = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."order where  paystatus =1 and shopcost > 0 and status = 3  and  is_goshop = 2 and mid=".$mid."  order by id asc");

		$data['wmNum'] = $wmNum;
		$data['dtNum'] = $dtNum;
		$data['ydNum'] = $ydNum;
	    $data['wmNum_percent'] = round($wmNum/$tjdata['allorder']);  
	    $data['dtNum_percent'] = round($dtNum/$tjdata['allorder']);
	    $data['ydNum_percent'] = round($ydNum/$tjdata['allorder']);

		//支付类型（会员卡，微信，支付宝，现金）
		$wxpayNum  	   = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."order where   paystatus =1 and shopcost > 0 and status = 3  and  paytype_name = 'W' and mid=".$mid."   order by id asc   ");
		$cartpayNum  = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."order where   paystatus =1 and shopcost > 0 and status = 3  and  paytype_name = 'M'  and mid=".$mid."  order by id asc  ");
		$alipayNum  = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."order where   paystatus =1 and shopcost > 0 and status = 3  and  paytype = 6  and mid=".$mid."  order by id asc  ");
		$cashpayNum  = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."order where   paystatus =1 and shopcost > 0 and status = 3  and  paytype = 5 and mid=".$mid."  order by id asc  ");
	 
	    $data['wxpayNum_percent']     = round($wxpayNum/$tjdata['allorder'],2);
	    $data['cartpayNum_percent'] = round($cartpayNum/$tjdata['allorder'],2);
	    $data['alipayNum_percent'] = round($alipayNum/$tjdata['allorder'],2);
	    $data['cashpayNum_percent'] = round($cashpayNum/$tjdata['allorder'],2);


	    //每月销售额统计
	   	$year         = date('Y',time());
 		$timelist     = array();  
	  	$checknowtime = time();
	  	for($i=1;$i<13;$i++){
	  	  	    $starttime = strtotime($year.'-'.$i.'-01');
	  	  	    $endtime   = strtotime("$year-$i-01 +1 month -1 day")+86400;
	  	  	    if($starttime < $checknowtime){
	  	  	       	$tempdata['name']      = $year.'-'.$i;
	  		        $tempdata['starttime'] = $starttime;
	  		        $tempdata['endtime']   = $endtime;
	  		        $timelist[]   	       = $tempdata;
	  	  	    }
	  	}

	  	if(is_array($timelist)){

			foreach($timelist as $key=>$value){
				$where2 = 'and addtime > '.$value['starttime'].' and addtime < '.$value['endtime'];

				//weixin
		     	$weixin = $this->mysql->select_one("select  count(id) as shuliang,sum(allcost) as allcost from ".Mysite::$app->config['tablepre']."order  where  paytype =1 and shopcost > 0 and status = 3 ".$where2." order by id asc  limit 0,1000"); 
		     	//cart
		     	$cart   = $this->mysql->select_one("select count(id) as shuliang,sum(allcost) as allcost from ".Mysite::$app->config['tablepre']."order  where  paytype =2  and shopcost > 0 and status = 3 ".$where2."   order by id asc  limit 0,1000");
		     	//alipay
		     	$alipay   = $this->mysql->select_one("select count(id) as shuliang,sum(allcost) as allcost from ".Mysite::$app->config['tablepre']."order  where  paytype =6  and shopcost > 0 and status = 3 ".$where2."   order by id asc  limit 0,1000");
		     	//cash
		     	$cash   = $this->mysql->select_one("select count(id) as shuliang,sum(allcost) as allcost from ".Mysite::$app->config['tablepre']."order  where  paytype =5  and shopcost > 0 and status = 3 ".$where2."   order by id asc  limit 0,1000");

		      
		         $value['allcost'] = $weixin['allcost'] +$cart['allcost']+$alipay['allcost']+$cash['allcost'];

	             //商品销售量销售额
		 	        $where1 = ' where ord.addtime > '.$value['starttime'].' and ord.addtime < '.$value['endtime'].' and ord.status = 3  and ord.is_reback = 0 and ord.mid = '.$mid ;
		 	        $goodsSql = "select sum(ordet.goodscount) as shuliang from ".Mysite::$app->config['tablepre']."orderdet  as ordet left join  ".Mysite::$app->config['tablepre']."order as ord on ordet.order_id = ord.id".$where1."";
		 	 		$goods = $this->mysql->select_one($goodsSql);
		 	 		$value['month_goodscount'] = $goods['shuliang']? $goods['shuliang']:0;

		 	 		$list[] = $value;
				}
			 
	 
			  	//处理金额数组
			  	$month_money = array();
			  	foreach ($list as $key => $value) {
			  		$month_money[] = ($value['allcost']/10000);
			  		$month_goodsmoney[] = $value['allcost'];
			  		$month_goodscount[] = $value['month_goodscount'];

			  	}
			  	 
			  	if(count($month_money) < 12){
			  		$a = array_fill(count($month_money), 12-count($month_money), '0');
			  		$month_money = array_merge($month_money,$a);
			  		$month_goodsmoney =  array_merge($month_goodsmoney,$a);
			  		$month_money = implode(',', $month_money);
			  		$month_goodsmoney = implode(',', $month_goodsmoney);
			  	}
			  	if(count($month_goodscount) < 12){
			  		$a = array_fill(count($month_goodscount), 12-count($month_goodscount), '0');
			  		$month_goodscount = array_merge($month_goodscount,$a);
			  		$month_goodscount = implode(',', $month_goodscount);
			  	}
				
				$data['month_money']      = $month_money;
				$data['month_goodsmoney'] = $month_goodsmoney;
				$data['month_goodscount'] = $month_goodscount;
		}


		 //数据列表
	    
		if($cha_starttime){   

	    	$where2 = 'and addtime > '.$cha_starttime.' and addtime < '.$cha_endtime.' and mid='.$mid;
	    	//weixin
	     	$weixin = $this->mysql->select_one("select  count(id) as shuliang,sum(allcost) as allcost from ".Mysite::$app->config['tablepre']."order  where  paytype =1 and shopcost > 0 and status = 3 ".$where2." order by id asc  limit 0,1000"); 
	     	//cart
	     	$cart   = $this->mysql->select_one("select count(id) as shuliang,sum(allcost) as allcost from ".Mysite::$app->config['tablepre']."order  where  paytype =2  and shopcost > 0 and status = 3 ".$where2."   order by id asc  limit 0,1000");
	     	//alipay
	     	$alipay   = $this->mysql->select_one("select count(id) as shuliang,sum(allcost) as allcost from ".Mysite::$app->config['tablepre']."order  where  paytype =6  and shopcost > 0 and status = 3 ".$where2."   order by id asc  limit 0,1000");
	     	//cash
	     	$cash   = $this->mysql->select_one("select count(id) as shuliang,sum(allcost) as allcost from ".Mysite::$app->config['tablepre']."order  where  paytype =5  and shopcost > 0 and status = 3 ".$where2."   order by id asc  limit 0,1000");
	 
	        $data['cha_orderNum']       = $weixin['shuliang']+ $cart['shuliang']+ $alipay['shuliang']+ $cash['shuliang'];//订单总个数
	        $data['cha_allcost'] 	    = $weixin['allcost'] + $cart['allcost']+ $alipay['allcost']+ $cash['allcost'];
		    $data['cha_weixin_allcost']	= $weixin['allcost']?$weixin['allcost']:0;
		    $data['cha_cart_allcost']	= $cart['allcost']? $cart['allcost']:0;
		    $data['cha_alipay_allcost']	= $alipay['allcost']? $alipay['allcost']:0;
		    $data['cha_cash_allcost']	= $cash['allcost']? $cash['allcost']:0;
		    $data['cha_starttime']		= date('Y-m-d',$cha_starttime);
		    $data['cha_endtime']		= date('Y-m-d',$cha_endtime-86400);

	    }

		Mysite::$app->setdata($data); 
	 }
}