<?php
/**
 *  @file 商家管理method.php
 *  @brief Brief
 */
class method   extends baseclass
{
	function index(){ 
		$this->checkshoplogin();
		$this->setstatus();
		$shopid 	   = ICookie::get('adminshopid');
		$cha_starttime = strtotime(IReq::get('starttime'));
		$cha_endtime   = (IReq::get('endtime')==" ")? time()+86400 :strtotime(IReq::get('endtime'))+86400;
		$mid 		   = $this->mid;
		if(empty($shopid)) $this->message('emptycookshop');
	    $year = empty($year)? date('Y',time()):$year;
 		$timelist = array();  
        $year 	  	  = empty($year)? date('Y',time()):$year;
 		$timelist 	  = array();  
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
			    $weixin = $this->mysql->select_one("select  count(id) as shuliang,sum(allcost) as allcost from ".Mysite::$app->config['tablepre']."order  where shopid = '".$shopid."' and paytype =1 and shopcost > 0 and status = 3 ".$where2." order by id asc  limit 0,1000");
		     	//cart
		     	$cart   = $this->mysql->select_one("select count(id) as shuliang,sum(allcost) as allcost from ".Mysite::$app->config['tablepre']."order  where shopid = '".$shopid."' and paytype =2  and shopcost > 0 and status = 3 ".$where2."   order by id asc  limit 0,1000");
		        //alipay
		     	$alipay = $this->mysql->select_one("select count(id) as shuliang,sum(allcost) as allcost from ".Mysite::$app->config['tablepre']."order  where shopid = '".$shopid."' and paytype =6  and shopcost > 0 and status = 3 ".$where2."   order by id asc  limit 0,1000");
		        //cash
		        $cash   = $this->mysql->select_one("select count(id) as shuliang,sum(allcost) as allcost from ".Mysite::$app->config['tablepre']."order  where shopid = '".$shopid."' and paytype =5  and shopcost > 0 and status = 3 ".$where2."   order by id asc  limit 0,1000");

				$value['allcost'] = $weixin['allcost'] + $cart['allcost'] + $alipay['allcost'] + $cash['allcost'];
		 

//商品销售量销售额
	 	        $where1 = ' where ord.addtime > '.$value['starttime'].' and ord.addtime < '.$value['endtime'].' and ord.status = 3  and ord.is_reback = 0  and ord.mid = '.$mid.' and ord.shopid = '.$shopid.'';
	 	        $goodsSql = "select sum(ordet.goodscount) as shuliang from ".Mysite::$app->config['tablepre']."orderdet  as ordet left join  ".Mysite::$app->config['tablepre']."order as ord on ordet.order_id = ord.id and ordet.shopid = ord.shopid ".$where1."";
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
		}

		$data['month_money']      = $month_money;
		$data['month_goodsmoney'] = $month_goodsmoney;
		$data['month_goodscount'] = $month_goodscount;
 		
 //今日订单			
		$starttime = strtotime(date('Y-m-d',time()));
		$endtime   = strtotime(date('Y-m-d',time()+86400));
	 	 
		$where2 = 'and addtime > '.$starttime.' and addtime < '.$endtime;

	 	//weixin
	    $weixin = $this->mysql->select_one("select  count(id) as shuliang,sum(allcost) as allcost from ".Mysite::$app->config['tablepre']."order  where shopid = '".$shopid."' and paytype =1 and shopcost > 0 and status = 3 ".$where2." order by id asc  limit 0,1000");
     	//cart
     	$cart   = $this->mysql->select_one("select count(id) as shuliang,sum(allcost) as allcost from ".Mysite::$app->config['tablepre']."order  where shopid = '".$shopid."' and paytype =2  and shopcost > 0 and status = 3 ".$where2."   order by id asc  limit 0,1000");
     	//alipay
     	$alipay   = $this->mysql->select_one("select count(id) as shuliang,sum(allcost) as allcost from ".Mysite::$app->config['tablepre']."order  where shopid = '".$shopid."' and paytype =6  and shopcost > 0 and status = 3 ".$where2."   order by id asc  limit 0,1000");
        //cash
     	$cash   = $this->mysql->select_one("select count(id) as shuliang,sum(allcost) as allcost from ".Mysite::$app->config['tablepre']."order  where shopid = '".$shopid."' and paytype =5  and shopcost > 0 and status = 3 ".$where2."   order by id asc  limit 0,1000");
 
        $data['today_orderNum']     = $weixin['shuliang']+$cart['shuliang']+$alipay['shuliang']+$cash['shuliang'];//订单总个数
        $data['today_allcost'] 	    = $weixin['allcost'] +$cart['allcost']+$alipay['allcost']+$cash['allcost'];
        $data['today_online']       = $weixin['allcost'] +$cart['allcost']+$alipay['allcost']+$cash['allcost'];

//总订单

    	//weixin
     	$weixin = $this->mysql->select_one("select  count(id) as shuliang,sum(allcost) as allcost from ".Mysite::$app->config['tablepre']."order  where shopid = '".$shopid."' and paytype =1 and shopcost > 0 and status = 3 order by id asc  limit 0,1000");
     	//cart
     	$cart   = $this->mysql->select_one("select count(id) as shuliang,sum(allcost) as allcost from ".Mysite::$app->config['tablepre']."order  where shopid = '".$shopid."' and paytype =2  and shopcost > 0 and status = 3 order by id asc  limit 0,1000");
     	//alipay
     	$alipay  = $this->mysql->select_one("select count(id) as shuliang,sum(allcost) as allcost from ".Mysite::$app->config['tablepre']."order  where shopid = '".$shopid."' and paytype =6  and shopcost > 0 and status = 3 order by id asc  limit 0,1000");
     	//cash
     	$cash   = $this->mysql->select_one("select count(id) as shuliang,sum(allcost) as allcost from ".Mysite::$app->config['tablepre']."order  where shopid = '".$shopid."' and paytype =5  and shopcost > 0 and status = 3 order by id asc  limit 0,1000");


 
         //月 份	订单数量	在线付款	线下支付	使用优惠券	店铺优惠	使用积分	打包费	配送费	商品总价
        $data['orderNum']     = $weixin['shuliang']+$cart['shuliang']+$alipay['shuliang']+$cash['shuliang'];//订单总个数
        $data['allcost'] 	  = $weixin['allcost'] +$cart['allcost']+$alipay['allcost']+$cash['allcost'];
        $data['online']       = $weixin['allcost'] +$cart['allcost']+$alipay['allcost']+$cash['allcost'];
     
	    $data['today_income_percent'] = sprintf("%01.2f",($data['today_allcost']/$data['allcost'])*100).'%';
	    $data['today_order_percent']  = sprintf("%01.2f",($data['today_orderNum']/$data['orderNum'])*100).'%';
	    $data['today_online_percent'] = sprintf("%01.2f",($data['today_online']/$data['online'])*100).'%';

		//商品数量    
		$goodsNum  = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."goods  where  shopid = ".$shopid."");
		$data['goodsNum'] = $goodsNum;

		//餐桌数
		$tableNum  = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."table where  mid=".$mid." and shopid = ".$shopid." ");
		$data['tableNum'] = $tableNum;
		//打印机

		//排队数
		$lineNum = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."table_krdl where shopid=".$shopid." and status = 1 and to_days(addtime) = to_days(now())");
		$data['lineNum'] = $lineNum;
		
		//用户人数
		$memberNum  = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."member  where  mid=".$mid." and shopid = ".$shopid." ");
		$data['memberNum'] = $memberNum;

		//订单类型（外卖，堂食，预定）
		$wmNum  = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."order where shopid = '".$shopid."' and paystatus =1 and shopcost > 0 and status = 3  and  is_goshop = 0  order by id asc  limit 0,1000");
		$dtNum  = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."order where shopid = '".$shopid."' and paystatus =1 and shopcost > 0 and status = 3  and  is_goshop = 1  order by id asc  limit 0,1000");
		$ydNum  = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."order where shopid = '".$shopid."' and paystatus =1 and shopcost > 0 and status = 3  and  is_goshop = 2  order by id asc  limit 0,1000");

		$data['wmNum'] = $wmNum;
		$data['dtNum'] = $dtNum;
		$data['ydNum'] = $ydNum;
	    $data['wmNum_percent'] = round($wmNum/$data['orderNum'],2); 	 
	    $data['dtNum_percent'] = round($dtNum/$data['orderNum'],2);
	    $data['ydNum_percent'] = round($ydNum/$data['orderNum'],2);

	    //支付类型（会员卡，微信）
		$wxpayNum  = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."order where shopid = '".$shopid."' and paystatus =1 and shopcost > 0 and status = 3  and  paytype = 1    order by id asc");
		$cartpayNum  = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."order where shopid = '".$shopid."' and paystatus =1 and shopcost > 0 and status = 3  and  paytype = 2    order by id asc");
		$alipayNum  = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."order where shopid = '".$shopid."' and paystatus =1 and shopcost > 0 and status = 3  and  paytype = 6    order by id asc");
		$cashpayNum  = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."order where shopid = '".$shopid."' and paystatus =1 and shopcost > 0 and status = 3  and  paytype = 5    order by id asc");
 
	    $data['wxpayNum_percent']   = round($wxpayNum/$data['orderNum'],2);
	    $data['cartpayNum_percent'] = round($cartpayNum/$data['orderNum'],2);
	    $data['alipayNum_percent'] = round($alipayNum/$data['orderNum'],2);
	    $data['cashpayNum_percent'] = round($cashpayNum/$data['orderNum'],2);


	    //数据列表
	    
	    if($cha_starttime){

	    	$where2 = 'and addtime > '.$cha_starttime.' and addtime < '.$cha_endtime;
	    	//weixin
	     	$weixin = $this->mysql->select_one("select  count(id) as shuliang,sum(allcost) as allcost from ".Mysite::$app->config['tablepre']."order  where shopid = '".$shopid."' and paytype =1 and shopcost > 0 and status = 3 ".$where2." order by id asc  limit 0,1000");
	     	//cart
	     	$cart   = $this->mysql->select_one("select count(id) as shuliang,sum(allcost) as allcost from ".Mysite::$app->config['tablepre']."order  where shopid = '".$shopid."' and paytype =2  and shopcost > 0 and status = 3 ".$where2."   order by id asc  limit 0,1000");
	     	//alipay
	     	$alipay   = $this->mysql->select_one("select count(id) as shuliang,sum(allcost) as allcost from ".Mysite::$app->config['tablepre']."order  where shopid = '".$shopid."' and paytype =6  and shopcost > 0 and status = 3 ".$where2."   order by id asc  limit 0,1000");
	     	//cash
	     	$cash   = $this->mysql->select_one("select count(id) as shuliang,sum(allcost) as allcost from ".Mysite::$app->config['tablepre']."order  where shopid = '".$shopid."' and paytype =5  and shopcost > 0 and status = 3 ".$where2."   order by id asc  limit 0,1000");
	    
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
	
	function outgoods(){
		echo 'xxxx';
		exit;
	}
	//保存店铺标签
	function saveshopbq()
	{
		$id = IReq::get('ids');
		$shopid = intval(IReq::get('shopid'));
		if(empty($shopid)){
			echo "<script>parent.uploaderror('店铺获取失败');</script>";
			exit;
		}
		//fis_com
		$is_recom = intval(IReq::get('is_recom'));
		$shopinfo= $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where id=".$shopid."  ");
		if(!empty($shopinfo)){
			$udata['is_recom'] = $is_recom;
			$this->mysql->update(Mysite::$app->config['tablepre'].'shop',$udata,"id='".$shopid."'");
		}
		if($shopinfo['shoptype'] == 0){
			$fastfood = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shopfast where shopid=".$shopid."  ");
			if(count($fastfood) > 0){
				$data['is_com'] = intval(IReq::get('fis_com'));
				$data['is_hot'] = intval(IReq::get('fis_hot'));
				$data['is_new'] = intval(IReq::get('fis_new'));
				$this->mysql->update(Mysite::$app->config['tablepre'].'shopfast',$data,"shopid='".$shopid."'");
			}
		}

		$attrinfo = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shoptype where  cattype = '".$shopinfo['shoptype']."' and parent_id = 0 and is_admin = 1  order by orderid desc limit 0,1000");
		$tempinfo = array();
		foreach($attrinfo as $key=>$value){
			$tempinfo[] = $value['id'];
		}
		if(count($tempinfo) > 0){
			//删除店铺属性是前台控制部分
			$this->mysql->delete(Mysite::$app->config['tablepre']."shopattr"," shopid='".$shopid."' and firstattr in(".join(',',$tempinfo).") ");
			//写店铺数据
			foreach($attrinfo as $key=>$value){
				//shopid     value ;
				$attrdata['shopid'] = $shopid;
				$attrdata['cattype'] = $shopinfo['shoptype'];
				$attrdata['firstattr']  = $value['id'];
				$inputdata = IFilter::act(IReq::get('mydata'.$value['id']));

				//shopid  cattype     value;
				if($value['type'] == 'input'){
					$attrdata['attrid'] = 0;
					$attrdata['value'] = $inputdata;
					$this->mysql->insert(Mysite::$app->config['tablepre']."shopattr",$attrdata);
				}elseif($value['type'] == 'img'){
					$temp = array();
					$temp = is_array($inputdata)?$inputdata:array($inputdata);
					$ids = join(',',$temp);
					if(empty($ids)){
						continue;
					}
					$tempattr  = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shoptype where id in(".$ids.")   order by orderid desc limit 0,1000");
					foreach($tempattr as $ky=>$val){
						$attrdata['attrid'] = $val['id'];
						$attrdata['value'] = $val['name'];
						$this->mysql->insert(Mysite::$app->config['tablepre']."shopattr",$attrdata);
					}
				}elseif($value['type'] =='checkbox'){
					$temp = array();
					$temp = is_array($inputdata)?$inputdata:array($inputdata);
					$ids = join(',',$temp);
					if(empty($ids)){
						continue;
					}
					$tempattr  = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shoptype where id in(".$ids.")   order by orderid desc limit 0,1000");
					foreach($tempattr as $ky=>$val){
						$attrdata['attrid'] = $val['id'];
						$attrdata['value'] = $val['name'];
						$this->mysql->insert(Mysite::$app->config['tablepre']."shopattr",$attrdata);
					}
				}elseif($value['type'] = 'radio'){
					//$this->json($inputdata);
					$tempattr  = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shoptype where id in(".intval($inputdata).")   order by orderid desc limit 0,1000");
					if(empty($tempattr)){
						continue;
					}

					$attrdata['attrid'] = $tempattr['id'];
					$attrdata['value'] = $tempattr['name'];
					$this->mysql->insert(Mysite::$app->config['tablepre']."shopattr",$attrdata);
				}else{
					continue;
				}
			}
			//is_search
			$this->mysql->delete(Mysite::$app->config['tablepre']."shopsearch"," shopid='".$shopid."'  and parent_id in(".join(',',$tempinfo).") ");
			foreach($attrinfo as $key=>$value){
				if($value['is_search'] == 1 && $value['type'] != 'input'){
					$inputdata = IFilter::act(IReq::get('mydata'.$value['id']));
					$temp = is_array($inputdata)?$inputdata:array($inputdata);
					foreach($temp as $ky=>$val){
						$searchdata['shopid'] = $shopid;
						$searchdata['parent_id'] = $value['id'];
						$searchdata['cattype'] = $shopinfo['shoptype'];
						$searchdata['second_id'] = intval($val);
						if($val > 0){
							$this->mysql->insert(Mysite::$app->config['tablepre']."shopsearch",$searchdata);
						}
					}

				}
			}
		}
		echo "<script>parent.uploadsucess('');</script>";
		exit;
	}

	//桌台设置
	function tableslist(){
		$this->checkshoplogin(); //检测登陆信息为下面作铺垫
		$shopid = ICookie::get('adminshopid');//获得首页传递过来的门店id
		$shopinfo = $this->shopinfo(); //根据门店id返回门店的所有信息
		$mid = $this->mid;

		$where = " where a.sid = {$shopinfo['sid']} and a.mid = {$mid}"; //var_dump($shopinfo);exit;
		$tablename =   trim(IReq::get('tablename'));
		$sn =   trim(IReq::get('sn'));
		if(!empty($tablename)){
			$where .= " and name like '%$tablename%'";
		}
		if(!empty($sn)){
			$where .= " and  sn like '%$sn%'";
		}


		$tablesdata['list'] = $this->mysql->getarr("select a.id,c.id as cid,c.sn,c.tableid from ".Mysite::$app->config['tablepre']."table as a left join ".Mysite::$app->config['tablepre']."table_qrcode_set as c on c.tableid = a.id ".$where." order by id desc");


		$tablesdata['lists'] = $this->mysql->getarr("select a.*,b.shopname from ".Mysite::$app->config['tablepre']."table as a left join ".Mysite::$app->config['tablepre']."shop  as b on a.sid=b.sid left join ".Mysite::$app->config['tablepre']."table_qrcode_set as c on c.tableid =a.id".$where." group by a.id order by a.id desc");

		foreach ($tablesdata['list'] as $k => $v){
			// var_dump($tablesdata['lists']);exit; //二维数组
			if(!empty($v['cid'])){
				$sn = $v['sn'];  // var_dump($sn);exit;


				$arr['sn'] = $sn;

				$url = Mysite::$app->config['banklay_url']."/getsnresdata";

				$rs = doPost($url,$arr);
				$rs =json_decode($rs,true);


				if($rs['operator'] !=''){
					$tablesdata['list'][$k]['sn'] = $rs['operator']; // var_dump(  $tablesdata['list']);exit;
				}
				else{
					$tablesdata['list'][$k]['sn'] = "未绑定";
				}

			}else{
				$tablesdata['list'][$k]['sn'] = "未绑定";
			}


		}
                
                //计算七天内预订的桌台次数
		$shopSql  = "SELECT can_reserve_day,date_of_appointment  FROM ".Mysite::$app->config['tablepre']."shop WHERE id = ".$shopid." AND mid = ".$mid."";
		$shop     = $this->mysql->select_one($shopSql);
		$daynum  = intval($shop['can_reserve_day']);
                $starttime = strtotime(date('Y-m-d H:i',time()));
                $endtime = strtotime(date('Y-m-d H:i',time()+86400*$daynum));
                //var_dump($endtime);
                foreach ($tablesdata['lists'] as $k => $v){
                    $info = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."table where id = '".$v['id']."'");
                    $where2 = "and booktime >= ".$starttime." and booktime <= ".$endtime." and table_area='".$info['table_area']."' and table_type='".$info['table_type']."' and tableid='".$info['id']."'";

                    $ordertj =$this->mysql->select_one("select count(id) as yyy_total from ".Mysite::$app->config['tablepre']."order where shopid='".$shopid."' and mid='".$mid."' ".$where2."  and  is_goshop=2 and isbefore=2 and ydstatus!=2 and ydstatus!=3 and ydstatus!=0");
                    $tablesdata['lists'][$k]['ydcount'] = $ordertj['yyy_total'];
                }

		$tablesdata['shopname'] = $shopinfo['shopname'];
		$tablesdata['sid'] = $shopinfo['sid'];
		Mysite::$app->setdata($tablesdata);

	}

	//桌台类型
	function tablestype(){
		$this->checkshoplogin(); //检测登陆信息为下面作铺垫
		$shopid = ICookie::get('adminshopid');//获得首页传递过来的门店id
		$shopinfo = $this->shopinfo(); //根据门店id返回门店的所有信息
		$table_type = $this->mysql->getarr("select *  from ".Mysite::$app->config['tablepre']."table_type where sid="."'$shopid'"." and is_deleted= 0 order by type asc");
		$data['list'] = $table_type;
		//var_dump($table_type);die;

//                foreach($table_type as $key=>$val){
//			$val['name'] = $val['name'].'人桌';
//		        $data['list'][] = $val;
//	           }

		Mysite::$app->setdata($data);
	}

	//添加类别
	function addtabletype(){
		$this->checkshoplogin(); //检测登陆信息为下面作铺垫
		$shopid = ICookie::get('adminshopid');//获得首页传递过来的门店id
		$name =   trim(IReq::get('name'));

		//正规处理后只拿字符串中的数字
		preg_match_all('/\d+/',$name,$arr);
		$arr = join('',$arr[0]);
		$type = $arr;
		//$this->message($type);

		$data  =array(
			'sid'=>$shopid,
			'name' => $name,
			'type' => $type,
			'addtime' => date("Y-m-d h:i:s",time()),
		);
		$rs = $this->mysql->insert(Mysite::$app->config['tablepre']."table_type", $data);
		$id = $this->mysql->insertid();
		$info = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."table_type where id = '$id'");
		if(empty($info)) $this->message('type_empty');
		$this->success($info);
	}

	//删除餐桌类型
	function deltypes(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');
		$id = intval(IReq::get('id'));
		if(empty($id))  $this->message('goods_empty');//(array('error'=>true,'msg'=>''));

		$table_type =  $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."table_type where id='".$id."' ");
		$table =  $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."table where shopid='".$shopid."' and mid='".$this->mid."' and table_type='".$table_type['type']."' ");

		if(!empty($table)){
			$this->message("\"".$table_type['type']."人桌\"下存在餐桌,请先删除餐桌");
		}
		$this->mysql->delete(Mysite::$app->config['tablepre'].'table_type',"id = '$id' and  sid='".$shopid."'");
		$this->success('success');
	}

	//编辑类型
	function updatetypes(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');
		$controlname =  trim(IFilter::act(IReq::get('controlname')));
		$typeid =  intval(IReq::get('typeid'));
		$values =  trim(IReq::get('values'));
		if(empty($typeid)) $this->message('types_empty');

		//正规处理后只拿字符串中的数字
		preg_match_all('/\d+/',$values,$arr);
		$arr = join('',$arr[0]);
		$type = $arr;

		switch($controlname){
			case 'name'://更新商品标题
				//if(!(IValidate::len($values,2,50))) $this->message('goods_titlelenth');
				if(empty($values)) $this->message('桌台类型不能为空!');
				$values = str_replace("人桌","",$values);
				//if(!is_numeric($values)) $this->message('桌台类型只能为数字!');
				$data['name'] = $values;
				$data['type'] = $type;
				break;

			default:
				$this->message('nodefined_func');
				break;
		}
		$this->mysql->update(Mysite::$app->config['tablepre'].'table_type',$data,"id='".$typeid."' and  sid = '".$shopid."'");
		$this->success('success');
	}

	//桌台区域
	function tablesarea(){
		$this->checkshoplogin(); //检测登陆信息为下面作铺垫
		$shopid = ICookie::get('adminshopid');//获得首页传递过来的门店id
		$shopinfo = $this->shopinfo(); //根据门店id返回门店的所有信息
		$table_type = $this->mysql->getarr("select *  from ".Mysite::$app->config['tablepre']."table_area where sid="."'$shopid'"." and is_deleted= 0");
		$data['list'] = $table_type;
		//var_dump($table_type);die;
		Mysite::$app->setdata($data);
	}

	//添加桌台区域
	function addtablearea(){
		$this->checkshoplogin(); //检测登陆信息为下面作铺垫
		$shopid = ICookie::get('adminshopid');//获得首页传递过来的门店id
		$name =   trim(IReq::get('name'));
		$data  =array(
			'sid'=>$shopid,
			'name' => $name,
			'addtime' => date("Y-m-d h:i:s",time()),
		);
		$rs = $this->mysql->insert(Mysite::$app->config['tablepre']."table_area", $data);
		$id = $this->mysql->insertid();
		$info = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."table_area where id = '$id'");
		if(empty($info)) $this->message('type_empty');
		$this->success($info);
	}

	//删除桌台区域
	function delarea(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		$mid = $this->mid;
		if(empty($shopid)) $this->message('emptycookshop');
		$id = intval(IReq::get('id'));
		if(empty($id))  $this->message('goods_empty');//(array('error'=>true,'msg'=>''));

		$table_area =  $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."table_area where id='".$id."' ");
		$table =  $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."table where shopid='".$shopid."' and mid='".$this->mid."' and table_area='".$table_area['name']."' ");

		if(!empty($table)){
			$this->message("\"".$table_area['name']."\"下存在餐桌,请先删除餐桌");
		}
		//$this->message($table['name']);

		$this->mysql->delete(Mysite::$app->config['tablepre'].'table_area',"id = '$id' and  sid='".$shopid."'");
		$this->success('success');
	}

	//编辑地区
	function updatearea(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');
		$controlname =  trim(IFilter::act(IReq::get('controlname')));
		$typeid =  intval(IReq::get('typeid'));
		$values =  trim(IReq::get('values'));
		if(empty($typeid)) $this->message('types_empty');
		switch($controlname){
			case 'name'://更新商品标题
				if(!(IValidate::len($values,2,50))) $this->message('goods_titlelenth');
				$data['name'] = $values;
				break;

			default:
				$this->message('nodefined_func');
				break;
		}
		$this->mysql->update(Mysite::$app->config['tablepre'].'table_area',$data,"id='".$typeid."' and  sid = '".$shopid."'");
		$this->success('success');
	}
	//添加手表设备
	function getoprtotables(){
		$sn =   trim(IReq::get('sn'));
		$id =   trim(IReq::get('id'));

		$sel =  $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."table_qrcode_set where tableid="."'$id' and sn="."'$sn'");

		if(!empty($sel)){
			$this->success('不可重复添加');
		}else{
			$data  =array(
				'tableid' => $id,
				'sn' => $sn,
				'addtime' => date("Y-m-d h:i:s"),
			);

			$rs = $this->mysql->insert(Mysite::$app->config['tablepre']."table_qrcode_set", $data);
			if(false != $rs ){
				$this->message('success');
			}
		}


	}
	//删除手表设备
	function delwatch(){
		$id = IFilter::act(IReq::get('id'));

		$sel =  $this->mysql->select_one("select id  from ".Mysite::$app->config['tablepre']."table_qrcode_set where id="."'$id'");
		//   $this->message("select id from ".Mysite::$app->config['tablepre']."table_qrcode_set where id="."'$id'");
		if(false != $sel){
			$rs =  $this->mysql->delete(Mysite::$app->config['tablepre'].'table_qrcode_set',"id = '$id'");
			$this->message('success');
		}else{
			$this->success('id不存在或已被删除');
		}

	}
	//堂食二维码页面by yanyh
	function tablesqrcode(){
		$this->checkshoplogin(); //检测登陆信息为下面作铺垫
		$shopid   = ICookie::get('adminshopid');//获得首页传递过来的门店id
		$shopinfo = $this->shopinfo(); //根据门店id返回门店的所有信息
		$sid      = IFilter::act(IReq::get('sid'));
		$id       = IFilter::act(IReq::get('id'));
		$mid      = $shopinfo['mid'];
		$table    = Mysite::$app->config['siteurl']."/index.php/{$shopinfo['mid']}/wxsite/mealTable/sid/$sid/id/$id";

		$sql        = "select name  from ".Mysite::$app->config['tablepre']."table where id="."'$id'";
		$table_name = $this->mysql->select_one($sql);
		$data['table_name'] = $table_name['name'];


		//生成桌台带参数二维码
		include  "plug/pay/weixin/phpqrcode/phpqrcode.php"; //引入二维码文件
		$phpqrcode = new QRcode();

		$filename = $_SERVER['DOCUMENT_ROOT'].'/upload/qrcode/table/'.$mid.'_'.$id.'.png';

		$phpqrcode->png($table,$filename,1,10);

		$data['id'] = $id;
		$data['qrcode'] = '/upload/qrcode/table/'.$mid.'_'.$id.'.png';
		Mysite::$app->setdata($data);

	}
	//下载二维码by yanyh
	function downloadQrcode(){
		$shopid   = ICookie::get('adminshopid');//获得首页传递过来的门店id
		$shopinfo = $this->shopinfo(); //根据门店id返回门店的所有信息
		$id       = IFilter::act(IReq::get('id'));
		$mid      = $shopinfo['mid'];

		//图片合成
		$uname  = '桌号：'.IReq::get('uname');
		iconv('GB2312', 'UTF-8', $uanme);
		$path_1 = Mysite::$app->config['siteurl']."/upload/qrcode/table/qrcode_bg.jpg";
		$path_2 = Mysite::$app->config['siteurl']."/upload/qrcode/table/".$mid."_".$id.".png";

		$this->hebingImg($path_1,$path_2,$uname);

		//图片输出
		$filename = "./upload/qrcode/table/qrcode_new.png";
		Header("Content-type: image/png");
		Header("Content-Disposition: attachment; filename='qrcode_new.png'");
		Header("Content-Length: ".filesize($filename));
		readfile($filename);
		exit();
	}
	//图片合成（包括文字）by yanyh
	function hebingImg($path_1,$path_2,$uname){//加文字

		$image_1 = imagecreatefromjpeg($path_1);
		$textcolor = imagecolorallocate($image_1, 255, 255,255); //设置水印字体颜色
		$font = './newstyle/fonts/msyh.ttf'; //定义字体 http://www.bcty365.com
		imagettftext($image_1, 15, 0, 120, 390, $textcolor, $font, $uname);//将文字写到图片中

		$image_2 = imagecreatefrompng($path_2);
		$image_3 = imageCreatetruecolor(imagesx($image_1),imagesy($image_1));
		imagecopymerge($image_3,$image_1,0,0,0,0,imagesx($image_1),imagesy($image_1),100);
		list($width,$height)=getimagesize($path_2);
		imagecopyresampled($image_3,$image_2,95,198,0,0,150,150,$width,$height);
		$url = $_SERVER['DOCUMENT_ROOT'].'/upload/qrcode/table/qrcode_new.png';
		imagejpeg($image_3,$url,100);
		imagedestroy($image_3);

	}


	//通过接口获取商户后台sn和操作员
	function getmersnandoperator(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		$shopinfo = $this->shopinfo();
		$type =  IFilter::act(IReq::get('type'));
		if($type == 'get'){
			$url = Mysite::$app->config['banklay_url']."/getTablesnandopr";
			$arr = array();
			$arr['mid'] = $shopinfo['mid'];
			$arr['sid'] = $shopinfo['sid'];
			$rs = doPost($url,$arr);
			if(!empty($rs)){
				$this->message(json_decode($rs));
			}
		}

	}
        
        //通过接口获取员工设备号
	function getstaffoperator(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		$shopinfo = $this->shopinfo();
                $type =  IFilter::act(IReq::get('type'));
                
		if($type == 'get'){
			$url = Mysite::$app->config['banklay_url']."/getTablesnandopr";
			$arr = array();
			$arr['mid'] = $shopinfo['mid'];
			$arr['sid'] = $shopinfo['sid'];
			$rs = doPost($url,$arr);
			if(!empty($rs)){
				$arr_obj = json_decode($rs);
			}
		}
                $arr1 = $this->object2array($arr_obj);
                
                $arr2 = $this->mysql->getarr("select b.sn,a.shopid from ".Mysite::$app->config['tablepre']."staff a, ".Mysite::$app->config['tablepre']."staff_qrcode_set b where a.id = b.staffid and a.shopid='".$shopid."' ");
                
                //var_dump($arr1);
                foreach ($arr1 as $key => $value) {
                   
                    if($value['sn']==$arr2[0]['sn'] || $value['sn']==$arr2[1]['sn']){
                        
                        unset($arr1[$key]);
                        //unset($arr1[$key]['operator']);
                    }
                }
                //var_dump($arr2);die;
                //$arr = array_diff($arr2,$arr1);
                //var_dump($arr2);
                //var_dump($arr2);die;
		$this->success($arr1);

	}
        
        function object2array(&$object) {
             $object =  json_decode( json_encode( $object),true);
             return  $object;
        }


	
	//添加桌台编辑
	function addtables(){
		$this->checkshoplogin(); //检测登陆信息为下面作铺垫
		$shopid = ICookie::get('adminshopid');//获得首页传递过来的门店id
		$shopinfo = $this->shopinfo();	 //返回门店的所有信息
		$mid = $shopinfo['mid'];

		$id = intval(IReq::get('id'));
		$data['id']= $id;

		$data['cxinfo'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."table where id = '".$id."' ");
		//找出该店桌台区域
		$table_area = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."table_area where sid = '".$shopid."' ");
		if($table_area){
			$data['table_area'] = $table_area;
		}

		$table_type = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."table_type where sid = '".$shopid."' order by type asc");
		if($table_area){
			$data['table_type'] = $table_type;
		}

		$data['table_type'] = $table_type;
		Mysite::$app->setdata($data);

	}

	//保存 添加/修改桌台
	function tables_save(){

		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');//获得首页传递过来的门店id
		$shopinfo = $this->shopinfo();	 //返回门店的所有信息

		$data['sid'] = $shopinfo['sid'];
		$data['mid'] = $this->mid;
		$id = intval(IReq::get('id'));
		$data['name'] = IFilter::act(IReq::get('name'));
		$data['table_area'] = IFilter::act(IReq::get('table_area'));
		$data['table_type'] = IFilter::act(IReq::get('table_type'));
		$data['reserve_status'] = IFilter::act(IReq::get('reserve_status'));
		//$this->message($data['table_type']);
		$data['shopid'] = $shopinfo['id'];
		$data['remark'] = IFilter::act(IReq::get('remark'));

		if(empty($data['name'])){$this->message('桌台名称不能为空！');}

		if(empty($id)){

			//判断是否存在
			$sql = "select *  from ".Mysite::$app->config['tablepre']."table where shopid='".$shopid."' and mid='".$this->mid."' and name='".$data['name']."' ";
			$checktable =  $this->mysql->select_one($sql);
			if(!empty($checktable)){
				$this->message('此桌台名称已存在');
			}

			$data['addtime'] = date('Y-m-d h:i:s');
			$this->mysql->insert(Mysite::$app->config['tablepre']."table",$data);
		}else{

			$this->mysql->update(Mysite::$app->config['tablepre'].'table',$data,"id='". $id."'");
		}
		//
		$this->success('success');
	}

	//删除桌台
	function tabledel(){
		$data = IFilter::act(IReq::get('id'));

		$sel =  $this->mysql->select_one("select id  from ".Mysite::$app->config['tablepre']."table where id="."'$data'");
		if($sel != false){

			$this->mysql->delete(Mysite::$app->config['tablepre'].'table',"id = '$data'");
			$this->message('删除成功');
		}else{
			$this->success('桌台不存在或已被删除');
		}
	}
	//更新桌台
	function updatatable(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		$shopinfo = $this->shopinfo();
		$tablesdata['sid'] = $shopinfo['sid'];
		$tablesdata['id'] = IFilter::act(IReq::get('id'));
		$tablesdata['list']  = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."table  where id = "."'{$tablesdata['id']}'");
		Mysite::$app->setdata($tablesdata);


		$data['name'] = IFilter::act(IReq::get('name'));
	}

//        function uptable_status(){
//                $id =  intval(IReq::get('id'));
//		$values =  trim(IReq::get('values'));
//                $data['is_live'] = $values;
//
//		$this->mysql->update(Mysite::$app->config['tablepre'].'table',$data,"id='".$id."'");
//		$this->success('success');
//        }



	//编辑店铺信息
	function useredit(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		$shopinfo = $this->shopinfo();
		if($shopid > 0){  
			$data['shopname'] = IFilter::act(IReq::get('shopname'));
			if(!empty($data['shopname'])){  
				$data['phone'] = IFilter::act(IReq::get('phone'));
				$data['address'] = IFilter::act(IReq::get('address'));
				$data['shortname'] = IFilter::act(IReq::get('shortname'));
				$data['goodattrdefault'] = IFilter::act(IReq::get('goodattrdefault'));
				$data['email'] = IFilter::act(IReq::get('email'));
				$data['is_open'] = intval(IReq::get('is_open'));
				//$data['is_onlinepay'] = intval(IReq::get('is_onlinepay'));
				$data['is_daopay'] = intval(IReq::get('is_daopay'));
				$starttime = IFilter::act(IReq::get('starttime'));
				$data['otherlink'] = IFilter::act(IReq::get('otherlink'));
				$data['IMEI'] = IFilter::act(IReq::get('IMEI'));
				$data['maphone'] =  IFilter::act(IReq::get('maphone'));
				$data['is_preeat'] = intval(IReq::get('is_preeat'));
				$link = IUrl::creatUrlEx($this->mid,'shopcenter/base');
				if(!(IValidate::len($data['shopname'],2,50))) $this->message('shop_shopnamelenth');//$this->refunction('店铺名长度大于4小于50',$link);
				if(!(IValidate::phone($data['phone']))) $this->message('shop_dphone');//$this->refunction('正录入正确的订餐电话',$link);
				if(!(IValidate::len($data['address'],2,50)))  $this->message('shop_addresslenth');//$this->refunction('店铺门市地址长度大于4小于50',$link);
				if(!empty($data['shortname'])){
					if(!(IValidate::email($data['email']))) $this->message('erremail');//$this->refunction('邮箱地址不是邮件',$link);
				}
				if(in_array($data['shortname'],array('shopcenter','site','admin','member','membercenter','shop','comment','ask','single','gift','news','adv'))) $this->message('访问地址已存在');// $this->refunction('访问地址已存在',$link); //判断是否是系统设置的类型
				if(empty($data['goodattrdefault'])) $this->message('默认商品单位不能为空！');
				if(!(IValidate::len($data['goodattrdefault'],0,10))) $this->message('默认商品单位长度大于0小于10');
				$data['starttime']='';
				if(empty($starttime)) $this->message('emptytime');
				$starttime =  explode(',',$starttime);
				if(!is_array($starttime)) $this->message('errtime');// $this->refunction('请录入正确时间格式',$link);
				$checkshu = count($starttime);
				if($checkshu%4 !=0) $this->message('errtime');
				$newtime = array();
				$checktime1 = 0;
				$dotime1a = 0;
				$dotime1b = 0;
				foreach($starttime as $key=>$value)
				{

					if($key%4 == 0){
						$data['starttime'] .= $value.':';
						$dotime1a= $value.':';
					}elseif($key%4 == 1){
						$data['starttime'] .= $value.'-';
						$dotime1a .= $value;
						$tempccheck = strtotime($dotime1a);
						if($tempccheck >  $checktime1){
							$checktime1 = $tempccheck;
						}else{
							$this->message('时间格式错误上次结束时间'.date('H:i',$checktime1).'大于'.date('H:i',$tempccheck));
						}
					}elseif($key%4 == 2){
						$data['starttime'] .= $value.':';
						$dotime1b= $value.':';
					}elseif($key%4 == 3){
						$data['starttime'] .= $value.'|';
						$dotime1b .= $value;
						$tempccheck = strtotime($dotime1b);
						if($tempccheck >  $checktime1){
							$checktime1 = $tempccheck;
						}else{
							$this->message('时间格式错误上次结束时间'.date('H:i',$checktime1).'大于'.date('H:i',$tempccheck));
						}
					}

				}

				if(empty($data['starttime'])) $this->message('shop_erroptime');// $this->refunction('请录入营业时间',$link);
				if(count($newtime)%2==1) $this->message('errtime');//$this->refunction('请录入正确时间格式',$link);

				$data['starttime'] = substr($data['starttime'],0,strlen($data['starttime'])-1);
				$this->mysql->update(Mysite::$app->config['tablepre'].'shop',$data,"id='".$shopid."'");
				//$basearea = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."areatowait where shopid=".$shopid."    order by id desc  limit 0,1000");

				//更新店铺名称
				$Searchk = new searchkey($this->mysql);
				$checkiex = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."shopfast where shopid ='".$shopid."'  ");
				if($checkiex > 0){
					$Searchk->setdata(1,$shopid,$data['shopname']);
					$Searchk->save();
				}


				$this->success('success');    
			 
			}


		}


 


		$shopid = ICookie::get('adminshopid');
		$checkinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop  where id = '".$shopid."' ");

		if($checkinfo['shoptype'] == 0){

			$data['shopfast'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast  where shopid = '".$shopid."' ");
			if(empty($data['shopfast'])){
				$udata['shopid'] = $shopid;
				$this->mysql->insert(Mysite::$app->config['tablepre']."shopfast",$udata);
				$data['shopfast'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast  where shopid = '".$shopid."' ");
			}
		}
		if($checkinfo['shoptype'] == 1){

			$data['shopfast'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket  where shopid = '".$shopid."' ");
			if(empty($data['shopfast'])){
				$udata['shopid'] = $shopid;
				$this->mysql->insert(Mysite::$app->config['tablepre']."shopmarket",$udata);
				$data['shopfast'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket  where shopid = '".$shopid."' ");
			}

		}

		$nowhout = strtotime(date('Y-m-d',time()));//当天最小linux 时间
		$timelist = !empty($data['shopfast']['postdate'])?unserialize($data['shopfast']['postdate']):array();
		$data['pstimelist'] = array();
		foreach($timelist as $key=>$value){
			$tempt = array();
			$tempt['s'] = date('H:i',$nowhout+$value['s']);
			$tempt['e'] = date('H:i',$nowhout+$value['e']);
			$tempt['i'] =  $value['i'];
			$tempt['cost'] = isset($value['cost'])?$value['cost']:0;
			$data['pstimelist'][] = $tempt;
		}
		$tempopendata = explode('|',$checkinfo['starttime']);
		$opendata = array();
		foreach($tempopendata as $key=>$value){
			if(!empty($value)){
				$newtemp = explode('-',$value);
				if(count($newtemp)==2 && !empty($newtemp[0]) && !empty($newtemp[1])){
					$ccc =array();
					$cc['stime'] = $newtemp[0];
					$cc['etime'] = $newtemp[1];
					$opendata[] = $cc;
				}
			}
		}
		$data['opendata'] = $opendata;
		//获取所有extend属性 xiaozu_shoptype
		//id  name 分类名 type checkbox多选，radio单选，img图片，input输入框 parent_id 0表示导航明，1值  1外卖2订台3其他 is_search 0不是搜索关键字1搜索关键字 is_main 是否展示在店铺列表 0否1是 is_admin 设置类型0店铺1后台 instro 简单介绍 orderid
		$attrinfo = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shoptype where  cattype = ".$checkinfo['shoptype']." and parent_id = 0 and is_admin = 0  order by orderid desc limit 0,1000");
		$data['attrlist'] = array();
		foreach($attrinfo as $key=>$value){
			$value['det'] =  $this->mysql->getarr("select id,name,instro from ".Mysite::$app->config['tablepre']."shoptype where   parent_id = ".$value['id']." order by id desc ");
			$data['attrlist'][] = $value;
		}
		// shopid  cattype 1外卖2订台 firstattr  attrid 该属性ID value 值
		$shopsetatt = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shopattr where  cattype = ".$checkinfo['shoptype']."   and shopid = '".$shopid."'  limit 0,1000");
		//firstvalue attrid
		$myattr = array();
		foreach($shopsetatt as $key=>$value){
			$myattr[$value['firstattr'].'-'.$value['attrid']] = $value['value'];
		}
		$data['myattr'] = $myattr;
		$data['pestypearr'] = array(
			'1'=>'店铺统一配送费',
			'2'=>'店铺区域设置配送费',
			'3'=>'不计算配送费',
			'4'=>'根据定位距离计算',
			'5'=>'根据菜品数计算配送费'
		);
		$data['defaultset'] = array('pstype'=>'0','psvalue1'=>'0','psvalue2'=>'0','psvalue3'=>'0');




		$data['newtimedata'] = array();
		Mysite::$app->setdata($data);
	}
	//店铺设置
	function usershopfast(){
		$this->checkshoplogin();
		$data['sitetitle'] = '店铺设置';
		$shopid = ICookie::get('adminshopid');
		$checkinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop  where id = '".$shopid."' ");

		if($checkinfo['shoptype'] == 0){

			$data['shopfast'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast  where shopid = '".$shopid."' ");
			if(empty($data['shopfast'])){
				$udata['shopid'] = $shopid;
				$this->mysql->insert(Mysite::$app->config['tablepre']."shopfast",$udata);
				$data['shopfast'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast  where shopid = '".$shopid."' ");
			}
		}

		$nowhout = strtotime(date('Y-m-d',time()));//当天最小linux 时间
		$timelist = !empty($data['shopfast']['postdate'])?unserialize($data['shopfast']['postdate']):array();
		$data['pstimelist'] = array();
		foreach($timelist as $key=>$value){
			$tempt = array();
			$tempt['s'] = date('H:i',$nowhout+$value['s']);
			$tempt['e'] = date('H:i',$nowhout+$value['e']);
			$tempt['i'] =  $value['i'];
			$tempt['cost'] = isset($value['cost'])?$value['cost']:0;
			$data['pstimelist'][] = $tempt;
		}
		$tempopendata = explode('|',$checkinfo['starttime']);
		$opendata = array();
		foreach($tempopendata as $key=>$value){
			if(!empty($value)){
				$newtemp = explode('-',$value);
				if(count($newtemp)==2 && !empty($newtemp[0]) && !empty($newtemp[1])){
					$ccc =array();
					$cc['stime'] = $newtemp[0];
					$cc['etime'] = $newtemp[1];
					$opendata[] = $cc;
				}
			}
		}
		$data['opendata'] = $opendata;
		//获取所有extend属性 xiaozu_shoptype
		//id  name 分类名 type checkbox多选，radio单选，img图片，input输入框 parent_id 0表示导航明，1值  1外卖2订台3其他 is_search 0不是搜索关键字1搜索关键字 is_main 是否展示在店铺列表 0否1是 is_admin 设置类型0店铺1后台 instro 简单介绍 orderid
		$attrinfo = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shoptype where  cattype = ".$checkinfo['shoptype']." and parent_id = 0 and is_admin = 0  order by orderid desc limit 0,1000");
		$data['attrlist'] = array();
		foreach($attrinfo as $key=>$value){
			$value['det'] =  $this->mysql->getarr("select id,name,instro from ".Mysite::$app->config['tablepre']."shoptype where   parent_id = ".$value['id']." order by id desc ");
			$data['attrlist'][] = $value;
		}
		// shopid  cattype 1外卖2订台 firstattr  attrid 该属性ID value 值
		$shopsetatt = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shopattr where  cattype = ".$checkinfo['shoptype']."   and shopid = '".$shopid."'  limit 0,1000");
		//firstvalue attrid
		$myattr = array();
		foreach($shopsetatt as $key=>$value){
			$myattr[$value['firstattr'].'-'.$value['attrid']] = $value['value'];
		}
		$data['myattr'] = $myattr;
		$data['pestypearr'] = array(
			'1'=>'店铺统一配送费',
			'2'=>'店铺区域设置配送费',
			'3'=>'不计算配送费',
			'4'=>'根据定位距离计算',
			'5'=>'根据菜品数计算配送费'
		);
		$data['defaultset'] = array('pstype'=>'0','psvalue1'=>'0','psvalue2'=>'0','psvalue3'=>'0');


		$tempinfo = array();
		if($checkinfo['shoptype'] == 0){
			$tempinfo = 	$this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast where shopid=".$shopid."  ");
		}else{
			$tempinfo = 	$this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket where shopid=".$shopid."  ");
		}

		$data['shopdetinfo'] = $tempinfo;

		Mysite::$app->setdata($data);
	}
	//超市信息展示
	function usershopmarket(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		$checkinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop  where id = '".$shopid."' ");


		$data['shopfast'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket  where shopid = '".$shopid."' ");
		if(empty($data['shopfast'])){
			$udata['shopid'] = $shopid;
			$this->mysql->insert(Mysite::$app->config['tablepre']."shopmarket",$udata);
			$data['shopfast'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket  where shopid = '".$shopid."' ");
		}
		$nowhout = strtotime(date('Y-m-d',time()));//当天最小linux 时间
		$timelist = !empty($data['shopfast']['postdate'])?unserialize($data['shopfast']['postdate']):array();
		$data['pstimelist'] = array();
		foreach($timelist as $key=>$value){
			$tempt = array();
			$tempt['s'] = date('H:i',$nowhout+$value['s']);
			$tempt['e'] = date('H:i',$nowhout+$value['e']);
			$tempt['i'] =  $value['i'];
			$tempt['cost'] = isset($value['cost'])?$value['cost']:0;
			$data['pstimelist'][] = $tempt;
		}
		$tempopendata = explode('|',$checkinfo['starttime']);
		$opendata = array();
		foreach($tempopendata as $key=>$value){
			if(!empty($value)){
				$newtemp = explode('-',$value);
				if(count($newtemp)==2 && !empty($newtemp[0]) && !empty($newtemp[1])){
					$ccc =array();
					$cc['stime'] = $newtemp[0];
					$cc['etime'] = $newtemp[1];
					$opendata[] = $cc;
				}
			}
		}
		$data['opendata'] = $opendata;
		//获取所有extend属性 xiaozu_shoptype
		//id  name 分类名 type checkbox多选，radio单选，img图片，input输入框 parent_id 0表示导航明，1值  1外卖2订台3其他 is_search 0不是搜索关键字1搜索关键字 is_main 是否展示在店铺列表 0否1是 is_admin 设置类型0店铺1后台 instro 简单介绍 orderid
		$attrinfo = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shoptype where  cattype = ".$checkinfo['shoptype']." and parent_id = 0 and is_admin = 0  order by orderid desc limit 0,1000");
		$data['attrlist'] = array();
		foreach($attrinfo as $key=>$value){
			$value['det'] =  $this->mysql->getarr("select id,name,instro from ".Mysite::$app->config['tablepre']."shoptype where   parent_id = ".$value['id']." order by id desc ");
			$data['attrlist'][] = $value;
		}
		// shopid  cattype 1外卖2订台 firstattr  attrid 该属性ID value 值
		$shopsetatt = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shopattr where  cattype = ".$checkinfo['shoptype']."   and shopid = '".$shopid."'  limit 0,1000");
		//firstvalue attrid
		$myattr = array();
		foreach($shopsetatt as $key=>$value){
			$myattr[$value['firstattr'].'-'.$value['attrid']] = $value['value'];
		}
		$data['myattr'] = $myattr;
		$data['pestypearr'] = array(
			'1'=>'店铺统一配送费',
			'2'=>'店铺区域设置配送费',
			'3'=>'不计算配送费',
			'4'=>'百度地图测算配送费',
			'5'=>'根据菜品数计算配送费'
		);
		$data['defaultset'] = array('pstype'=>'0','psvalue1'=>'0','psvalue2'=>'0','psvalue3'=>'0');
		Mysite::$app->setdata($data);
	}
	//店铺信息 获取
	function shopinfo(){
		$shopid = ICookie::get('adminshopid');
		if($shopid > 0){
			$shopinfo =	$this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop  where id = '".$shopid."' ");
		}else{
			$shopinfo = '';
		}
		return $shopinfo;
	}
	//保存店铺信息
	function savefastfood(){
		$this->checkshoplogin();


		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');
		$shopinfo = $this->shopinfo();
		if($shopinfo['shoptype'] ==  0){//外卖
			$data['is_orderbefore'] = intval(IFilter::act(IReq::get('is_orderbefore')));
			$data['befortime'] = intval(IFilter::act(IReq::get('befortime')));
			$data['limitcost']  = intval(IFilter::act(IReq::get('limitcost')));
			$data['limitstro'] = IFilter::act(IReq::get('limitstro'));
			$data['personcost'] = intval(IFilter::act(IReq::get('personcost')));
			//	$data['sendtype'] = intval(IFilter::act(IReq::get('sendtype')));
			$data['maketime']  = intval(IFilter::act(IReq::get('maketime')));
			$data['is_waimai'] = intval(IFilter::act(IReq::get('is_waimai')));
			$data['is_goshop'] = intval(IFilter::act(IReq::get('is_goshop')));
			$data['personcount'] = intval(IFilter::act(IReq::get('personcount')));
			$data['arrivetime'] = IFilter::act(IReq::get('arrivetime'));
			$data['interval_minit'] = intval(IFilter::act(IReq::get('interval_minit')));
			$data['pscost'] =  intval(IReq::get('pscost'));
			$data['pradius'] =  intval(IReq::get('pradius'));
			$data['autoreceipttime'] =  intval(IReq::get('autoreceipttime'));//自动收货超时时间

			$pstimestime = IFilter::act(IReq::get('pstimestime'));
			$pstimeetime = IFilter::act(IReq::get('pstimeetime'));
			$pstimecost = IFilter::act(IReq::get('pstimecost'));
			$choiceps = IFilter::act(IReq::get('choiceps'));
			$postdata = array();
			$miniday = strtotime(date('Y-m-d',time()));
			$minidaydate = date('Y-m-d',time());
			if(is_array($pstimestime)){
				foreach($pstimestime as $key=>$value){
					$tempcheck2 = $choiceps[$key];
					if(isset($tempcheck2) && $tempcheck2 == 1){
						if(isset($pstimeetime[$key]) && !empty($pstimeetime[$key]) && !empty($value)){
							$tempb = array();
							$tempb['s'] = strtotime($minidaydate.' '.$value.':00')-$miniday;
							$tempb['e'] = strtotime($minidaydate.' '.$pstimeetime[$key].':00')-$miniday;
							$tempb['i'] = '';
							$checkcost=isset($pstimecost[$key])?$pstimecost[$key]:0;
							$tempb['cost'] = $checkcost < 0?0:$checkcost;
							$postdata[] = $tempb;
						}
					}

				}
			}



			$data['postdate'] =serialize($postdata);


			$checkinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast  where shopid = '".$shopinfo['id']."' ");
			if(!empty($checkinfo['sendtype'])){
				$data['pradius'] = intval(IFilter::act(IReq::get('pradius')));
				$data['pscost'] = intval(IFilter::act(IReq::get('pscost')));
				$tempdo = array();
				for($i=0;$i< $data['pradius'];$i++){
					$tempdo[$i] = intval(IReq::get('radiusvalue'.$i));
				}
				$data['pradiusvalue'] = serialize($tempdo);
			}


			$this->mysql->update(Mysite::$app->config['tablepre'].'shopfast',$data,"shopid='".$shopinfo['id']."'");
		}elseif($shopinfo['shoptype'] == 1){ //超市
			$data['is_orderbefore'] = intval(IFilter::act(IReq::get('is_orderbefore')));

			$data['befortime'] = intval(IFilter::act(IReq::get('befortime')));
			$data['limitcost']  = intval(IFilter::act(IReq::get('limitcost')));
			$data['limitstro'] = IFilter::act(IReq::get('limitstro'));

			$data['maketime']  = intval(IFilter::act(IReq::get('maketime')));
			$data['arrivetime'] = IFilter::act(IReq::get('arrivetime'));
			$data['interval_minit'] = intval(IFilter::act(IReq::get('interval_minit')));
			$pstimestime = IFilter::act(IReq::get('pstimestime'));
			$pstimeetime = IFilter::act(IReq::get('pstimeetime'));
			$pstimecost = IFilter::act(IReq::get('pstimecost'));
			$choiceps = IFilter::act(IReq::get('choiceps'));
			$postdata = array();
			$miniday = strtotime(date('Y-m-d',time()));
			$minidaydate = date('Y-m-d',time());
			if(is_array($pstimestime)){
				foreach($pstimestime as $key=>$value){
					$tempcheck2 = $choiceps[$key];
					if(isset($tempcheck2) && $tempcheck2 == 1){
						if(isset($pstimeetime[$key]) && !empty($pstimeetime[$key]) && !empty($value)){
							$tempb = array();
							$tempb['s'] = strtotime($minidaydate.' '.$value.':00')-$miniday;
							$tempb['e'] = strtotime($minidaydate.' '.$pstimeetime[$key].':00')-$miniday;
							$tempb['i'] = '';
							$checkcost=isset($pstimecost[$key])?$pstimecost[$key]:0;
							$tempb['cost'] = $checkcost < 0?0:$checkcost;
							$postdata[] = $tempb;
						}
					}

				}
			}



			$data['postdate'] =serialize($postdata);



			$checkinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket  where shopid = '".$shopinfo['id']."' ");

			if(!empty($checkinfo['sendtype'])){
				$data['pradius'] = intval(IFilter::act(IReq::get('pradius')));
				$data['pscost'] = intval(IFilter::act(IReq::get('pscost')));
				$tempdo = array();
				for($i=0;$i< $data['pradius'];$i++){
					$tempdo[$i] = intval(IReq::get('radiusvalue'.$i));
				}
				$data['pradiusvalue'] = serialize($tempdo);
			}
			$this->mysql->update(Mysite::$app->config['tablepre'].'shopmarket',$data,"shopid='".$shopinfo['id']."'");
		}
		$attrinfo = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shoptype where  cattype = ".$shopinfo['shoptype']." and parent_id = 0 and is_admin = 0  order by orderid desc limit 0,1000");
		$tempinfo = array();
		foreach($attrinfo as $key=>$value){
			$tempinfo[] = $value['id'];
		}
		if(count($tempinfo) > 0){
			//删除店铺属性是前台控制部分
			$this->mysql->delete(Mysite::$app->config['tablepre']."shopattr"," shopid='".$shopinfo['id']."' and firstattr in(".join(',',$tempinfo).") ");
			//写店铺数据
			foreach($attrinfo as $key=>$value){
				//shopid     value ;
				$attrdata['shopid'] = $shopinfo['id'];
				$attrdata['cattype'] = $shopinfo['shoptype'];
				$attrdata['firstattr']  = $value['id'];
				$inputdata = IFilter::act(IReq::get('mydata'.$value['id']));

				//shopid  cattype     value;
				if($value['type'] == 'input'){
					$attrdata['attrid'] = 0;
					$attrdata['value'] = $inputdata;
					$this->mysql->insert(Mysite::$app->config['tablepre']."shopattr",$attrdata);
				}elseif($value['type'] == 'img'){
					$temp = array();
					$temp = is_array($inputdata)?$inputdata:array($inputdata);
					$ids = join(',',$temp);
					if(empty($ids)){
						continue;
					}
					$tempattr  = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shoptype where id in(".$ids.")   order by orderid desc limit 0,1000");
					foreach($tempattr as $ky=>$val){
						$attrdata['attrid'] = $val['id'];
						$attrdata['value'] = $val['name'];
						$this->mysql->insert(Mysite::$app->config['tablepre']."shopattr",$attrdata);
					}
				}elseif($value['type'] =='checkbox'){
					$temp = array();
					$temp = is_array($inputdata)?$inputdata:array($inputdata);
					$ids = join(',',$temp);
					if(empty($ids)){
						continue;
					}
					$tempattr  = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shoptype where id in(".$ids.")   order by orderid desc limit 0,1000");
					foreach($tempattr as $ky=>$val){
						$attrdata['attrid'] = $val['id'];
						$attrdata['value'] = $val['name'];
						$this->mysql->insert(Mysite::$app->config['tablepre']."shopattr",$attrdata);
					}
				}elseif($value['type'] = 'radio'){
					$tempattr  = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shoptype where id in(".intval($inputdata).")   order by orderid desc limit 0,1000");
					if(empty($tempattr)){
						continue;
					}
					$attrdata['attrid'] = $tempattr['id'];
					$attrdata['value'] = $tempattr['name'];
					$this->mysql->insert(Mysite::$app->config['tablepre']."shopattr",$attrdata);
				}else{
					continue;
				}
			}
			//is_search
			$this->mysql->delete(Mysite::$app->config['tablepre']."shopsearch"," shopid='".$shopinfo['id']."'  and parent_id in(".join(',',$tempinfo).") ");
			foreach($attrinfo as $key=>$value){
				if($value['is_search'] == 1 && $value['type'] != 'input'){
					$inputdata = IFilter::act(IReq::get('mydata'.$value['id']));
					$temp = is_array($inputdata)?$inputdata:array($inputdata);
					foreach($temp as $ky=>$val){
						$searchdata['shopid'] = $shopinfo['id'];
						$searchdata['parent_id'] = $value['id'];
						$searchdata['cattype'] =$shopinfo['shoptype'];
						$searchdata['second_id'] = intval($val);
						if($val > 0){
							$this->mysql->insert(Mysite::$app->config['tablepre']."shopsearch",$searchdata);
						}
					}

				}
			}
		}


		$link = '';
		if(empty($shopinfo['shoptype'])){

			$link = IUrl::creatUrlEx($this->mid,'shopcenter/usershopfast');
			$this->message('success',$link);
		}elseif($shopinfo['shoptype'] == 1){

			$link = IUrl::creatUrlEx($this->mid,'shopcenter/usershopmarket');
			$this->message('success',$link);
		}

	}
	//获取店铺通知
	function usershopnotice(){
		$this->checkshoplogin();
		$uid = IFilter::act(IReq::get('uid'));
		if(!empty($uid)){
			$data['notice_info'] = IReq::get('notice_info');
			$shopid = ICookie::get('adminshopid');
			$link = IUrl::creatUrlEx($this->mid,'shopcenter/usershopnotice');
			$this->mysql->update(Mysite::$app->config['tablepre'].'shop',$data,"id='".$shopid."'");
			$this->message('success',$link);
		}
	}
	//获取店铺促销信息
	function usershopcxnotice(){
		$this->checkshoplogin();
		$uid = IFilter::act(IReq::get('uid'));
		if(!empty($uid)){
			$data['cx_info'] = IReq::get('cx_info');
			$shopid = ICookie::get('adminshopid');
			$link = IUrl::creatUrlEx($this->mid,'shopcenter/usershopcxnotice');
			$this->mysql->update(Mysite::$app->config['tablepre'].'shop',$data,"id='".$shopid."'");
			$this->message('success',$link);
		}
	}
	//获取店铺 介绍信息
	function usershopinstro()
	{
		$this->checkshoplogin();
		$uid = IFilter::act(IReq::get('uid'));
		if(!empty($uid)){
			$data['intr_info'] = IReq::get('intr_info');
			$shopid = ICookie::get('adminshopid');
			$link = IUrl::creatUrlEx($this->mid,'shopcenter/usershopinstro');
			$this->mysql->update(Mysite::$app->config['tablepre'].'shop',$data,"id='".$shopid."'");
			$this->message('success',$link);
		}
	}

	//店铺商品信息
	function goodslist(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		$shopinfo = $this->shopinfo();
		if(empty($shopinfo)) $this->message('shop_noexit');
		$shoptype = $shopinfo['shoptype'];
		if(empty($shopid)) $this->message('emptycookshop');
		if(empty($shoptype)){
			$listtype = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodstype where shopid = '".$shopid."'  order by orderid asc  ");
		}elseif($shoptype ==1){
			$listtype = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."marketcate where shopid = '".$shopid."'  order by orderid asc  ");
		}
		//获取该菜单下的所有商品
		$alllist = array();
		if(is_array($listtype))
		{
			foreach($listtype as $key=>$value)
			{
				$value['det'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goods where typeid = '".$value['id']."' and shopid=".$shopid." order by good_order asc limit 0,1000  ");
				$alllist[]= $value;
			}
		}
		$data['list'] =$alllist;
		//获取所有的  商品标签 goodssign
		$goodssign = $this->mysql->getarr("select id,imgurl,name,instro from ".Mysite::$app->config['tablepre']."goodssign where type = 'goods'  order by id asc  ");
		$checksign = array();
		if(is_array($goodssign)){
			foreach($goodssign as $key=>$value){
				$checksign[] = $value['id'];
			}
		}
		$data['mid'] = $this->mid;
		$data['goodssign'] = $goodssign;
		$data['checksign'] = $checksign;
		$data['showshu'] = count($goodssign);
		$data['jsondata'] = json_encode($goodssign);
		Mysite::$app->setdata($data);
	}
	//超市产品
	function marketgoodslist(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		$shopinfo = $this->shopinfo();
		if(empty($shopinfo)) $this->message('shop_noexit');
		$shoptype = $shopinfo['shoptype'];
		if(empty($shopid)) $this->message('emptycookshop');
		$topid =   intval(IFilter::act(IReq::get('topid')));
		$toplist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."marketcate where shopid = '".$shopid."' and parent_id = 0 order by orderid asc  ");
		$topcheck = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."marketcate where shopid = '".$shopid."' and parent_id = 0 and id=".$topid." order by orderid asc  ");
		$newtopid = !empty($topcheck) ? $topid:0;
		if($newtopid == 0){
			if(isset($toplist['0']['id']) && count($toplist) > 0){
				$newtopid = $toplist[0]['id'];
			}
		}
		$data['toplist'] = $toplist;
		$data['topid'] = $newtopid;
		$listtype =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."marketcate where shopid = '".$shopid."' and parent_id =".$newtopid." order by orderid asc  ");
		$alllist = array();
		if(is_array($listtype))
		{
			foreach($listtype as $key=>$value)
			{
				$value['det'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goods where typeid = '".$value['id']."' and shopid=".$shopid." order by id asc limit 0,1000  ");
				$alllist[]= $value;
			}
		}
		$data['list'] =$alllist;
		//获取所有的  商品标签 goodssign
		$goodssign = $this->mysql->getarr("select id,imgurl,name,instro from ".Mysite::$app->config['tablepre']."goodssign where type = 'goods'  order by id asc  ");
		$checksign = array();
		if(is_array($goodssign)){
			foreach($goodssign as $key=>$value){
				$checksign[] = $value['id'];
			}
		}
		$data['goodssign'] = $goodssign;
		$data['checksign'] = $checksign;
		$data['showshu'] = count($goodssign);
		$data['jsondata'] = json_encode($goodssign);
		Mysite::$app->setdata($data);
	}
	//保存商品类型
	function savegoodstype(){
		$data['name'] = IFilter::act(IReq::get('name'));
		$data['orderid'] = intval(IReq::get('orderid'));
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		$data['shopid'] =    $shopid;
		if(empty($data['shopid'])) $this->message('emptycookshop');
		$shopinfo = $this->shopinfo();
		if(!(IValidate::len($data['name'],1,10)))$this->message('goods_namelenth');
		if(empty($shopinfo['shoptype'])){
			$checkwaimai = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast where shopid = '".$shopid."'  order by shopid asc  ");
			$data['cattype'] = 0;
			if(empty($checkwaimai)) $this->message('shop_noexit');
			$checkshuliang = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."goodstype where shopid='".$shopid."'");
			$checkshuliang +=1;
			if(Mysite::$app->config['shoptypelimit'] < $checkshuliang) $this->message('goods_countlimit');
			$this->mysql->insert(Mysite::$app->config['tablepre'].'goodstype',$data);
		}elseif($shopinfo['shoptype'] == 1){
			$checkwaimai = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket where shopid = '".$shopid."'  order by shopid asc  ");
			if(empty($checkwaimai)) $this->message('shop_noexit');
			$checkshuliang = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."marketcate where shopid='".$shopid."'");
			$checkshuliang +=1;
			if(Mysite::$app->config['shoptypelimit'] < $checkshuliang) $this->message('goods_countlimit');
			$data['orderid'] = $data['orderid'] == 0 ? $checkshuliang:$data['orderid'];
			$parent_id =  intval(IFilter::act(IReq::get('parent_id')));
			if($parent_id > 0){
				$checkshuliang = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."marketcate where shopid='".$shopid."' and id=".$parent_id."");
				if(empty($checkshuliang)) $this->message('goods_parentnoown');
			}
			$data['parent_id'] = $parent_id;
			$this->mysql->insert(Mysite::$app->config['tablepre'].'marketcate',$data);
		}
		$this->success('success');
	}
	//编辑商品分类信息
	function editgoodstype()
	{
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');
		$shopinfo = $this->shopinfo();
		$what = trim(IFilter::act(IReq::get('what')));
		$addressid = intval(IReq::get('addressid'));
		if(empty($addressid))$this->message('goods_emptytype');//$this->json(array('error'=>true,'message'=>''));
		if($what == 'name')
		{
			$arr['name'] = IFilter::act(IReq::get('controlname'));
			if(!(IValidate::len($arr['name'],2,10))) $this->message('gods_typelenth');// $this->json(array('error'=>true,'message'=>''));
			if(empty($shopinfo['shoptype'])){
				$this->mysql->update(Mysite::$app->config['tablepre'].'goodstype',$arr,"id='".$addressid."' and shopid='".$shopid."' ");
			}elseif($shopinfo['shoptype'] == 1){
				$this->mysql->update(Mysite::$app->config['tablepre'].'marketcate',$arr,"id='".$addressid."' and shopid='".$shopid."' ");
			}

			$this->success('success');// $this->json(array('error'=>false,'message'=>'操作完成'));
		}elseif($what == 'orderid'){
			$arr['orderid'] = intval(IReq::get('controlname'));
			if(empty($shopinfo['shoptype'])){
				$this->mysql->update(Mysite::$app->config['tablepre'].'goodstype',$arr,"id='".$addressid."' and shopid='".$shopid."' ");
			}elseif($shopinfo['shoptype'] == 1){
				$this->mysql->update(Mysite::$app->config['tablepre'].'marketcate',$arr,"id='".$addressid."' and shopid='".$shopid."' ");
			}

			$this->success('操作成功');// $this->json(array('error'=>false,'message'=>'操作完成'));
		}elseif($what == 'allinfo'){
			$arr['name'] = IFilter::act(IReq::get('name'));
			$arr['orderid'] = intval(IFilter::act(IReq::get('orderid')));
			if(empty($shopinfo['shoptype'])){
				$this->mysql->update(Mysite::$app->config['tablepre'].'goodstype',$arr,"id='".$addressid."' and shopid='".$shopid."' ");
			}elseif($shopinfo['shoptype'] == 1){
				$arr['parent_id'] = intval(IFilter::act(IReq::get('parent_id')));
				$this->mysql->update(Mysite::$app->config['tablepre'].'marketcate',$arr,"id='".$addressid."' and shopid='".$shopid."' ");
			}
			$this->success('success');
		}else{
			$this->message('nodefined_func');//  		$this->json(array('error'=>true,'message'=>'提交失败'));
		}
	}
	//修改商品的销量（仅限后台管理员可以修改，商家没权限）
	function savesellcounts(){

		$goodid = intval( IFilter::act(IReq::get('goodid')) );
		$savesellcounts = intval( IFilter::act(IReq::get('savesellcounts')) );
		if( empty($goodid) )  $this->message("获取商品失败！");
		$goodinfo = $this->mysql->select_one( "select * from ".Mysite::$app->config['tablepre']."goods where id='".$goodid."'" );
		if(empty($goodinfo)) $this->message("获取商品失败！");
		$data['virtualsellcount'] = $savesellcounts;
		$this->mysql->update(Mysite::$app->config['tablepre'].'goods',$data,"id='".$goodid."'");
		$this->success('success');
	}
	function goodsone(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		$data['adminuid'] = ICookie::get('adminuid');
		if(empty($shopid)) $this->message('emptycookshop');
		$shopinfo = $this->shopinfo();
		if(empty($shopinfo)) $this->message('shop_noexit');
		$shoptype = $shopinfo['shoptype'];
		$id = intval(IFilter::act(IReq::get('gid')));
		$mid = intval(IFilter::act(IReq::get('mid')));
		if($mid != $this->mid){
			$this->message('非法操作');
		}
		if(empty($id)) $this->message('goods_empty');
		$goodsinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goods where shopid='".$shopinfo['id']."' and id=".$id."");
		if(empty($goodsinfo)) $this->message('goods_empty');
		if(empty($shoptype)){
			$listtype = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodstype where shopid = '".$shopinfo['id']."'  order by orderid asc  ");
		}elseif($shoptype ==1){
			$listtype = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."marketcate where shopid = '".$shopinfo['id']."'  and parent_id  != 0 order by orderid asc  ");
		}
		//获取所有的  商品标签 goodssign
		$goodssign = $this->mysql->getarr("select id,imgurl,name,instro from ".Mysite::$app->config['tablepre']."goodssign where type = 'goods'  order by id asc  ");
		$checksign = array();
		if(is_array($goodssign)){
			foreach($goodssign as $key=>$value){
				$checksign[] = $value['id'];
			}
		}

		$cxinfo =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goodscx where    goodsid=".$id."");
		if(!empty($cxinfo)){
			$nowdate = strtotime(date('Y-m-d',time()));
			$cxinfo['cxstime1'] = date('H:i',$nowdate+$cxinfo['cxstime1']);
			$cxinfo['cxetime1'] = date('H:i',$nowdate+$cxinfo['cxetime1']);
			$cxinfo['cxstime2'] = empty($cxinfo['cxstime2'])?$cxinfo['cxstime2']:date('H:i',$nowdate+$cxinfo['cxstime2']);
			$cxinfo['cxetime2'] = empty($cxinfo['cxetime2'])?$cxinfo['cxetime2']:date('H:i',$nowdate+$cxinfo['cxetime2']);
		}
		$data['cxinfo'] = $cxinfo;



		//  goodsinfo
		$product_attr = empty($goodsinfo['product_attr'])?array():unserialize($goodsinfo['product_attr']);
		$productlist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."product where goodsid = '".$goodsinfo['id']."'  order by id asc  ");
		//产品离别哦
		$data['gglist'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodsgg where shoptype = '".$shoptype."'  order by orderid asc limit 0,1000  ");
		$data['productlist'] = $productlist;
		$chilids = array();
		foreach($productlist as $key=>$value){
			$tepmids = explode(',',$value['attrids']);
			foreach($tepmids as $k=>$value){
				$chilids[]=$value;
			}
		}
		$chilids = array_unique($chilids);
		$product_attrkey = array_keys($product_attr);
		sort($product_attrkey);

		$data['ggfids'] = join(',',$product_attrkey);
		$data['fdidsss'] = 		$chilids;
		$data['goodssign'] = $goodssign;
		$data['checksign'] = $checksign;
		$data['showshu'] = count($goodssign);
		$data['goodsinfo'] = $goodsinfo;
		$data['listtype'] = $listtype;
		Mysite::$app->setdata($data);
	}
	function savegoodsall(){
		$this->checkshoplogin();
		$gid = intval(IFilter::act(IReq::get('gid')));
		$shopid = ICookie::get('adminshopid');
		$link = IUrl::creatUrlEx($this->mid,'shopcenter/goodsone/gid/'.$gid);
		if(empty($shopid)) $this->message('emptycookshop',$link);
		$shopinfo = $this->shopinfo();
		if(empty($shopinfo)) $this->message('shop_noexit',$link);
		$shoptype = $shopinfo['shoptype'];
		if(empty($gid)) $this->message('goods_empty',$link);
		$goodsinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goods where shopid='".$shopinfo['id']."' and id=".$gid."");
		if(empty($goodsinfo)) $this->message('goods_empty',$link);
		//构造数据
		$data['is_waisong'] =intval(IFilter::act(IReq::get('is_waisong')));
		$data['is_dingtai'] =intval(IFilter::act(IReq::get('is_dingtai')));
		$tempweek = IFilter::act(IReq::get('weeks'));
		$data['weeks'] = is_array($tempweek)?join(',',$tempweek):$tempweek;
		$data['goodattr'] =IFilter::act(IReq::get('goodattr'));
		$data['is_new'] =intval(IFilter::act(IReq::get('is_new')));
		$data['is_hot'] =intval(IFilter::act(IReq::get('is_hot')));
		$data['is_com'] =intval(IFilter::act(IReq::get('is_com')));
		$temp = IFilter::act(IReq::get('cxids'));
		$data['signid'] = is_array($temp)?join(',',$temp):$temp;
		$data['typeid'] = intval(IFilter::act(IReq::get('typeid')));
		$data['instro'] = IReq::get('instro');



		$have_det = intval(IFilter::act(IReq::get('have_det')));
		$data['have_det'] = 0;
		$data['product_attr'] = '';
		$idtonamearray = array();
		if($have_det == 1){
			$fggids = trim(IFilter::act(IReq::get('fggids')));
			if(!empty($fggids)){
				$gglist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodsgg where  FIND_IN_SET( `id` , '".$fggids."' ) and parent_id = 0  order by orderid asc limit 0,1000  ");
				$product_attr = array();
				if(!empty($gglist)){//获取所有规格不为空
					foreach($gglist as $key=>$value){
						$checkid = IFilter::act(IReq::get('choicegg'.$value['id']));
						if(!empty($checkid)){
							$checkid = is_array($checkid)?join(',',$checkid):intval($checkid);
							$value['det'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodsgg where  FIND_IN_SET( `id` , '".$checkid."' ) and parent_id = ".$value['id']."  order by orderid asc limit 0,1000  ");
							$product_attr[$value['id']] = $value;
							foreach($value['det']  as $k=>$v){
								$idtonamearray[$v['id']] = $v['name'];
							}
						}
					}
				}
				if(count($product_attr) > 0){

					$data['have_det'] = 1;
					$data['product_attr'] = serialize($product_attr);
					//循环写入入字表
					// goodsdetids
					$goodsdetids  =IFilter::act(IReq::get('goodsdetids'));  //删除所有 改商品 gooids 相同但不在goodsdetids 里的值
					$goodsdetids = is_array($goodsdetids)?join(',',$goodsdetids):intval($goodsdetids);

					$this->mysql->delete(Mysite::$app->config['tablepre'].'product'," `id` not in(".$goodsdetids.")  and `goodsid`=".$gid." ");
					$productlist = array();
					$gg_scost=IFilter::act(IReq::get('gg_scost'));
					$gg_sstock = IFilter::act(IReq::get('gg_sstock'));
					$gg_sids =  IFilter::act(IReq::get('gg_sids'));
					$goodsdetids =  IFilter::act(IReq::get('goodsdetids'));
					if(is_array($gg_scost)){
						$data['count'] = 0;
						$data['cost'] = 1000000;
						foreach($gg_scost as $key=>$value){
							if(isset($gg_sids[$key]) && !empty($gg_sids[$key])){
								$tempids = $gg_sids[$key];
								$attr_ids = explode(',',$tempids);
								$attr_arr = array();
								foreach($attr_ids as $k=>$v){
									if(isset($idtonamearray[$v])){
										$attr_arr[] = $idtonamearray[$v];
									}
								}
								$prodata['shopid'] = $shopid;
								$prodata['goodsid'] = $gid;
								$prodata['goodsname'] = $goodsinfo['name'];
								$prodata['attrname'] = join(',',$attr_arr);//需要根据参数
								$prodata['attrids']   = $gg_sids[$key];//需要根据参数
								$prodata['stock'] =  isset($gg_sstock[$key])?$gg_sstock[$key]:0;//需要参数量
								$prodata['bagcost'] = $goodsinfo['bagcost'];
								$prodata['cost'] = $value;//
								$prodata['id'] = $goodsdetids[$key];
								$productlist[] = $prodata;
								$data['cost'] = $data['cost']>$value?$value:$data['cost'];
								$data['count'] = $data['count']+$prodata['stock'];
							}
						}
					}
					foreach($productlist as $key=>$value){
						if($value['id'] > 0){
							$tempp = $value;
							unset($tempp['id']);
							$this->mysql->update(Mysite::$app->config['tablepre'].'product',$tempp,"id='".$value['id']."'  ");
						}else{
							unset($value['id']);
							$this->mysql->insert(Mysite::$app->config['tablepre'].'product',$value);
						}
					}

					if( empty($productlist) ){
						$data['have_det'] = 0;
					}


				}
			}
		}

		/* 限时促销折扣 */
		$data['descgoods'] = IReq::get('descgoods');

		$data['is_cx'] = intval(IFilter::act(IReq::get('is_cx')));
		if($data['is_cx']==1){
			//检查促销规则
			$cxzhe = intval(IFilter::act(IReq::get('cxzhe')));
			if($cxzhe < 1 || $cxzhe > 100){
				$this->message('折扣比例设置错误',$link);
			}
			$cxdata['cxzhe'] = $cxzhe;
			$cxstarttime  = trim(IFilter::act(IReq::get('cxstarttime')));
			if(empty($cxstarttime)){
				$this->message('促销开始日期错误',$link);
			}
			$cxdata['cxstarttime'] = strtotime($cxstarttime);
			$ecxendttime  = trim(IFilter::act(IReq::get('ecxendttime')));
			if(empty($ecxendttime)){
				$this->message('促销结束日期错误',$link);
			}
			$cxdata['ecxendttime'] = strtotime($ecxendttime)+86399;
			if($cxdata['cxstarttime'] > $cxdata['ecxendttime']){
				$this->message('开始时间大于结束日期',$link);
			}

			$nowdate = date('Y-m-d',time());
			$nowtime = strtotime($nowdate);

			$cxstime1 = trim(IFilter::act(IReq::get('cxstime1')));
			if(empty($cxstime1)){
				$this->message('第一个时间开始时间不能为空',$link);
			}
			$temptime  = strtotime($nowdate.' '.$cxstime1);
			$cxdata['cxstime1'] = $temptime-$nowtime;
			$cxetime1 = trim(IFilter::act(IReq::get('cxetime1')));
			if(empty($cxetime1)){
				$this->message('第一个时间结束时间不能为空',$link);
			}
			$temptime  = strtotime($nowdate.' '.$cxetime1);
			$cxdata['cxetime1'] = $temptime-$nowtime;
			if($cxdata['cxstime1'] > $cxdata['cxetime1']){
				$this->message('第一个时间段开始时间大于结束时间',$link);
			}
			$cxstime2 = trim(IFilter::act(IReq::get('cxstime2')));
			if(empty($cxstime2)){
				$cxdata['cxstime2'] = 0;
				$cxdata['cxetime2'] = 0;
			}else{
				$temptime  = strtotime($nowdate.' '.$cxstime2);
				$cxdata['cxstime2'] = $temptime-$nowtime;
				$cxetime2 = trim(IFilter::act(IReq::get('cxetime2')));
				if(empty($cxetime2)){
					$this->message('第二个时间结束时间不能为空',$link);
				}
				$temptime  = strtotime($nowdate.' '.$cxetime2);
				$cxdata['cxetime2'] = $temptime-$nowtime;
				if($cxdata['cxstime2'] > $cxdata['cxetime2']){
					$this->message('第一个时间段开始时间大于结束时间',$link);
				}
			}
			$cxdata['goodsid'] = $gid;
			if($goodsinfo['is_cx'] == 1){
				$this->mysql->update(Mysite::$app->config['tablepre'].'goodscx',$cxdata,"goodsid='".$gid."' ");
			}else{
				$this->mysql->insert(Mysite::$app->config['tablepre'].'goodscx',$cxdata);
			}
		}else{
			$this->mysql->delete(Mysite::$app->config['tablepre'].'goodscx',"goodsid = '$gid'");
		}


		$this->mysql->update(Mysite::$app->config['tablepre'].'goods',$data,"id='".$gid."' and  shopid = '".$shopinfo['id']."'");
		$data['id'] = $gid;
		$goodsinfo['typeid'] = $data['typeid'];
		$goodsinfo['have_det'] = $data['have_det'];
		echo "<script>parent.refreshgoods(".json_encode($goodsinfo).");</script>";
		exit;
	}
	function addgoods(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');
		$shopinfo = $this->shopinfo();
		$data['name'] = trim(IFilter::act(IReq::get('name')));
		$data['typeid'] = IFilter::act(IReq::get('typeid'));
		$data['count'] = intval(IFilter::act(IReq::get('count')));
		$data['cost'] = IFilter::act(IReq::get('cost'));
		$data['bagcost'] = IFilter::act(IReq::get('bagcost'));
		$data['good_order'] = IFilter::act(IReq::get('good_order'));
		$data['is_live'] = IFilter::act(IReq::get('is_live'));
		$data['is_waisong'] = IFilter::act(IReq::get('is_waisong'));
		$data['is_dingtai'] = IFilter::act(IReq::get('is_dingtai'));
		$data['img'] = '';
		$data['signid'] = '';
		$checkshuliang = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."goods where shopid='".$shopid."'");
		$checkshuliang +=1;
		if(Mysite::$app->config['shopgoodslimit'] < $checkshuliang) $this->message('goods_limit');
		if(!(IValidate::len($data['name'],2,50))) $this->message('goods_titlelenth');
		$chekcount = $data['cost']*100;
		if($data['cost'] < 0) $this->message('goods_cost');
		$data['shopid'] =  $shopid;
		$data['uid'] = $this->member['uid'];
		$data['point'] = 0;
		$data['sellcount']   = 0;
		$data['instro'] = '';
		$data['daycount'] = 100;
		$data['shoptype'] = $shopinfo['shoptype'];
		$this->mysql->insert(Mysite::$app->config['tablepre'].'goods',$data);
		$id = $this->mysql->insertid();
		$info = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goods where id = '$id'");
		if(empty($info)) $this->message('goods_empty');
		$this->success($info);
	}
	function updategoods(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');
		$controlname =  trim(IFilter::act(IReq::get('controlname')));
		$goodsid =  intval(IReq::get('goodsid'));
		$values =  trim(IReq::get('values'));
		if(empty($goodsid)) $this->message('goods_empty');
		switch($controlname){
			case 'name'://更新商品标题
				if(!(IValidate::len($values,2,50))) $this->message('goods_titlelenth');
				$data['name'] = $values;
				$cdata['goodsname'] = $data['name'];
				$this->mysql->update(Mysite::$app->config['tablepre'].'product',$cdata,"goodsid='".$goodsid."' ");
				break;
			case 'count':
                                if(!preg_match('/^\d+$/i', $values)){
                                    $this->message('只能输入整数');
                                }
				$data['count'] = intval($values);
				$data['daycount']= intval($values);
				break;
			case 'instro':
				if(!(IValidate::len($values,0,200))) $this->message('goods_instrolenth');
				$data['instro'] = $values;
				break;
			case 'cost':
				$values = $values * 100;
				$kk = intval($values);
				if($kk < 0) $this->message('goods_cost');
				$data['cost'] = $values/100;
				break;
			case 'bagcost':
				$values = $values * 100;
				$kk = intval($values);
				if($kk < 0) $this->message('goods_bagcost');
				$data['bagcost'] = $values/100;

				/****更新所有规格***/
				$cdata['bagcost'] = $data['bagcost'];
				$this->mysql->update(Mysite::$app->config['tablepre'].'product',$cdata,"goodsid='".$goodsid."' ");
				break;
			case 'good_order':
				$values = $values;
				$kk = intval($values);
				if($kk < 0) $this->message('good_order');
				$data['good_order'] = $values;
				break;
			case 'sellcount':
				$values = $values * 100;
				$kk = intval($values);
				if($kk < 0) $this->message('goods_count');
				$data['sellcount'] = $values/100;
				break;
			case 'typeid':
				$values = intval($values);
				if(empty($values)) $this->message('goods_typeid');
				$shopinfo = $this->shopinfo();
				$checkshuliang = 0;
				if(empty($shopinfo['shoptype'])){
					$checkshuliang = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."goodstype where id = '$values' and shopid='".$shopid."'");
				}elseif($shopinfo['shoptype']==1){
					$checkshuliang = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."marketcate where id = '$values' and shopid='".$shopid."'");
				}
				if($checkshuliang < 1) $this->message('goods_typeid');
				$data['typeid'] = $values;
				break;
			case 'signid':
				if(empty($values)) $this->message('goods_sign');
				$data['signid'] = $values;
				break;
			case 'is_new':
				$data['is_new'] = $values;
				break;
			case 'is_com':
				$data['is_com'] = $values;
				break;
			case 'is_hot':
				$data['is_hot'] = $values;
				break;
			case 'is_live':
				$data['is_live'] = $values;
				break;
			default:
				$this->message('nodefined_func');
				break;
		}
		$this->mysql->update(Mysite::$app->config['tablepre'].'goods',$data,"id='".$goodsid."' and  shopid = '".$shopid."'");
		$this->success('success');
	}
	function delgoods(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');
		$uid = intval(IReq::get('id'));
		if(empty($uid))  $this->message('goods_empty');//(array('error'=>true,'msg'=>''));
		$this->mysql->delete(Mysite::$app->config['tablepre'].'goods',"id = '$uid' and  shopid='".$shopid."'");
		$this->mysql->delete(Mysite::$app->config['tablepre'].'product',"goodsid = '$uid' and  shopid='".$shopid."'");
		$this->success('success');
	}
//改变支持状态yanyh
	function changegoods(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');
		$uid 		= intval(IReq::get('id'));
		$is_support = intval(IReq::get('is_support'));
		$type 		= IReq::get('type');
		if(empty($uid))  $this->message('goods_empty');//(array('error'=>true,'msg'=>''));
		if($type == 'ws'){
			$this->mysql->update(Mysite::$app->config['tablepre'].'goods',"is_waisong = '".$is_support."'","id ='".$uid."'");
		}else if($type == 'dt'){
			$this->mysql->update(Mysite::$app->config['tablepre'].'goods',"is_dingtai = '".$is_support."'","id ='".$uid."'");
		}
		$this->success('success');
	}
        //改变支付类型状态
	function changepayment(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');
		$uid 		= intval(IReq::get('id'));
		$is_support = intval(IReq::get('is_support'));
		if(empty($uid))  $this->message('goods_empty');//(array('error'=>true,'msg'=>''));
                $type 		= IReq::get('type');
		if($type == 'card'){
			$this->mysql->update(Mysite::$app->config['tablepre'].'goods',"is_membercard = '".$is_support."'","id ='".$uid."'");
		}else if($type == 'coupon'){
			$this->mysql->update(Mysite::$app->config['tablepre'].'goods',"is_coupon = '".$is_support."'","id ='".$uid."'");
		}
		$this->success('success');
	}
	//添加促销信息
	function addcxrule(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');
		/// $shopinfo = $this->shopinfo();
		$id = intval(IReq::get('id'));
		$this->setstatus();
		$data['cxsignlist'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodssign where type='cx' order by id desc limit 0, 100");
		$data['cxinfo'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."rule where id = '".$id."'  and shopid =  ".$shopid."  order by id desc limit 0, 100");
		Mysite::$app->setdata($data);
	}
	//保存促销信息
	function savecxrule(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');
		$shopinfo = $this->shopinfo();
		$cxid = intval(IReq::get('cxid'));
		$data['name']=  trim(IFilter::act(IReq::get('name')));
		$data['limitcontent'] = intval(IReq::get('limitcontent'));
		$controltype = intval(IReq::get('controltype'));
		$data['cattype'] = $shopinfo['shoptype'];
		if(empty($data['name'])) $this->message('cx_titleerr');
		$data['type'] = 1;//默认购物车限制
		$limittype = intval(IReq::get('limittype'));//limittype int(1)   否       1表示 不制定    2表示制定星期     3表示具体时间段
		$data['limittype'] = in_array($limittype,array('1,','2','3')) ? $limittype:1;

		if($data['limittype'] ==  1){
			$data['limittime'] = '';
		}elseif($data['limittype'] == 2){
			$limittime = IFilter::act(IReq::get('limittime1'));
			if(!is_array($limittime)) $this->message('errweek');
			$data['limittime'] = join(',',$limittime);
		}else{
			$limittime2 = IFilter::act(IReq::get('limittime2'));
			$limittime22 = IFilter::act(IReq::get('limittime22'));

			if(empty($limittime2) || empty($limittime22)) $this->message('errtime');
			$limittime2 = is_array($limittime2)? $limittime2:array($limittime2);
			$limittime22 = is_array($limittime22)? $limittime22:array($limittime22);
			if(count($limittime2) != count($limittime22)) $this->message('errtime');
			$contents = '';
			foreach($limittime2 as $key=>$value){
				//12:00-13:00,
				if(!empty($value) && !empty($limittime22[$key])){
					$contents .= $value.'-'.$limittime22[$key].',';

				}
			}
			if(empty($contents)) $this->message('errtime');
			$data['limittime'] = substr($contents,0,strlen($contents)-1);
		}
		$supporttype = IFilter::act(IReq::get('supporttype'));//支持类型
		if(!is_array($supporttype)){
			$data['supporttype'] = '';
		}else{
			$data['supporttype'] = join(',',$supporttype);
		}

		$supportplatform = IFilter::act(IReq::get('supportplatform'));//支持平台
		if(!is_array($supportplatform)){
			$data['supportplatform'] = '';
		}else{
			$data['supportplatform'] = join(',',$supportplatform);
		}
		if(!in_array($controltype,array('1','2','3','4'))) $this->message('cx_typeerr');
		$data['controltype'] = $controltype;
		$data['controlcontent'] = intval(IReq::get('controlcontent'));
		if($controltype != 4){
			if(empty($data['controlcontent'])) $this->message('cx_typeerr');
		}
		$data['presenttitle'] = $controltype == 1? trim(IFilter::act(IReq::get('presenttitle'))):'';
		$starttime = IFilter::act(IReq::get('starttime'));
		$endtime = IFilter::act(IReq::get('endtime'));
		if(empty($starttime)) $this->message('cx_starttime');
		if(empty($endtime)) $this->message('cx_endtime');
		$data['signid'] = intval(IReq::get('signid'));
		if(empty($data['signid'])) $this->message('cx_signerr');
		$data['starttime'] = strtotime($starttime.' 00:00:00');
		$data['endtime'] = strtotime($endtime.' 23:59:59');
		$data['status'] = intval(IReq::get('status'));
		$data['shopid'] = $shopid;

		if(empty($cxid)){
			$this->mysql->insert(Mysite::$app->config['tablepre'].'rule',$data);
		}else{
			unset($data['shpid']);
			$this->mysql->update(Mysite::$app->config['tablepre'].'rule',$data,"id='".$cxid."' and shopid = '".$shopid."'");
		}
		//
		$this->success('success');//(array('error'=>false));
	}
	function delcxrule(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');
		$cxid = intval(IReq::get('cxid'));
		if(empty($cxid)) $this->message('cx_empty');
		$this->mysql->delete(Mysite::$app->config['tablepre'].'rule',"id='".$cxid."' and shopid = '".$shopid."'");
		$this->success('success');
	}







	function saveshanhuigift(){  //闪惠是否开启送积分设置
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');
		$shopinfo = $this->shopinfo();
		if(empty($shopinfo)) $this->message("未获取到店铺信息！");
		$is_shgift = intval(IReq::get('is_shgift')); // 是否开启送积分设置 0为关闭 1为开启
		$sendgift = intval(IReq::get('sendgift'));  // 多少元送1积分
		if(empty($sendgift) || $sendgift == 0 ) $this->message("不能填写为0或者为空");
		$data['is_shgift'] = $is_shgift;
		$data['sendgift'] = $sendgift;
		if( $shopinfo['shoptype'] == 0){
			$this->mysql->update(Mysite::$app->config['tablepre'].'shopfast',$data,"shopid='".$shopinfo['id']."' ");
		}else{
			$this->mysql->update(Mysite::$app->config['tablepre'].'shopmarket',$data,"shopid='".$shopinfo['id']."' ");
		}
		$this->success('success');

	}
	function makeerwei(){   //制作闪惠二维码
		$this->checkshoplogin();
		$shopid = intval(IReq::get('shopid'));
		$wx_s = new wx_s();
		$ifmake = $wx_s->makeforever($shopid);

		if($ifmake == true){
			$wxhui_ewmurl = $wx_s->get_shopurl();

		}else{
			$this->message("生成二维码数据失败");
		}
		if(!empty($wxhui_ewmurl)){
			$data['wxhui_ewmurl'] = $wxhui_ewmurl;
			#	print_r($wxhui_ewmurl);
			$this->mysql->update(Mysite::$app->config['tablepre'].'shop',$data,"id='".$shopid."' ");
			$this->success($wxhui_ewmurl);

		}else{
			$this->message("生成二维码数据失败");
		}

	}
	function setshophui(){
		$this->checkshoplogin();
		$data['shophuilist'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shophui");
		#	print_r($data['shophuilist']);
		$shopinfo = $this->shopinfo();
		$data['shopinfo'] = $shopinfo;
		#print_r($data['shopinfo']);
		if( $shopinfo['shoptype'] == 1 ){
			$shopdata = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket where shopid = '".$shopinfo['id']."'  " );
		}else{
			$shopdata = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast where shopid = '".$shopinfo['id']."'  " );
		}
		$data['shopdata'] = $shopdata;
		#print_r($data['shopdata']);
		Mysite::$app->setdata($data);

	}
	//开启或这关闭闪惠功能
	function saveshanhui(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');
		$shopinfo = $this->shopinfo();
		if(empty($shopinfo)) $this->message("未获取到店铺信息！");
		$is_shophui = intval(IReq::get('is_shophui'));
		$data['is_shophui'] = $is_shophui;

		if( $shopinfo['shoptype'] == 0){
			$this->mysql->update(Mysite::$app->config['tablepre'].'shopfast',$data,"shopid='".$shopinfo['id']."' ");
		}else{
			$this->mysql->update(Mysite::$app->config['tablepre'].'shopmarket',$data,"shopid='".$shopinfo['id']."' ");
		}
		$this->success('success');
	}
	//保存闪惠信息
	function saveshophui(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');
		$shopinfo = $this->shopinfo();
		$cxid = intval(IReq::get('cxid'));
		$data['name']=  trim(IFilter::act(IReq::get('name')));   // 闪惠标题
		$data['mjlimitcost'] = intval(IReq::get('mjlimitcost'));  //每满费用金额
		$data['limitzhekoucost'] = intval(IReq::get('limitzhekoucost'));  //折扣限制金额
		$controltype = intval(IReq::get('controltype'));          // 闪惠类型  2 每满多少减多少   3 折扣
		$data['cattype'] = $shopinfo['shoptype'];
		if(empty($data['name'])) $this->message('闪惠标题不能为空！');
		$limittype = intval(IReq::get('limittype'));			//       limittype int(1)   否       1表示 不制定    2表示制定星期和具体时间段
		$data['limittype'] = in_array($limittype,array('1,','2')) ? $limittype:1;

		if($data['limittype'] ==  1){
			$data['limittimes'] = '';   //不限制时间
			$data['limitweek'] = '';   //不限制时间
			#}
			#elseif($data['limittype'] == 2){

		}else{
			$limittime = IFilter::act(IReq::get('limittime1'));
			if(!is_array($limittime)) $this->message('errweek');
			$data['limitweek'] = join(',',$limittime);  //限制具体星期几


			$limittime2 = IFilter::act(IReq::get('limittime2'));
			$limittime22 = IFilter::act(IReq::get('limittime22'));

			if(empty($limittime2) || empty($limittime22)) $this->message('errtime');
			$limittime2 = is_array($limittime2)? $limittime2:array($limittime2);
			$limittime22 = is_array($limittime22)? $limittime22:array($limittime22);
			if(count($limittime2) != count($limittime22)) $this->message('errtime');
			$contents = '';
			foreach($limittime2 as $key=>$value){
				//12:00-13:00,
				if(!empty($value) && !empty($limittime22[$key])){
					$contents .= $value.'-'.$limittime22[$key].',';

				}
			}
			if(empty($contents)) $this->message('errtime');
			$data['limittimes'] = substr($contents,0,strlen($contents)-1);  // 限制具体时间段
		}



		if(!in_array($controltype,array('2','3','4'))) $this->message('闪惠类型错误！');
		$data['controltype'] = $controltype;   // 促销类型
		$data['controlcontent'] = intval(IReq::get('controlcontent'));  // 限制减费用金额  或者  折扣
		if($controltype == 2){
			if(empty($data['mjlimitcost'])) $this->message('未设置每满费用金额');
		}
		if($controltype == 3){
			if(empty($data['limitzhekoucost'])) $this->message('未设置折扣限制金额');
		}
		if($controltype != 4){
			if(empty($data['controlcontent'])) $this->message('cx_typeerr');
		}
		$starttime = IFilter::act(IReq::get('starttime'));  //生效开始时间
		$endtime = IFilter::act(IReq::get('endtime'));      //结束时间
		if(empty($starttime)) $this->message('未设置闪惠开始时间');
		if(empty($endtime)) $this->message('未设置闪惠结束时间');

		$data['starttime'] = strtotime($starttime.' 00:00:00');
		$data['endtime'] = strtotime($endtime.' 23:59:59');
		$data['status'] = intval(IReq::get('status'));
		$data['shopid'] = $shopid;
		if( $data['status'] == 1 ){
			$checkhuistatus = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shophui where status = 1 and shopid = '".$shopid."' ");

			if( count($checkhuistatus) == 1 ){
				$checkhuione = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shophui where id = '".$cxid."' and  status = 1 ");

				/* print_r($checkhuistatus[0]['id']);
				exit;
				 */
				if( $checkhuistatus[0]['id'] == $checkhuione['id'] ){
					unset($data['shpid']);
					$this->mysql->update(Mysite::$app->config['tablepre'].'shophui',$data,"id='".$cxid."' and shopid = '".$shopid."'");
				}else{
					#if( !empty($checkhuistatus) ){
					$this->message("已开启闪惠规则,只能开启一种,不能兼得！");
					#	}
				}
			}else{
				if( count($checkhuistatus) > 1 ){
					$this->message("已开启闪惠规则,只能开启一种,不能兼得！");

				}
			}
		}
		if(empty($cxid)){
			$this->mysql->insert(Mysite::$app->config['tablepre'].'shophui',$data);
		}else{
			unset($data['shpid']);
			$this->mysql->update(Mysite::$app->config['tablepre'].'shophui',$data,"id='".$cxid."' and shopid = '".$shopid."'");
		}
		//
		$this->success('success');//(array('error'=>false));
	}
	//添加闪惠信息
	function addshophui(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');
		/// $shopinfo = $this->shopinfo();
		$id = intval(IReq::get('id'));
		$this->setstatus();
		$data['cxinfo'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shophui where id = '".$id."'  and shopid =  ".$shopid."  order by id desc limit 0, 100");
		Mysite::$app->setdata($data);
	}
	//删除闪惠信息
	function delshophui(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');
		$cxid = intval(IReq::get('cxid'));
		if(empty($cxid)) $this->message('cx_empty');
		$this->mysql->delete(Mysite::$app->config['tablepre'].'shophui',"id='".$cxid."' and shopid = '".$shopid."'");
		$this->success('success');
	}











	function savepx(){//保存排序
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');
		$shopinfo = $this->shopinfo();
		$pxids = IFilter::act(IReq::get('pxids'));
		$pxindex = IFilter::act(IReq::get('pxindex'));
		if(empty($pxids)||empty($pxindex)) $this->message('system_err');
		$testinfo = explode(',',$pxids);
		//	$newdata = array();
		$test2 = explode(',',$pxindex);
		if(count($testinfo) !=  count($test2)) $this->message('system_err');
		foreach($testinfo as $key=>$value){
			if(intval($value) > 0){
				//$newdata[] = $value;
				$data['orderid'] = intval($test2[$key]);
				if(empty($shopinfo['shoptype'])){
					$this->mysql->update(Mysite::$app->config['tablepre'].'goodstype',$data,"id='".$value."'");
				}elseif($shopinfo['shoptype'] == 1){
					$this->mysql->update(Mysite::$app->config['tablepre'].'marketcate',$data,"id='".$value."'");
				}
				//
			}
		}
		$this->success('success');
	}
	function delgoodstype()
	{
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');
		$shopinfo = $this->shopinfo();
		$uid = intval(IReq::get('addressid'));
		if(empty($uid))  $this->message('goods_emptytype');//$this->json(array('err'=>true,'msg'=>'删除ID不能为空'));
		if(empty($shopinfo['shoptype'])){
			$checkshuliang = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."goodstype where id = '$uid' and shopid='".$shopid."'");
			if($checkshuliang < 1) $this->message('goods_emptytype');//$this->json(array('err'=>true,'msg'=>''));
			$this->mysql->delete(Mysite::$app->config['tablepre'].'goods',"typeid = '$uid' and  shopid='".$shopid."'");
			$this->mysql->delete(Mysite::$app->config['tablepre'].'goodstype',"id = '$uid' and  shopid='".$shopid."'");
		}elseif($shopinfo['shoptype']==1){

			$checkshuliang = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."marketcate where id = '$uid' and shopid='".$shopid."'");
			if($checkshuliang < 1) $this->message('goods_emptytype');//$this->json(array('err'=>true,'msg'=>''));
			$checkinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."marketcate where id = '$uid' and shopid='".$shopid."'");
			if(empty($checkinfo['parent_id'])){
				$checkshuliang = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."marketcate where parent_id = '$uid' and shopid='".$shopid."'");
				if($checkshuliang > 0){
					$this->message('goods_typeexitchild');
				}
			}
			$this->mysql->delete(Mysite::$app->config['tablepre'].'goods',"typeid = '$uid' and  shopid='".$shopid."'");
			$this->mysql->delete(Mysite::$app->config['tablepre'].'marketcate',"id = '$uid' and  shopid='".$shopid."'");
		}
		$this->success('success');
	}

	function setstatus(){
		$data['buyerstatus'] = array(
			'0'=>'待处理订单',
			'1'=>'待发货',
			'2'=>'订单已发货',
			'3'=>'订单完成',
			'4'=>'买家取消订单',
			'5'=>'卖家取消订单'
		);
		$paytypelist = array(0=>'货到支付',1=>'微信支付',2=>'会员卡支付',3=>'小微支付',4=>'零元支付',5=>'现金支付',6=>'支付宝支付',7=>'小程序支付');

		$data['shoptype'] = array(
			'0'=>'外卖',
			'1'=>'超市',
			'2'=>'其他',
		);
		$data['ordertypearr'] = array(
			'0'=>'网站',
			'1'=>'网站',
			'2'=>'电话',
			'3'=>'微信',
			'4'=>'AndroidAPP',
			'5'=>'手机网站',
			'6'=>'iosApp',
			'7'=>'后台客服下单',
			'8'=>'商家后台下单',
			'9'=>'html5手机站'
		);
		$data['backarray'] = array(
			'0'=>'',
			'1'=>'退款中..',
			'2'=>'退款成功',
			'3'=>'拒绝退款'
		);
		$data['payway'] = array(
			'open_acout'=>'余额支付',
			'W'=>'微信支付',
			'M'=>'会员卡支付',
			'alipay'=>'支付宝',
			'alimobile'=>'手机支付宝',
                        'X'=>'现金支付',
		);
		$data['paytypearr'] = $paytypelist;
		Mysite::$app->setdata($data);
	}


	//保存弹出层
	function savegoodinstro(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');
		$goodsid = intval(IFilter::act(IReq::get('goodsid')));
		$instro =  IFilter::act(IReq::get('content'));
		if(empty($goodsid)){
			echo "<script>parent.setinerror('产品ID获取失败');</script>";
			exit;
		}
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) {
			echo "<script>parent.setinerror('COOK失效，请重新登陆');</script>";
			exit;
		}
		$shopinfo = $this->shopinfo();
		if(empty($shopinfo)){
			echo "<script>parent.setinerror('COOK失效，请重新登陆');</script>";
			exit;
		}
		$data['instro'] = $instro;
		$this->mysql->update(Mysite::$app->config['tablepre'].'goods',$data,"id='".$goodsid."' and  shopid = '".$shopinfo['id']."'");
		echo "<script>parent.setinsucess('保存成功');</script>";
		exit;
	}
	function delgoodsimg(){
		$id = intval(IReq::get('id'));
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('shop_noexit');

		$goodsinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goods where id ='".$id."' and shopid ='".$shopid."' ");
		if(empty($goodsinfo)) $this->message('goods_empty');
		if(!empty($goodsinfo['img'])){
			IFile::unlink(hopedir.$goodsinfo['img']);
			$udata['img'] = '';
			$this->mysql->update(Mysite::$app->config['tablepre'].'goods',$udata,"id='".$id."'");

		}
		$this->success('操作成功');



	}

	//超市     商家快速从商品库中选择商品

	function alltoshopgoods(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');

		$shopinfo = $this->shopinfo();
		if(empty($shopinfo)) $this->message('shop_noexit');
		$shoptype = $shopinfo['shoptype'];
		if(empty($shopid)) $this->message('shop_noexit');
		$kutypeid = intval(IFilter::act(IReq::get('kutypeid')));
		$fenleiid = intval(IFilter::act(IReq::get('fenleiid'))); 		// 添加商品所属的分类ID

		$data['fenleiid'] = $fenleiid;
		#	  print_r($data['fenleiid']);
		$data['kutypeid'] = $kutypeid;
		$data['typelist']  =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodslibrarycate order by orderid");
		$where = '';
		$where = $kutypeid > 0?' where typeid ='.$kutypeid:'';
		$this->pageCls->setpage(intval(IReq::get('page')),10);
		$data['list'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodslibrary  ".$where."  limit ".$this->pageCls->startnum().", ".$this->pageCls->getsize()."");
		$shuliang  = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."goodslibrary  ".$where."   ");

		$checkids = array();
		if(is_array($data['list'])){
			foreach($data['list'] as $key=>$value){
				$checkids[] = $value['id'];
			}
		}

		$checkstr = join(',',$checkids);
		$data['checkids'] = array();
		if(!empty($checkstr)){
			$templistids = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goods  where shopid = ".$shopid." and shoptype = ".$shoptype."    and parentid in(".$checkstr.")");
			if(is_array($templistids)){
				foreach($templistids as $key=>$value){

					$data['checkids'][] = $value['parentid'];

				}
			}
		}
		$this->pageCls->setnum($shuliang);
		$data['pagecontent'] = $this->pageCls->getpagebar();// $this->pageCls->getpagebar();
		Mysite::$app->setdata($data);
	}
	function adgoodstoshop(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');

		$shopinfo = $this->shopinfo();
		if(empty($shopinfo)) $this->message('shop_noexit');
		$shoptype = $shopinfo['shoptype'];

		$parentid = intval(IFilter::act(IReq::get('goodsid')));
		$fenleiid = intval(IFilter::act(IReq::get('fenleiid')));
		$yangshiid = intval(IFilter::act(IReq::get('yangshiid')));
		if($parentid < 1) $this->message('产品ID获取失败');
		if($shopid < 1) $this->message('店铺获取失败');
		$tempinfo =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goodslibrary where  id=".$parentid."   ");

		if(empty($tempinfo)) $this->message('商品不存在');
		if($yangshiid == 1){
			//添加
			$checkshu = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."goods  where  parentid =".$parentid."  and shopid = ".$shopid."   ");
			if($checkshu > 0){
				$this->message('此商品已添加');
			}
			$data['shopid'] = $shopid;
			$data['parentid'] = $parentid;
			$data['typeid']  = $fenleiid;
			$data['name']   = $tempinfo['name'];
			$data['count']   =  100 ;
			$data['cost']   =  $tempinfo['cost'];
			$data['img']   =  $tempinfo['img'];
			$data['instro']   =  $tempinfo['instro'];
			$data['shoptype'] = $shoptype;

			$this->mysql->insert(Mysite::$app->config['tablepre']."goods",$data);
			$data['id'] = $this->mysql->insertid();
			$this->success($data);

		}else{
			//删除
			$goodsinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goods where parentid =".$parentid."  and shopid = ".$shopid."   ");

			$this->mysql->delete(Mysite::$app->config['tablepre']."goods","  parentid =".$parentid."  and shopid = ".$shopid."  ");

			$this->mysql->delete(Mysite::$app->config['tablepre']."product","  goodsid =".$goodsinfo['id']."  and shopid = ".$shopid."  ");


			$this->success('操作成功');
		}
	}

	function uploadmarketimggoods(){

		$gid = intval(IFilter::act(IReq::get('gid')));

		$data['img']= trim(IFilter::act(IReq::get('imglujing')));

		$this->mysql->update(Mysite::$app->config['tablepre'].'goods',$data,"id='".$gid."'");

		$this->success('success');


	}

	/************商家订单处理部分***************/
	function shoporderlist(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');
		$starttime = trim(IFilter::act(IReq::get('starttime')));
		$orderSource = intval(IReq::get('orderSource'));
		$nowday = date('Y-m-d',time());
		$starttime = empty($starttime)? $nowday:$starttime;
		$endtime = empty($endtime)? $nowday:$endtime;
		$where = '';
		$where = '  and addtime > '.strtotime($starttime.' 00:00:00').' and addtime < '.strtotime($starttime.' 23:59:59');
                $wh_gobe= " is_goshop!=2 and isbefore!=2 and ";
                
		$data['orderSource'] = $orderSource;
		$data['starttime'] = $starttime;
		$this->setstatus();
		//获取订单的方式是所有 有效订单  status > 0 and < 4 and (paytype == 'outpay' or paytype='open_acout or (paystatus=1)  //

//		$orderSourcetoarray = array(
//			'0'=>' and status > 0  ',
//			'1'=>' and ordertype !=2 and status > 0 and status < 4 and ( paytype = 0 or  paystatus=1)',
//			'2'=>' and ordertype =2 and status > 0 and status < 4 and ( paytype = 0 or  paystatus=1)',
//			'3'=>' and is_make = 0 and status > 0 and status < 3 and ( paytype = 0 or  paystatus=1)',
//			'4'=>' and status = 1 and is_make = 1 and ( paytype =0 or  paystatus=1)',
//			'5'=>' and status > 1 and status < 4  and ( paytype = 0 or  paystatus=1)  ' ,
//			'6'=>' and status > 0 and status < 4  and ( paytype = 1 or  paystatus=1) and is_reback = 1 ' ,
//			'7'=>' and status = 4 and ( paytype = 1 or  paystatus=1) and is_reback = 2 ' ,
//			'8'=>' and status > 0 and status < 4  and ( paytype = 1 or  paystatus=1) and is_reback = 3 '
//
//		);
//
//		if(isset($orderSourcetoarray[$orderSource]) && $orderSource!=0){
//			$where .= ''.$orderSourcetoarray[$orderSource];
//		}
                
                //订单来源:
                $ordertype = IReq::get('ordertype');
                if($ordertype==1){
	            $where .= ' and (ordertype =1 or is_goshop=1 or is_goshop=0)';    //网站订单
                }elseif($ordertype==2){
                    $where .= ' and ordertype =2 and status > 0 and status < 4 and ( paytype = 0 or  paystatus=1) ';  //电话订单
                }
                $data['ordertype'] = $ordertype;
                
                //订单类别:
                $is_goshop = IReq::get('is_goshop');
                if($is_goshop=="0" ){
	            $where .= ' and is_goshop=0 ';    //外卖
                }elseif($is_goshop=="1"){
                    $where .= ' and is_goshop=1 ';  //堂食
                }
                $data['is_goshop'] = $is_goshop;
                
                //订单状态:
                $status = IReq::get('status');
                if($status==3){
	            $where .= ' and is_make = 0 and status > 0 and status < 3 and ( paytype = 0 or  paystatus=1)';    //待确认
                }elseif($status==4){
                    $where .= ' and status = 1 and is_make = 1 and ( paytype =0 or  paystatus=1) ';  //待发货
                }elseif($status==5){
                    $where .= ' and status > 1 and status < 4  and ( paytype = 0 or  paystatus=1) ';  //已发货
                }elseif($status==6){
                    $where .= ' and status > 0 and status < 4  and ( paytype = 1 or  paystatus=1) and is_reback = 1 ';  //待退款
                }elseif($status==7){
                    $where .= ' and status = 4 and ( paytype = 1 or  paystatus=1) and is_reback = 2 ';  //已退款
                }elseif($status==8){
                    $where .= ' and status > 0 and status < 4  and ( paytype = 1 or  paystatus=1) and is_reback = 3 ';  //已拒绝退款
                }elseif($status==9){
                    $where .= ' and (status=4 or is_make=2) and is_reback=0';  //已取消
                }
                $data['status'] = $status;
                
                

		$orderlist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."order where ".$wh_gobe." shopid='".$shopid."'  ".$where." order by id desc limit 0,1000");
		$shuliang  = $this->mysql->select_one("select count(id) as shuliang,sum(allcost) as allcost from ".Mysite::$app->config['tablepre']."order where ".$wh_gobe." shopid='".$shopid."' ".$where." limit 0,1000");
		$data['tongji'] = $shuliang;
		$data['list'] = array();
		if($orderlist)
		{
			foreach($orderlist as $key=>$value)
			{
				$value['detlist'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."orderdet where   order_id = ".$value['id']." order by id desc ");
				if( $value['is_reback'] > 0 ){
					$value['drawbacklog'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."drawbacklog where   orderid = ".$value['id']." limit 1 ");
				}
				$value['maijiagoumaishu'] = 0;
				if($value['buyeruid'] > 0)
				{
					$value['maijiagoumaishu'] =$this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."order where ".$wh_gobe." buyeruid='".$value['buyeruid']."' and  status = 3 order by id desc");
				}
                                if( $value['is_goshop'] ==1 ){
					$value['sn'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."table_qrcode_set where tableid = '".$value['tableid']."'  ");
				}
                                
                                if( $value['is_goshop'] ==1 ){
					$value['tablename'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."table where id = '".$value['tableid']."'  ");
				}
                                
				$data['list'][] = $value;
			}
		}


		$daymintime = strtotime(date('Y-m-d',time()));
		$tempshu =  $this->mysql->select_one("select count(id) as shuliang  from ".Mysite::$app->config['tablepre']."order where ".$wh_gobe." shopid='".$shopid."' and  status > 0  and  status <  4 and posttime > ".$daymintime." limit 0,1000");
		//统计当天订单
		$data['mid'] = $this->mid;
		$data['hidecount'] = $tempshu['shuliang'];
		$data['playwave'] = ICookie::get('playwave'); //shoporderlist
		Mysite::$app->setdata($data);
	}
	function wavecontrol(){
		$type =  IReq::get('type');
		if($type == 'closewave'){
			//关闭声音
			ICookie::set('playwave',2,2592000);
		}else{
			//开启声音
			ICookie::set('playwave',0,2592000);
		}
		$this->success('成功');
	}
	function shopcontrol(){
		$this->checkshoplogin();
		$controlname =trim(IFilter::act(IReq::get('controlname')));
		$orderid = intval(IReq::get('orderid'));
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');
		$shopinfo = $this->mysql->select_one("select uid from ".Mysite::$app->config['tablepre']."shop where id = ".$shopid."");
		switch($controlname)
		{
			case 'unorder':
			$orderinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where id = ".$orderid." and shopid=".$shopid." ");
			 
			  if($orderinfo['is_reback'] > 0 && $orderinfo['is_reback'] < 3) $this->message('order_baklogcantdoover');
	     $reason = trim(IFilter::act(IReq::get('reason')));
	     if(empty($reason)) $this->message('order_emptyclosereason');
	   	 $ordercontrol = new ordercontrol($orderid);
	   	 if($ordercontrol->sellerunorder($shopinfo['uid'],$reason))
	   	 {
	   	 	$ordCls = new orderclass();
			
			$this->mysql->delete(Mysite::$app->config['tablepre'].'orderps',"orderid = '$orderid' and status !=3 "); 
			
			
	               $ordCls->noticeclose($orderid,$reason);
				 $this->success('success');
	     }else{
				  $this->message($ordercontrol->Error());
		   }
			break;
			case 'makeorder':
			//制作该订单

			   $checkorderinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where id = ".$orderid." and shopid=".$shopid." ");
			   if(empty($checkorderinfo)){
			        $this->message('order_noexit');
			   }
			   if($checkorderinfo['status'] != 1){
			        $this->message('order_cantmake');
			   }
			   if(!empty($checkorderinfo['is_make'])){
			       $this->message('order_cantmake');
			   }
			    if($checkorderinfo['is_reback'] > 0 &&  $checkorderinfo['is_reback'] < 3) $this->message('order_baklogcantdoover');
			    $udata['is_make'] = 1;
			   $this->mysql->update(Mysite::$app->config['tablepre'].'order',$udata,"id='".$orderid."'");
		 
		    
				$ordCls = new orderclass();
			
			   $ordCls->writewuliustatus($orderid,4,$checkorderinfo['paytype']);  //商家确认接单  物流状态 
			
	               $ordCls->noticemake($orderid);
			   $this->success('success');
			break;
			case 'unmakeorder': 
			//不制作该订单

		    	$checkorderinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where id = ".$orderid." and shopid=".$shopid." ");
                                
                               //不是先吃后付才发微信取消制作的通知
                                if($checkorderinfo['is_preeat'] != 1){
                                    $ordCls = new orderclass();
                                    $ordCls->writewuliustatus($orderid,14,$checkorderinfo['paytype']);  // 管理员退款给用户 物流信息
                                    $ordCls->noticeunmake($orderid);  //商家不制作微信通知
                                }

			   	if(empty($checkorderinfo)){
			        $this->message('order_noexit');
			   	}
			    if($checkorderinfo['is_reback'] > 0 &&  $checkorderinfo['is_reback'] < 3) $this->message('order_baklogcantdoover');
			   	if($checkorderinfo['status'] != 1){
			        $this->message('order_cantunmake');
			   	}
			   	if($checkorderinfo['is_goshop'] != 1){
				   	if(!empty($checkorderinfo['is_make'])){
					  	 $this->message('order_cantunmake');
				   	}
			   	}
                                
                                //堂食先吃后付取消订单不用退款,到这里就返回
                                if($checkorderinfo['is_preeat'] == 1){
                                    $udata['is_make'] = 2;
                                    $udata['status']    = 4;
	               	            $this->mysql->update(Mysite::$app->config['tablepre'].'order',$udata,"id='".$orderid."'"); 
                                    $this->success('success');
                                }
			  	
			   	$orderinfo = $checkorderinfo;
			   	//退款接口
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
                                    //$this->message('123');
                                  
                                    
				}
				//如果是会员卡支付
				if($orderinfo['paytype_name'] == 'M'){
					$refundOk = true;
				}

				if ($refundOk) {
 
				   	$udata['is_make'] = 2;
                                        //$udata['suretime'] = time();
	               	$this->mysql->update(Mysite::$app->config['tablepre'].'order',$udata,"id='".$orderid."'"); 
					$this->mysql->delete(Mysite::$app->config['tablepre'].'orderps',"orderid = '$orderid' and status != 3"); 
					$ordCls = new orderclass();
					$ordCls->writewuliustatus($orderid,5,$checkorderinfo['paytype']);  //商家不接单  物流状态
				    if( $checkorderinfo['paytype'] == 1 &&  $checkorderinfo['paystatus'] == 1  ){
							$drawbacklog = new drawbacklog($shopinfo['mid'],$this->mysql,$this->memberCls);
							$drawdata = array();
							$drawdata['allcost'] = $checkorderinfo['allcost'];
							$drawdata['orderid'] = $checkorderinfo['id'];
							$drawdata['reason'] =  '商家取消订单';
							$drawdata['content'] = '商家取消订单,由于某原因不能制作此订单';
							$drawdata['typeid'] = 1;
							$this->mysql->delete(Mysite::$app->config['tablepre'].'orderps',"orderid = '$orderid' and status !=3 ");  //写配送订单 
							$check = $drawbacklog->setsavedraw($drawdata)->shopcnetersave(); 
							
							if($check == true){ 
								logwrite('商家取消订单，提交退款信息！');
							}else{
								// $msg = $drawbacklog->GetErr();
								// $this->message($msg);
							} 
						 
						 
					 }

					//超管退款功能移动至此by yanyh
		   	       	$drawbacklog = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."drawbacklog where orderid=".$orderid." order by  id desc  limit 0,2");
		   	       	if(empty($drawbacklog)) 		 $this->message('order_emptybaklog');
		   	       	if($drawbacklog['status'] != 0)  $this->message('order_baklogcantdoover');
				   	if($orderinfo['is_reback'] == 2) $this->message('订单已退款成功不能重复操作');
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
		            $ordCls->noticeback($orderid);

	         		$this->success('success');
	         	}else{
	         		$this->message('退款失败:'.$return_msg);
	         	}

			break;
			case 'sendorder':
		  $ordercontrol = new ordercontrol($orderid);
		  if($ordercontrol->sendorder($shopinfo['uid']))
		  {
				  $ordCls = new orderclass();
	               $ordCls->noticesend($orderid);
				    $ordCls->writewuliustatus($orderid,6,$checkorderinfo['paytype']);  //商家配送发货
				$this->success('success');
		  }else{
				 $this->message($ordercontrol->Error());
		  }
			break;
			case 'shenhe':
		  $ordercontrol = new ordercontrol($orderid);
		  if($ordercontrol->shenhe($shopinfo['uid']))
		  {
					$this->success('success');
		  }else{
				 $this->message($ordercontrol->Error());
		  }
			break;
			case 'delorder':
			$ordercontrol = new ordercontrol($orderid);
		  if($ordercontrol->sellerdelorder($shopinfo['uid']))
		  {
			   $this->mysql->delete(Mysite::$app->config['tablepre'].'orderps',"orderid = '$orderid' and status != 3"); 
			  
				$this->success('success');
		  }else{
			   $this->message($ordercontrol->Error());
		  }
			break;
			case 'wancheng':
			 $checkorderinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where id = ".$orderid." and shopid=".$shopid." ");


			 if($checkorderinfo['is_reback'] > 0 && $checkorderinfo['is_reback'] < 3) $this->message('order_baklogcantdoover');
			 if( $checkorderinfo['is_goshop'] != 1 ){
	   	       if($checkorderinfo['status'] != 2)  $this->message('order_cantover');
			 }
			  $ordCls = new orderclass();
			  $ordCls->writewuliustatus($checkorderinfo['id'],9,$checkorderinfo['paytype']);  // 商家 操作 完成订单
			  
			  
			  $orderdata['is_acceptorder'] = 1; 
	   	      $orderdata['status'] = 3;
	   	      $orderdata['suretime'] = time();
	   	      //写用户数据
	   	       $this->mysql->update(Mysite::$app->config['tablepre'].'order',$orderdata,"id='".$orderid."'");
			   
			    //更新销量 
				$shuliang  = $this->mysql->select_one("select sum(goodscount) as sellcount from ".Mysite::$app->config['tablepre']."orderdet where order_id='".$checkorderinfo['id']."'  ");
				if(!empty($shuliang) && $shuliang['sellcount'] > 0){
					$this->mysql->update(Mysite::$app->config['tablepre'].'shop','`sellcount`=`sellcount`+'.$shuliang['sellcount'].'',"id ='".$checkorderinfo['shopid']."' ");
				}
				//自动完成配送单
				$this->mysql->update(Mysite::$app->config['tablepre'].'orderps','`status`=3',"orderid ='".$checkorderinfo['id']."' ");
			
			   
	   	       //更新用户成长值
	   	       if(!empty($checkorderinfo['buyeruid']))
	   	       {
	   	      	   $memberinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where uid='".$checkorderinfo['buyeruid']."'   ");
//		             if(!empty($memberinfo)){
//		             	 $this->mysql->update(Mysite::$app->config['tablepre'].'member','`total`=`total`+'.$checkorderinfo['allcost'],"uid ='".$checkorderinfo['buyeruid']."' ");
//		              }

                   if(!empty($memberinfo)){
                       $data['total']=$memberinfo['total']+$checkorderinfo['allcost'];
                       $data['score'] = $memberinfo['score']+Mysite::$app->config['consumption'];
                       if(Mysite::$app->config['con_extend'] > 0){
                           $allscore= $checkorderinfo['allcost']*Mysite::$app->config['con_extend'];
                           $data['score']+=$allscore;
                           $consumption=$allscore;
                       }
                       if(!empty($consumption)){
                           $consumption+=Mysite::$app->config['consumption'];
                       }else{
                           $consumption=Mysite::$app->config['consumption'];
                       }
                       $this->mysql->update(Mysite::$app->config['tablepre'].'member',$data,"uid ='".$checkorderinfo['buyeruid']."' ");
                       if($consumption > 0){
                           $this->memberCls->addlog($checkorderinfo['buyeruid'],1,1,$consumption,'消费送积分','消费送积分'.$consumption,$data['score']);
                       }
                   }
		              /*
		               // 写优惠券
		              */
		              if($memberinfo['parent_id'] > 0){

		                 $upuser = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where uid='".$memberinfo['parent_id']."'   ");
		                 if(!empty($upuser)){
		                 	 if(Mysite::$app->config['tui_juan'] ==1){
		                  $nowtime = time();
	 	   $endtime = $nowtime+Mysite::$app->config['tui_juanday']*24*60*60;
	 	   $juandata['card'] = $nowtime.rand(100,999);
       $juandata['card_password'] =  substr(md5($juandata['card']),0,5);
       $juandata['status'] = 1;// 状态，0未使用，1已绑定，2已使用，3无效
       $juandata['creattime'] = $nowtime;// 制造时间
       $juandata['cost'] = Mysite::$app->config['tui_juancost'];// 优惠金额
       $juandata['limitcost'] =  Mysite::$app->config['tui_juanlimit'];// 购物车限制金额下限
       $juandata['endtime'] = $endtime;// 失效时间
       $juandata['uid'] = $upuser['uid'];// 用户ID
       $juandata['username'] = $upuser['username'];// 用户名
       $juandata['name'] = '推荐送优惠券';//  优惠券名称
	 	   $this->mysql->insert(Mysite::$app->config['tablepre'].'juan',$juandata);
	 	    $this->mysql->update(Mysite::$app->config['tablepre'].'member','`parent_id`=0',"uid ='".$checkorderinfo['buyeruid']."' ");
	 	    $logdata['uid'] = $upuser['uid'];
	 	    $logdata['childusername'] = $memberinfo['username'];
	 	    $logdata['titile'] = '推荐送优惠券';
	 	    $logdata['addtime'] = time();
	 	    $logdata['content'] = '被推荐下单完成';
	 	    $this->mysql->insert(Mysite::$app->config['tablepre'].'sharealog',$logdata);
	 	                     }
	 	                 }




		              }
	   	       }
			 $this->success('success');
			break;
			case 'reback'://同意退款
			   	$orderinfo   = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where id = ".$orderid." and shopid=".$shopid." ");
			   	if(empty($orderinfo)) $this->message('订单不存在');
				$drawbacklog = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."drawbacklog where orderid=".$orderid." order by  id desc  limit 0,2");
				if(empty($drawbacklog)) 		$this->message('order_emptybaklog');
				if($drawbacklog['status'] != 0) $this->message('order_baklogcantdoover');
				if($orderinfo['status'] > 2)    $this->message('order_cantbak');
				
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
					  
					$ordCls = new orderclass();
					$ordCls->noticeback($orderid); 
					
					//超管退款功能移动至此by yanyh
		   	       	$drawbacklog = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."drawbacklog where orderid=".$orderid." order by  id desc  limit 0,2");
		   	       	if(empty($drawbacklog)) 		 $this->message('order_emptybaklog');
		   	       	if($drawbacklog['status'] != 0)  $this->message('order_baklogcantdoover');
				   	if($orderinfo['is_reback'] == 2) $this->message('订单已退款成功不能重复操作');
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
		            $ordCls->noticeback($orderid);
					$this->success('success');
				}
				else {
					$this->error('退款失败:'.$return_msg);
				}
			break;
			case 'unreback'://拒绝退款 
				$orderinfo   = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where id = ".$orderid." and shopid=".$shopid." ");
				if(empty($orderinfo)) $this->message('订单不存在');
				$drawbacklog = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."drawbacklog where orderid=".$orderid." order by  id desc  limit 0,2");
				if(empty($drawbacklog)) 			$this->message('order_emptybaklog');
				if($drawbacklog['status'] !=  0) 	$this->message('order_baklogcantdoover');
				if($orderinfo['status'] > 2) 		$this->message('order_cantbak');
			 
				$data['type'] = 2;//
				$this->mysql->update(Mysite::$app->config['tablepre'].'drawbacklog',$data,"id='".$drawbacklog['id']."'");
				$ordCls = new orderclass();
				$ordCls->noticeunback($orderid);

				//超管不退款功能移动至此by yanyh
	   	       	$drawbacklog = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."drawbacklog where orderid=".$orderid." order by  id desc  limit 0,2");
	   	       	if(empty($drawbacklog)) 		 $this->message('order_emptybaklog');
	   	       	if($drawbacklog['status'] !=  0) $this->message('order_baklogcantdoover');
			   	if($orderinfo['is_reback'] == 2) $this->message('订单已退款成功不能重复操作');
	   	       	$arr['is_reback'] = 3;//订单状态
	   	        $this->mysql->update(Mysite::$app->config['tablepre'].'order',$arr,"id='".$orderid."'");
	   	        $data['bkcontent'] = IReq::get('reasons');
	   	       	$data['status']   = 2;//
			   	$data['admin_id'] = ICookie::get('adminuid') ;
	   	       	$this->mysql->update(Mysite::$app->config['tablepre'].'drawbacklog',$data,"id='".$drawbacklog['id']."'");

	   	       	$ordCls = new orderclass(); 
			   	$ordCls->writewuliustatus($orderinfo['id'],15,$orderinfo['paytype']);  // 管理员拒绝退款给用户 物流信息 
	           	#$ordCls->noticeunback($id);
				$this->success('success'); 

				break;
			default:
			$this->message('nodefined_func');
			break;
		}



	}
	function shoptotal(){
		$this->checkshoplogin();
		$this->setstatus();
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');
		//	$date("Y-m-d",strtotime("+10 year"))

		$year = IFilter::act(IReq::get('year'));
		$year = empty($year)? date('Y',time()):$year;
		$month = IFilter::act(IReq::get('month'));



		$timelist = array();
		// $firstday = date('Y-m-01', strtotime($date));
		if(!empty($year)){
			if(empty($month)){
				$checknowtime = time();
				for($i=1;$i<13;$i++){
					$starttime = strtotime($year.'-'.$i.'-01');
					$endtime = strtotime("$year-$i-01 +1 month -1 day")+86400;
                                        
					if($starttime < $checknowtime){
						$tempdata['name'] = $year.'-'.$i;
						$tempdata['starttime'] = $starttime;
						$tempdata['endtime'] = $endtime;
						$timelist[] = $tempdata;
					}
				}
			}else{
				$stime = strtotime($year.'-'.$month.'-01');
				$etime = 	strtotime("$year-$month-01 +1 month -1 day")+86400;
                                //echo date("Y-m-d",$etime);die;
				$checknowtime = time();

				while($stime < $etime&&$stime< $checknowtime)
				{
					$tempdata['name'] = date('Y-m-d',$stime);
					$tempdata['starttime'] = $stime;
					$stime = $stime+86400;
					$tempdata['endtime'] = $stime;
					$timelist[] = $tempdata;


				}
			}
		}else{
			/*转换为时间格式*/
			//当年到默念
			$nowyear = date('Y',time());
			$nowyear = $nowyear+1;
			for($i=10;$i>0;$i--){
				$tempdata['name'] = $nowyear-$i;
				$tempdata['starttime'] = strtotime($nowyear-$i.'-01-01');
				$tempdata['endtime'] = strtotime($nowyear-$i.'-12-31')+86400;
                               
				$timelist[] = $tempdata;
                                

			}

		}

		$data['year'] = $year;
		$data['month'] = empty($month)?'0':$month;
		$data['startyear'] = date('Y',time());




		$list = array();
		$data['allsum'] = 0;
		$data['allnum'] = 0;
		if(is_array($timelist))
		{
			foreach($timelist as $key=>$value){
				$where2 = 'and addtime > '.$value['starttime'].' and addtime < '.$value['endtime'];
//echo date("Y-m-d",$value['endtime']);die;
		        //weixin
	     		$weixin = $this->mysql->select_one("select  count(id) as shuliang,sum(allcost) as allcost,sum(shopcost) as shopcost from ".Mysite::$app->config['tablepre']."order  where shopid = '".$shopid."' and paytype =1 and shopcost > 0 and status = 3 ".$where2." order by id asc  limit 0,1000");
	     		//cart
	     		$cart   = $this->mysql->select_one("select count(id) as shuliang,sum(allcost) as allcost,sum(shopcost) as shopcost  from ".Mysite::$app->config['tablepre']."order  where shopid = '".$shopid."' and paytype =2  and shopcost > 0 and status = 3 ".$where2."   order by id asc  limit 0,1000");
	     		//alipay
                        $alipay   = $this->mysql->select_one("select count(id) as shuliang,sum(allcost) as allcost,sum(shopcost) as shopcost  from ".Mysite::$app->config['tablepre']."order  where shopid = '".$shopid."' and paytype =6  and shopcost > 0 and status = 3 ".$where2."   order by id asc  limit 0,1000");
	     		//cash
                        $cash   = $this->mysql->select_one("select count(id) as shuliang,sum(allcost) as allcost,sum(shopcost) as shopcost  from ".Mysite::$app->config['tablepre']."order  where shopid = '".$shopid."' and paytype =5  and shopcost > 0 and status = 3 ".$where2."   order by id asc  limit 0,1000");
 				
				$value['orderNum'] = $weixin['shuliang']+$cart['shuliang']+$alipay['shuliang']+$cash['shuliang'];//订单总个数
                                
				$value['weixin']   = $weixin['allcost']?$weixin['allcost']:0; 
				$value['cart']     = $cart['allcost']?$cart['allcost']:0; 
				$value['alipay']   = $alipay['allcost']?$alipay['allcost']:0; 
				$value['cash']     = $cash['allcost']?$cash['allcost']:0; 
				$value['allcost']  = $weixin['allcost']+$cart['allcost']+$alipay['allcost']+$cash['allcost'];
				$data['allsum'] += $value['allcost'];
				$data['allnum'] += $value['orderNum'];
				$value['goodscost'] = $weixin['shopcost'] +$cart['shopcost']+$alipay['shopcost']+$cash['shopcost'];

				$list[] = $value;

 
			}

		}
		//var_dump($list);die;
		$data['list'] =$list;
		Mysite::$app->setdata($data);
	}
	//菜品分析by yanyh
	function goodsanalysis(){
		$this->checkshoplogin();
		$this->setstatus();
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');

		$year  = IFilter::act(IReq::get('year'));
		$year  = empty($year)? date('Y',time()):$year;
		$month = IFilter::act(IReq::get('month'));
		$month = empty($month)? date('m',time()):$month;

		$stime = strtotime($year.'-'.$month.'-01');
		$etime = strtotime("$year-$month-01 +1 month -1 day")+86400;


		$where1 = ' and ord.addtime > '.$stime.' and ord.addtime < '.$etime;		
		$list   = $this->mysql->getarr("select count(ordet.id) as shuliang ,ordet.goodsid,ordet.goodsname as shopname,SUM(ord.allcost) as allsum from ".Mysite::$app->config['tablepre']."order as ord left join ".Mysite::$app->config['tablepre']."orderdet  as ordet  on ordet.order_id = ord.id and ord.shopid='".$shopid."' and ord.status = 3  and is_reback = 0 ".$where1." group by ordet.goodsid   order by shuliang desc");
 
		$alist   = $this->mysql->getarr("select count(ordet.id) as shuliang ,ordet.goodsid,ordet.goodsname as shopname ,ordet.goodscost from  ".Mysite::$app->config['tablepre']."order as ord left join ".Mysite::$app->config['tablepre']."orderdet  as ordet on ordet.order_id = ord.id and ord.shopid='".$shopid."' and ord.status = 3  and is_reback = 0  group by ordet.goodsid   order by shuliang desc");		
		$allnum = 0;
		$allsum = 0;
		foreach ($list as $key => $value) {
			if($value['shopname'] != ''){
				$allsum += $value['allsum'];
				$allnum += $value['shuliang'];
				foreach ($alist as $k => $v) {
					if($value['goodsid'] == $v['goodsid']){
						$list[$key]['count_percent'] = round(($value['shuliang']/$v['shuliang'])*100).'%';
						$list[$key]['all_worth']     = $value['shuliang']*$v['goodscost'];
					}
				}
			}
		}
 		$data['allnum'] = $allnum;
 		$data['allsum'] = $allsum;

		$data['year'] = $year;
		$data['month']= $month;
		$data['startyear'] = date('Y',time());
		
		$data['list'] = $list;

		//趋势分析
		$goodsid  = IFilter::act(IReq::get('goodsid'));
		if($goodsid){
			$sql = "select count(ordet.id) as shuliang ,ordet.goodsname as shopname,DATE_FORMAT(FROM_UNIXTIME(`addtime`),'%d') as hao from ".Mysite::$app->config['tablepre']."order as ord inner join ".Mysite::$app->config['tablepre']."orderdet  as ordet  on ordet.order_id = ord.id and ordet.goodsid ='".$goodsid."' where ord.shopid='".$shopid."' and ord.status = 3  and is_reback = 0 ".$where1." GROUP BY hao order by hao asc";

			$list = $this->mysql->getarr($sql);

			$lists = array();
		 
			if(is_array($list)){
	 	 	  	foreach($list as $key=>$value){
	 	 	  		$hao = intval($value['hao']);
	 	 	  		$lists[$hao] = $value['shuliang'];
	 	 	  	}
	 	 	}
	 	
	 	 	$arr = array_fill(1,31,0);
	 	  	$keys = '';
	 	 	foreach ($arr as $key => $value) {
	 	 		$keys .= $keys? ','.$key : $key; 
	 	 		foreach ($lists as $k => $v) {
	 	 			if($key == $k){
	 	 				$arr[$k] = $v;
	 	 			}
	 	 		}
	 	 	}

	 	 	$vals = implode(',', $arr);
	 	 	$data['flag'] = 1;
 	 	}
 	  	$data['keys'] = $keys;
 	  	$data['vals'] = $vals;

		Mysite::$app->setdata($data);
	}
	//ajax趋势by yanyh
/*	function ajaxqushi(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');

		$year  = IFilter::act(IReq::get('year'));
		$year  = empty($year)? date('Y',time()):$year;
		$month = IFilter::act(IReq::get('month'));
		$month = empty($month)? date('m',time()):$month;
		$goodsid = IFilter::act(IReq::get('goodsid'));

		$stime = strtotime($year.'-'.$month.'-01');
		$etime = strtotime("$year-$month-01 +1 month -1 day")+86400;

		$where1 = ' and ord.addtime > '.$stime.' and ord.addtime < '.$etime;
 
		$sql = "select count(ordet.id) as shuliang ,ordet.goodsname as shopname,DATE_FORMAT(FROM_UNIXTIME(`addtime`),'%d') as hao from ".Mysite::$app->config['tablepre']."order as ord inner join ".Mysite::$app->config['tablepre']."orderdet  as ordet  on ordet.order_id = ord.id and ordet.goodsid ='".$goodsid."' where ord.shopid='".$shopid."' and ord.status = 3  and is_reback = 0 ".$where1." GROUP BY hao order by hao asc";

		$list = $this->mysql->getarr($sql);

		$lists = array();
	 
		if(is_array($list)){
 	 	  	foreach($list as $key=>$value){
 	 	  		$hao = intval($value['hao']);
 	 	  		$lists[$hao] = $value['shuliang'];
 	 	  	}
 	 	}
 	
 	 	$arr = array_fill(1,31,0);
 	  	$keys = '';
 	 	foreach ($arr as $key => $value) {
 	 		$keys .= $keys? ','.$key : $key; 
 	 		foreach ($lists as $k => $v) {
 	 			if($key == $k){
 	 				$arr[$k] = $v;
 	 			}
 	 		}
 	 	}

 	 	$vals = implode(',', $arr);
 	 	$data = array($keys,$vals);

		$this->success($data);
	}
*/

	function ajaxcheckshoporder(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');
		$daymintime = strtotime(date('Y-m-d',time()));
		$tempshu =  $this->mysql->select_one("select count(id) as shuliang  from ".Mysite::$app->config['tablepre']."order where shopid='".$shopid."' and  status > 0  and  status <  4 and posttime > ".$daymintime." limit 0,1000");
		$hidecount = $tempshu['shuliang'];
		$this->success($hidecount);
	}
	/**********商家订单处理部分结束***************/

	function shopask(){
		$this->checkshoplogin();
	}
	function getgodigui($arraylist,$nowid,$nowkey){
		if(count($arraylist) > 0){
			foreach($arraylist as $key=>$value){
				if($value['parent_id'] == $nowid){
					$value['space'] = $nowkey;
					$donextkey = $nowkey+1;
					$donextid = $value['id'];
					$this->digui[] = $value;
					$this->getgodigui($arraylist,$donextid,$donextkey);
				}
			}

		}
	}




	function shopbaidumap(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		$shopinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where  id = '".$shopid."' order by id asc");
		$data['dlng'] = empty($shopinfo['lng'])||$shopinfo['lng']=='0.000000'?Mysite::$app->config['baidulng']:$shopinfo['lng'];
		$data['dlat'] = empty($shopinfo['lat'])||$shopinfo['lat']=='0.000000'?Mysite::$app->config['baidulat']:$shopinfo['lat'];
		$data['baidumapkey'] = Mysite::$app->config['baidumapkey_config'];
		Mysite::$app->setdata($data);
	}
	function savemapshoplocation(){
		$this->checkshoplogin();
		$data['lng'] = IReq::get('lng');
		$data['lat'] = IReq::get('lat');
		$shopid = ICookie::get('adminshopid');
		if(empty($data['lng'])) $this->message('emptylng');
		if(empty($data['lat'])) $this->message('emptylat');
		if(empty($shopid)) $this->message('emptycookshop');
		$shopid = ICookie::get('adminshopid');
		$this->mysql->update(Mysite::$app->config['tablepre'].'shop',$data,"id='".$shopid."'");
		$shopinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where  id = '".$shopid."' order by id asc");
		if(!empty($shopinfo)){
			$areasearch = new areasearch($this->mysql);
			$areasearch->setdata($shopinfo['shopname'],'2',$shopinfo['id'],$data['lat'],$data['lng']);
			$areasearch->del();
			$areasearch->save();
			$areasearch->setdata($shopinfo['address'],'2',$shopinfo['id'],$data['lat'],$data['lng']);
			$areasearch->save();
		}




		$this->success('success');
	}
	function setshoparea(){
		$this->checkshoplogin();
		$areainfo = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."area   order by orderid asc");
		//	print_r($areainfo);
		//&nbsp;&nbsp;&nbsp;&nbsp;
		$parentids = array();
		foreach($areainfo as $key=>$value){
			$parentids[] = $value['parent_id'];
		}
		//去重
		$parentids = array_unique($parentids);
		$data['parent_ids'] = $parentids;
		$this->getgodigui($areainfo,0,0);
		$data['arealist'] = $this->digui;
		Mysite::$app->setdata($data);
	}
	function shoptoadcost(){
		$this->checkshoplogin();
		$areaid = intval(IReq::get('areaid'));//,areaid,cost
		$cost =  IFilter::act(IReq::get('cost'));
		$cost = intval($cost*10)/10;
		if(empty($areaid)) $this->message('area_empty');
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('shop_noexit');
		$data['cost'] = $cost;
		$shopinfo =	$this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop  where id = '".$shopid."' ");
		if($shopinfo['shoptype'] == 0){
			$this->mysql->update(Mysite::$app->config['tablepre'].'areatoadd',$data,"shopid='".$shopid."' and areaid = '".$areaid."'");
		}elseif($shopinfo['shoptype'] ==  1){
			$this->mysql->update(Mysite::$app->config['tablepre'].'areatomar',$data,"shopid='".$shopid."' and areaid = '".$areaid."'");
		}
		$this->success('success');
	}
	function shoptoappcost(){
		$this->checkshoplogin();
		$gongli = intval(IReq::get('gongli'));//,areaid,cost
		$cost =  intval(IFilter::act(IReq::get('cost')));


		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('shop_noexit');

		$shopinfo =	$this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop  where id = '".$shopid."' ");
		if($shopinfo['shoptype'] == 0){
			//pradius
			$fastfood = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast where shopid=".$shopid."  ");
			$pradius = empty($fastfood['pradius'])?1:intval($fastfood['pradius']);
			$tempdoid = empty($fastfood['pradiusvalue'])?array():unserialize($fastfood['pradiusvalue']);
			$result = array();
			for($i=0;$i< $pradius;$i++){
				if($i == $gongli){
					$result[$i] = $cost;
				}else{
					$result[$i]=  isset($tempdoid[$i])? $tempdoid[$i]:0;
				}
			}
			$data['pradiusvalue'] =  serialize($result);


			$this->mysql->update(Mysite::$app->config['tablepre'].'shopfast',$data,"shopid='".$shopid."' ");
		}elseif($shopinfo['shoptype'] ==  1){
			$fastfood = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket where shopid=".$shopid."  ");
			$pradius = empty($fastfood['pradius'])?1:intval($fastfood['pradius']);
			$tempdoid = empty($fastfood['pradiusvalue'])?array():unserialize($fastfood['pradiusvalue']);
			$result = array();
			for($i=0;$i< $pradius;$i++){
				if($i == $gongli){
					$result[$i] = $cost;
				}else{
					$result[$i]=  isset($tempdoid[$i])? $tempdoid[$i]:0;
				}
			}
			$data['pradiusvalue'] =  serialize($result);

			$this->mysql->update(Mysite::$app->config['tablepre'].'shopmarket',$data,"shopid='".$shopid."' ");
		}
		$this->success('success');
	}
	function shopidtoad(){//添加店铺配送区域
		$this->checkshoplogin();
		$areaid = intval(IReq::get('areaid'));//,areaid,cost
		$types =  IFilter::act(IReq::get('types'));

		$shopid = ICookie::get('adminshopid');
		if(!in_array($types,array('add','del'))) $this->message('nodefined_func');
		$checkarea = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."area where id ='".$areaid."'   ");
		if(empty($shopid)) $this->message('shop_noexit');
		if(empty($checkarea)) $this->message('area_empty');
		$shopinfo =	$this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop  where id = '".$shopid."' ");
		if($types == 'add'){
			//循环添加 区域店铺表
			$whiledata = $checkarea;
			$tempcheckid = array();
			while(!empty($whiledata)){
				$checkarea = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."areashop where areaid ='".$whiledata['id']."'  and shopid = '".$shopid."' ");
				if($shopinfo['admin_id'] > 0){
					if($checkarea['admin_id'] != $shopinfo['admin_id']) $this->message('area_adminiderr');
				}
				if(empty($areatocost)){//价格区间不存在
					$parentinfo['shopid'] = $shopid;
					$parentinfo['areaid'] = $whiledata['id'];
					$this->mysql->insert(Mysite::$app->config['tablepre']."areashop",$parentinfo);
				}
				$tempcheckid[] = $whiledata['id'];
				if($whiledata['parent_id'] == 0){
					break;
				}
				if(in_array($whiledata['parent_id'],$tempcheckid)){
					break;
				}
				$whiledata = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."area where id ='".$whiledata['parent_id']."'   ");
			}

		}else{
			//删除配送地址
			$whiledata = $checkarea;
			$tempcheckid = array();
			while(!empty($whiledata)){
				//检测该区域是否被删除
				$checkdeldata = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."areashop where areaid in(select id from ".Mysite::$app->config['tablepre']."area where  parent_id ='".$whiledata['id']."')  and shopid = '".$shopid."' ");
				if(!empty($checkdeldata)){
					break;
				}

				$this->mysql->delete(Mysite::$app->config['tablepre']."areashop","areaid ='".$whiledata['id']."' and shopid = '".$shopid."'");
				$tempcheckid[] = $whiledata['id'];
				if($whiledata['parent_id'] == 0){
					break;
				}
				if(in_array($whiledata['parent_id'],$tempcheckid)){
					break;
				}
				$whiledata = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."area where id ='".$whiledata['parent_id']."'   ");
			}


		}
		$this->success('success');
	}
	function showcommlist(){
		$this->checkshoplogin();
		$id = IReq::get('id');
		if(empty($id)) $this->message('order_noexitping');
		$checkinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."comment where id='".$id."'  ");
		if(empty($checkinfo)) $this->message('order_noexitping');
		$data['is_show'] = $checkinfo['is_show'] == 1?0:1;
		$this->mysql->update(Mysite::$app->config['tablepre'].'comment',$data,"id='".$id."'");
		$this->success('success');
	}
	function delcommlist()
	{
		$this->checkshoplogin();
		$id = IReq::get('id');
		if(empty($id))  $this->message('order_noexitping');
		$ids = is_array($id)? join(',',$id):$id;
		$this->mysql->delete(Mysite::$app->config['tablepre'].'comment'," id in($ids) ");
		$this->success('success');
	}
	function delask(){
		$id = IFilter::act(IReq::get('id'));
		if(empty($id))  $this->message('ask_empty');
		$ids = is_array($id)? join(',',$id):$id;
		$where = " id in($ids)";
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		if(!empty($shopid)){
			$where = $where." and shopid = ".$shopid;
		}

		$this->mysql->delete(Mysite::$app->config['tablepre'].'ask',$where);
		$this->success('success');
	}
	function backask()
	{
		$id = intval(IReq::get('askbackid'));
		if(empty($id)) $this->message('ask_empty');
		$checkinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."ask where id='".$id."'  ");
		if(empty($checkinfo)) $this->message('ask_empty');
		if(!empty($checkinfo['replycontent']))  $this->message('ask_isreplay');
		$where = " id='".$id."' ";
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('ask_notownreplay');
		if($checkinfo['shopid'] != $shopid) $this->message('ask_notownreplay');
		$data['replycontent'] = IFilter::act(IReq::get('askback'));
		if(empty($data['replycontent'])) $this->message('ask_emptyreplay');
		$data['replytime'] = time();
		$this->mysql->update(Mysite::$app->config['tablepre'].'ask',$data,$where);
		$this->success('success');
	}
	function selectmarketimg(){
		$data['gid'] = intval(IReq::get('gid'));
		$data['mid'] = $this->mid;
		$this->pageCls->setpage(intval(IReq::get('page')),18);
		$total = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."imglist      ");
		$data['imglist'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."imglist      order by addtime desc limit ".$this->pageCls->startnum().", ".$this->pageCls->getsize()." ");

		$link = IUrl::creatUrlEx($this->mid,'shopcenter/selectmarketimg/gid/'.$data['gid']);//index.php?ctrl=&action=&module=

		$data['pagecontent'] =  $this->pageCls->multi($total, 18, intval(IReq::get('page')), $link);
		$data['page'] = intval(IReq::get('page'));
		Mysite::$app->setdata($data);

	}

	//打印 订单
	function orderprint(){
		$orderid = intval(IReq::get('orderid'));
		$data['printtype'] = trim(IReq::get('printtype'));//打印类型
		$this->setstatus();
		$data['orderinfo'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order  where id ='".$orderid."' ");
		$data['orderdet'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."orderdet where   order_id = ".$orderid." order by id desc ");
		Mysite::$app->setdata($data);
	}
	//保存配送时间
	function savepostdate(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');
		$shopinfo = $this->shopinfo();
		$starthour =  intval(IFilter::act(IReq::get('starthour')));
		$startminit =  intval(IFilter::act(IReq::get('startminit')));
		$endthour =  intval(IFilter::act(IReq::get('endthour')));
		$endminit = intval(IFilter::act(IReq::get('endminit')));
		$instr = trim(IFilter::act(IReq::get('instr')));
		if($shopinfo['shoptype'] ==  0){//外卖
			$tempshopinfo= $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast  where shopid = '".$shopinfo['id']."' ");
		}else{
			$tempshopinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket  where shopid = '".$shopinfo['id']."' ");
		}
		if(empty($tempshopinfo)) $this->message('店铺不存在');

		$bigetime = $starthour*60*60+$startminit*60;
		$endtime = $endthour*60*60+$endminit*60;
		if($bigetime < 1){
			$this->message('配送时间段起始时间必须从凌晨1分开始');
		}
		if($bigetime > $endtime){
			$this->message('开始时间段必须大于结束时间');
		}
		if($endtime >=86400) $this->message('配送时间段结束时间最大值23:59');
		$nowlist = !empty($tempshopinfo['postdate'])?unserialize($tempshopinfo['postdate']):array();
		//postdata数据结构   array(  '0'=>array('s'=>shuzi,e=>'shuzi'),'1'=>array('s'=>shuzi,e=>'shuzi')
		$checkshu = count($nowlist);
		if($checkshu > 0){
			$checknowendo  =  $nowlist[$checkshu-1]['e'];
			if($checknowendo > $bigetime) $this->message('已设置配送时间段已包含提交的开始时间');
		}
		$tempdata['s'] = $bigetime;
		$tempdata['e'] = $endtime;
		$tempdata['i'] = $instr;
		$nowlist[] = $tempdata;
		$savedata['postdate'] = serialize($nowlist);
		//$this->message($savedata['postdate']);
		if($shopinfo['shoptype'] ==  0){//外卖
			$this->mysql->update(Mysite::$app->config['tablepre'].'shopfast',$savedata,"shopid='".$shopinfo['id']."'");
		}else{
			$this->mysql->update(Mysite::$app->config['tablepre'].'shopmarket',$savedata,"shopid='".$shopinfo['id']."'");
		}

		$this->success('success');
	}
	//删除配送时间
	function delpostdate(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');
		$shopinfo = $this->shopinfo();
		$nowdelid =  intval(IFilter::act(IReq::get('id')));

		if($shopinfo['shoptype'] ==  0){//外卖
			$tempshopinfo= $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast  where shopid = '".$shopinfo['id']."' ");
		}else{
			$tempshopinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket  where shopid = '".$shopinfo['id']."' ");
		}
		if(empty($tempshopinfo)) $this->message('店铺不存在');
		if(empty($tempshopinfo['postdate'])) $this->message('未设置配送时间段');
		$nowlist = unserialize($tempshopinfo['postdate']);
		//postdata数据结构   array(  '0'=>array('s'=>shuzi,e=>'shuzi'),'1'=>array('s'=>shuzi,e=>'shuzi')

		$newdata = array();
		foreach($nowlist as $key=>$value){
			if($key != $nowdelid){
				$newdata[] = $value;
			}
		}
		$savedata['postdate'] = serialize($newdata);
		if($shopinfo['shoptype'] ==  0){//外卖
			$this->mysql->update(Mysite::$app->config['tablepre'].'shopfast',$savedata,"shopid='".$shopinfo['id']."'");
		}else{
			$this->mysql->update(Mysite::$app->config['tablepre'].'shopmarket',$savedata,"shopid='".$shopinfo['id']."'");
		}

		$this->success('success');
	}

	/************商家订单处理部分***************/
	function draworderset(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');
		$starttime = trim(IFilter::act(IReq::get('starttime')));
		$orderSource = intval(IReq::get('orderSource'));
		$nowday = date('Y-m-d',time());
		$starttime = empty($starttime)? $nowday:$starttime;
		$endtime = empty($endtime)? $nowday:$endtime;
		$where = '';
		$where = '  and b.addtime > '.strtotime($starttime.' 00:00:00').' and b.addtime < '.strtotime($starttime.' 23:59:59');
		$data['orderSource'] = $orderSource;
		$data['starttime'] = $starttime;
		$this->setstatus();
		//获取订单的方式是所有 有效订单  status > 0 and < 4 and (paytype == 'outpay' or paytype='open_acout or (paystatus=1)
		$orderSourcetoarray = array(
			'0'=>' and   ( b.paytype = 1 or  b.paystatus=1) and b.is_reback > 0 ' ,
			'1'=>'    and ( b.paytype = 1 or  b.paystatus=1) and b.is_reback = 1 ' ,
			'2'=>'    and ( b.paytype = 1 or  b.paystatus=1) and b.is_reback = 2 ' ,
			'3'=>'    and ( b.paytype = 1 or  b.paystatus=1) and b.is_reback = 3 '

		);
		if(isset($orderSourcetoarray[$orderSource])){
			$where .= ''.$orderSourcetoarray[$orderSource];
		}
		$draworderlist = $this->mysql->getarr("select a.*,b.id,b.is_reback,b.paystatus,b.paytype,b.status,b.addtime from ".Mysite::$app->config['tablepre']."drawbacklog as a left join ".Mysite::$app->config['tablepre']."order as b on a.orderid = b.id  where a.shopid='".$shopid."'  ".$where." order by a.id desc limit 0,1000");
		$shuliang  = $this->mysql->select_one("select a.*,b.id,b.is_reback,b.paystatus,b.paytype,b.status,b.addtime from ".Mysite::$app->config['tablepre']."drawbacklog as a left join ".Mysite::$app->config['tablepre']."order as b on a.orderid = b.id  where a.shopid='".$shopid."'  ".$where." order by a.addtime desc limit 0,1000");
		$shuliang1  = $this->mysql->counts("select a.*,b.id,b.is_reback,b.paystatus,b.paytype,b.status,b.addtime from ".Mysite::$app->config['tablepre']."drawbacklog as a left join ".Mysite::$app->config['tablepre']."order as b on a.orderid = b.id  where a.shopid='".$shopid."'  ".$where." order by a.addtime desc limit 0,1000");
 	//	var_dump($draworderlist);
		$data['tongji'] = $shuliang;
		$data['tongji1'] = $shuliang1;
		$data['list'] = array();
		if($draworderlist)
		{
			foreach($draworderlist as $key=>$val){

				$val['orderone'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where id='".$val['orderid']."'  order by id desc limit 1");

				$val['detlist'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."orderdet where   order_id = ".$val['orderid']." order by id desc ");
				$val['maijiagoumaishu'] = 0;
				if($val['buyeruid'] > 0)
				{
					$val['maijiagoumaishu'] =$this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."order where mid='{$this->mid}' and buyeruid='".$val['buyeruid']."' and  status = 3 order by id desc");
				}
				$data['allsum'] += $val['cost'];
				$data['list'][] = $val;

			}
		}
       //  var_dump($data['allsum']);
		$daymintime = strtotime(date('Y-m-d',time()));
		$tempshu =  $this->mysql->select_one("select count(id) as shuliang  from ".Mysite::$app->config['tablepre']."order where shopid='".$shopid."' and  status > 0  and  status <  4 and posttime > ".$daymintime." limit 0,1000");
		//统计当天订单
		$data['hidecount'] = $tempshu['shuliang'];
		$data['playwave'] = ICookie::get('playwave'); //shoporderlist
		Mysite::$app->setdata($data);
	}




	/************商家闪惠订单处理部分***************/
	function shophuiorder(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');
		$starttime = trim(IFilter::act(IReq::get('starttime')));
		$orderSource = intval(IReq::get('orderSource'));
		$nowday = date('Y-m-d',time());
		$starttime = empty($starttime)? $nowday:$starttime;
		$endtime = empty($endtime)? $nowday:$endtime;
		$where = '';
		$where = '  and addtime > '.strtotime($starttime.' 00:00:00').' and addtime < '.strtotime($starttime.' 23:59:59');

		$data['orderSource'] = $orderSource;
		$data['starttime'] = $starttime;

		$orderSourcetoarray = array(
			'0'=>'   ',
			'1'=>' and  status = 0  and paystatus=0',
			'2'=>' and  status=1   and  paystatus=1 ',

		);



		if(isset($orderSourcetoarray[$orderSource])){

			$where .= ''.$orderSourcetoarray[$orderSource];
		}




		$orderlist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shophuiorder where shopid='".$shopid."'  ".$where." order by id desc limit 0,1000");
		$shuliang  = $this->mysql->select_one("select count(id) as shuliang,sum(sjcost) as sjcost from ".Mysite::$app->config['tablepre']."shophuiorder where shopid='".$shopid."' ".$where." limit 0,1000");
		$data['tongji'] = $shuliang;
		$data['list'] = array();
		if($orderlist)
		{
			foreach($orderlist as $key=>$value)
			{
				$value['maijiagoumaishu'] = 0;
				/*   if($value['uid'] > 0)
                  { */
				$value['shopinfo'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where  id = ".$value['shopid']." order by id desc");
				$value['maijiagoumaishu'] =$this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."shophuiorder where  status = 1 order by id desc");
				/*  } */
				$data['list'][] = $value;
			}
		}
		#  print_r($where);
		# print_r($data['list']);
		$daymintime = strtotime(date('Y-m-d',time()));
		$tempshu =  $this->mysql->select_one("select count(id) as shuliang  from ".Mysite::$app->config['tablepre']."shophuiorder where shopid='".$shopid."' and  status > 0  and  status <  2 and completetime > ".$daymintime." limit 0,1000");
		//统计当天订单
		$data['hidecount'] = $tempshu['shuliang'];
		Mysite::$app->setdata($data);
	}


	//闪惠订单统计
	function shophuitotal(){
		$this->checkshoplogin();
		$this->setstatus();
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');
		//	$date("Y-m-d",strtotime("+10 year"))

		$year = IFilter::act(IReq::get('year'));
		$year = empty($year)? date('Y',time()):$year;
		$month = IFilter::act(IReq::get('month'));



		$timelist = array();
		// $firstday = date('Y-m-01', strtotime($date));
		if(!empty($year)){
			if(empty($month)){
				$checknowtime = time();
				for($i=1;$i<13;$i++){
					$starttime = strtotime($year.'-'.$i.'-01');
					$endtime = strtotime("$year-$i-01 +1 month -1 day")+86400;
					if($starttime < $checknowtime){
						$tempdata['name'] = $year.'-'.$i;
						$tempdata['starttime'] = $starttime;
						$tempdata['endtime'] = $endtime;
						$timelist[] = $tempdata;
					}
				}
			}else{
				$stime = strtotime($year.'-'.$month.'-01');
				$etime = 	strtotime("$year-$month-01 +1 month -1 day")+86400;
				$checknowtime = time();

				while($stime < $etime&&$stime< $checknowtime)
				{
					$tempdata['name'] = date('Y-m-d',$stime);
					$tempdata['starttime'] = $stime;
					$stime = $stime+86400;
					$tempdata['endtime'] = $stime;
					$timelist[] = $tempdata;


				}
			}
		}else{
			/*转换为时间格式*/
			//当年到默念
			$nowyear = date('Y',time());
			$nowyear = $nowyear+1;
			for($i=10;$i>0;$i--){
				$tempdata['name'] = $nowyear-$i;
				$tempdata['starttime'] = strtotime($nowyear-$i.'-01-01');
				$tempdata['endtime'] = strtotime($nowyear-$i.'-12-31')+86400;
				$timelist[] = $tempdata;

			}

		}

		$data['year'] = $year;
		$data['month'] = empty($month)?'0':$month;
		$data['startyear'] = date('Y',time());




		$list = array();
		$data['allsum'] = 0;
		$data['allnum'] = 0;
		if(is_array($timelist))
		{
			foreach($timelist as $key=>$value){
				$where2 = 'and addtime > '.$value['starttime'].' and addtime < '.$value['endtime'];
				$shoptj=  $this->mysql->select_one("select  count(id) as shuliang,sum(xfcost) as xfcost,sum(yhcost) as yhcost, sum(sjcost) as sjcost from ".Mysite::$app->config['tablepre']."shophuiorder  where shopid = '".$shopid."' and   paystatus =1 and status = 1 ".$where2." order by id asc  limit 0,1000");

				# print_r($shoptj);
				//月 份	订单数量	在线付款	线下支付	使用优惠券	店铺优惠	使用积分	打包费	配送费	商品总价
				$value['orderNum'] =  $shoptj['shuliang'];//订单总个数

				$value['xfcost'] = $shoptj['xfcost'];//
				$value['yhcost'] = $shoptj['yhcost'];//
				$value['sjcost'] = $shoptj['sjcost'];//

				$data['allsum'] += $value['sjcost'];
				$data['allnum'] += $value['orderNum'];

				$list[] = $value;

			}

		}
		$data['list'] =$list;
		#print_r($data['list']);
		Mysite::$app->setdata($data);
	}
	function savegoodsmoduletype(){
		$shopid = intval(IFilter::act(IReq::get('shopid')));
		$moduletype =  intval(IFilter::act(IReq::get('moduletype')));
		$shopinfo = $this->mysql->select_one("  select * from ".Mysite::$app->config['tablepre']."shop where id = ".$shopid." ");
		if(empty($shopinfo)) $this->message("获取店铺信息失败");
		$data['goodlistmodule'] = $moduletype;
		$this->mysql->update(Mysite::$app->config['tablepre'].'shop',$data,"id='".$shopid."'");
		$this->success('success');

	}
	/**********************规格处理**********************/
	//获取店铺规格
	function getgoodssgg(){
		/*
		$id = intval(FD('id'));
	    $templist =  $this->mdb->Clear()->Table('shopgg')->Order('orderid desc')->SetSize(1000)->Blist("  FIND_IN_SET( '".$id."' , `shopgdcatid` ) ");
		$html = '';
		$ggids = trim(FD('ggids'));
		$seararray = explode(',',$ggids);
		if(is_array($templist['list'])){
			foreach($templist['list'] as $key=>$value){
				if($value['parent_id'] == 0){

						//复选
						  $html .= '<tr class="guige">
      <td>&nbsp;</td>
      <td>'.$value['name'].'</td>
      <td class="maincheck">
       <ul>';

					 $tempc =  $this->mdb->Clear()->Table('shopgg')->Order('orderid desc')->SetSize(1000)->Blist(array('parent_id'=>$value['id']));
					foreach($tempc['list'] as $k=>$v){
					        $varhtml = in_array($v['id'],$seararray)?'checked':'';
							$html .='<li class="check" style="width:auto;"><label><input type="checkbox" name="choicegg'.$value['id'].'[]" value="'.$v['id'].'" data="'.$value['id'].'" '.$varhtml.'><i class="mul"></i></label><a>'.$v['name'].'</a></li>';

					}
					$html .='</ul>
      </td>
     </tr>';


				}
			}
		}
		print_r($html);
		exit;  */
	}

	function backcomment()   //  商家回复评论
	{
		$id = intval(IReq::get('askbackid'));
		if(empty($id)) $this->message('获取失败');
		$checkinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."comment where id='".$id."'  ");
		if(empty($checkinfo)) $this->message('评论不存在');
		if(!empty($checkinfo['replycontent']))  $this->message('已回复过');
		$where = " id='".$id."' ";
		$data['replycontent'] = IFilter::act(IReq::get('askback'));
		if(empty($data['replycontent'])) $this->message('请填写回复内容');
		$data['replytime'] = time();
		$this->mysql->update(Mysite::$app->config['tablepre'].'comment',$data,$where);
		$this->success('success');
	}
	function fastfood(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		$shopinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop  where id = '".$shopid."' ");

		if($shopinfo['shoptype'] == 0){
			$shoptype = $this->mysql->getarr("select id,name from ".Mysite::$app->config['tablepre']."goodstype where shopid='".$shopid."' order by orderid asc ");
			$data['shopdet'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast where shopid='".$shopid."' ");
		}else{
			$shoptype = $this->mysql->getarr("select id,name from ".Mysite::$app->config['tablepre']."marketcate where shopid = '".$shopid."' and parent_id != 0 order by orderid asc  ");
			$data['shopdet'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket where shopid='".$shopid."' ");
		}

		$goodslist = array();
		$tempgoodslist = $this->mysql->getarr("select id,name,cost,bagcost,count,typeid,have_det from ".Mysite::$app->config['tablepre']."goods where   shopid=".$shopid." order by id asc limit 0,1000  ");
		foreach($tempgoodslist as $key=>$value){
			if($value['have_det'] ==1) {
				$detlist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."product where   shopid=".$shopid." and goodsid= ".$value['id']." ");
				foreach($detlist as $k=>$v){
					$newtemp = $value;
					$newtemp['product_id'] = $v['id'];
					$newtemp['name'] = $value['name']."【".$v['attrname']."】";
					$newtemp['cost'] = $v['cost'];
					$goodslist[] =$newtemp;
				}
			}else{
				$value['product_id'] = 0;
				$goodslist[] = $value;
			}
		}
		$data['shop'] = $shopinfo;
		$data['goodstype'] = $shoptype;
		$data['goods'] = $goodslist;

		Mysite::$app->setdata($data);
	}


	/* 8.2 新增函数 */
	//商家实景分类名称
	function usershopreal()
	{
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		if($shopid > 0){
			$reallist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shopreal where shopid ='".$shopid."'");
			$data['reallist']=$reallist;
		}
		Mysite::$app->setdata($data);
	}



	//删除商家实景分类名称
	function delshopreal()
	{
		$this->checkshoplogin();
		$id=IReq::get('id');
		$imgurl=$this->mysql->getarr("select img from ".Mysite::$app->config['tablepre']."shoprealimg where parent_id ='".$id."'");

		if($imgurl){
			foreach($imgurl as $key=>$val){
				IFile::unlink(hopedir.$val['img']);
				logwrite($key.":".hopedir.$val['img']);
			}
		}
		$this->mysql->delete(Mysite::$app->config['tablepre'].'shopreal',"id = '".$id."'");
		$this->mysql->delete(Mysite::$app->config['tablepre'].'shoprealimg',"parent_id = '$id'");

		$this->success('success');
	}



	//商家实景分类图片
	function shoprealimg()
	{
		$this->checkshoplogin();
		$this->pageCls->setpage(intval(IReq::get('page')),10);
		$parent_id = intval(IReq::get('parent_id'));
		$total = $this->mysql->counts("select id from ".Mysite::$app->config['tablepre']."shoprealimg where parent_id=".$parent_id);
		$data['imglist'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shoprealimg where parent_id=".$parent_id."   order by id desc limit ".$this->pageCls->startnum().", ".$this->pageCls->getsize()." ");
		$shuliang  = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."shoprealimg where parent_id=".$parent_id);
		$this->pageCls->setnum($shuliang);
		$data['pagecontent'] = $this->pageCls->ajaxbar('realimg');
		Mysite::$app->setdata($data);
	}


	//商家实景批量上传图片
	function shoprealimgupload(){
		$parent_id = intval(IReq::get('parent_id'));
		$shopid = ICookie::get('adminshopid');
		if(is_array($_FILES)&& isset($_FILES['imgFile']))
		{
			$uploaddir = "shoprealimg";
			$json = new Services_JSON();
			$uploadpath = 'upload/'.$uploaddir.'/';
			$filepath = '/upload/'.$uploaddir.'/';
			$upload = new upload($uploadpath,array('gif','jpg','jpge','doc','png'));//upload 自动生成压缩图片
			$file = $upload->getfile();
			if($upload->errno!=15&&$upload->errno!=0){

				$this->message($upload->errmsg());
			}else{
				//写到商家实景图片表中
				$data['imgname']= $file[0]['saveName'];
				$data['img']= $filepath.$file[0]['saveName'];
				$data['parent_id'] = $parent_id;
				$data['mid'] = $this->mid;
				$data['shopid'] = $shopid;
				$this->mysql->insert(Mysite::$app->config['tablepre'].'shoprealimg',$data);
				$this->success($file[0]['saveName']);
			}
		}else{

			$this->message('未定义的上传类型');
		}
	}

	//删除商家实景图片
	function delshoprealimg(){
		$imglujing = trim(IReq::get('imglujing'));
		IFile::unlink(hopedir.$imglujing);
		$this->mysql->delete(Mysite::$app->config['tablepre'].'shoprealimg',"img = '$imglujing'");
		$this->success('success');
	}

	//保存、修改商家实景类型
	function saveshopreal(){
		$data['mid'] = $this->mid;
		$data['name'] = IFilter::act(IReq::get('name'));
		$data['shopid'] = ICookie::get('adminshopid');
		$type = IFilter::act(IReq::get('type'));
		$this->checkshoplogin();
		if(empty($data['shopid'])) $this->message('emptycookshop');
		if(!(IValidate::len($data['name'],1,5)))$this->message('分类名称大于1小于5');
		if($type == 'insert'){
			$checkname = $this->mysql->select_one("select name from ".Mysite::$app->config['tablepre']."shopreal where name='".$data['name']."' and shopid=".$data['shopid']);
			if($checkname) $this->message('分类名已存在');
			$this->mysql->insert(Mysite::$app->config['tablepre'].'shopreal',$data);
		}
		if($type == 'update'){
			$realid=IReq::get('id');
			$checkname = $this->mysql->select_one("select name from ".Mysite::$app->config['tablepre']."shopreal where name='".$data['name']."' and shopid=".$data['shopid']." and id!=".$realid);
			if($checkname) $this->message('分类名已存在');
			$this->mysql->update(Mysite::$app->config['tablepre']."shopreal",$data,"id=".$realid);
		}
		$this->success('success');

	}


	function goodsupload(){//点击多张上传
		$goodsid = intval(IReq::get('goodsid'));
		$data['goodsid']=$goodsid;
		$mid = intval(IReq::get('mid'));
		$data['mid']=$mid;
		Mysite::$app->setdata($data);
	}
	function showgoodsimg(){
		$this->checkshoplogin();
		$goodsid = intval(IReq::get('goodsid'));
		$this->pageCls->setpage(intval(IReq::get('page')),15);
		$total = $this->mysql->counts("select id from ".Mysite::$app->config['tablepre']."goodsimg where goodsid=".$goodsid);
		$data['imglist'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodsimg where goodsid=".$goodsid."   order by id desc limit ".$this->pageCls->startnum().", ".$this->pageCls->getsize()." ");
		$link = IUrl::creatUrlEx($this->mid,'shopcenter/showgoodsimg');
		$data['pagecontent'] =  $this->pageCls->multi($total, 15, intval(IReq::get('page')), $link);
		$data['page'] = intval(IReq::get('page'));
		$mid = intval(IReq::get('mid'));
		$data['mid']=$mid;
//        $data['imglist'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodsimg where goodsid=".$goodsid);
		Mysite::$app->setdata($data);
	}
//商家后台菜单管理商品多张上传图片
	function uploadgoodsimg()
	{
		$goodsid = intval(IReq::get('goodsid'));
		if(is_array($_FILES)&& isset($_FILES['imgFile']))
		{
			$uploaddir = "goods";
			$json = new Services_JSON();
			$uploadpath = 'upload/'.$uploaddir.'/';
			$filepath = '/upload/'.$uploaddir.'/';
			$upload = new upload($uploadpath,array('gif','jpg','jpge','doc','png'));//upload 自动生成压缩图片
			$file = $upload->getfile();
			if($upload->errno!=15&&$upload->errno!=0){

				$this->message($upload->errmsg());
			}else{
				//写到商品图片表中
				$data['imgname']= $file[0]['saveName'];
				$data['imgurl']= $filepath.$file[0]['saveName'];
				$data['goodsid'] = $goodsid;

				$this->mysql->insert(Mysite::$app->config['tablepre'].'goodsimg',$data);
				$this->success($file[0]['saveName']);
			}
		}else{

			$this->message('未定义的上传类型');
		}
	}



	//删除商品图片
	function delonegoodsimg(){
		$imglujing = trim(IReq::get('imglujing'));
		$this->mysql->delete(Mysite::$app->config['tablepre'].'goodsimg',"imgurl = '$imglujing'");
		IFile::unlink(hopedir.$imglujing);
		$this->success($imglujing);
	}

	//店铺结算
	function shopjs(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		$stime = IFilter::act(IReq::get('stime')); //结算日
		$etime = IFilter::act(IReq::get('etime')); //结算结束日  假设从1号到 3号
		$where = " where  shopid = ".$shopid." ";
		$data['stime'] = $stime;
		$data['etime'] = $etime;
		$checklink = "";
		if(!empty($stime)){
			$where .= "  and addtime > ".strtotime($stime)." ";
			$checklink .= '/stime/'.$stime;
		}
		if(!empty($etime)){
			$ktime = strtotime($etime)+86399;
			$where .= "  and addtime < ".$ktime." ";
			$checklink .= '/etime/'.$etime;
		}

		$pageshow = new page();
		$pageshow->setpage(IReq::get('page'),10);

		$txlist =   $this->mysql->getarr("select *  from ".Mysite::$app->config['tablepre']."shopjs  ".$where."   order by addtime desc   limit ".$pageshow->startnum().", ".$pageshow->getsize()."");
		$shuliang  = $this->mysql->counts("select *  from ".Mysite::$app->config['tablepre']."shopjs ".$where."  order by addtime asc  ");
		$pageshow->setnum($shuliang);

		$link = IUrl::creatUrlEx($this->mid,'/shopcenter/shopjs'.$checklink);
		$data['pagecontent'] = $pageshow->getpagebar($link);
		$tempdata = array();
		if(is_array($txlist)){
			foreach($txlist as $key=>$value){
				$value['name'] =  date('Y-m-d',$value['jstime']).'日结算金额';
				$value['adddate'] = date('Y-m-d H:i:s',$value['addtime']);
				$tempdata[] = $value;
			}
		}
		$data['jslist'] = $tempdata;
		$data['tjdata'] = $this->mysql->select_one("select sum(acountcost) as actcost,sum(onlinecost) as online,sum(unlinecost) as unline, sum(yjcost) as yj from ".Mysite::$app->config['tablepre']."shopjs  ".$where."    ");
		Mysite::$app->setdata($data);
	}


	function txlog(){
		$this->checkshoplogin();

		$shopid = ICookie::get('adminshopid');
		$shopinfo = $this->shopinfo();
		$stime = IFilter::act(IReq::get('stime')); //结算日
		$etime = IFilter::act(IReq::get('etime')); //结算结束日  假设从1号到 3号
		$where = " where  shopuid = ".$shopinfo['uid']." ";
		$data['stime'] = $stime;
		$data['etime'] = $etime;
		$checklink = "";
		if(!empty($stime)){
			$where .= "  and addtime > ".strtotime($stime)." ";
			$checklink .= '/stime/'.$stime;
		}
		if(!empty($etime)){
			$ktime = strtotime($etime)+86399;
			$where .= "  and addtime < ".$ktime." ";
			$checklink .= '/etime/'.$etime;
		}

		$pageshow = new page();
		$pageshow->setpage(IReq::get('page'),10);

		$txlist =   $this->mysql->getarr("select *  from ".Mysite::$app->config['tablepre']."shoptx  ".$where."   order by addtime desc   limit ".$pageshow->startnum().", ".$pageshow->getsize()."");
		$shuliang  = $this->mysql->counts("select *  from ".Mysite::$app->config['tablepre']."shoptx ".$where."  order by addtime asc  ");
		$pageshow->setnum($shuliang);

		$link = IUrl::creatUrlEx($this->mid,'/shopcenter/txlog'.$checklink);
		$data['pagecontent'] = $pageshow->getpagebar($link);

		$typearray = array(0=>'提现申请',1=>'账号充值',2=>'取消提现');
		$statusarray = array(0=>'空',1=>'申请',2=>'处理成功',3=>'已取消');

		$tempdata = array();
		if(is_array($txlist)){
			foreach($txlist as $key=>$value){
				$value['adddate'] = date('Y-m-d H:i:s',$value['addtime']);
				$value['statusname'] = isset($statusarray[$value['status']])?$statusarray[$value['status']]:'未定义';
				$tempdata[] = $value;
			}
		}
		$data['jslist'] = $tempdata;
		$data['typearray'] = $typearray;
		Mysite::$app->setdata($data);
	}

	//客人队列
	function lineupmanage(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		$mid = $this->mid;
        $status=1;
		$phszlist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."table_phsz  where shopid='".$shopid."' and mid='".$mid."' and status='".$status."' order by id desc");

		foreach($phszlist as $key=>$val){
			$val['phrs'] =$this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."table_krdl where shopid='".$shopid."' and mid='".$mid."' and phsz_id='".$val['id']."' and  status = 1 and to_days(addtime) = to_days(now())");
			$data['list'][] = $val;
		}
		//var_dump($data);
		Mysite::$app->setdata($data);

	}

	//排号设置
	function phsz(){

		$mid = $this->mid;
		$shopid = ICookie::get('adminshopid');
		$data['mid']=$mid;
		$data['shopid']=$shopid;
		Mysite::$app->setdata($data);
	}

	//添加/编辑排号设置
	function phsz_add(){

		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');
		$id = intval(IReq::get('id'));

		$data['cxinfo'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."table_phsz where id = '".$id."'  and shopid =  ".$shopid."  order by id desc limit 0, 100");
		Mysite::$app->setdata($data);
	}

	//保存排号设置
	function phsz_save(){

		$this->checkshoplogin();

		$shopid = ICookie::get('adminshopid');
		$data['shopid'] = $shopid;
		$data['sid'] = $shopid;
		$data['mid'] = $this->mid;
		$cxid = intval(IReq::get('cxid'));
		$data['dlname'] = IFilter::act(IReq::get('dlname'));
		$data['krslsy'] = IFilter::act(IReq::get('krslsy'));
		$data['dlphqz'] = IFilter::act(IReq::get('dlphqz'));
		$data['tqtzrs'] = IFilter::act(IReq::get('tqtzrs'));
		$data['status'] = IFilter::act(IReq::get('status'));
		$data['px'] = IFilter::act(IReq::get('px'));

		//正规处理后只拿字符串中的数字
		$dlname = IFilter::act(IReq::get('dlname'));
		preg_match_all('/\d+/',$dlname,$arr);
		$arr = join('',$arr[0]);
		$data['table_type'] = $arr;

		$starttime = IFilter::act(IReq::get('starttime'));
		$endtime = IFilter::act(IReq::get('endtime'));
		if(empty($starttime)) $this->message('phsz_starttime');
		if(empty($endtime)) $this->message('phsz_endtime');
		$data['starttime'] = strtotime($starttime);
		$data['endtime'] = strtotime($endtime);

		//if(!(IValidate::len($data['shopname'],2,50))) $this->message('shop_shopnamelenth');//$this->refunction('店铺名长度大于4小于50',$link);
		if(empty($data['dlname'])) $this->message('队列名称不能为空！');
		if(empty($data['krslsy'])) $this->message('客人数量少于等于多少人排入此队列不能为空！');
		if(empty($data['dlphqz'])) $this->message('队列编号前缀不能为空！');
		if(empty($data['tqtzrs'])) $this->message('提前通知人数不能为空！');

		if(empty($cxid)){

			//判断是否存在
			$sql = "select *  from ".Mysite::$app->config['tablepre']."table_phsz where shopid='".$shopid."' and mid='".$this->mid."' and dlname='".$data['dlname']."' ";
			$phsz =  $this->mysql->select_one($sql);
			if(!empty($phsz)){
				$this->message('此队列名称已存在');
			}


			$data['addtime'] = date('Y-m-d:H:i:s',time());
			$this->mysql->insert(Mysite::$app->config['tablepre'].'table_phsz',$data);
		}else{

			$this->mysql->update(Mysite::$app->config['tablepre'].'table_phsz',$data,"id='".$cxid."' ");
		}
		//
		$this->success('success');//(array('error'=>false));

	}

	//客人队列列表详情
	function krdl_list(){
		$this->checkshoplogin();
		$mid = $this->mid;
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');
		$id = intval(IReq::get('id'));
		$status = intval(IReq::get('status'));
		if($status){
			$where = 'and status='.$status;
		}
		$data['list'] = $this->mysql->getarr("select *  from ".Mysite::$app->config['tablepre']."table_krdl where phsz_id=".$id." and mid='".$mid."' and shopid='".$shopid."' and to_days(addtime) = to_days(now()) ".$where." order by start_queue_time asc");
		$data['id'] = $id;
                $data['krdl_id'] = $id;
		Mysite::$app->setdata($data);

	}

	//客人队列添加/编辑
	function krdl_add(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		if(empty($shopid)) $this->message('emptycookshop');
		$id = intval(IReq::get('id'));
		$data['kid']= intval(IReq::get('kid'));
		$data['cxinfo'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."table_krdl where id = '".$id."'  order by id desc limit 0, 100");
		Mysite::$app->setdata($data);

	}

	//保存客人队列
	function krdl_save(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		$data['shopid'] = $shopid;
		$data['shopid'] = $shopid;
		$data['mid'] = $this->mid;
		$cxid = intval(IReq::get('cxid'));
		$data['wxname'] = IFilter::act(IReq::get('wxname'));
		$data['status'] = IFilter::act(IReq::get('status'));
		$data['is_notice'] = IFilter::act(IReq::get('is_notice'));
		$data['tel'] = IFilter::act(IReq::get('tel'));
		$data['guest_numer'] = IFilter::act(IReq::get('guest_numer'));
		$data['phsz_id'] = IFilter::act(IReq::get('phsz_id'));
		$start_queue_time = IFilter::act(IReq::get('start_queue_time'));
		$data['start_queue_time'] = strtotime($start_queue_time);
		if(empty($cxid)){
			$data['addtime'] = date('Y-m-d:H:i:s',time());
			$this->mysql->insert(Mysite::$app->config['tablepre'].'table_krdl',$data);
		}else{

			$this->mysql->update(Mysite::$app->config['tablepre'].'table_krdl',$data,"id='".$cxid."' ");
		}
		$this->success('success');//(array('error'=>false));
	}

	//快速改变客人队列状态
	function editsaatus(){
		$this->checkshoplogin();
		$id = IReq::get('id');
		$status = IReq::get('status');
		$is_notice = IReq::get('is_notice');
		$shopid = ICookie::get('adminshopid');
		if(empty($id)) $this->message('操作有误');

		if($status){
			$data['status'] = $status;
			$this->mysql->update(Mysite::$app->config['tablepre'].'table_krdl',$data,"id='".$id."'");


			if($status==2){

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
			}

		}elseif($is_notice){
			$checkinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."table_krdl where id='".$id."'  ");
			if(empty($checkinfo)) $this->message('操作有误');

			//发送微信信息
			$krdl_info = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."wxuser where uid='".$checkinfo['uid']."' ");
			//sendCustomMsg($this->mid, "到你了亲",$krdl_info['openid'])
			//sendCustomMsg(90022, "到你了亲","oeOB9wFGRypT-LCJK5BeMIJmLN-Q");

			if(sendCustomMsg($this->mid, $this->SendWxMsg($checkinfo['uid']),$krdl_info['openid'])){
				$this->message('发送成功');
				//通知计数
				$data['is_notice'] = $checkinfo['is_notice'] +1;
				$this->mysql->update(Mysite::$app->config['tablepre'].'table_krdl',$data,"id='".$id."'");
			}else{
				$this->message('发送失败');
			}
		}


		$this->success('success');
	}

	function SendWxMsg($uid){
		if(empty($uid)) $this->message('微信信息发送失败！');
		$shopid = ICookie::get('adminshopid');
		$mid = $this->mid;

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


	//屏幕设置
	function screen_setting(){

		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		//if(empty($shopid)) $this->message('emptycookshop');
		$cxid = IFilter::act(IReq::get('cxid'));

		if($cxid=="ok"){
			$data['title'] = IFilter::act(IReq::get('title'));
			$data['shopid'] = $shopid;
			$data['mid'] = $this->mid;
			//$this->message($shopid.$this->mid);
			if(IReq::get('html5logo')){
				$data['imggb_url'] = IFilter::act(IReq::get('html5logo'));
			}
			if(IReq::get('wxqrimg')){
				$data['wxqrimg'] = IFilter::act(IReq::get('wxqrimg'));
			}

			//生成公众号二维码
			$qrfile = plugdir.'/qr/phpqrcode.php';
			$qrimages = "/upload/qrcode/screen/".$shopid."_".$this->mid.".png";
			if(file_exists($qrfile)){
				//$this->message($qrfile);
				include $qrfile;
				QRcode::png('http://www.baidu.com',$outfile=".".$qrimages, $level='L', $size=12, $margin=0,    $saveandprint=true);

				if(file_exists(".".$qrimages)){
					//$this->message($qrimages);
					$data['wxqrimg'] = $qrimages;
				}
			}


			$data['bottom_wz'] = IFilter::act(IReq::get('bottom_wz'));

			$cx = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."screen_setting where mid = '".$this->mid."' and shopid='".$shopid."' ");

			if($cx){
				$this->mysql->update(Mysite::$app->config['tablepre'].'screen_setting',$data,"mid = '".$this->mid."' and shopid='".$shopid."' ");
			}else{
				$this->mysql->insert(Mysite::$app->config['tablepre']."screen_setting",$data);
			}
			$this->success('success');

		}else{

			//$id = intval(IReq::get('id'));
			$data['cxinfo'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."screen_setting where mid = '".$this->mid."' and shopid='".$shopid."' ");
			Mysite::$app->setdata($data);

		}


	}

	//预定管理
	function Reserve() {
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		$mid = $this->mid;

		$list = $this->mysql->getarr("select *  from ".Mysite::$app->config['tablepre']."pre_time_slot where shopid='".$shopid."' and mid='".$mid."' order by px asc");
		//$data['list'] = $this->mysql->getarr("select a.*,b.yyy_total from ".Mysite::$app->config['tablepre']."pre_time_slot as a left join ".Mysite::$app->config['tablepre']."order  as b on a.table_area=b.table_area and a.table_type=b.table_type order by a.id desc");
		foreach($list as $key=>$val){
			$s = date('H:i',$val['start_booktime']);
			$e = date('H:i',$val['end_booktime']);
			$start_booktime = strtotime(date('Y-m-d',time()).' '.$s);//只取当天的数据
			$end_booktime = strtotime(date('Y-m-d',time()).' '.$e);
			$where2 = " and booktime >= ".$start_booktime." and booktime <= ".$end_booktime." and table_area='".$val['table_area']."' and table_type='".$val['table_type']."'";
			$val['yyy_total'] =$this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."order where reserve_id='".$val['id']."' and  is_goshop=2 and isbefore=2 ".$where2." ");
			if(empty($val['yyy_total'])){
				$val['yyy_total'] = 0;
			}
			$val['allowance'] = $val['reserve_num'] - $val['yyy_total'];
			$data['list'][] = $val;
		}
		Mysite::$app->setdata($data);
	}

	//更新预定排序
	function reserve_px_save() {
		$this->checkshoplogin();

		$px = IReq::get('px');
		$id = IReq::get('id');
		if($id){
			$n=count($px);
			//$this->message('a'.$n);
			for($i=0;$i<$n;$i++){
				$data['px'] = $px[$i];
				$this->mysql->update(Mysite::$app->config['tablepre'].'pre_time_slot',$data,"id='".$id[$i]."' ");
			}
			$this->success('success');
		}
	}

	//新建可预订时间段 添加/编辑
	function yudingtime_add(){

		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		$mid = $this->mid;
		$shopinfo = $this->shopinfo();	 //返回门店的所有信息
		$sid = $shopinfo['sid'];
		if (empty($sid)) {
			$this->message('emptycookshop');
		}
		$id = intval(IReq::get('id'));
		$data['id']= $id;
		$data['table_area_list'] = $this->mysql->getarr("select *, COUNT(DISTINCT TABLE_area) from ".Mysite::$app->config['tablepre']."table where shopid = '".$shopid."' and mid='".$mid."' GROUP BY TABLE_area order by id desc");
		$data['table_type_list'] = $this->mysql->getarr("select *, COUNT(DISTINCT TABLE_type) from ".Mysite::$app->config['tablepre']."table where shopid = '".$shopid."' and mid='".$mid."' GROUP BY TABLE_type order by id desc");
		$data['cxinfo'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."pre_time_slot where shopid = '".$shopid."' and mid='".$mid."' and id = ".$id." ");
		$data['mid']        = $mid;
		$data['shopid']     = $shopid;


		Mysite::$app->setdata($data);

	}

	//ajax各开放预约区域下的桌子by yanyh
	function getpreordertable(){

		$area = IReq::get('table_area');
		$mid = $this->mid;
		$shopid = ICookie::get('adminshopid');;

		$sql = "select distinct(table_type) from ".Mysite::$app->config['tablepre']."table where shopid = '".$shopid."'  and mid = '".$mid."' and table_area = '".$area."'";
		$preordertable = $this->mysql->getarr($sql);

		if(empty($preordertable)){
			$this->message('未开放！');
		}

		$this->success($preordertable);
	}

	//ajax查剩桌台余量
	function getsytable(){

		$shopid 	= ICookie::get('adminshopid');
		$mid    	= $this->mid;

		$yytime 	= IReq::get('yytime');
		$reserve_id = IReq::get('reserve_id');
		$starttime 	= IReq::get('starttime');
		$endtime 	= IReq::get('endtime');

		$cxinfo 	= $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."pre_time_slot where shopid = '".$shopid."'  and id = ".$reserve_id." ");

		$yytime_arr = explode('|',$yytime);
		$starttime  = strtotime($yytime_arr[0]);
		$endtime  	= strtotime($yytime_arr[1]);


		$where2 = "and booktime >= ".$starttime." and booktime <= ".$endtime." and table_area='".$cxinfo['table_area']."' and table_type='".$cxinfo['table_type']."'";
		$sql = "select * from ".Mysite::$app->config['tablepre']."order where shopid = '".$shopid."' and  is_goshop=2 and isbefore=2 and ydstatus!=2 and ydstatus!=3 and ydstatus!=0 ".$where2." ";
		$arr2 = $this->mysql->getarr($sql);


		$arr1 = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."table where shopid = '".$shopid."' and table_area='".$cxinfo['table_area']."' and table_type='".$cxinfo['table_type']."' and reserve_status=1 order by id desc");

		$ids1 = [];
		foreach ($arr1 as $key => $value) {
			$ids1[] = $value['id'];
		}

		$ids2 = [];
		foreach ($arr2 as $key => $value) {
			$ids2[] = $value['tableid'];
		}

		$arr = array_diff($ids1,$ids2);

		$count = count($arr);

		if($count == 0){
			$this->message('该时间段座位已满！');
		}
		$data =[];
		foreach ($arr as $key => $value) {
			//var_dump($value);
			$returnarr = $this->mysql->getarr("select id,name from ".Mysite::$app->config['tablepre']."table where id='".$value."' order by id desc");

			$data[$key]['id'] = $returnarr[0]['id'];
			$data[$key]['name'] = $returnarr[0]['name'];
		}

		$this->success($data);
	}

	//保存可预订时间段
	function yudingtime_save(){

		$this->checkshoplogin();

		$shopid = ICookie::get('adminshopid');
		$data['shopid'] = $shopid;
		$data['mid'] = $this->mid;
		$id = intval(IReq::get('id'));
		$data['status'] = IFilter::act(IReq::get('status'));
		$data['table_area'] = IFilter::act(IReq::get('table_area'));
		$data['table_type'] = IFilter::act(IReq::get('table_type'));
		//$data['reserve_num'] = IFilter::act(IReq::get('reserve_num'));
		$data['bookpay'] = IFilter::act(IReq::get('bookpay'));

		$start_booktime = IReq::get('start_booktime');
		$end_booktime= IReq::get('end_booktime');

		//统计可预约桌台总量
		$data['reserve_num'] = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."table where shopid = '".$shopid."' and table_area='".$data['table_area']."' and table_type='".$data['table_type']."' and reserve_status=1 ");

		//if($data['start_booktime']>$data['end_booktime']) $this->message('结束时间不能小于开始时间！');
		//if(empty($data['start_booktime'])) $this->message('开始时间不能为空！');
		//if(empty($data['end_booktime'])) $this->message('结束时间不能为空！');
		//if(empty($data['reserve_num'])) $this->message('可预定桌台总量不能为空！');

		if(empty($id)){
			//$data['addtime'] = date('Y-m-d:H:i:s',time());
			//$this->mysql->insert(Mysite::$app->config['tablepre'].'pre_time_slot',$data);

//                  foreach($data['start_booktime'] as $key=>$value){
//                        $data['start_booktime'] = strtotime($value);
//                        $data['addtime'] = date('Y-m-d:H:i:s',time());
//		  	$this->mysql->insert(Mysite::$app->config['tablepre'].'pre_time_slot',$data);
//                        //$this->message($value);
//		  }
			if(!is_array($start_booktime) && !is_array($end_booktime)){
				$this->message('请选择时间段！');

			}

			$n=count($start_booktime);
			if(empty($n)){
				$n=1;
			}
			//$this->message('a'.$n);
			for($i=0;$i<$n;$i++){
				$data['start_booktime'] = strtotime($start_booktime[$i]);
				$data['end_booktime'] = strtotime($end_booktime[$i]);
				//$this->message($start_booktime[$i].$end_booktime[$i]);
				$data['addtime'] = date('Y-m-d:H:i:s',time());
				$this->mysql->insert(Mysite::$app->config['tablepre'].'pre_time_slot',$data);
			}

		}else{
			$data['start_booktime'] = strtotime(IReq::get('start_booktime'));
			$data['end_booktime'] = strtotime(IReq::get('end_booktime'));
			$this->mysql->update(Mysite::$app->config['tablepre'].'pre_time_slot',$data,"id='".$id."' ");
		}
		//
		$this->success('success');//(array('error'=>false));
	}

	//删除可预订时间段
	function delreserve(){
		$id = IFilter::act(IReq::get('id'));

		$sel =  $this->mysql->select_one("select id  from ".Mysite::$app->config['tablepre']."pre_time_slot where id="."'$id'");

		$link =IUrl::creatUrlEx($this->mid,'shopcenter/Reserve');

		if(false != $sel){
			$rs =  $this->mysql->delete(Mysite::$app->config['tablepre'].'pre_time_slot',"id = '$id'");
			$this->success('success');
		}else{
			$this->message('id不存在或已被删除',$link);
		}

	}

        
         //桌台预订详情
        function table_reserve_detail(){ 
            $this->checkshoplogin();
            $shopid = ICookie::get('adminshopid');
            $mid = $this->mid;
            $id = IReq::get('id');
            $data['id'] =$id;
            
            //可预约天数
            $shopSql  = "SELECT can_reserve_day,date_of_appointment  FROM ".Mysite::$app->config['tablepre']."shop WHERE id = ".$shopid." AND mid = ".$mid."";
            $shop     = $this->mysql->select_one($shopSql);
            $daynum  = intval($shop['can_reserve_day']);
            
            $cxtabale = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."table where id = '".$id."' "); 
            $table_area = $cxtabale['table_area'];
            $table_type = $cxtabale['table_type'];
             
            $cx = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."pre_time_slot where shopid='".$shopid."' and mid='".$mid."' and table_area = '".$table_area."' and table_type='".$table_type."' "); 
            $ss = date('H:i',$cx['start_booktime']);
            $ee = date('H:i',$cx['end_booktime']);
            
            $timelist = array();
            $nowyear = date('Y',time());
            $nowyear = $nowyear+1;
            for($i=0;$i<$daynum;$i++){
                      $tempdata['name'] = date('Y-m-d',time()+86400*$i);
                      $tempdata['starttime'] = strtotime(date('Y-m-d',time()+86400*$i).' '.$ss);
                      $tempdata['endtime'] = strtotime(date('Y-m-d',time()+86400*$i).' '.$ee);
                      //$btime = $tempdata['name'];
                      //$btime = strtotime($btime)+60;  //当前时间加上一分钟
                      //$tempdata['bookstime'] = date('Y-m-d H:i:s ',$btime);
                      $timelist[] = $tempdata;
                      //echo  date('Y-m-d H:i:s',$tempdata['endtime']);
            }

            $list = array();
            foreach($timelist as $key=>$value){
                
                $where2 = "and booktime >= ".$value['starttime']." and booktime <= ".$value['endtime']." and table_area='".$table_area."' and table_type='".$table_type."' and tableid='".$id."'";
                $ordertj =$this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where shopid='".$shopid."' and mid='".$mid."' ".$where2."  and  is_goshop=2 and isbefore=2 and ydstatus!=2 and ydstatus!=3 and ydstatus!=0");
                
                $tlist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."pre_time_slot where shopid='".$shopid."' and mid='".$mid."' and table_area = '".$table_area."' and table_type='".$table_type."' "); 
                foreach ($tlist as $k => $v) {
                    
                    $day = date('Y-m-d',$ordertj['booktime']);
                    $stime = strtotime($day.' '.date('H:i',$v['start_booktime']));
                    $etime = strtotime($day.' '.date('H:i',$v['end_booktime']));
                    $otime = $ordertj['booktime'];
                 
                    if($otime >= $stime && $otime<=$etime){
                        $tlist[$k]['flag'] = 1;
                    }else{
                         $tlist[$k]['flag'] = 0;
                    }
                    $tlist[$k]['reserve_id'] = $v['id'];
                }
                $value['tlist'] = $tlist;
                  $list[]=$value;
              }
               
              //var_dump($list[0]["tlist"]['table_area']);
            $data['list'] =$list;
            $data['tablename'] = $cxtabale['name'];
            Mysite::$app->setdata($data); 
        }
        


	//后台预订订单下单 添加/编辑
	function reserve_order_add(){

		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		$mid = $this->mid;
		$shopinfo = $this->shopinfo();	 //返回门店的所有信息
		$sid = $shopinfo['sid'];
                $reserve_id = intval(IReq::get('reserve_id'));
                $date = IReq::get('date');
                $time = IReq::get('time');
                $tablename = IReq::get('tablename');
                
                $cx = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."pre_time_slot where id='".$reserve_id."' "); 
                $start_booktime = date('H:i',$cx['start_booktime']);
                $end_booktime = date('H:i',$cx['end_booktime']);
		
		$table_area = $cx['table_area'];
		$table_type = $cx['table_type'];

		$id = intval(IReq::get('id'));
		$data['id']= $id;
		$data['reserve_id']= $reserve_id;

		$data['table_list'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."table where shopid = '".$shopid."' and table_area='".$table_area."' and table_type='".$table_type."' and reserve_status=1 order by id desc");

		$data['cxinfo'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where id = ".$id." ");

		//可预约天数
		$shopSql  = "SELECT can_reserve_day,date_of_appointment  FROM ".Mysite::$app->config['tablepre']."shop WHERE id = ".$shopid." AND mid = ".$mid."";
		$shop     = $this->mysql->select_one($shopSql);
		$daynum  = intval($shop['can_reserve_day']);

//	    $year = date('Y',time());
//	    $month = date('M',time());
//            $timelist = array();
//            $checknowtime = time();
//            for($i=1;$i<7;$i++){
//              $starttime = strtotime($year.'-'.$i.'-01');
//              $endtime = strtotime("$year-$i-01 +1 month -1 day")+86400;
//              if($starttime < $checknowtime){
//                 $tempdata['name'] = date('Y-m-d',$checknowtime+86400*$i);
//                   $tempdata['starttime'] = $starttime;
//                   $tempdata['endtime'] = $endtime;
//                   $timelist[] = $tempdata;
//                }
//            }

		$timelist = array();
		$nowyear = date('Y',time());
		$nowyear = $nowyear+1;
		for($i=0;$i<$daynum;$i++){
			$tempdata['name'] = date('Y-m-d',time()+86400*$i);
			$tempdata['starttime'] = strtotime(date('Y-m-d',time()+86400*$i).' '.$start_booktime);
			$tempdata['endtime'] = strtotime(date('Y-m-d',time()+86400*$i).' '.$end_booktime);
			$btime = $tempdata['name']." ".$start_booktime;
			$btime = strtotime($btime)+60;  //当前时间加上一分钟
			$tempdata['bookstime'] = date('Y-m-d H:i:s ',$btime);
			$timelist[] = $tempdata;
			//echo  date('Y-m-d H:i:s',$tempdata['endtime']);
		}

		$list = array();
		foreach($timelist as $key=>$value){
			$where2 = "and booktime >= ".$value['starttime']." and booktime <= ".$value['endtime']." and table_area='".$table_area."' and table_type='".$table_type."'";
			$ordertj =$this->mysql->select_one("select count(id) as yyy_total from ".Mysite::$app->config['tablepre']."order where shopid='".$shopid."' and mid='".$mid."' ".$where2."  and  is_goshop=2 and isbefore=2 and ydstatus!=2 and ydstatus!=3 and ydstatus!=0");

			$tabletotal = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."table where shopid = '".$shopid."' and mid='".$mid."' and table_area='".$table_area."' and table_type='".$table_type."' and reserve_status=1 ");
			$value['reserve_num'] = $tabletotal;//总量

			$value['allowance'] =  $tabletotal - $ordertj['yyy_total']; //剩余量
			$value['yyy_total'] =  $ordertj['yyy_total']; //已预约桌台量

			$list[] = $value;

		}

		$data['list'] =$list;

		$data['start_booktime'] =  $start_booktime;
		$data['end_booktime'] = $end_booktime;
		$data['date'] = $date;
		$data['time'] = $time;
		$data['tablename'] = $tablename;

		Mysite::$app->setdata($data);

	}

	//保存后台预订订单下单
	function reserve_order_save(){

		$this->checkshoplogin();

		$shopid = ICookie::get('adminshopid');
		$data['shopid'] = $shopid;
		$data['mid'] = $this->mid;
		$id = intval(IReq::get('id'));
		$data['booktime'] = strtotime(IFilter::act(IReq::get('booktime')));
		$data['tableid'] = intval(IFilter::act(IReq::get('tableid')));
		$data['buyername'] = IFilter::act(IReq::get('buyername'));
		$data['sex'] = IFilter::act(IReq::get('sex'));
		$data['buyerphone'] = IFilter::act(IReq::get('buyerphone'));
		$data['content'] = IFilter::act(IReq::get('content'));
		$data['reserve_id'] = IFilter::act(IReq::get('reserve_id'));
		$data['is_goshop'] = 2;  //预约用2标识
		$data['isbefore'] = 2;  //预约用2标识
                $data['ordertype']  	= 2;//订单类型,2表示电话
		$data['ydstatus'] = 1;//已接单
                $data['status'] = 1;//已接单
		$data['dno'] = time().rand(1000,9999);
		$data['addtime'] = time();
                
                $cxshop = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where id = '".$shopid."' ");
                $data['shopname'] = $cxshop['shopname'];
                $data['shopaddress'] = $cxshop['address'];
                $data['shopphone'] = $cxshop['phone'];

		if(empty($data['booktime'])) $this->message('预定时间不能为空！');
		if(empty($data['tableid'])) $this->message('请选择桌号！');
		if(!(IValidate::phone($data['buyerphone']))) $this->message('输入的电话号码格式不正确');
//		$time = date('Y-m-d',time());
//		$shopSql  = "SELECT can_reserve_day  FROM ".Mysite::$app->config['tablepre']."shop WHERE id = ".$shopid." ";
//		$shop     = $this->mysql->select_one($shopSql);
//		$daynum  = intval($shop['can_reserve_day']);
                
		//$stime = strtotime(date('Y-m-d H:i',time()));
		$timenow = time();
                //$this->message($data['reserve_id']);
		if($data['booktime']<$timenow){
			$this->message("所选的预定时间已过时");
			// if( strtotime(date('H:i',$data['booktime'])) < $stwotime || strtotime(date('H:i',$data['booktime']))>$etwotime )
		}
                
                //判断用户选择是否在此时间内
		$cxslot = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."pre_time_slot where shopid = '".$shopid."' and id = ".$data['reserve_id']." ");
		$stwotime = strtotime(date('H:i',$cxslot['start_booktime']));
		$etwotime = strtotime(date('H:i',$cxslot['end_booktime']));
		if( strtotime(date('H:i',$data['booktime'])) < $stwotime || strtotime(date('H:i',$data['booktime'])) > $etwotime ){
			$this->message("所选的预定时间不在此时间段内");
		}

		//根据table的id查桌台的区域和类型
		$cxinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."table where id = ".$data['tableid']." ");
		$data['table_area'] = $cxinfo['table_area'];
		$data['table_type'] = $cxinfo['table_type'];

		if(empty($id)){
			//$data['addtime'] = date('Y-m-d:H:i:s',time());
			//$this->message('aaaaa');
			$this->mysql->insert(Mysite::$app->config['tablepre'].'order',$data);

			//用客户预定时间标识此桌此时间是否被预约
			$table_data['booktime'] = $data['booktime'];
			$this->mysql->update(Mysite::$app->config['tablepre'].'table',$table_data,"id='".$data['tableid']."' ");

		}else{

			$this->mysql->update(Mysite::$app->config['tablepre'].'order',$data,"id='".$id."' ");
		}
		//
		$this->success('success');//(array('error'=>false));
	}

	//批量可预订时间段 添加/编辑
	function yudingtime_batch_add(){

		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		$shopinfo = $this->shopinfo();	 //返回门店的所有信息
		$sid = $shopinfo['sid'];
		$mid = $this->mid;
		if(empty($sid)) $this->message('emptycookshop');
		$id = intval(IReq::get('id'));
		$data['id']= $id;
		$data['table_area_list'] = $this->mysql->getarr("select *, COUNT(DISTINCT TABLE_area) from ".Mysite::$app->config['tablepre']."table where shopid = '".$shopid."' and mid='".$mid."' GROUP BY TABLE_area order by id desc");
		//$data['table_type_list'] = $this->mysql->getarr("select *, COUNT(DISTINCT TABLE_type) from ".Mysite::$app->config['tablepre']."table where sid = '".$sid."' and mid='".$mid."' GROUP BY TABLE_type order by id desc");
		$data['cxinfo'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."pre_time_slot where shopid = '".$shopid."' and mid='".$mid."' and id = ".$id." ");

		Mysite::$app->setdata($data);

	}

	//保存批量可预订时间段
	function yudingtime_batch_save(){

		$this->checkshoplogin();

		$shopid = ICookie::get('adminshopid');
		$data['shopid'] = $shopid;
		$data['mid'] = $this->mid;
		$id = intval(IReq::get('id'));
		$data['status'] = IFilter::act(IReq::get('status'));
		//$data['booktime'] = strtotime(IReq::get('booktime'));
		$data['start_booktime'] = strtotime(IReq::get('start_booktime'));
		$data['end_booktime'] = strtotime(IReq::get('end_booktime'));
		$data['table_area'] = IFilter::act(IReq::get('table_area'));
		//$data['table_type'] = IFilter::act(IReq::get('table_type'));

		//统计可预约桌台总量
		$data['reserve_num'] = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."table where shopid = '".$shopid."' and table_area='".$data['table_area']."' and reserve_status=1 ");
		//$data['reserve_num'] = IFilter::act(IReq::get('reserve_num'));
		$data['bookpay'] = IFilter::act(IReq::get('bookpay'));

		if(empty($data['start_booktime'])) $this->message('开始时间不能为空！');
		if(empty($data['end_booktime'])) $this->message('结束时间不能为空！');
		if(empty($data['reserve_num'])) $this->message('可预定桌台总量不能为空！');

		$table_type = IFilter::act(IReq::get('table_type'));
		$table_type = str_replace("，",",",$table_type);
		$table_type = explode(",", $table_type); //把字符串转成数组

		foreach($table_type as $key=>$value){
			$data['table_type'] = $value;
			$data['addtime'] = date('Y-m-d:H:i:s',time());
			$this->mysql->insert(Mysite::$app->config['tablepre'].'pre_time_slot',$data);
			//$this->message($value);
		}

		$this->success('success');//(array('error'=>false));
	}

	//预约详情设置编辑
	function Reserve_detail_setting(){

		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
		$mid = $this->mid;

		$data['cxinfo'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where id = '".$shopid."' and mid='".$mid."' ");
		//$data['reserve_remarks'] =  urldecode($data['cxinfo']['reserve_remarks']);
		Mysite::$app->setdata($data);

	}

	//保存预约详情设置
	function Reserve_detail_settig_save(){

		$this->checkshoplogin();

		$sid = ICookie::get('adminshopid');
		$mid = $this->mid;
		if(!$sid && !$mid) $this->message('操作有误！');

		$data['is_advance_order'] = IFilter::act(IReq::get('is_advance_order'));
		$data['is_reserve_today'] = IFilter::act(IReq::get('is_reserve_today'));
		$data['can_reserve_day'] = IFilter::act(IReq::get('can_reserve_day'));
		$data['date_of_appointment'] = IFilter::act(IReq::get('date_of_appointment'));
		$data['reserve_remarks'] = IFilter::act(IReq::get('reserve_remarks'));
		$data['is_wxpay'] = IFilter::act(IReq::get('is_wxpay'));
		$data['auto_cancel_reserve'] = intval(IReq::get('auto_cancel_reserve'));
		$data['overtime_consumption'] = intval(IReq::get('overtime_consumption'));

		$this->mysql->update(Mysite::$app->config['tablepre'].'shop',$data,"id = '".$sid."' and mid='".$mid."' ");

		$this->success('success');//(array('error'=>false));
	}

	//预定订单管理
	function Reserve_order() {

		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');

		$mid = $this->mid;
		if(empty($shopid)) $this->message('emptycookshop');
		$starttime = trim(IFilter::act(IReq::get('starttime')));
		$endtime = trim(IReq::get('endtime'));
		$dno = trim(IFilter::act(IReq::get('dno')));
		$ydstatus = IFilter::act(IReq::get('ydstatus'));
		$paystatus = IFilter::act(IReq::get('paystatus'));
		$is_order = IFilter::act(IReq::get('is_order'));
		$table_area = IFilter::act(IReq::get('table_area'));
		$table_type = IFilter::act(IReq::get('table_type'));
		$orderSource = intval(IReq::get('orderSource'));
                $tableid = IFilter::act(IReq::get('tableid'));
//		$data['starttime'] = empty($starttime)?date('Y-m-d'):$starttime;
//		$data['endtime'] = empty($endtime)?date('Y-m-d'):$endtime;
                $data['starttime'] = $starttime;
		$data['endtime'] = $endtime;
		$data['orderSource'] = $orderSource;
		$data['ydstatus'] = $ydstatus;
		$data['paystatus'] = $paystatus;
                
		$data['is_order'] = $is_order;
		$data['table_area'] = $table_area;
		$data['table_type'] = $table_type;
		//$nowday = date('Y-m-d',time());
		//$starttime = empty($starttime)? $nowday:$starttime;
		//$endtime = empty($endtime)? $nowday:$endtime;
		$newlink = '';
		$where= " and shopid='".$shopid."' and mid='".$mid."'";
		if($starttime){
			$where .= " and addtime > ".strtotime($starttime.' 00:00:00');
			$newlink .= '/starttime/'.$starttime;
		}

		if($endtime)
		{
			$where .= " and addtime < ".strtotime($endtime.' 23:59:59');
			$newlink .= '/endtime/'.$endtime;
		}
		if ($dno) {
			$where .= " and dno=".$dno;
			$newlink .= '/dno/'.$dno;
		}
		if ($ydstatus !='') {
			$where .= " and ydstatus=".$ydstatus;
			$newlink .= '/ydstatus/'.$ydstatus;
		}
		if ($paystatus !='') {
			$where .= " and paystatus=".$paystatus;
			$newlink .= '/paystatus/'.$paystatus;
		}
		if ($is_order) {
			$where .= " and is_order=".$is_order;
			$newlink .= '/is_order/'.$is_order;
		}
		if ($table_area) {
			$where .= " and table_area ='".$table_area."' ";
			$newlink .= '/table_area/'.$table_area;
		}
		if ($table_type) {
			$where .= " and table_type=".$table_type;
			$newlink .= '/table_type/'.$table_type;
		}
                if ($tableid) {
                        $table  = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."table where name='".$tableid."' ");
//                        echo $table['id'];die;
			$where .= " and tableid=".$table['id'];
			$newlink .= '/tableid/'.$table['id'];
		}

		$data['outlink'] =IUrl::creatUrlEx($this->mid,'shopcenter/out_reserve_order/outtype/query'.$newlink);
		$link = IUrl::creatUrlEx($this->mid,'shopcenter/Reserve_order'.$newlink);

		$this->pageCls->setpage(IReq::get('page'));
		$data['list'] = $this->mysql->getarr("select *  from ".Mysite::$app->config['tablepre']."order where is_goshop=2 and isbefore=2 ".$where." order by id desc limit ".$this->pageCls->startnum().", ".$this->pageCls->getsize()."");

		foreach ($data['list'] as $k=>$v){
			$table  = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."table where id='".$v['tableid']."' ");
			$data['list'][$k]['table_name'] = $table['name'];

		}

		$shuliang  = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."order where  is_goshop=2 and isbefore=2 ".$where." ");
		$this->pageCls->setnum($shuliang);
		$data['pagecontent'] = $this->pageCls->getpagebar($link);

		$data['table_area_list'] = $this->mysql->getarr("select *, COUNT(DISTINCT TABLE_area) from ".Mysite::$app->config['tablepre']."table where shopid = '".$shopid."' and mid='".$mid."' GROUP BY TABLE_area order by id desc");
		$data['table_type_list'] = $this->mysql->getarr("select *, COUNT(DISTINCT TABLE_type) from ".Mysite::$app->config['tablepre']."table where shopid = '".$shopid."' and mid='".$mid."' GROUP BY TABLE_type order by id desc");

		$sl  = $this->mysql->select_one("select count(id) as sl,sum(allcost) as allcost from ".Mysite::$app->config['tablepre']."order where is_goshop=2 and isbefore=2  ".$where." order by addtime desc");
		$data['tongji'] = $sl;

		Mysite::$app->setdata($data);
	}

	//预订订单操作
	function reserve_order_op_more() {
		$shopid = ICookie::get('adminshopid');
		$mid = $this->mid;
		$ydstatus = intval(IReq::get('ydstatus'));
		$paystatus = intval(IReq::get('paystatus'));
		$refund = intval(IReq::get('refund'));
		$id = IReq::get('id');
                
                $order = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where id = '".$id."' ");


		if (empty($id)){
			$this->message('不存在此条记录');}

		if($ydstatus){
			$data['ydstatus'] = $ydstatus;
			if(is_array($id)){
				//如果是数组就批量操作
				foreach($id as $key=>$value){
                                    
                                    $cxorder = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where id = '".$value."' ");   
                                   
                                    if($cxorder['booktime']>time() && $ydstatus==2){ //点击批量完成操作
                                        $this->message("未到预约时间不能设置为已完成");
                                    }else{
                                            $data['suretime'] = time();//更新完成时间
                                            $this->mysql->update(Mysite::$app->config['tablepre'].'order',$data," id=".$value." "); 
                                            $info = $this->mysql->select_one("select a.buyeruid,a.shopid,b.openid,a.wx_notice,a.id from ".Mysite::$app->config['tablepre']."order a, ".Mysite::$app->config['tablepre']."wxuser b where a.buyeruid = b.uid and a. id = '".$value."'");
                                            sendCustomMsg($this->mid, $this->SendWxMsgStatus($info['id'],$ydstatus),$info['openid']);  //发送已完成的微信通知信息
                                    }
                                    
                                    if($ydstatus==1){    //点击批量接单操作
                                    
                                            if($cxorder['ydstatus']==1){
                                            $this->message('已经接过此订单了！');}
                                        
                                            //要选接单后才分配桌台
                                            $tableid = $this->ReserveOrders_GetTableid($value);
                                            $tdata['tableid'] = $tableid;
                                            $this->mysql->update(Mysite::$app->config['tablepre'].'order',$tdata,"id='".$value."'");

                                            //发送预订微信信息
                                            $info = $this->mysql->select_one("select a.buyeruid,a.shopid,b.openid,a.wx_notice,a.id from ".Mysite::$app->config['tablepre']."order a, ".Mysite::$app->config['tablepre']."wxuser b where a.buyeruid = b.uid and a. id = '".$value."'");
                                            if(sendCustomMsg($this->mid, $this->SendWxMsg_yd($info['buyeruid'],$info['shopid'],$info['id']),$info['openid'])){
                                              
                                                //通知计数
                                                $data['wx_notice'] = $info['wx_notice'] +1;
                                                $data['ydstatus'] = $ydstatus;
                                                $this->mysql->update(Mysite::$app->config['tablepre'].'order',$data,"id='".$info['id']."'");
                                                //$this->message('通知成功');
                                            }
                                    }
				
				}
                        }elseif($ydstatus==4){
                            $this->mysql->update(Mysite::$app->config['tablepre'].'order',$data," id=".$id." ");  
                        }else{
                        
                                if($order['ydstatus']==1){
                                 $this->message('已经接过此订单了！');}
                            
                                //要选接单后才分配桌台
                                $tableid = $this->ReserveOrders_GetTableid($id);
                                $tdata['tableid'] = $tableid;
                                $this->mysql->update(Mysite::$app->config['tablepre'].'order',$tdata,"id='".$id."'");
                                
                                //发送预订微信信息
                                $info = $this->mysql->select_one("select a.buyeruid,a.shopid,b.openid,a.wx_notice,a.id from ".Mysite::$app->config['tablepre']."order a, ".Mysite::$app->config['tablepre']."wxuser b where a.buyeruid = b.uid and a. id = '".$id."'");
                                if(sendCustomMsg($this->mid, $this->SendWxMsg_yd($info['buyeruid'],$info['shopid'],$info['id']),$info['openid'])){
                                    
                                    //通知计数
                                    $data['wx_notice'] = $info['wx_notice'] +1;
                                    $data['ydstatus'] = $ydstatus;
                                    $this->mysql->update(Mysite::$app->config['tablepre'].'order',$data,"id='".$info['id']."'");
                                    $this->success('通知成功');
                                }else{
                                    $this->message('通知失败');
                                }
                                
                                
			}
		}elseif($paystatus){
			$data['paystatus'] = $paystatus;
			if(is_array($id)){
				//如果是数组就批量操作
				foreach($id as $key=>$value){

					$this->mysql->update(Mysite::$app->config['tablepre'].'order',$data," id='".$value."' ");

				}
			}else{
				$this->mysql->update(Mysite::$app->config['tablepre'].'order',$data," id='".$id."' ");
			}
		}

		$this->success('success');
	}
        
         //预订订单接单后分配桌台号
        function ReserveOrders_GetTableid($orderid) {
            $shopid = ICookie::get('adminshopid');
            $mid = $this->mid;
            $cxorder = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where id = '".$orderid."' ");
            $click_unix = $cxorder['booktime'];
            $area = $cxorder['table_area'];
            $type = $cxorder['table_type'];
            
             //查找对比点击的时间是否在某预约段
             $sql = "select * from ".Mysite::$app->config['tablepre']."pre_time_slot where  shopid = '".$shopid."' and table_area = '".$area."' and table_type = '".$type."'";	
             $preordertime = $this->mysql->getarr($sql);

             foreach ($preordertime as $key => $value) {
                     $sbooktime = strtotime(date('Y-m-d',$click_unix)." ".date('H:i:s',$value['start_booktime']));
                     $ebooktime = strtotime(date('Y-m-d',$click_unix)." ".date('H:i:s',$value['end_booktime']));

                     if($click_unix >= $sbooktime && $click_unix <= $ebooktime){

                             //能预约的桌子
                             $pretableSql = "select * from ".Mysite::$app->config['tablepre']."table where shopid = '".$shopid."' and table_area='".$area."' and table_type='".$type."' and reserve_status=1 order by id desc";
                             $pretable    = $this->mysql->getarr($pretableSql); 
                             //已预约的桌子
                             $preorderSql = "select * from ".Mysite::$app->config['tablepre']."order where shopid = '".$shopid."' and  is_goshop=2 and isbefore=2 and ydstatus!=2 and ydstatus!=3 and ydstatus!=0 and booktime >= ".$sbooktime." and booktime <= ".$ebooktime." and table_area='".$area."' and table_type='".$type."'";
                             $preorder    = $this->mysql->getarr($preorderSql);

                             $ids1 = [];
                             foreach ($pretable as $key => $value) {
                                 $ids1[] = $value['id'];
                             }

                             $ids2 = [];
                             foreach ($preorder as $key => $value) {
                                 $ids2[] = $value['tableid'];
                             }

                             $arr = array_diff($ids1,$ids2);

                             $count = count($arr);

                                     if($count == 0){
                                             $this->message('该时间段座位已满！');
                                     }

                                     $tableid = array_rand(array_flip($arr));
                     } 
             }   

           return $tableid;
        
        }
        
        //预订订单微信通知操作
	function reserve_order_wxnotice() {
		$shopid = ICookie::get('adminshopid');
		$mid = $this->mid;
		$wx_notice = intval(IReq::get('wx_notice'));
		$id = IReq::get('id');
                
                $order = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where id = '".$id."' ");

		if (empty($id) || empty($wx_notice)){
			$this->message('不存在此条记录');}
                
                if(is_array($id)){
                    //如果是数组就批量操作
                    foreach($id as $key=>$value){
                       
                        //发送预订微信信息
                        $info = $this->mysql->select_one("select a.buyeruid,a.shopid,b.openid,a.wx_notice,a.id from ".Mysite::$app->config['tablepre']."order a, ".Mysite::$app->config['tablepre']."wxuser b where a.buyeruid = b.uid and a. id = '".$value."'");
                        if(sendCustomMsg($this->mid, $this->SendWxMsg_yd($info['buyeruid'],$info['shopid'],$info['id']),$info['openid'])){
                            
                            //通知计数
                            $datas['wx_notice'] = $info['wx_notice'] +1;
                            $this->mysql->update(Mysite::$app->config['tablepre'].'order',$datas,"id='".$info['id']."'");
                            $this->message('通知成功');
                        }else{
                            $this->message('通知失败');
                        }
                       
                    }
                }else{
                    
                        //发送预订微信信息
                        $info = $this->mysql->select_one("select a.buyeruid,a.shopid,b.openid,a.wx_notice,a.id from ".Mysite::$app->config['tablepre']."order a, ".Mysite::$app->config['tablepre']."wxuser b where a.buyeruid = b.uid and a. id = '".$id."'");
                        if(sendCustomMsg($this->mid, $this->SendWxMsg_yd($info['buyeruid'],$info['shopid'],$id),$info['openid'])){
                            //通知计数
                            $datas['wx_notice'] = $info['wx_notice'] +1;
                            $this->mysql->update(Mysite::$app->config['tablepre'].'order',$datas,"id='".$id."'");
                            $this->message('通知成功');
                        }else{
                            $this->message('通知失败');
                        }
                }

		$this->success('success');
	}
        
        
        //预订微信内容
        function SendWxMsg_yd($uid=NULL,$shopid=NULL,$orderid=NULL){
            if(empty($uid)) $this->message('微信信息发送失败！');

            $info = $this->mysql->select_one("select b.shopname,a.booktime,a.table_area,a.table_type,c.name as tablename,a.dno from ".Mysite::$app->config['tablepre']."order a, ".Mysite::$app->config['tablepre']."shop b,".Mysite::$app->config['tablepre']."table c where a.shopid = b.id and a.tableid=c.id and  a. id = '".$orderid."'");
   //$this->message($info['shopname']);         
            $str = "在线预订成功提醒\n";
            $str .= "门店：".$info['shopname']."\n";
            $str .= "订单号：".$info['dno']."\n";
            $str .= "预订时间：".date('Y-m-d H:i',$info['booktime']) ."\n";
            $str .= "区域：".$info['table_area']."\n";
            $str .= "桌型：".$info['table_type']."人桌\n";
            $str .= "桌号：".$info['tablename']."\n";
            $str .= "请按照预订时间到店消费，超过规定时间将自动取消订单\n";
            //$link = IUrl::creatUrlEx($this->mid,'wxsite/phdetails/shopid/'.$shopid);
            //$str .= "<a href='".$link."'>查看详情</a>";
            
            return $str;
        }
        
    function SendWxMsgStatus($orderid=NULL,$ydstatus){
       if(empty($orderid)) $this->message('微信信息发送失败！');

       $info = $this->mysql->select_one("select a.suretime,a.dno from ".Mysite::$app->config['tablepre']."order a, ".Mysite::$app->config['tablepre']."shop b,".Mysite::$app->config['tablepre']."table c where a.shopid = b.id and a.tableid=c.id and  a. id = '".$orderid."'");
       switch ($ydstatus) {
            case 2:
                $str = "您已完成订单！\n";
                $str .= "完成时间：".date('Y-m-d H:i',$info['suretime']) ."\n";
                $str .= "订单号：".$info['dno']."\n";
                $str .= "期待您的再次光临！\n";
            break;

           default:
               break;
       }                

       return $str;
   }

	//删除预约订单
	function delreserve_order(){
		$id = IReq::get('id');
		if(empty($id))  $this->message('不存在');
		$ids = is_array($id)? join(',',$id):$id;
		$this->mysql->delete(Mysite::$app->config['tablepre'].'order'," id in($ids) ");
		$this->success('success');
	}

	//预约订单详情
	function reserve_order_detail(){

		$this->checkshoplogin();
		$sid = ICookie::get('adminshopid');
		$mid = $this->mid;
		if(empty($sid)) $this->message('emptycookshop');
		$id = intval(IReq::get('id'));

		$data['cxinfo'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where shopid = '".$sid."' and mid='".$mid."' and id = ".$id." ");
		Mysite::$app->setdata($data);

	}

	//导出预约订单
	function out_reserve_order()
	{
		$outtype = IReq::get('outtype');

		if(!in_array($outtype,array('query','ids')))
		{
			header("Content-Type: text/html; charset=UTF-8");
			echo '查询条件错误';
			exit;
		}
		$where = '';
		if($outtype == 'ids')
		{
			$id = trim(IReq::get('id'));
			if(empty($id))
			{
				header("Content-Type: text/html; charset=UTF-8");
				echo '查询条件不能为空';
				exit;
			}
			$doid = explode('-',$id);
			$id = join(',',$doid);
			$where .= ' and id in('.$id.') ';
		}else{

			$starttime = trim(IReq::get('starttime'));
			$where .= !empty($starttime)? ' and   addtime > '.strtotime($starttime.' 00:00:01').' ':'';

			$endtime = trim(IReq::get('endtime'));
			$where .= !empty($endtime)? ' and   addtime < '.strtotime($endtime.' 23:59:59').' ':'';

		}

		$outexcel = new phptoexcel();
		$titledata = array('订单号','姓名','联系电话','下单时间','预约日期','订单状态','桌台区域','桌台类型');
		$titlelabel = array('dno','buyername','buyerphone','addtime','booktime','ydstatus','table_area','table_type');

		$datalist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."order where is_goshop=2 and isbefore=2  ".$where." order by id desc  limit 0,2000");
                
                foreach ($datalist as $k=>$v){
                   
                    $datalist[$k]['addtime'] = "\r\n".date('Y-m-d H:i',$v['addtime']);
                    $datalist[$k]['booktime'] = "\r\n".date('Y-m-d H:i',$v['booktime']);
                    $datalist[$k]['dno'] = "\r\n".$v['dno'];
                    $datalist[$k]['buyerphone'] = "\r\n".$v['buyerphone'];
                    $datalist[$k]['table_type'] = $v['table_type']."人桌";
                    
                    switch ($v['ydstatus']) {
                        case 0:
                            $datalist[$k]['ydstatus'] = "未接单";
                            break;
                        case 1:
                            $datalist[$k]['ydstatus'] = "已接单";
                            break;
                        case 2:
                            $datalist[$k]['ydstatus'] = "已完成";
                            break;
                        case 3:
                            $datalist[$k]['ydstatus'] = "已取消";
                            break;
                        case 4:
                            $datalist[$k]['ydstatus'] = "消费中";
                            break;
                        default:
                            break;
                    }

		}

		$outexcel->out($titledata,$titlelabel,$datalist,'','预约订单');
	}

	//智能小二 by yanyh
	function noopsychePetty(){

		$this->checkshoplogin();
		$data['sitetitle'] = '智能小二';
		$shopid 		   = ICookie::get('adminshopid');

		//修改wifi
		$wifiname     = IReq::get('wifiname');
		$wifipassword = IReq::get('wifipassword');

		if($wifiname && $wifipassword){
			$wifi 			  = array($wifiname,$wifipassword);
			$res['wifi_info'] = serialize($wifi);
			$this->mysql->update(Mysite::$app->config['tablepre'].'shop',$res,"id='".$shopid."'");

		}

		$shopSql           = "select * from ".Mysite::$app->config['tablepre']."shop  where id = '".$shopid."' ";
		$shopinfo 		   = $this->mysql->select_one($shopSql);

		$data['wifi'] = unserialize($shopinfo['wifi_info']);
		//小二广告,活动
		$ad1Sql = "select * from ".Mysite::$app->config['tablepre']."adv  where advtype='ad1'  and shopid=".$shopid." order by id desc limit 3";
		$ad1    = $this->mysql->select_one($ad1Sql);
		$ad2Sql = "select * from ".Mysite::$app->config['tablepre']."adv  where advtype='ad2'  and shopid=".$shopid." order by id desc limit 3";
		$ad2    = $this->mysql->select_one($ad2Sql);
		$ad3Sql = "select * from ".Mysite::$app->config['tablepre']."adv  where advtype='ad3'  and shopid=".$shopid." order by id desc limit 3";
		$ad3    = $this->mysql->select_one($ad3Sql);
		$ad4Sql = "select * from ".Mysite::$app->config['tablepre']."adv  where advtype='ad4'  and shopid=".$shopid." order by id desc limit 3";
		$ad4    = $this->mysql->select_one($ad4Sql);
		$ad5Sql = "select * from ".Mysite::$app->config['tablepre']."adv  where advtype='ad5'  and shopid=".$shopid." order by id desc limit 3";
		$ad5    = $this->mysql->select_one($ad5Sql);
		$ad6Sql = "select * from ".Mysite::$app->config['tablepre']."adv  where advtype='ad6'  and shopid=".$shopid." order by id desc limit 3";
		$ad6    = $this->mysql->select_one($ad6Sql);

		$data['ad1']   = $ad1;
		$data['ad2']   = $ad2;
		$data['ad3']   = $ad3;
		$data['ad4']   = $ad4;
		$data['ad5']   = $ad5;
		$data['ad6']   = $ad6;
	 
		$data['shopinfo']  = $shopinfo;
		Mysite::$app->setdata($data);

	}

	//改变状态 by yanyh
	function changestatus(){

		$type   	= IReq::get('type');
		$status 	= IReq::get('status');
		$shopid 	= ICookie::get('adminshopid');

		$shophdimg  = IReq::get('_src');//修改首页模版

		$shophdimg = strstr($shophdimg, '/templates');  

		if($shophdimg){
			$res['shophdimg'] = $shophdimg;
			$this->mysql->update(Mysite::$app->config['tablepre'].'shop',$res,"id='".$shopid."'");

			$this->success('操作成功');
		}

		$is_status = 'is_'.$type;
		$data[$is_status] = $status;
		$this->mysql->update(Mysite::$app->config['tablepre'].'shop',$data,"id='".$shopid."'");


		$this->success('操作成功！');


	}
	//删除活动图片 by yanyh
	function actimgdelete(){ 
		$mid 	= $this->mid;
		$shopid = ICookie::get('adminshopid');
		$type   = IReq::get('type');

		$this->mysql->delete(Mysite::$app->config['tablepre'].'adv',"shopid = ".$shopid." and mid=".$mid." and advtype='".$type."'");

		$link = IUrl::creatUrlEx($this->mid,'shopcenter/noopsychePetty');
		$this->message('删除成功！',$link);

	}
        
        //员工管理
	function staffmanage(){
                $this->checkshoplogin();
		$mid 	= $this->mid;
		$shopid = ICookie::get('adminshopid');
                $shopinfo = $this->shopinfo(); //根据门店id返回门店的所有信息
                $data['sid'] = $shopinfo['sid'];
                $keywords =   trim(IReq::get('keywords'));
                $stafftype =   trim(IReq::get('stafftype'));
                
                //岗位下拉框
                $data["catlist"] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."staff_post where shopid = '".$shopid."' and mid='".$mid."' ");

		$where = " where a.sid = {$shopinfo['sid']} and a.mid = {$mid}"; //var_dump($shopinfo);exit;
                $sql = "select a.id,c.id as cid,c.sn,c.staffid from ".Mysite::$app->config['tablepre']."staff as a left join ".Mysite::$app->config['tablepre']."staff_qrcode_set as c on c.staffid = a.id ".$where." order by id desc";
                $data['qrcodelist'] = $this->mysql->getarr($sql);
               
                if ($keywords) {
			$where .= " and name='".$keywords."' or sn='".$keywords."'";
			//$newlink .= '/dno/'.$dno;
		}
                if ($stafftype) {
			$where .= " and applypost='".$stafftype."'";
			//$newlink .= '/dno/'.$dno;
		}

                $stafflist = $this->mysql->getarr("select a.*,b.shopname from ".Mysite::$app->config['tablepre']."staff as a left join ".Mysite::$app->config['tablepre']."shop  as b on a.sid=b.sid left join ".Mysite::$app->config['tablepre']."staff_qrcode_set as c on c.staffid =a.id".$where." group by a.id order by a.id desc");
                
                foreach ($stafflist as $k=>$v){
			$cx  = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."attendancesetting where id='".$v['shift_id']."' ");
			$stafflist[$k]['bcname'] = $cx['name'];

		}
		$data['list']   = $stafflist;
		Mysite::$app->setdata($data);
	}
        
        //员工新建/编辑
	function staff_add(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
                $mid 	= $this->mid;
		if(empty($shopid)) $this->message('emptycookshop');
                
		$id = intval(IReq::get('id'));
		$data['cxinfo'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."staff where id = '".$id."' ");
                $data['catlist'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."staff_post where shopid = '".$shopid."' and mid='".$mid."' ");
                $data['attlist'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."attendancesetting where shopid = '".$shopid."' order by px asc");
                
		Mysite::$app->setdata($data);
	}

	//保存员工
	function staff_save(){
		$this->checkshoplogin();
                $cxid = intval(IReq::get('cxid'));
		$shopid = ICookie::get('adminshopid');
                $shopinfo = $this->shopinfo();	 //返回门店的所有信息
		$data['sid'] = $shopinfo['sid'];
		$data['shopid'] = $shopid;
		$data['mid'] = $this->mid;
		$data['name'] = IFilter::act(IReq::get('name'));
		$data['tel'] = IFilter::act(IReq::get('tel'));
		$data['status'] = IFilter::act(IReq::get('status'));
		$data['remarks'] = IFilter::act(IReq::get('remarks'));
        $data['shift_id'] = IFilter::act(IReq::get('shift_id'));
        $data['picurl'] = IFilter::act(IReq::get('picurl'));
        $data['wechat_qrcode'] = IFilter::act(IReq::get('wechat_qrcode'));
                
                $applypost = IFilter::act(IReq::get('applypost'));
                $applypost = implode(",",$applypost);//数组转字符串,      explode(",", $table_type); //把字符串转成数组
		$data['applypost'] = $applypost;
                //$this->message($postid);
                if(empty($data['name'])) $this->message('姓名不能为空');
                if(!(IValidate::phone($data['tel']))) $this->message('输入的电话号码格式不正确');
                
		if(empty($cxid)){
			$data['addtime'] = date('Y-m-d:H:i:s',time());
			$this->mysql->insert(Mysite::$app->config['tablepre'].'staff',$data);
		}else{

			$this->mysql->update(Mysite::$app->config['tablepre'].'staff',$data,"id='".$cxid."' ");
		}
		$this->success('success');//(array('error'=>false));
	}
        
        //批量更新员工管理排序
	function staff_px_save() {
		$this->checkshoplogin();

		$px = IReq::get('px');
		$id = IReq::get('id');
		if($id){
			$n=count($px);
			//$this->message('a'.$n);
			for($i=0;$i<$n;$i++){
				$data['px'] = $px[$i];
				$this->mysql->update(Mysite::$app->config['tablepre'].'staff',$data,"id='".$id[$i]."' ");
			}
			$this->success('success');
		}
	}
        
        //删除员工
	function delstaff(){
		$id = IFilter::act(IReq::get('id'));

		$sel =  $this->mysql->select_one("select id  from ".Mysite::$app->config['tablepre']."staff where id="."'$id'");

		$link =IUrl::creatUrlEx($this->mid,'shopcenter/staffmanage');

		if(false != $sel){
			$rs =  $this->mysql->delete(Mysite::$app->config['tablepre'].'staff',"id = '$id'");
			$this->success('success');
		}else{
			$this->message('id不存在或已被删除',$link);
		}
	}
        
        //添加员工手表设备
	function getoprtostaff(){
		$sn =   trim(IReq::get('sn'));
		$id =   trim(IReq::get('id'));

		$sel =  $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."staff_qrcode_set where staffid="."'$id' and sn="."'$sn'");

		if(!empty($sel)){
			$this->success('不可重复添加');
		}else{
			$data  =array(
				'staffid' => $id,
				'sn' => $sn,
				'addtime' => date("Y-m-d h:i:s"),
			);

			$rs = $this->mysql->insert(Mysite::$app->config['tablepre']."staff_qrcode_set", $data);
			if(false != $rs ){
				$this->message('success');
			}
		}


	}
        
	//删除员工手表设备
	function delstaffwatch(){
		$id = IFilter::act(IReq::get('id'));
		$sel =  $this->mysql->select_one("select id  from ".Mysite::$app->config['tablepre']."staff_qrcode_set where id="."'$id'");
		if(false != $sel){
			$rs =  $this->mysql->delete(Mysite::$app->config['tablepre'].'staff_qrcode_set',"id = '$id'");
			$this->message('success');
		}else{
			$this->success('id不存在或已被删除');
		}

	}
        
        //岗位管理
	function staff_post(){
                $this->checkshoplogin();
		$mid 	= $this->mid;
		$shopid = ICookie::get('adminshopid');

		$list = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."staff_post where mid=".$mid." and shopid=".$shopid." order by id desc ");
                
		$data['list']   = $list;
		Mysite::$app->setdata($data);

	}
        
        //添加岗位
	function addstaff_post(){
                $this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');//获得首页传递过来的门店id
		$name =   trim(IReq::get('name'));
                $mid 	= $this->mid;
		$data  =array(
			'shopid'=>$shopid,
                        'mid' => $mid,
			'name' => $name,
			'addtime' => date("Y-m-d h:i:s",time()),
		);
		$rs = $this->mysql->insert(Mysite::$app->config['tablepre']."staff_post", $data);
		$id = $this->mysql->insertid();
		$info = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."staff_post where id = '$id'");
		if(empty($info)) $this->message('type_empty');
		$this->success($info);
	}
        
        //删除岗位
	function delstaff_post(){
                $this->checkshoplogin();
		$id = IFilter::act(IReq::get('id'));

		$sel =  $this->mysql->select_one("select id  from ".Mysite::$app->config['tablepre']."staff_post where id="."'$id'");

		$link =IUrl::creatUrlEx($this->mid,'shopcenter/staff_post');

		if(false != $sel){
			$rs =  $this->mysql->delete(Mysite::$app->config['tablepre'].'staff_post',"id = '$id'");
			$this->success('success');
		}else{
			$this->message('id不存在或已被删除',$link);
		}

	}
        
        //考勤设置
	function attendancesetting(){
                $this->checkshoplogin();
		$mid 	= $this->mid;
		$shopid = ICookie::get('adminshopid');
                
		$data['cxinfo'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where id = '".$shopid."' ");

		$list = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."attendancesetting where mid=".$mid." and shopid=".$shopid." order by px asc ");
                
//                foreach ($list as $k=>$v){
//			$cx  = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."staff_post where id='".$v['postid']."' ");
//			$list[$k]['staffname'] = $cx['name'];
//		}
		$data['list']   = $list;
		Mysite::$app->setdata($data);
	}
        
        //工作时间段新建/编辑
	function worktime_add(){
		$this->checkshoplogin();
		$shopid = ICookie::get('adminshopid');
                $mid 	= $this->mid;
		if(empty($shopid)) $this->message('emptycookshop');
                
		$id = intval(IReq::get('id'));
		$cxinfo  = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."attendancesetting where id = '".$id."' ");
                $catlist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."staff_post where shopid = '".$shopid."' and mid='".$mid."' ");
                
                $worktimearray = explode('-',$cxinfo['worktime'] );
                $data['startworktime']  = $worktimearray[0];
                $data['endworktime']  = $worktimearray[1];
                
                $cxinfoarray = explode(',',$cxinfo['applypost'] );
                foreach ($catlist as $key => $value) {
                    if(in_array( $value['name'],$cxinfoarray)){
                        $catlist[$key]['flag'] = 1;
                    }else{
                        $catlist[$key]['flag'] = 0;
                    }
                }
                $data['catlist'] = $catlist;
                $data['cxinfo']  = $cxinfo;       
		Mysite::$app->setdata($data);
	}

	//保存员工
	function worktime_save(){
		$this->checkshoplogin();
                $cxid = intval(IReq::get('cxid'));
		$shopid = ICookie::get('adminshopid');
                $shopinfo = $this->shopinfo();	 //返回门店的所有信息
		$data['sid'] = $shopinfo['sid'];
		$data['shopid'] = $shopid;
		$data['mid'] = $this->mid;
		$data['name'] = IFilter::act(IReq::get('workname'));
                $starttime = IFilter::act(IReq::get('starttime'));
                $endtime = IFilter::act(IReq::get('endtime'));
		$data['worktime'] = $starttime.'-'.$endtime;
                $data['upsignin'] = IFilter::act(IReq::get('upsignin'));
                $data['downsignin'] = IFilter::act(IReq::get('downsignin'));
		$data['status'] = IFilter::act(IReq::get('status'));
		
                $applypost = IFilter::act(IReq::get('applypost'));
                $applypost = implode(",",$applypost);//数组转字符串,      explode(",", $table_type); //把字符串转成数组
		$data['applypost'] = $applypost;
                //$this->message($applypost);
                if(empty($data['name'])) $this->message('工作时间段名称不能为空');
//                if(!(IValidate::phone($data['tel']))) $this->message('输入的电话号码格式不正确');
                
		if(empty($cxid)){
			$data['addtime'] = date('Y-m-d:H:i:s',time());
			$this->mysql->insert(Mysite::$app->config['tablepre'].'attendancesetting',$data);
		}else{

			$this->mysql->update(Mysite::$app->config['tablepre'].'attendancesetting',$data,"id='".$cxid."' ");
		}
		$this->success('success');//(array('error'=>false));
	}
        
        //批量更新考勤设置
	function worktime_px_save() {
		$this->checkshoplogin();

		$px = IReq::get('px');
		$id = IReq::get('id');
		if($id){
			$n=count($px);
			//$this->message('a'.$n);
			for($i=0;$i<$n;$i++){
				$data['px'] = $px[$i];
				$this->mysql->update(Mysite::$app->config['tablepre'].'attendancesetting',$data,"id='".$id[$i]."' ");
			}
			$this->success('success');
		}
	}
        
        //删除考勤设置
	function delworktime(){
		$id = IFilter::act(IReq::get('id'));

		$sel =  $this->mysql->select_one("select id  from ".Mysite::$app->config['tablepre']."attendancesetting where id="."'$id'");

		$link =IUrl::creatUrlEx($this->mid,'shopcenter/attendancesetting');

		if(false != $sel){
			$rs =  $this->mysql->delete(Mysite::$app->config['tablepre'].'attendancesetting',"id = '$id'");
			$this->success('success');
		}else{
			$this->message('id不存在或已被删除',$link);
		}
	}
        
         //打卡记录
	function punchrecording(){
           
                $this->checkshoplogin();
		$mid 	= $this->mid;
		$shopid = ICookie::get('adminshopid');
                $shopinfo = $this->shopinfo(); //根据门店id返回门店的所有信息
                $data['sid'] = $shopinfo['sid'];
                $year =   IReq::get('year');
                $month =   IReq::get('month');

                //echo $month;die;
                //年份下拉列表
		$nowyear = date('Y',time());
                for($i=0;$i<=3;$i++){
                    $yearlist[] = $nowyear-$i;
                }
		$data['yearlist'] = $yearlist;
                $data['year'] = $year ? $year:'';
                
                //月份下拉列表
                for($i=1;$i<=12;$i++){
                    $monthlist[] = iconv_strlen($i,'gb2312')==1?'0'.$i:$i;
                     
                }
                
		$data['monthlist'] = $monthlist;
                $nowmonth = date('m',time());
                $data['nowmonth'] = $nowmonth;
                $data['month'] = $month ? $month:$nowmonth;
                
                if ($data['year'] && $data['month']) {
			$where = " and year='".$data['year']."' and month='".$data['month']."'";
			$newlink .= '/year/'.$data['year'].'/month/'.$data['month'];
		}
                
                $list = $this->mysql->getarr("select *, COUNT(DISTINCT sn) from ".Mysite::$app->config['tablepre']."punchrecording where shopid = '".$shopid."' and mid='".$mid."' ".$where." GROUP BY sn order by id desc");
                //var_dump($list);die;
           
            //返回日期
            for($i=1;$i<=31;$i++){
                $dayarr[] = $i;    
	    }
            $data["dayarr"] =  $dayarr;         
            
            //遍历打卡记录
          	foreach ($list as $k=>$v){
	            //$hitcard = date("H:i",$v['hitcard']);
	          
	            $attend  = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."attendancesetting where id = '".$v['shift_id']."' ");
	            $attendarr =explode("-", $attend['worktime']);
                
                    //考勤设置的上班时间
	            $atime = '2017-06-01  '.date("H:i",strtotime($attendarr[0]));
                    $atime = strtotime($atime);
                    
                    //考勤设置的下班时间
                    $ctime = '2017-06-01  '.date("H:i",strtotime($attendarr[1]));
                    $ctime = strtotime($ctime);
                    //echo date("H:i",strtotime($attendarr[1]));die;
                    $hitcard = $this->mysql->getarr("select hitcard from ".Mysite::$app->config['tablepre']."punchrecording where sn='".$v['sn']."' order by id asc");
                 
                    $hitcardarr = [];
                    for($i=1;$i<=31;$i++){ 
                        foreach ($hitcard as $key => $value) {
                            $d = date("d",$value['hitcard']);
                            $time = date("H:i",$value['hitcard']);
                            
                            if($i == $d){
                                
                                $hitcardarr[$i].= $hitcardarr[$i]? '<br/>'.$time : $time;
                                
                            }else{
                             
                                $hitcardarr[$i]= $hitcardarr[$i];
                            }
                        }
                    }
                    
	            foreach ($hitcardarr as $key => $value) {
           
                            if($value){
                                $arr = explode("<br/>", $value);
                                //asort($arr);
                                foreach ($arr as $kk => $vv){
                                    $btime = strtotime('2017-06-01  '.$vv);
                                    if($btime > $atime && $btime < $ctime){
                                        $arr[$kk] = "<span style='color:red'>".$vv."</span>";
                                    }
                                }     

                            }
                           $arr = implode('<br/>', $arr);
                           $hitcardarr[$key] = $arr;
            
                    }
	            
	            $list[$k]['dakai'] = $hitcardarr;
        	}   
          
          
                
                $data['outlink'] =IUrl::creatUrlEx($this->mid,'shopcenter/out_punchrecording/outtype/query'.$newlink);
                $data['list']   = $list;
                Mysite::$app->setdata($data);
	}
        
                //导出员工打卡记录
	function out_punchrecording()
	{
                $mid 	= $this->mid;
		$shopid = ICookie::get('adminshopid');
		$outtype = IReq::get('outtype');

		if(!in_array($outtype,array('query','ids')))
		{
			header("Content-Type: text/html; charset=UTF-8");
			echo '查询条件错误';
			exit;
		}
		$where = '';
		if($outtype == 'ids')
		{
			$id = trim(IReq::get('id'));
			if(empty($id))
			{
				header("Content-Type: text/html; charset=UTF-8");
				echo '查询条件不能为空';
				exit;
			}
			$doid = explode('-',$id);
			$id = join(',',$doid);
			$where .= ' and id in('.$id.') ';
		}else{

			$year = trim(IReq::get('year'));
                        $month = trim(IReq::get('month'));
			$where .= ' and year= '.$year.' and month='.$month;
		}

		$outexcel = new phptoexcel();
                //所有人单个月的打卡记录（去重）
		//$datalist = $this->mysql->getarr("select distinct name,sn,applypost from ".Mysite::$app->config['tablepre']."punchrecording where shopid = '".$shopid."' and mid='".$mid."' ".$where." order by id desc ");
                $datalist = $this->mysql->getarr("select *, COUNT(DISTINCT sn) from ".Mysite::$app->config['tablepre']."punchrecording where shopid = '".$shopid."' and mid='".$mid."' ".$where." GROUP BY sn order by id desc");
                foreach ($datalist as $k=>$v){
                    //每个人的单个月打卡记录
                    $hitcard = $this->mysql->getarr("select hitcard from ".Mysite::$app->config['tablepre']."punchrecording where sn='".$v['sn']."' order by id asc");
                    $hit = [];
                    for($i=1;$i<=31;$i++){ 
                        foreach ($hitcard as $key => $value) {
                            $d = date("d",$value['hitcard']);
                            $time = date("H:i",$value['hitcard']);
                            if($i == $d){
                                $hit[$i].= $hit[$i]? "\r\n".$time : $time;

                            }else{
                                $hit[$i] = $hit[$i];
                            }
                            $datalist[$k]['datetime'] = ' '.date('Y-m',$value['hitcard']);
                            
                        }
                        $datalist[$k]['m'.$i] = $hit[$i];   
                        
                        $dayarr[$i] = $i;    
                        $dayarr2[$i] = 'm'.$i;
                    } 
                    $datalist[$k]['sn'] = "[".$v['sn']."]";
                       
                        
                    //var_dump($datalist);die;
                        //$datalist[$k]['m'.$i] = $hitcard[$i-1];
                        //$datalist[$k]['m'.$i] = $v['year'];
                        
                 
		}
                
                //$daystr = implode(",",$dayarr);//数组转字符串
                $titledata = [];
                foreach ($dayarr as $key => $value) {
                    $titledata[0] = '日期';
                    $titledata[1] = '设备号';
                    $titledata[2] = '姓名';
                    $titledata[3] = '岗位';
                    $titledata[$key+4] = $value;
                }
                $titlelabel = [];
                foreach ($dayarr2 as $key => $value) {
                    $titlelabel[0] = 'datetime';
                    $titlelabel[1] = 'sn';
                    $titlelabel[2] = 'name';
                    $titlelabel[3] = 'applypost';
                    $titlelabel[$key+4] = $value;
                }
                //var_dump($arr);die;
                //$titledata = array('设备号','姓名','岗位',$daystr);
                      
		//$titlelabel = array('sn','name','applypost','dakai');
//                var_dump($datalist);
//                exit;
                
		$outexcel->out($titledata,$titlelabel,$datalist,'员工打卡记录');
	}
        
         //改变员工考勤设置状态
	function changeattendancesetting(){
                $mid 	= $this->mid;
		$shopid = ICookie::get('adminshopid');

		//$is_scanhitcard   	= IReq::get('is_scanhitcard');
		$is_longhitcard 	= IReq::get('is_longhitcard');
		$hitcarddistance 	= IReq::get('hitcarddistance');
                if(isset($is_longhitcard)){
                   $data['is_longhitcard'] = $is_longhitcard;
                }
                if(isset($hitcarddistance)){
		   $data['hitcarddistance'] = $hitcarddistance;
                }
		$this->mysql->update(Mysite::$app->config['tablepre'].'shop',$data,"id='".$shopid."' and mid='".$mid."'");


		$this->success('操作成功！');
	}


	public function rewardStatistics(){
		$operator  = IReq::get('operator');
		$StartDate = IReq::get('StartDate')? IReq::get('StartDate'):date('Y-m-d',strtotime("-7 day"));
		$EndDate   = IReq::get('EndDate')? IReq::get('EndDate'):date('Y-m-d',time());
        $mid = $this->mid;
 

        $info = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."weixin_reward where Operator='{$operator}'  and successed='Y' and MID='{$mid}'and time between '{$StartDate}:00:00:00' and '{$EndDate}:23:59:59'  order by id desc");
 
 
        $data['StartDate'] = $StartDate;
        $data['EndDate'] = $EndDate;
        $data['mid'] = $mid;
        $data['operator'] = $operator;
        $data['info'] = $info;
 
        Mysite::$app->setdata($data);
    }

    //打赏设置
    public function rewardSet(){
        $mid = $this->mid;
        $shopid = ICookie::get('adminshopid');
    	
    	$rewardSetInfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."weixin_reward_set where mid= '".$mid."' and  shopid= '".$shopid."'");

       	$comment1 = IReq::get('comment1');
       	$comment2 = IReq::get('comment2');
       	$comment3 = IReq::get('comment3');
       	$comment4 = IReq::get('comment4');
       	$comment5 = IReq::get('comment5');
       	$price1 = IReq::get('price1');
       	$price2 = IReq::get('price2');
       	$price3 = IReq::get('price3');
       	$price4 = IReq::get('price4');
       	$price5 = IReq::get('price5');
       	$memo = IReq::get('memo');
       	$status = IReq::get('status');

        if($rewardSetInfo && $comment1){
            $data['comment1'] = 	$comment1; 
            $data['comment2'] = 	$comment2; 
            $data['comment3'] = 	$comment3; 
            $data['comment4'] = 	$comment4; 
            $data['comment5'] = 	$comment5; 
            $data['price1']   = 	$price1;           
            $data['price2']   = 	$price2;           
            $data['price3']   = 	$price3;
            $data['price4']   = 	$price4;
            $data['price5']   = 	$price5;
            $data['memo']     = 	$memo;
            $data['status']   = 	$status;

            $return_str = $this->mysql->update(Mysite::$app->config['tablepre']."weixin_reward_set",$data,"shopid= '".$shopid."'");

            $link = IUrl::creatUrlEx($this->mid,'shopcenter/rewardSet');
			$this->message('保存成功',$link);

           
        }elseif(!$rewardSetInfo && $comment1){
        	$data['comment1'] = 	$comment1; 
            $data['comment2'] = 	$comment2; 
            $data['comment3'] = 	$comment3; 
            $data['comment4'] = 	$comment4; 
            $data['comment5'] = 	$comment5; 
            $data['price1']   = 	$price1;           
            $data['price2']   = 	$price2;           
            $data['price3']   = 	$price3;
            $data['price4']   = 	$price4;
            $data['price5']   = 	$price5;
            $data['memo']     = 	$memo;
            $data['status']   = 	$status;
            $data['mid']   = 	$mid;
            $data['shopid']   = 	$shopid;

            $return_str = $this->mysql->insert(Mysite::$app->config['tablepre']."weixin_reward_set",$data);

            $link = IUrl::creatUrlEx($this->mid,'shopcenter/rewardSet');
			$this->message('保存成功',$link);
        }

        $rewardSetInfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."weixin_reward_set where mid= '".$mid."' and  shopid= '".$shopid."'");
    	
        $data['info'] = $rewardSetInfo;
 
    	Mysite::$app->setdata($data);
     }
    //打赏统计
    public function midRewardStatistics(){ 
        $mid =$this->mid;
        $shopid = ICookie::get('adminshopid');
        $StartDate = IReq::get('StartDate');
        $dateType = IReq::get('dateType')?IReq::get('dateType'):'today'; 

        $StartDate = IReq::get('StartDate')? IReq::get('StartDate'):date('Y-m-d',strtotime("-7 day"));
		$EndDate  = IReq::get('EndDate')? IReq::get('EndDate'):date('Y-m-d',time());
 
        $date = date('Y-m-d',time());
        $date7 = date('Y-m-d',strtotime('-6 day'));
        $date30 = date('Y-m-d',strtotime('-29 day'));
        $where7 =" and time between '{$date7} 00:00:00' and '{$date} 23:59:59'";
        $where30 =" and time between '{$date30} 00:00:00' and '{$date} 23:59:59'";

        //$mid ='90002';
        $dzArr = array();
        $totalArr = array();
        $viewsArr = array();


        if($dateType == 'today'){ 
            /*-------------------------------------当天数据-------------------------------------------------------*/
            $num =1;
            $dianzan = $this->mysql->getarr("select count(id) cn,DATE_FORMAT(time, '%Y-%m-%d') time from ".Mysite::$app->config['tablepre']."weixin_reward where mid='{$mid}' and shopid='{$shopid}' and comment <>'' and TO_DAYS(time) = TO_DAYS(NOW())");
 			$tota = $this->mysql->getarr("select IFNULL(round(SUM(fee),2),0) total,DATE_FORMAT(time, '%Y-%m-%d') time from ".Mysite::$app->config['tablepre']."weixin_reward where mid='{$mid}' and successed='Y' and shopid='{$shopid}' and TO_DAYS(time) = TO_DAYS(NOW())");

 			$views = $this->mysql->getarr("select count(id) cn,DATE_FORMAT(time, '%Y-%m-%d') time from ".Mysite::$app->config['tablepre']."weixin_reward where mid='{$mid}'  and shopid='{$shopid}' and TO_DAYS(time) = TO_DAYS(NOW())");
 
            $Zdianzan = $dianzan[0]['cn'];
            $Ztotal = $tota[0]['total'];
            $Zviews = $views[0]['cn'];
 
        }
        if($dateType == 'week'){ 
            /*-------------------------------------7天数据-------------------------------------------------------*/
            $num =7;
 
            $dianzan = $this->mysql->getarr("select count(id) cn,DATE_FORMAT(time, '%Y-%m-%d') time from ".Mysite::$app->config['tablepre']."weixin_reward where mid='{$mid}'  and shopid='{$shopid}' and comment <>''and DATE_SUB(CURDATE(), INTERVAL 7 DAY) <= date(time) GROUP BY DATE_FORMAT(time, '%Y-%m-%d')");
            $tota = $this->mysql->getarr("select IFNULL(round(SUM(fee),2),0) total,DATE_FORMAT(time, '%Y-%m-%d') time from ".Mysite::$app->config['tablepre']."weixin_reward where mid='{$mid}' and successed='Y' and shopid='{$shopid}' and DATE_SUB(CURDATE(), INTERVAL 7 DAY) <= date(time) GROUP BY DATE_FORMAT(time, ' %Y-%m-%d')");
            $views = $this->mysql->getarr("select count(id) cn,DATE_FORMAT(time, '%Y-%m-%d') time from ".Mysite::$app->config['tablepre']."weixin_reward where mid='{$mid}'  and shopid='{$shopid}' and DATE_SUB(CURDATE(), INTERVAL 7 DAY) <= date(time) GROUP BY DATE_FORMAT(time, '%Y-%m-%d')");

     		$Zdianzan = $this->mysql->select_one("select count(id) cn from ".Mysite::$app->config['tablepre']."weixin_reward where mid='{$mid}'  and shopid='{$shopid}' and comment <>'' {$where7} ");
            $Zdianzan = $Zdianzan['cn'];

            $Ztotal = $this->mysql->select_one("select IFNULL(round(SUM(fee),2),0) total from ".Mysite::$app->config['tablepre']."weixin_reward where mid='{$mid}'  and successed='Y' and shopid='{$shopid}' {$where7} ");
            $Ztotal = $Ztotal['total'];
 
            $Zviews = $this->mysql->select_one("select count(id) cn from ".Mysite::$app->config['tablepre']."weixin_reward where mid='{$mid}'  and shopid='{$shopid}' {$where7} ");
            $Zviews = $Zviews['cn'];
   
        }
        if($dateType == 'month'){
            /*-------------------------------------30天数据-------------------------------------------------------*/
            $num =30;
    
            $dianzan = $this->mysql->getarr("select count(id) cn,DATE_FORMAT(time, '%Y-%m-%d') time from ".Mysite::$app->config['tablepre']."weixin_reward where mid='{$mid}' and shopid='{$shopid}' and comment <>''and DATE_SUB(CURDATE(), INTERVAL 7 DAY) <= date(time) GROUP BY DATE_FORMAT(time, '%Y-%m-%d')");
            $tota = $this->mysql->getarr("select IFNULL(round(SUM(fee),2),0) total,DATE_FORMAT(time, '%Y-%m-%d') time from ".Mysite::$app->config['tablepre']."weixin_reward where mid='{$mid}' and successed='Y' and shopid='{$shopid}' and DATE_SUB(CURDATE(), INTERVAL 7 DAY) <= date(time) GROUP BY DATE_FORMAT(time, '%Y-%m-%d')");
            $views = $this->mysql->getarr("select count(id) cn,DATE_FORMAT(time, '%Y-%m-%d') time from ".Mysite::$app->config['tablepre']."weixin_reward where mid='{$mid}'  and shopid='{$shopid}' and DATE_SUB(CURDATE(), INTERVAL 7 DAY) <= date(time) GROUP BY DATE_FORMAT(time, '%Y-%m-%d')");
 
            $Zdianzan = $this->mysql->select_one("select count(id) cn from ".Mysite::$app->config['tablepre']."weixin_reward where mid='{$mid}' and shopid='{$shopid}' and comment <>'' {$where30} ");
            $Zdianzan = $Zdianzan['cn'];

            $Ztotal = $this->mysql->select_one("select IFNULL(round(SUM(fee),2),0) total from ".Mysite::$app->config['tablepre']."weixin_reward where mid='{$mid}'  and  successed='Y' and shopid='{$shopid}' {$where30} ");
            $Ztotal = $Ztotal['total'];

            $Zviews = $this->mysql->select_one("select count(id) cn from ".Mysite::$app->config['tablepre']."weixin_reward where mid='{$mid}'  and shopid='{$shopid}' {$where7} ");
            $Zviews = $Zviews['cn'];
     		
        }
 
        for($i = 0;$i<$num;$i++){
            $day = strftime('%Y-%m-%d',strtotime("-$i day"));
            $dzArr['data'][$i]=0;
            $dzArr['dateList'][$i]=$day;
            $totalArr['data'][$i]=0;
            $totalArr['dateList'][$i]=$day;
            $viewsArr['data'][$i]=0;
            $viewsArr['dateList'][$i]=$day;

            foreach ($dianzan as $v) {
                if ($v['time'] == $day) {
                    $dzArr['data'][$i] = (int)$v['cn'];
                }
            }
 
            foreach ($tota as $v) {  
                if ($v['time'] == $day) {
                    $totalArr['data'][$i] = (int)$v['total'];
                }
            }
            foreach ($views as $v) {
                //echo $v['time']."==".$day;
                if ($v['time'] == $day) {
                    $viewsArr['data'][$i] = (int)$v['cn'];
                }
            }
        }
		//小二打赏排行
        $xiaoer = $this->mysql->getarr("select round(SUM(fee),2) tfee,operator,nickname,comment,time,round(AVG(start),2) start from ".Mysite::$app->config['tablepre']."weixin_reward where mid='{$mid}'  and shopid='{$shopid}' and successed='Y' group by operator order by tfee desc  limit 0,10");
 
        //小二查询
        $xiaoer_cha = $this->mysql->getarr("select  round(SUM(fee),2) tfee,operator,nickname,comment,time,round(AVG(start),2) start from ".Mysite::$app->config['tablepre']."weixin_reward where mid='{$mid}'  and shopid='{$shopid}'  and time between '{$StartDate}:00:00:00' and '{$EndDate}:23:59:59' group by nickname,operator order by time desc ");
 		
        $xiaoer_cha_fee = $this->mysql->getarr("select  round(SUM(fee),2) tfee, nickname, operator from ".Mysite::$app->config['tablepre']."weixin_reward where mid='{$mid}'  and shopid='{$shopid}' and successed='Y'  and time between '{$StartDate}:00:00:00' and '{$EndDate}:23:59:59' group by nickname,operator");
 
        foreach ($xiaoer_cha as $key => $value) {
        	foreach ($xiaoer_cha_fee as $k => $v) {
        		 if($value['nickname']==$v['nickname'] && $value['operator'] == $v['operator'] ){
        		 	$xiaoer_cha[$key]['fee'] = $v['tfee'];
        		 } 
        	}	
        }
 

        $data['Zdianzan'] = $Zdianzan;
        $data['Ztotal'] = $Ztotal;
        $data['Zviews'] = $Zviews;
        $data['dateType'] = $dateType;
        $data['dzArr'] = $dzArr;
        $data['totalArr'] = $totalArr;
        $data['viewsArr'] = $viewsArr;
        $data['dzjson'] = json_encode($dzArr);
        $data['totaljson'] = json_encode($totalArr); 
        $data['viewsjson'] = json_encode($viewsArr);
        $data['StartDate'] = $StartDate;
        $data['EndDate'] = $EndDate;
        $data['xiaoer'] = $xiaoer;
        $data['xiaoer_cha'] = $xiaoer_cha;

 
        Mysite::$app->setdata($data);
    }
    
    //员工能操作排号的手表
    function watchequipment(){
            $this->checkshoplogin();
            $mid 	= $this->mid;
            $shopid = ICookie::get('adminshopid');
            $shopinfo = $this->shopinfo(); //根据门店id返回门店的所有信息
            $data['sid'] = $shopinfo['sid'];
            $keywords =   trim(IReq::get('keywords'));
            $stafftype =   trim(IReq::get('stafftype'));

            //岗位下拉框
            $data["catlist"] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."staff_post where shopid = '".$shopid."' and mid='".$mid."' ");

            $where = " where a.sid = {$shopinfo['sid']} and a.mid = {$mid}"; //var_dump($shopinfo);exit;

            if ($keywords) {
                    $where .= " and (a.name='".$keywords."' or c.sn='".$keywords."')";
                    //$newlink .= '/dno/'.$dno;
            }
            if ($stafftype) {
                    $where .= " and applypost='".$stafftype."'";
                    //$newlink .= '/dno/'.$dno;
            }

            
            
            $stafflist = $this->mysql->getarr("select a.*,c.*,a.id as aid from ".Mysite::$app->config['tablepre']."staff a, ".Mysite::$app->config['tablepre']."staff_qrcode_set c ".$where." and c.staffid =a.id order by a.id desc");

            $data['list']   = $stafflist;
            Mysite::$app->setdata($data);
    }
    
    //改变员工是否有操作排号功能
    function changerownumber(){
            $this->checkshoplogin();
            $shopid = ICookie::get('adminshopid');
            if(empty($shopid)) $this->message('emptycookshop');
            $uid 		= intval(IReq::get('id'));
            $is_support = intval(IReq::get('is_support'));
            if(empty($uid))  $this->message('goods_empty');//(array('error'=>true,'msg'=>''));

            $this->mysql->update(Mysite::$app->config['tablepre'].'staff',"is_rownumber = '".$is_support."'","id ='".$uid."'");

            $this->success('success');
    }

 

	
}