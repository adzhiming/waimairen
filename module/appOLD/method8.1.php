<?php
/*
*  app 用户端和 商家段合用
*   版本v.2.1
*/
class method   extends baseclass
{
	/*
	  保存APP通知百度信息
	  参数
	  uid  用户UID
	  pwd 用户密码
	  channelid  百度地图ID
	  userid  百度地图生成的userid
	  公共代码  保存百度 账号绑定信息
	*/
	function testlist(){
		$ztylist =   $this->mysql->getarr("select* from ".Mysite::$app->config['tablepre']."specialpage where is_show=1  order by orderid  asc");
		$data['ztylist'] = $ztylist;
		print_r($ztylist);
		exit;
	}
	function appbaidu(){
		$backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		if($backinfo['group'] != 3){
		   $this->message('noshangjia');
		}
		$channelid = trim(IFilter::act(IReq::get('channelid')));
		$userid = trim(IFilter::act(IReq::get('userid')));
		if(empty($userid)) $this->message('获取失败');
		// if(empty($channelid)) $this->message('changlid获取失败');
		$checkmid =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."applogin where uid='".$backinfo['uid']."' ");
  		if(empty($checkmid)){
  		      $Mdata['channelid'] = $channelid;
  		       $Mdata['userid'] = $userid;
	          $Mdata['uid']=$backinfo['uid'];
	          $Mdata['addtime'] = time();
	      //    $this->mysql->delete(Mysite::$app->config['tablepre']."applogin"," channelid='".$channelid."' and  userid = '".$userid."'"); //删除设备历史记录
            $this->mysql->insert(Mysite::$app->config['tablepre'].'applogin',$Mdata);  //插入新数据
  		}else{
			if($checkmid['userid'] != $userid){
  			 	//     $this->mysql->delete(Mysite::$app->config['tablepre']."applogin"," uid='".$backinfo['uid']."'  "); //删除数据库用户
	           //  $this->mysql->delete(Mysite::$app->config['tablepre']."applogin"," channelid='".$channelid."' and userid = '".$userid."' "); //删除历史相同设备ID
	           $Mdata['channelid'] = $channelid;
  		       $Mdata['userid'] = $userid;
	           $Mdata['uid']=$backinfo['uid'];
	           $Mdata['addtime'] = time();
			   $this->mysql->update(Mysite::$app->config['tablepre'].'applogin',$Mdata,"uid='".$backinfo['uid']."'");  
  			}
  		}

		$this->success('操作成功');


	}
	function getgoodsattr(){
		$shoptype = intval(IFilter::act(IReq::get('shoptype')));
		//shoptype == 1 时获取 所有 超市 规格
		$gglist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodsgg where   shoptype=".$shoptype." and parent_id = 0  order by id desc limit 0,1000  ");
				 
				$product_attr = array();
				if(!empty($gglist)){//获取所有规格不为空
				   foreach($gglist as $key=>$value){
					       
							    $value['det'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodsgg where   parent_id = ".$value['id']."  order by id desc limit 0,1000  ");
						      
								$product_attr[] = $value; 
				   }
				}
		$this->success($product_attr);		 
				exit;
	}
  
	/*
	*  检测商家是否登陆
	*/
	function checkapp(){
		$uid = trim(IFilter::act(IReq::get('uid')));
		$pwd = trim(IFilter::act(IReq::get('pwd')));
		$member= $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where uid='".$uid."' ");
		$backarr = array('uid'=>0);
		if(!empty($member)){
			if($member['password'] == md5($pwd)){
				$backarr = $member;
			}

		}
		return $backarr;
	}
	/*
	* 商家获取促销规则
	2015-12-26新增
	*/
	function shopcxlist(){
		 
		$backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$shopinfo= $this->mysql->select_one("select is_open as shopopentype,starttime as opentime,shopname,id,address as shopaddress,phone as shopphone from ".Mysite::$app->config['tablepre']."shop where uid='".$backinfo['uid']."' ");
		  
		 
		if(empty($shopinfo)) $this->message('获取店铺资料失败'); 
		$controltype = intval(IFilter::act(IReq::get('controltype')));
		$controltype = in_array($controltype,array(1,2,3,4))?$controltype:1;
		 
		$page = intval(IFilter::act(IReq::get('page')));
		$pagesize = intval(IFilter::act(IReq::get('pagesize'))); 
		$pagesize = $pagesize > 0? $pagesize:10; 
		$this->pageCls->setpage(intval(IReq::get('page')),$pagesize);  
		$tempcxlist =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."rule where shopid='".$shopinfo['id']."'  and controltype='".$controltype."' order by id desc  limit   ".$this->pageCls->startnum().", ".$this->pageCls->getsize()."  ");
        $cxlist = array();
        foreach($tempcxlist as $key=>$value){
			$tempc = array();
			$tempc['id'] = $value['id'];
			$tempc['name'] = $value['name'];
			$tempc['status'] = $value['status'];
			$content_c = '';
			$tempc['starttime'] = date('Y-m-d H:i',$value['starttime']);
			$tempc['endtime'] = date('Y-m-d H:i',$value['endtime']);
			
			if($value['limittype'] == 1){
				$content_c = '';
			}elseif($value['limittype'] == 2){
				$content_c = '每周星期【'.$value['limittime'].'】';
			}elseif($value['limittype'] == 3){
				$content_c = '每天时间【'.$value['limittime'].'】';
			}
			if($value['controltype'] == 1){
				$content_c .= '订单总金额不少于￥'.$value['limitcontent'].'元，赠送'.$value['presenttitle'];
			}elseif($value['controltype'] == 2){
					$content_c .= '订单总金额不少于￥'.$value['limitcontent'].'元，减'.$value['controlcontent'].'元';
			}elseif($value['controltype'] == 3){
				$tempdd = $value['controlcontent']*0.1;
					$content_c .= '订单总金额不少于￥'.$value['limitcontent'].'元，'.$tempdd.'折';
			}elseif($value['controltype'] == 4){
					$content_c .= '订单总金额不少于￥'.$value['limitcontent'].'元，免配送费';
			}
			$tempc['content_c'] = $content_c;
			$cxlist[] = $tempc;
			
		} 
		$this->success($cxlist);
	}
	function getshopcx(){ 
	 
		$backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$shopinfo= $this->mysql->select_one("select is_open as shopopentype,starttime as opentime,shopname,id,address as shopaddress,phone as shopphone,shoptype from ".Mysite::$app->config['tablepre']."shop where uid='".$backinfo['uid']."' ");
		if(empty($shopinfo)) $this->message('获取店铺资料失败');  
		 
		 
		$id = intval(IFilter::act(IReq::get('id')));
		$temparr  =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."rule where shopid='".$shopinfo['id']."'  and id='".$id."' order by id desc  limit 0,1 ");
        if(!empty($temparr)){
			$data['cxinfo'] =$temparr;
			$data['cxinfo']['starttime'] = date('Y-m-d',$data['cxinfo']['starttime']);
			$data['cxinfo']['endtime'] = date('Y-m-d',$data['cxinfo']['endtime']);
			if($data['cxinfo']['controltype'] ==3){
				$data['cxinfo']['controlcontent'] = $data['cxinfo']['controlcontent']*0.1;
			}
			
		}else{
			$data['cxinfo']  = null;
		}
    	    
		$data['cxsignlist'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodssign where type='cx' order by id desc limit 0, 100");
	    $this->success($data);  
	}
	function savecx(){
		 
		$backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$shopinfo= $this->mysql->select_one("select is_open as shopopentype,starttime as opentime,shopname,id,address as shopaddress,phone as shopphone,shoptype from ".Mysite::$app->config['tablepre']."shop where uid='".$backinfo['uid']."' ");
		if(empty($shopinfo)) $this->message('获取店铺资料失败');   
		 
		 $id = intval(IFilter::act(IReq::get('id')));
		 $data['name'] = trim(IFilter::act(IReq::get('name')));
		 if(empty($data['name'])) $this->message('促销活动标题不能为空'); 
		 if($id > 0){
			 $temparr  =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."rule where shopid='".$shopinfo['id']."'  and id='".$id."' order by id desc  limit 0,1 ");
			 if(empty($temparr)){
				 $this->message('促销规则不存在');
			 }
      
		 }
		 $limittype = intval(IFilter::act(IReq::get('limittype')));
		 if($limittype == 2){
			 $data['limittype'] =2;
			 $data['limittime'] = trim(IFilter::act(IReq::get('limittype')));//格式 未1到7中任意组合   使用逗号分隔 
		 }elseif($limittype == 3){
			  $data['limittype'] =3;
			  $data['limittime'] = trim(IFilter::act(IReq::get('limittype')));//使用 08:00-09:00 多个时间段,分隔
		 }else{
			 $data['limittype'] =1;
			 $data['limittime'] = '';
		 }
		 $data['limitcontent'] = intval(IFilter::act(IReq::get('limitcontent')));
		 $controltype = intval(IFilter::act(IReq::get('controltype')));
		 if(!in_array($controltype,array('1','2','3','4'))) $this->message('未设置的促销类型');
	     $data['controltype'] = $controltype;
		 $data['presenttitle'] = $controltype == 1? trim(IFilter::act(IReq::get('presenttitle'))):'';
		 $controlcontent = IFilter::act(IReq::get('controlcontent'));
		 $data['controlcontent'] = $controltype == 3? intval($controlcontent*10):intval($controlcontent);  
		 if($controltype ==2 || $controltype ==3){
			 if($data['controlcontent'] < 1) $this->message('减少金额或者折扣不能等于0');
		 }elseif($controltype == 1){
			  if(empty($data['presenttitle']))$this->message('赠品标题不能为空');
		 }
		 $data['shopid'] = $shopinfo['id'];
		 $data['cattype'] = $shopinfo['shoptype'];
		 $data['status'] = intval(IFilter::act(IReq::get('status')));
		 $data['signid'] = intval(IFilter::act(IReq::get('signid')));
		 $data['starttime'] = strtotime(IFilter::act(IReq::get('starttime')));
		 $data['endtime'] = strtotime(IFilter::act(IReq::get('endtime')))+86399; 
		 if($id > 0){
			  $this->mysql->update(Mysite::$app->config['tablepre'].'rule',$data,"id='".$id."'");  
		 }else{
			 $this->mysql->insert(Mysite::$app->config['tablepre'].'rule',$data);  //插入新数据
			 $id = $this->mysql->insertid();
		 } 
		 $this->success($id); 
	}
	function delcx(){
		$backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$shopinfo= $this->mysql->select_one("select is_open as shopopentype,starttime as opentime,shopname,id,address as shopaddress,phone as shopphone,shoptype from ".Mysite::$app->config['tablepre']."shop where uid='".$backinfo['uid']."' ");
		if(empty($shopinfo)) $this->message('获取店铺资料失败');   
		 
		 $id = intval(IFilter::act(IReq::get('id')));
		 if(empty($id)) $this->message('促销ID为空'); 
	     $temparr  =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."rule where shopid='".$shopinfo['id']."'  and id='".$id."' order by id desc  limit 0,1 ");
		 if(empty($temparr)) $this->message('促销规则不存在');
		 $this->mysql->delete(Mysite::$app->config['tablepre']."rule"," shopid='".$shopinfo['id']."' and id=".$id." ");
		 $this->success('操作成功'); 
	}
	
	
	
	/**
	获取商家评价 
	2015-12-26
	***/
	function managescommt(){
		$backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$shopinfo= $this->mysql->select_one("select is_open as shopopentype,shoplogo,shoptype,starttime as opentime,shopname,id,address as shopaddress,phone as shopphone,intr_info,notice_info from ".Mysite::$app->config['tablepre']."shop where uid='".$backinfo['uid']."' ");
		if(empty($shopinfo)) $this->message('获取店铺资料失败');
		 
		$page = intval(IFilter::act(IReq::get('page')));
		$pagesize = intval(IFilter::act(IReq::get('pagesize'))); 
		$pagesize = $pagesize > 0? $pagesize:10;
		$pointtype = intval(IFilter::act(IReq::get('pointtype')));
		$pointwherearr = array(
		0=>'',
		1=>' and a.point > 3',
		2=>' and a.point > 1 and a.point < 4',
		3=>' and a.point < 2', 
		);
		$tempwhere =  isset($pointwherearr[$pointtype]) ? $pointwherearr[$pointtype]:'';
		$this->pageCls->setpage(intval(IReq::get('page')),$pagesize);   
		
		$backdata = array();
		$memberlog = $this->mysql->getarr("select a.*,b.username,b.logo,c.name from ".Mysite::$app->config['tablepre']."comment as a left join  ".Mysite::$app->config['tablepre']."member as b on a.uid = b.uid left join ".Mysite::$app->config['tablepre']."goods as c on a.goodsid = c.id  where a.shopid=".$shopinfo['id']." ".$tempwhere."  order by a.id desc   limit   ".$this->pageCls->startnum().", ".$this->pageCls->getsize()."  ");
	
		//$memberlog = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."comment   where shopid ='".$shopinfo['id']."'   ".$tempwhere." order by id desc limit ".$this->pageCls->startnum().", ".$this->pageCls->getsize()." ");
		
		foreach($memberlog as $key=>$value){
			 $value['addtime'] = date('Y-m-d',$value['addtime']);
			 
			 
			 
			 if(empty($value['replycontent'])){
				 $value['repstatus'] = 0;
				  $value['replytime'] = '';
			 }else{
			    $value['repstatus'] = 1;
			    $value['replytime'] = date('Y-m-d',$value['replytime']);
			 }
			 $backdata[] = $value;
		}
		$this->success($backdata); 
	}
	/*
	商家提交评价回复
	2015-12-26
	*/
	function shopreplyping(){
		$backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$shopinfo= $this->mysql->select_one("select is_open as shopopentype,shoplogo,shoptype,starttime as opentime,shopname,id,address as shopaddress,phone as shopphone,intr_info,notice_info from ".Mysite::$app->config['tablepre']."shop where uid='".$backinfo['uid']."' ");
		if(empty($shopinfo)) $this->message('获取店铺资料失败');
		
		$commentid = intval(IFilter::act(IReq::get('commintid')));
		$content = trim(IFilter::act(IReq::get('replaycontent')));
		$data['replytime'] = time();
		$checkcomment = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."comment   where id ='".$commentid."'     order by id desc limit 0,1 ");
		if(empty($checkcomment)) $this->message('评价不存在');
		if(!empty($checkcomment['replycontent'])) $this->message('该评价已回复');
		if($checkcomment['shopid'] != $shopinfo['id']) $this->message('该评价不属于该店铺');
		if(empty($content)) $this->message('评价回复不能为空');
		$data['replycontent'] = $content;
		$this->mysql->update(Mysite::$app->config['tablepre'].'comment',$data,"id='".$commentid."'");  
		$this->success('success');
	}
	
	/*
	*  获取商家订单
	*/
	function apporder(){
		$statusarr = array('0'=>'新订单','1'=>'待发货','2'=>'待完成','3'=>'完成','4'=>'关闭','5'=>'关闭');
		$gostatusarr = array('0'=>'新订单','1'=>'待消费','2'=>'待消费','3'=>'已消费','4'=>'关闭','5'=>'关闭');
		$backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		//
		$gettype = trim(IFilter::act(IReq::get('gettype')));
		$gettype = !in_array($gettype,array('wait','waitsend','is_send')) ?'wait':$gettype;
		$newwherearray =array(
			'wait'=> ' status > 0 and status < 2 and is_make = 0',
			'waitsend'=>' status = 1 and is_make = 1  and is_goshop = 0',
			'is_send'=>' status > 1 '
		);

		$todatay = strtotime(date('Y-m-d',time()));
		$endtime = strtotime(date('Y-m-d',time()).' 23:59:59');
		$orderlist =  $this->mysql->getarr("select id,addtime,posttime,paytype,paystatus,dno,is_reback,allcost,status,is_make,daycode,buyeruid,is_goshop,buyername,buyerphone,buyeraddress,postdate from ".Mysite::$app->config['tablepre']."order where shopuid = ".$backinfo['uid']." and ".$newwherearray[$gettype]."  and posttime > ".$todatay."");   /* and posttime < ".$endtime." */
		$backdatalist = array();
		foreach($orderlist as $key=>$value){
			if($value['is_goshop'] == 1){
				$value['showstatus'] = $gostatusarr[$value['status']];
			}else{
				$value['showstatus'] = $statusarr[$value['status']]; 
			}
			$value['addtime'] = date('H:i:s',$value['addtime']);
			$value['posttime'] = date('m-d',$value['posttime']);
			$value['posttime'] = $value['posttime'].' '.$value['postdate'];
			if($value['paytype'] == 1){
				$value['payresult'] = '在线支付';
				if($value['paystatus'] == 1){
					$value['payresult'] .= '已付';
					if($valuep['is_reback'] == 1){
						$value['payresult'] = '申请退款';
					}elseif($valuep['is_reback'] == 2){
						$value['payresult'] = '退款成功';
					}elseif($valuep['is_reback'] == 3){
						 $value['payresult'] = '取消退款';
					}
				}else{
					$value['payresult'] .= '未付';
				}
			}else{
				$value['payresult'] = '货到支付';
			}
			$checkuid = intval($value['buyeruid']);
			if($checkuid > 0){
				$value['orderNum'] = $this->mysql->counts("select  * from ".Mysite::$app->config['tablepre']."order where buyeruid = ".$value['buyeruid']."  and status = 3 ");
		
			}else{
				$value['orderNum'] =  0;
			}
			//统计下单次数
				if($value['status'] ==  1){
				if($value['is_make'] == 0){
					$value['showstatus'] = $value['is_goshop']  == 1?'新订台订单':'新订单';
				}elseif($value['status'] !=1){
					$value['showstatus'] = $value['is_goshop']  == 1?'商家取消订单':'取消制作'; 
				}
			}
			$backdatalist[] = $value;
		}
		$this->success($backdatalist);
	}
	/*
	* 商家获取信息
	2015-12-25修改
	*/
	function appshop(){
		$backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		//获取店铺
		$shopinfo= $this->mysql->select_one("select is_open as shopopentype,shoplogo,shoptype,starttime as opentime,shopname,id,address as shopaddress,phone as shopphone,intr_info,notice_info from ".Mysite::$app->config['tablepre']."shop where uid='".$backinfo['uid']."' ");
		if(empty($shopinfo)) $this->message('获取店铺资料失败');
		$shopinfo['opentime'] = str_replace("|", ",", $shopinfo['opentime']);
		$shopinfo['intr_info'] =strip_tags($shopinfo['intr_info']);
		$shopinfo['notice_info']= strip_tags($shopinfo['notice_info']);
		$shopinfo['shoplogo'] = empty($shopinfo['shoplogo'])? Mysite::$app->config['siteurl'].Mysite::$app->config['shoplogo']:Mysite::$app->config['siteurl'].$shopinfo['shoplogo'];
		if($shopinfo['shoptype'] == 0){
			$shopdet  = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast       where  shopid = ".$shopinfo['id']."   "); 
		  
		}else{
			$shopdet  = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket       where  shopid = ".$shopinfo['id']."   "); 
		 
		} 
	   $shopinfo['limitcost']  = empty($shopdet)?0:$shopdet['limitcost']; 
		$this->success($shopinfo);
	}
	/*
	* 商家获取商品分类
	2015-12-26修改
	*/
	function goodstype(){
		$backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$shopinfo= $this->mysql->select_one("select is_open as shopopentype,starttime as opentime,shopname,id,address as shopaddress,phone as shopphone,shoptype from ".Mysite::$app->config['tablepre']."shop where uid='".$backinfo['uid']."' ");
		if(empty($shopinfo)) $this->message('获取店铺资料失败');
		if($shopinfo['shoptype'] != 0)$this->message('超市店铺不通过此链接返回'); 
		
		$shoptype =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodstype where shopid='".$shopinfo['id']."' order by orderid desc");
		$tempc = array();
		foreach($shoptype as $key=>$value){
			$value['shuliang'] = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."goods where shopid='".$shopinfo['id']."' and typeid=".$value['id']." order by id desc");
			$tempc[] = $value;
		} 
		$this->success($tempc);
	}
	/*
	* 商家删除商品分类
	2015-12-26修改
	*/
	function delgoostype(){
		$backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$shopinfo= $this->mysql->select_one("select is_open as shopopentype,starttime as opentime,shopname,id,address as shopaddress,phone as shopphone,shoptype from ".Mysite::$app->config['tablepre']."shop where uid='".$backinfo['uid']."' ");
		if(empty($shopinfo)) $this->message('获取店铺资料失败');
		$id = intval(IFilter::act(IReq::get('id')));
		if(empty($id)) $this->message('删除ID获取失败');
		if($shopinfo['shoptype'] != 0)$this->message('超市店铺分类删除链接错误'); 
		//增加个check  判断是否
		$checkdata =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goods where shopid='".$shopinfo['id']."' and typeid=".$id." order by id desc");
		if(!empty($checkdata)) $this->message('该分类下有商品，删除失败');
		$this->mysql->delete(Mysite::$app->config['tablepre']."goodstype"," shopid='".$shopinfo['id']."' and id=".$id." ");
		$shoptype =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodstype where shopid='".$shopinfo['id']."' order by orderid desc");
		$this->success($shoptype); 
	}
	/*
	* 商家添加商品分类
	2015-12-26修改
	*/
	function addgoostype(){
		$backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$shopinfo= $this->mysql->select_one("select is_open as shopopentype,starttime as opentime,shopname,id,address as shopaddress,phone as shopphone,shoptype from ".Mysite::$app->config['tablepre']."shop where uid='".$backinfo['uid']."' ");
		if(empty($shopinfo)) $this->message('获取店铺资料失败');
		$id = intval(IFilter::act(IReq::get('id')));
		$name = trim(IFilter::act(IReq::get('name')));
		$orderid = intval(IFilter::act(IReq::get('orderid')));
		//	id	shopid 店铺ID	name 分类名称	orderid	cattype 1外卖 2订台
		if($shopinfo['shoptype'] != 0)$this->message('超市店铺分类添加不通过此处操作'); 
		if(empty($name)) $this->message('分类名称不能为空');
		$newdata['shopid'] = $shopinfo['id'];
		$newdata['name'] = $name;
		$newdata['orderid'] = $orderid;
		$newdata['cattype'] = $shopinfo['shoptype'];
/* 		$newdata['bagcost'] = intval(IFilter::act(IReq::get('bagcost')));
		$newdata['uid'] = $backinfo['uid']; */
		if(empty($id)){
			//新增
			$this->mysql->insert(Mysite::$app->config['tablepre']."goodstype",$newdata);
		}else{
			//编辑
			$this->mysql->update(Mysite::$app->config['tablepre'].'goodstype',$newdata,"id='".$id."' and shopid = '".$shopinfo['id']."'");
		}
		$shoptype =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodstype where shopid='".$shopinfo['id']."' order by orderid desc");
		$this->success($shoptype);
	}
	function typebyid(){
		$backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
	    $typeid = intval(IFilter::act(IReq::get('typeid'))); 
		$shopinfo= $this->mysql->select_one("select is_open as shopopentype,starttime as opentime,shopname,id,address as shopaddress,phone as shopphone,shoptype from ".Mysite::$app->config['tablepre']."shop where uid='".$backinfo['uid']."' ");
		
		if($shopinfo['shoptype'] == 1){
			$typeone = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."marketcate where id='".$typeid."'   order by id desc");
			if(empty($typeone)){
				$this->message('商品分类获取失败');
			}
			$data['typelist'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."marketcate where parent_id='".$typeone['parent_id']."' and shopid  ='".$typeone['shopid']."' order by orderid desc");
			$this->success($data); 
		}else{
			
			$typeone = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goodstype where id='".$typeid."'   order by id desc");
			if(empty($typeone)){
				$this->message('商品分类获取失败');
			}
			$data['typelist'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodstype where shopid='".$typeone['shopid']."'   order by orderid desc");
			$this->success($data); 
		}
		
	}
	/*
	* 商家获取商品
	2015-12-26修改
	*/
	function goodslist(){
		$backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$shopinfo= $this->mysql->select_one("select is_open as shopopentype,starttime as opentime,shopname,id,address as shopaddress,phone as shopphone from ".Mysite::$app->config['tablepre']."shop where uid='".$backinfo['uid']."' ");
		if(empty($shopinfo)) $this->message('获取店铺资料失败');
		$typeid = intval(IFilter::act(IReq::get('typeid'))); 
		$page = intval(IFilter::act(IReq::get('page')));
		$pagesize = intval(IFilter::act(IReq::get('pagesize'))); 
		$pagesize = $pagesize > 0? $pagesize:10; 
		$this->pageCls->setpage(intval(IReq::get('page')),$pagesize);  
		$goodslist =  $this->mysql->getarr("select id,typeid,name,count,cost,bagcost,img,have_det from ".Mysite::$app->config['tablepre']."goods where shopid='".$shopinfo['id']."' and typeid = ".$typeid." order by id desc  limit   ".$this->pageCls->startnum().", ".$this->pageCls->getsize()."  ");
		$this->success($goodslist);
	}
	/*
	*  商家删除商品
	2015-12-26 修改
	*/
	function delgoos(){
		$backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$shopinfo= $this->mysql->select_one("select is_open as shopopentype,starttime as opentime,shopname,id,address as shopaddress,phone as shopphone from ".Mysite::$app->config['tablepre']."shop where uid='".$backinfo['uid']."' ");
		if(empty($shopinfo)) $this->message('获取店铺资料失败');
  		$id = intval(IFilter::act(IReq::get('id')));
		if(empty($id)) $this->message('删除ID获取失败');
		$this->mysql->delete(Mysite::$app->config['tablepre']."goods"," shopid='".$shopinfo['id']."' and id=".$id." ");
		$typeid = intval(IFilter::act(IReq::get('typeid')));
		$this->mysql->delete(Mysite::$app->config['tablepre']."product"," shopid='".$shopinfo['id']."' and goodsid=".$id." ");
		//$goodslist =  $this->mysql->getarr("select id,typeid,name,count,cost,bagcost,img from ".Mysite::$app->config['tablepre']."goods where shopid='".$shopinfo['id']."' and typeid = ".$typeid." order by id desc");
		$this->success('ok');
	}
	function onegoods(){
		$backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$shopinfo= $this->mysql->select_one("select is_open as shopopentype,starttime as opentime,shopname,id,address as shopaddress,phone as shopphone from ".Mysite::$app->config['tablepre']."shop where uid='".$backinfo['uid']."' ");
	    $id = intval(IFilter::act(IReq::get('id')));
		if(empty($id)) $this->message('商品获取失败');
		$goodsinfo =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goods where shopid='".$shopinfo['id']."' and id = ".$id." order by id desc  limit   0,1 ");
		if(empty($goodsinfo)) $this->message("商品获取失败");
		$temparray= empty($goodsinfo['product_attr'])?array():unserialize($goodsinfo['product_attr']);
		//$temparray = array_keys($temparray);
		sort($temparray); 
		$doarray = array();
		if(count($temparray) > 0){
			foreach($temparray as $key=>$value){
			   $doarray[] = $value;
			}
		}
		$goodsinfo['product_attr'] = $doarray;
		 
		$productlist = array();
		if($goodsinfo['have_det'] == 1){
			$productlist= $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."product where shopid='".$shopinfo['id']."' and goodsid = ".$id." order by id desc  limit  0,100  ");
		}
		 
		
		
		
		$tempgglist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodsgg where shoptype = '".$shopinfo['shoptype']."'  order by orderid asc limit 0,1000  ");
        $gglist=array();
        foreach($tempgglist as $key=>$value){
			if($value['parent_id'] == 0){
				 
				
				$value['det'] = array();
				foreach($tempgglist as $c=>$d){
					if($d['parent_id'] == $value['id']){
						$value['det'][] = $d;
					}
				}
				 
				$gglist[] = $value;
			}
			
		}		
	    $backdata['goodsinfo'] =$goodsinfo;
		$backdata['productlist'] =$productlist;
		$backdata['gglist'] = $gglist;
		$this->success($backdata);
		
		
	}
	/*
	*  商家添加商品
	2015-12-25修改
	*/
	function addgoos(){
		$backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$shopinfo= $this->mysql->select_one("select is_open as shopopentype,starttime as opentime,shopname,id,address as shopaddress,phone as shopphone,shoptype from ".Mysite::$app->config['tablepre']."shop where uid='".$backinfo['uid']."' ");
		if(empty($shopinfo)) $this->message('获取店铺资料失败');
		$id = intval(IFilter::act(IReq::get('id')));
		$name = trim(IFilter::act(IReq::get('name')));
		$count = intval(IFilter::act(IReq::get('count')));
		$cost = trim(IFilter::act(IReq::get('cost')));
		$cost = intval($cost*100);
		$cost = $cost/100;
		$typeid = intval(IFilter::act(IReq::get('typeid'))); 
		$bagcost = trim(IFilter::act(IReq::get('bagcost')));
		$img = trim(IFilter::act(IReq::get('img')));
		//	id	shopid 店铺ID	name 分类名称	orderid	cattype 1外卖 2订台
		if(empty($name)) $this->message('商品名称不能为空'); 
		
	    if($id > 0){
			$goodsinfo =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goods where shopid='".$shopinfo['id']."' and id = ".$id." order by id desc  limit   0,1 ");
		    if(empty($goodsinfo)) $this->message('商品不存在');  
		} 
		if($typeid < 1) $this->message('商品分类不存在');
		if($shopinfo['shoptype'] == 0){
			$catinfo =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goodstype where shopid='".$shopinfo['id']."' and id = ".$typeid." order by id desc  limit   0,1 ");
		    if(empty($catinfo)) $this->message('分类不存在');
		}else{
			$catinfo =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."marketcate where shopid='".$shopinfo['id']."' and id = ".$typeid." order by id desc  limit   0,1 ");
		    if(empty($catinfo)) $this->message('分类不存在');
		}
		if(empty($name)) $this->message('商品名称不能为空');   
		
		
		
		
		
		$data['shopid'] = $shopinfo['id'];
		$data['name'] = $name;
		$data['count'] = $count;
		$data['cost'] = $cost;
		$data['typeid'] = $typeid; 
		$data['bagcost'] = $bagcost;
		if(!empty($img)){
			$data['img'] = $img; 
		}
		
		
		
		
	   $Productids = array();
	   $have_det = intval(IFilter::act(IReq::get('have_det')));
	   $data['have_det'] = 0;
	   $data['product_attr'] = '';
	   $idtonamearray = array();
	    if($have_det == 1){
			$fggids = trim(IFilter::act(IReq::get('fggids')));
			if(!empty($fggids)){ 
			 
				$gglist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodsgg where  FIND_IN_SET( `id` , '".$fggids."' ) and parent_id = 0  order by id desc limit 0,1000  ");
				 
				$product_attr = array();
				if(!empty($gglist)){//获取所有规格不为空
				   foreach($gglist as $key=>$value){
					      $checkid = IFilter::act(IReq::get('choicegg'.$value['id']));
						  if(!empty($checkid)){
							    $checkid = is_array($checkid)?join(',',$checkid):trim($checkid);
								
								 
							    $value['det'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodsgg where  FIND_IN_SET( `id` , '".$checkid."' ) and parent_id = ".$value['id']."  order by id desc limit 0,1000  ");
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
					 if($id > 0){
						$checkinfo = join(',',$goodsdetids);
						if(!empty($checkinfo)){
					    $this->mysql->delete(Mysite::$app->config['tablepre'].'product'," `id` not in(".$goodsdetids.")  and `goodsid`=".$id." ");  
						}else{
							 $this->mysql->delete(Mysite::$app->config['tablepre'].'product',"   `goodsid`=".$id." ");  
						
						}
					 }
					 $productlist = array();
					 $gg_scost=IFilter::act(IReq::get('gg_scost'));
					 $gg_sstock = IFilter::act(IReq::get('gg_sstock'));
					 $gg_sids =  IFilter::act(IReq::get('gg_sids'));
					 $goodsdetids =  IFilter::act(IReq::get('goodsdetids'));
					 if(is_array($gg_scost)){ 
						$data['count'] = 0;
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
								 $prodata['shopid'] = $shopinfo['id'];
								 $prodata['goodsid'] = $id;
								 $prodata['goodsname'] = $name;
								 $prodata['attrname'] = join(',',$attr_arr);//需要根据参数
								 $prodata['attrids']   = $gg_sids[$key];//需要根据参数
								 $prodata['stock'] =  isset($gg_sstock[$key])?$gg_sstock[$key]:0;//需要参数量
								 $prodata['bagcost'] = $bagcost;
								 $prodata['cost'] = $value;//
								  if($id > 0){
										$prodata['id'] = isset($goodsdetids[$key])?$goodsdetids[$key]:0;
								  }else{
									  $prodata['id'] = 0;
								  }
								 $productlist[] = $prodata;
								 $data['cost'] = $value;
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
							$ccid = $this->mysql->insertid(); 
								$Productids[] = $ccid;
							 
						} 
					} 
							
				}  
			}
		}  
		 
		if(empty($id)){
			//新增
			//sellcount 销售数量	shopid 店铺ID	uid  daycount  shoptype
			$data['sellcount'] = 0;
			$data['shopid'] = $shopinfo['id'];
			$data['uid'] =$backinfo['uid'];
			$data['shoptype'] =  $shopinfo['shoptype'];
			$data['daycount'] = $data['count'];
			$this->mysql->insert(Mysite::$app->config['tablepre']."goods",$data);
			$goodsid = $this->mysql->insertid();
			if(count($Productids)> 0){
				 
				$this->mysql->update(Mysite::$app->config['tablepre'].'product',array('goodsid'=>$goodsid),"id in(".join(',',$Productids).")  ");
					
			}
			
			
			
		}else{
			//编辑
			$this->mysql->update(Mysite::$app->config['tablepre'].'goods',$data,"id='".$id."' and shopid='".$shopinfo['id']."' ");
			$goodsid = $id;
		}
		$this->success($goodsid);
	}
	function editshop(){
		$backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$typename = trim(IFilter::act(IReq::get('typename')));
		$typevalue = trim(IFilter::act(IReq::get('typevalue')));
		//   opentime
		// if(!in_array($typename,array('opentype','opentime','shopphone'))) $this->message('未定义的操作');
		if($typename == 'shopopentype'){//,   ,            shopphone     notice_info   
			$data['is_open'] = intval($typevalue);
		}elseif($typename == 'opentime'){
			$bakcdata = str_replace(",", "|", $typevalue);
			$data['starttime'] = $bakcdata;
		}elseif($typename=='shopname'){ 
			$data['shopname'] = trim($typevalue);
		}elseif($typename=='shopaddress'){ 
			$data['address'] = trim($typevalue);
		}elseif($typename == 'shopphone'){
			if(!(IValidate::phone($typevalue))) $this->message('正录入正确的订餐电话');
			$data['phone'] = $typevalue;
		}elseif($typename == 'intr_info'){
			$data['intr_info'] = trim($typevalue);
		}elseif($typename == 'notice_info'){
			$data['notice_info'] = trim($typevalue);
		}elseif($typename == 'limitcost'){
			$data['limitcost'] = trim($typevalue);
			$shopinfo= $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."shop where uid='".$backinfo['uid']."' ");
		    if(empty($shopinfo)){
				$this->message('店铺不存在');
			}else{
				if($shopinfo['shoptype'] == 0){
					$shopdet  = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast       where  shopid = ".$shopinfo['id']."   "); 
				  
				}else{
					$shopdet  = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket       where  shopid = ".$shopinfo['id']."   "); 
				 
				} 
				if(!empty($shopdet)){
					if($shopinfo['shoptype'] == 0){
						$this->mysql->update(Mysite::$app->config['tablepre'].'shopfast',$data,"shopid='".$shopinfo['id']."'");
					}else{
						$this->mysql->update(Mysite::$app->config['tablepre'].'shopmarket',$data,"shopid='".$shopinfo['id']."'"); 
					}
				}
			}
			$this->success('ok');
		}else{
			$this->message('未定义的操作');
		}
		$this->mysql->update(Mysite::$app->config['tablepre'].'shop',$data,"uid='".$backinfo['uid']."'"); 

		$this->success('ok');
	}


	/*
	* 商家获取单个订单
	*/
	function appone(){
		$statusarr = array('0'=>'新订单','1'=>'待发货','2'=>'待完成','3'=>'完成','4'=>'关闭','5'=>'关闭');
		$gostatusarr = array('0'=>'新订单','1'=>'待消费','2'=>'待消费','3'=>'已消费','4'=>'关闭','5'=>'关闭');
		$paytypelist = array('0'=>'货到支付','1'=>'账号余额支付');
		$shoptypearr = array(
			'0'=>'外卖',
			'1'=>'超市',
			'2'=>'其他',
			'100'=>'跑腿',
	     );
		$paylist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."paylist   order by id asc limit 0,50");
		if(is_array($paylist)){
			foreach($paylist as $key=>$value){
				$paytypelist[$value['loginname']] = $value['logindesc'];
			}
		}
		$backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$orderid = trim(IFilter::act(IReq::get('orderid')));
		if(empty($orderid)){
			$this->message('订单不存在或者不属于您');
		}
		$order= $this->mysql->select_one("select id,dno,addtime,daycode,shopname,shopuid,paytype,paystatus,daycode,ipaddress,allcost,buyername,buyeraddress,buyerphone,posttime,status,is_make,pstype,shopps,shoptype,cxcost,yhjcost,scoredown,bagcost,content,othertext,is_goshop from ".Mysite::$app->config['tablepre']."order where id='".$orderid."' "); //cxids 促销规则ID集	cxcost  yhjcost  bagcost
		if(empty($order)){
			$this->message('订单不存在');
		}
		if($order['shopuid'] != $backinfo['uid']) $this->message('您不是订单所有者');
		$order['showstatus'] = $order['is_goshop'] == 1?$gostatusarr[$order['status']]: $statusarr[$order['status']];
		if($order['status'] ==  1){
			if($order['is_make'] == 0){
				$order['showstatus'] = '新订单';
			}elseif($order['status'] !=1){
				$order['showstatus'] = '取消制作';
			}
		}
		$order['othercontent'] = '';
		if(!empty($order['othertext'])){
			$dosendata = unserialize($order['othertext']);
			foreach($dosendata as $key=>$value){
				$order['othercontent']  = empty($order['othercontent'])?$key.$value:$key.$value.','.$order['othercontent'];
			}
		}
		$order['posttimename'] = '配送时间:';
		if($order['is_goshop'] == 1){
		   $order['ordershow'] = '预订/订座';
		   $order['posttimename'] = '消费时间:';
		   $paytypelist[0] = '到店支付';
		}else if($order['shoptype'] == 100){
		   $order['ordershow'] = '跑腿'; 
		}elseif($order['shoptype'] == 1){
			 $order['ordershow'] = '超市'; 
		}else{
			$order['ordershow'] = '外卖';
		}
		
 	    $order['shoptype'] = isset($shoptypearr[$order['shoptype']])?$shoptypearr[$order['shoptype']]:'其他';
		 
		
		//cxcost,yhjcost,scoredown,
		$scoretocost =Mysite::$app->config['scoretocost'];
		$scorcost = $order['scoredown'] > 0? intval($order['scoredown']/$scoretocost):0;
		$order['allcx'] = $order['cxcost']+$order['yhjcost']+$scorcost;
	
		$order['paytype'] = $paytypelist[$order['paytype']];
		$order['paystatustype'] =  empty($order['paystatus'])?'未支付':'已支付';
		$order['addtime'] = date('H:i:s',$order['addtime']);
		$order['posttime'] = date('Y-m-d H:i:s',$order['posttime']);
		$templist =   $this->mysql->getarr("select id,order_id,goodsname,goodscost,goodscount from ".Mysite::$app->config['tablepre']."orderdet where order_id='".$orderid."' ");
		$newdatalist = array();
		$shuliang = 0;
		foreach($templist as $key=>$value){
			$value['goodscost'] = $value['goodscost'].'(份)';
			$newdatalist[] = $value; 
			$shuliang += $value['goodscount'];
		}
		$newgoods = array('id'=>'0','order_id'=>$orderid,'goodsname'=>'总价','goodscount'=>$shuliang,'goodscost'=>$order['allcost']);
		$newdatalist[] = $newgoods;

		$order['det'] = $newdatalist;
		if(!empty($order['othertext'])){
			$tempcontent = unserialize($order['othertext']);
			foreach($tempcontent as $key=>$value){
				$order['content'] = $order['content'].$key.':'.$value.',';
			}
		}
		
		//content

		$this->success($order);
	}
	/*
     商家订单处理
	*/
	function ordercontrol(){
		$backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$orderid = trim(IFilter::act(IReq::get('orderid')));
		$dostring = trim(IFilter::act(IReq::get('dostring')));
		if(empty($orderid)) $this->message('订单获取失败');
		if(!in_array($dostring,array('domake','unmake','send','over'))) $this->message('未定义的操作');
		$order= $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where id='".$orderid."' ");
		if(empty($order)) $this->message('订单获取失败');
		if($order['shopuid'] != $backinfo['uid']) $this->message('您不是订单所有者');

		if($dostring == 'domake'){
			//制作订单
			if($order['status'] != 1) $this->message('订单状态已发货或者其他状态不可操作'); 
			if($order['is_make'] > 0) $this->message('订单制作状态已处理');
			$udata['is_make'] = 1;
			$this->mysql->update(Mysite::$app->config['tablepre'].'order',$udata,"id='".$orderid."'");
				$ordCls = new orderclass();
	               $ordCls->noticemake($orderid);
		}elseif($dostring == 'unmake'){ 
			if($order['status'] != 1) $this->message('订单状态已发货或者其他状态不可操作');
			if(!empty($order['is_make'])) $this->message('订单制作状态已处理');
			
			$udata['is_make'] = 2;
			$this->mysql->update(Mysite::$app->config['tablepre'].'order',$udata,"id='".$orderid."'");
		   $this->mysql->delete(Mysite::$app->config['tablepre'].'orderps',"orderid = '$orderid'");  //写配送订单  
			  $ordCls = new orderclass();
	               $ordCls->noticeunmake($orderid);
		}elseif($dostring == 'send'){
			//对订单发货
			if($order['is_reback'] > 0 ) $this->message('order_ispaycantdo');
			if($order['status'] != 1) $this->message('订单状态已发货或者其他状态不可操作');
			$udata['status'] = 2;
			$this->mysql->update(Mysite::$app->config['tablepre'].'order',$udata,"id='".$orderid."'"); 
			$ordCls = new orderclass();
	               $ordCls->noticesend($orderid);
		}elseif($dostring == 'over'){
			if($order['is_reback'] > 0 ) $this->message('order_ispaycantdo');
			if($order['status'] != 2) $this->message('订单状态已发货或者其他状态不可操作');
			$udata['status'] = 3;
			$this->mysql->update(Mysite::$app->config['tablepre'].'order',$udata,"id='".$orderid."'");
		}else{
			$this->message('未定义的操作');
		}
		$this->success('操作成功');
	}
	/*
	*   商家登陆
	*/
	function applogin(){
		$uname = trim(IFilter::act(IReq::get('uname')));
		$pwd = trim(IFilter::act(IReq::get('pwd')));
		$mDeviceID =  trim(IFilter::act(IReq::get('mDeviceID')));
		if(empty($uname)) $this->message('用户名为空');
		if(empty($pwd)) $this->message('密码为空');

		if(!$this->memberCls->login($uname,$pwd)){
			$this->message($this->memberCls->ero());
		}
		$uid = $this->memberCls->getuid();
		$member= $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where uid='".$uid."' ");
		//2015.6.6新增加获取店铺类型
		$showtype = trim(IFilter::act(IReq::get('showtype')));
		$member['shoptype'] = 0;//模式普通店铺
		if($showtype == 'shop'){
		    $shopinfo = $this->mysql->select_one("select shoptype from ".Mysite::$app->config['tablepre']."shop  where uid='".$uid."' ");
			if(!empty($shopinfo)){
			   $member['shoptype'] = $shopinfo['shoptype'];
			}
		}
		//获取结束
		/*userid
		/*channelid */
		$userid = trim(IFilter::act(IReq::get('userid')));
		 
			$checkmid =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."applogin where uid='".$member['uid']."' ");
			if(empty($checkmid)){
				$Mdata['channelid'] = $channelid;
				$Mdata['userid'] = $userid;
				$Mdata['uid']=$member['uid'];
				$Mdata['addtime'] = time();
			//    $this->mysql->delete(Mysite::$app->config['tablepre']."applogin"," channelid='".$channelid."' and  userid = '".$userid."'"); //删除设备历史记录
				$this->mysql->insert(Mysite::$app->config['tablepre'].'applogin',$Mdata);  //插入新数据
			}else{
				if($checkmid['userid'] != $userid){
					//     $this->mysql->delete(Mysite::$app->config['tablepre']."applogin"," uid='".$backinfo['uid']."'  "); //删除数据库用户
					//  $this->mysql->delete(Mysite::$app->config['tablepre']."applogin"," channelid='".$channelid."' and userid = '".$userid."' "); //删除历史相同设备ID
					$Mdata['channelid'] = $channelid;
					$Mdata['userid'] = $userid;
					$Mdata['uid']=$member['uid'];
					$Mdata['addtime'] = time();
					$this->mysql->update(Mysite::$app->config['tablepre'].'applogin',$Mdata,"uid='".$member['uid']."'");  
				}
			}
		 
		
		
		unset($member['password']);
		$this->success($member);
	}
	/*** 2016.3.5 新增
	商家申请提现
	***/
	function shoptxadd(){
		//$uid,$cost,$shopid
		$backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$uid = $backinfo['uid'];
		if($uid < 1){
			$this->message('nologin');
		}
		$cost = trim(IFilter::act(IReq::get('cost')));
		$shopinfo = $this->mysql->select_one(" select *  from ".Mysite::$app->config['tablepre']."shop where uid='".$uid."'  ");
		if(empty($shopinfo)){
			$this->message('未开启店铺');
		}
		$shopid = $shopinfo['id'];
		$userinfo = $backinfo; 
		$checkcost = intval($cost);
		if($checkcost < 100){
			$this->message('提现金额不能少于100元');
		}
		if($userinfo['shopcost'] < $checkcost){
			$this->message('账号金额小于提现金额');
		}
		$newdata['cost'] = $checkcost;
		$newdata['type'] = 0;
		$newdata['status'] = 1;
		$newdata['addtime'] = time();
		$newdata['shopid'] = $shopid;
        $newdata['shopuid'] =  $uid;
		$newdata['name'] = '申请提现';
	    $newdata['yue'] = $userinfo['shopcost']-$checkcost;
        $this->mysql->update(Mysite::$app->config['tablepre'].'member','`shopcost`=`shopcost`-'.$checkcost,"uid ='".$uid."' ");
		$this->mysql->insert(Mysite::$app->config['tablepre'].'shoptx',$newdata);
	    $orderid = $this->mysql->insertid(); 
		$info = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shoptx  where id = ".$orderid." ");
		$this->success($info); 
	}
	
	/***
	2016.3.5 新增
	商家资金记录***/
    function shopcostlog(){
			$backinfo = $this->checkapp();
			if(empty($backinfo['uid'])){
				$this->message('nologin');
			}
			$uid = $backinfo['uid'];
			if($uid < 1){
				$this->message('nologin');
			}
		 
		  $pageshow = new page();
	      $pageshow->setpage(IReq::get('page'),10); 
		  $where = " where shopuid=".$uid." ";
		  $type = intval(IReq::get('type'));// 0 表示提出        1表示充值   2表示取消提现  3结算转入
		  if($type == 1){
			  $where .=" and type = 0";
		  }elseif($type == 2){
			  $where .=" and type > 0";
		  } 
		  //+"&=" + starttime+"&endday=" + endtime
		  $startday = trim(IReq::get('startday'));
		  $endday = trim(IReq::get('endday'));
		  if(!empty($startday)) $where .=" and addtime > ".strtotime($startday);
		  if(!empty($endday)){
			  $info = strtotime($endday)+86399;
			  $where .=" and addtime < ".$info;
		  } 
		   
	      $txlist =   $this->mysql->getarr("select *  from ".Mysite::$app->config['tablepre']."shoptx  ".$where."  order by addtime desc   limit ".$pageshow->startnum().", ".$pageshow->getsize().""); 
	      $shuliang  = $this->mysql->counts("select *  from ".Mysite::$app->config['tablepre']."shoptx    ".$where."   order by id asc  ");
	     
		  $tempdata = array();
		  $typearray = array(0=>'提现申请',1=>'账号充值',2=>'取消提现',3=>'店铺结算转入');
		  $statusarray = array(0=>'空',1=>'处理中',2=>'处理成功',3=>'已取消');
		  if(is_array($txlist)){
			  foreach($txlist as $key=>$value){
				  //$value['name'] = isset($typearray[$value['type']])?$typearray[$value['type']]:'未定义';
				  $value['statusname'] = isset($statusarray[$value['status']])?$statusarray[$value['status']]:'未定义';
				  $value['adddate'] = date('Y-m-d H:i:s',$value['addtime']);
				  
				  $tempdata[] = $value;
			  }
		  } 
		  $this->success($tempdata); 
	 }
	 //获取提现记录
	 function shoptxlog(){
			$backinfo = $this->checkapp();
			if(empty($backinfo['uid'])){
				$this->message('nologin');
			}
			$uid = $backinfo['uid'];
			if($uid < 1){
				$this->message('nologin');
			}
		 
		  $pageshow = new page();
	      $pageshow->setpage(IReq::get('page'),10); 
		   
	      $txlist =   $this->mysql->getarr("select *  from ".Mysite::$app->config['tablepre']."shoptx  where shopuid=".$uid."  and type = 0 order by addtime desc   limit ".$pageshow->startnum().", ".$pageshow->getsize().""); 
	      $shuliang  = $this->mysql->counts("select *  from ".Mysite::$app->config['tablepre']."shoptx  where shopuid=".$uid." and type = 0   order by id asc  ");
	     
		  $tempdata = array();
		  $typearray = array(0=>'提现申请',1=>'账号充值',2=>'取消提现',3=>'店铺结算转入');
		  $statusarray = array(0=>'空',1=>'处理中',2=>'处理成功',3=>'已取消');
		  if(is_array($txlist)){
			  foreach($txlist as $key=>$value){
				  //$value['name'] = isset($typearray[$value['type']])?$typearray[$value['type']]:'未定义';
				  $value['statusname'] = isset($statusarray[$value['status']])?$statusarray[$value['status']]:'未定义';
				  $value['adddate'] = date('Y-m-d H:i:s',$value['addtime']);
				  $tempdata[] = $value;
			  }
		  } 
		  $this->success($tempdata); 
	 }
	 /***
		2016.3.5 店铺取消提现
	 ***/
	 function shopuntx(){
		 $backinfo = $this->checkapp();
			if(empty($backinfo['uid'])){
				$this->message('nologin');
			}
			$uid = $backinfo['uid'];
			if($uid < 1){
				$this->message('nologin');
			}
		 $txid = trim(IFilter::act(IReq::get('txid')));
		 
		 $txinfo =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shoptx where id='".$txid."'  ");
		 if(empty($txinfo)){
			$this->message('提现信息获取失败');
		 }
		 if($txinfo['status'] != 1){
			 $this->message('提现已受理不能取消');
		 }
		 if($txinfo['type'] != 0){
			 $this->message('不是店铺提现不能取消');
		 }
		 if($txinfo['shopuid'] != $uid){
			 $this->message('该条提现记录不属于您');
		 } 
		 $userinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where uid='".$txinfo['shopuid']."'  ");
		 if(empty($userinfo)){
			 $this->message('用户不存在');
		 }
	     $this->mysql->update(Mysite::$app->config['tablepre'].'shoptx','`status`=3',"id ='".$txid."' ");
		 $this->mysql->update(Mysite::$app->config['tablepre'].'member','`shopcost`=`shopcost`+'.$txinfo['cost'],"uid ='".$txinfo['shopuid']."' ");
		  
		 $newdata['cost'] = $txinfo['cost'];
		 $newdata['type'] = 2;
		 $newdata['status'] = 2;
		 $newdata['addtime'] = time();
		 $newdata['shopid'] = 0;
         $newdata['shopuid'] =  $uid;
		 $newdata['name'] = '取消'.date('Y-m-d',$txinfo['addtime']).$txinfo['name'];
		 $newdata['yue'] = $userinfo['shopcost']+$txinfo['cost'];
		 $this->mysql->insert(Mysite::$app->config['tablepre'].'shoptx',$newdata);
		 $orderid = $this->mysql->insertid(); 
		 $info = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shoptx  where id = ".$orderid." ");
		 $this->success($info);
	 }
	 function tjcxcost(){
		 
		 //可提现金额
		 //总收入
		 //支付佣金
	    $backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$newdata['shopcost'] = $backinfo['shopcost'];
		//shopjs
		//shopuid
		$info = $this->mysql->select_one("select sum(onlinecost) as acost,sum(unlinecost) as bcost,sum(yjcost) as yjc from ".Mysite::$app->config['tablepre']."shopjs  where shopuid = ".$backinfo['uid']."  ");
	    $newdata['zsr'] =  $info['acost']+$info['bcost'];
		$newdata['yj'] = $info['yjc']; 
		$this->success($newdata);
	 }
	
	function getshoploginfo(){
		$backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$uid = $backinfo['uid'];
		if($uid < 1){
			$this->message('nologin');
		}
		$txid = trim(IFilter::act(IReq::get('txid')));

		$txinfo =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shoptx where id='".$txid."'  ");
		if(empty($txinfo)){
			$this->message('信息获取失败');
		}
		 $statusarray = array(0=>'空',1=>'处理中',2=>'处理成功',3=>'已取消');
		if($txinfo['jsid'] > 0){
			$info = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopjs where id='".$txinfo['jsid']."'  ");
		    $info['name'] = '结算'.date('Y-m-d',$info['jstime']);
			$info['adddate'] = date('Y-m-d H:i:s',$info['addtime']);
			 $info['statusname'] = isset($statusarray[$info['status']])?$statusarray[$info['status']]:'未定义';
			$this->success($info);
		}else{
			 $txinfo['statusname'] = isset($statusarray[$txinfo['status']])?$statusarray[$txinfo['status']]:'未定义';
			
		    $txinfo['adddate'] = date('Y-m-d H:i:s',$txinfo['addtime']);
			$this->success($txinfo); 
		} 
	 }
	
	
	/*
	* 普通用户登陆
	*/
	function appMemlogin(){
		$uname = trim(IFilter::act(IReq::get('uname')));
		$pwd = trim(IFilter::act(IReq::get('pwd')));
		$mDeviceID =  trim(IFilter::act(IReq::get('mDeviceID')));
		if(empty($uname)) $this->message('用户名为空');
		if(empty($pwd)) $this->message('密码为空'); 
		if(!$this->memberCls->login($uname,$pwd)){
	    	    $this->message($this->memberCls->ero());
		}
		$uid = $this->memberCls->getuid();
		$member= $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where uid='".$uid."' ");
		$tjyhj = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."juan where uid='".$uid."' ");
		$member['logo'] = empty($member['logo'])?Mysite::$app->config['siteurl'].Mysite::$app->config['userlogo']:Mysite::$app->config['siteurl'].$member['logo'];
		$member['juancount'] = $tjyhj;
		$channelid = trim(IFilter::act(IReq::get('channelid')));
		$userid = trim(IFilter::act(IReq::get('userid')));
			ICookie::set('appuid',$member['uid'],86400);
		if(!empty($userid)){
			$checkmid =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."appbuyerlogin where uid='".$uid."' ");
			if(empty($checkmid)){
				$Mdata['channelid'] = $channelid;
				$Mdata['userid'] = $userid;
				$Mdata['uid']=$uid;
				$Mdata['addtime'] = time(); 
				$this->mysql->insert(Mysite::$app->config['tablepre'].'appbuyerlogin',$Mdata);  //插入新数据
			}else{
				if($checkmid['userid'] != $userid){ 
						$Mdata['channelid'] = $channelid;
						$Mdata['userid'] = $userid;
						$Mdata['uid']=$uid;
						$Mdata['addtime'] = time();
						$this->mysql->update(Mysite::$app->config['tablepre'].'appbuyerlogin',$Mdata,"uid='".$backinfo['uid']."'"); 
				}
			}
	    }
		unset($member['password']);
		$this->success($member);
	}
	function checkreg(){
		$this->success('成功');
	}
	/*
	*   检测普通用户登录
	*/
	function checkappMem(){
		$uid = trim(IFilter::act(IReq::get('uid')));
		$pwd = trim(IFilter::act(IReq::get('pwd')));
		$mapname = trim(IFilter::act(IReq::get('mapname')));

			$checklogintype = $_COOKIE['app_login'];
			
	    $logintype = trim(IFilter::act(IReq::get('logintype')));
		$phone = trim(IFilter::act(IReq::get('phone')));
			
		$uid = empty($uid)?ICookie::get('appuid'):$uid;
		$pwd = empty($pwd)?ICookie::get('apppwd'):$pwd;
		
		if($checklogintype == 'applogin'||$logintype=='phone'){
			 $checkphone = $_COOKIE['app_loginphone'];
			 if(empty($checkphone)){
				 $checkphone = $phone;
			 }
			 $member= $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where phone='".$checkphone."' ");
			 $backarr = array('uid'=>0);
			 if(!empty($member)){
				$backarr = $member;
			 }
		}else{
		

			 $member= $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where uid='".$uid."' ");
			 $backarr = array('uid'=>0);
			 if(!empty($member)){
				if($member['password'] == md5($pwd)){
					$backarr = $member;
					ICookie::set('appuid',$member['uid'],86400);
					ICookie::set('apppwd',$pwd,86400); 
					ICookie::set('appmapname',$mapname,86400);
					
					ICookie::set('email',$member['email'],86400);
					ICookie::set('memberpwd',$pwd,86400);
					ICookie::set('membername',$member['username'],86400);
					ICookie::set('uid',$member['uid'],86400); 
				}

			} 
		}
		return $backarr;
	}
	function gettest(){
		print_r($_COOKIE);
		$uid = ICookie::get('uid');
		$this->message($uid);
	}
	/*
	* 普通用户注册
	*/
	function reg(){
		$tname = IFilter::act(IReq::get('tname'));
		$password = IFilter::act(IReq::get('pwd'));
		$phone = IFilter::act(IReq::get('phone'));
		$password2 = IFilter::act(IReq::get('pwd2'));
		$email = IFilter::act(IReq::get('email'));
		$code = IFilter::act(IReq::get('code'));
		if($password2 != $password){
			$this->message('2次密码不一致');
		}
		
		
		
		$phonecls = new phonecode($this->mysql,0,$phone); 
		if($phonecls->checkcode($code)){  
				if($this->memberCls->regester($email,$tname,$password,$phone,5)){
					$this->memberCls->login($tname,$password);
					$uid = $this->memberCls->getuid();
					$member= $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where uid='".$uid."' ");
					unset($member['password']); 
					$channelid = trim(IFilter::act(IReq::get('channelid')));
					$userid = trim(IFilter::act(IReq::get('userid')));
					if(!empty($channelid) && !empty($userid)){
						$checkmid =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."appbuyerlogin where uid='".$uid."' ");
						if(empty($checkmid)){
							$Mdata['channelid'] = $channelid;
							$Mdata['userid'] = $userid;
							$Mdata['uid']=$uid;
							$Mdata['addtime'] = time(); 
							$this->mysql->insert(Mysite::$app->config['tablepre'].'appbuyerlogin',$Mdata);  //插入新数据
						}else{
							if($checkmid['channelid'] != $channelid ||  $checkmid['userid'] != $userid){ 
								$Mdata['channelid'] = $channelid;
								$Mdata['userid'] = $userid;
								$Mdata['uid']=$uid;
								$Mdata['addtime'] = time();
								$this->mysql->update(Mysite::$app->config['tablepre'].'appbuyerlogin',$Mdata,"uid='".$backinfo['uid']."'"); 
							}
						}
					} 
					$this->success($member);
				}else{
					$this->message($this->memberCls->ero());
				}
		}else{
			$this->message($phonecls->getError());
		} 

	}
	
	
	
	
	function appuserinfo(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		unset($backinfo['password']);
		$this->success($backinfo);
	}
	function modify(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$phone = IFilter::act(IReq::get('phone'));
		$oldpwd = IFilter::act(IReq::get('oldpwd'));
		$newpwd = IFilter::act(IReq::get('newpwd'));
		$surepwd = IFilter::act(IReq::get('surepwd')); 
		$code = IFilter::act(IReq::get('code'));
		 
		
		if(empty($newpwd)){
			$this->message('新密码不能为空');
		}
		if($newpwd != $surepwd){
			$this->message('新密码和确认密码不一致');
		}
		if(empty($backinfo['temp_password'])){
			if(empty($oldpwd)){
				$this->message('旧密码不能为空');
			}
			if($backinfo['password'] != md5($oldpwd)){
				$this->message('旧密码错误');
			}
		}
		/*if(empty($backinfo['temp_password'])){
			$phonecls = new phonecode($this->mysql,5,$phone); 
			if($phonecls->checkcode($code)){
				$newdata['password'] = md5($newpwd);
				$newdata['temp_password'] = '';
				$this->mysql->update(Mysite::$app->config['tablepre'].'member',$newdata,"uid='".$backinfo['uid']."'");
				unset($backinfo['password']);
				$backinfo['temp_password'] = '';
				$this->success($backinfo);
			}else{
				$this->message($phonecls->getError());
			} 
		}else{*/
			$newdata['password'] = md5($newpwd);
			$backinfo['temp_password'] = '';
			$this->mysql->update(Mysite::$app->config['tablepre'].'member',$newdata,"uid='".$backinfo['uid']."'");
			unset($backinfo['password']);
			$this->success($backinfo);
	//	}
		
	}
	function findpwd(){
		$phone =  trim(IFilter::act(IReq::get('phone'))); 
		$newpwd =  trim(IFilter::act(IReq::get('newpwd')));
		$surepwd = trim(IFilter::act(IReq::get('surepwd'))); 
		$code =  trim(IFilter::act(IReq::get('code')));  
	    $member= $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where phone='".$phone."' ");
  	    if(empty($member)){
			$this->message('用户不存在');
		}
		// if(!empty($member['temp_password'])){
			// $this->message('快捷登录用户不需找回密码');
		// }
		// if($code != $yanzhengcode){
			// $this->message('验证失败');
		// }
		if(empty($newpwd)){
			$this->message('新密码不能为空');
		}
		if($newpwd != $surepwd){
			$this->message('新密码和确认密码不一致');
		} 
		$phonecls = new phonecode($this->mysql,2,$phone); 
		if($phonecls->checkcode($code)){
			$newdata['password'] = md5($newpwd);
			$newdata['temp_password'] = '';
			$this->mysql->update(Mysite::$app->config['tablepre'].'member',$newdata,"uid='".$member['uid']."'");
		}else{
			$this->message($phonecls->getError());
		} 
		

	 
		$this->success('success');
	}
	function modifyphone(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$phone =  trim(IFilter::act(IReq::get('phone'))); 
		$newphone =  trim(IFilter::act(IReq::get('newphone'))); 
		$code =  trim(IFilter::act(IReq::get('code')));  
	    $member= $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where phone='".$phone."' ");
  	    if(empty($member)){
			$this->message('用户不存在');
		}
		if($backinfo['uid']  != $member['uid']){
		   $this->message('手机号不对应本账号');
		}
		 
		// if($code != $yanzhengcode){
			// $this->message('验证失败');
		// }
	   
		if(!IValidate::suremobi($newphone)){
			$this->message('新手机号格式错误');
		 } 
		  $check2 = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where phone='".$newphone."' ");
  	  
	    if(!empty($check2)){
			 $this->message('新手机号已绑定其他账号不能重复绑定');
		}
		$phonecls = new phonecode($this->mysql,3,$phone); 
		if($phonecls->checkcode($code)){
			$newdata['phone'] = $newphone;
			$this->mysql->update(Mysite::$app->config['tablepre'].'member',$newdata,"uid='".$member['uid']."'");
			$this->success('success');
		}else{
			$this->message($phonecls->getError());
		}  
	}
	function fastlogin(){
		$phone = trim(IFilter::act(IReq::get('phone'))); 
		$code =  trim(IFilter::act(IReq::get('code')));
		$checkcancode = Mysite::$app->config['allowedcode'];
		 if($checkcancode != 1){
				$this->message('平台不允许快捷登录');
		 } 
		$phonecls = new phonecode($this->mysql,4,$phone); 
		if($phonecls->checkcode($code)){
			$member = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where phone='".$phone."' ");
		    if(empty($member)){ 
			     $checkphone = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."mobileapp where phone ='".$phone."'   order by addtime desc limit 0,1");
				 $temp_password = rand(100000,999999);  
				 $checkstr = md5($checkphone['phone']);
				 $arr['username'] = substr($checkstr,0,8);
				 $arr['phone'] = $checkphone['phone'];
				 $arr['address'] = '';
				 $arr['password'] = md5($temp_password);
				 $arr['email'] = '';
				 $arr['creattime'] = time(); 
				 $arr['score']  = $score == 0?Mysite::$app->config['regesterscore']:$score;
				 $arr['logintime'] = time(); 
				 $arr['logo'] = Mysite::$app->config['userlogo'];
				 $arr['loginip'] = IClient::getIp();
				 $arr['group'] = 5;
				 $arr['cost'] = 0; 
				 $arr['parent_id'] =0;
				 $arr['temp_password'] = $temp_password;
				 $this->mysql->insert(Mysite::$app->config['tablepre'].'member',$arr);   
				 $uid = $this->mysql->insertid(); 
				 if($arr['score'] > 0){
					$this->memberCls->addlog($uid,1,1,$arr['score'],'注册送积分','注册送积分'.$arr['score'],$arr['score']);
				 } 
					 if(Mysite::$app->config['regester_juan'] ==1){
					   //注册送优惠券
					   $nowtime = time();
					   $endtime = $nowtime+Mysite::$app->config['regester_juanday']*24*60*60;
					   $juandata['card'] = $nowtime.rand(100,999);
						$juandata['card_password'] =  substr(md5($juandata['card']),0,5);	
						$juandata['status'] = 1;// 状态，0未使用，1已绑定，2已使用，3无效	
						$juandata['creattime'] = $nowtime;// 制造时间	
						$juandata['cost'] = Mysite::$app->config['regester_juancost'];// 优惠金额	
						$juandata['limitcost'] =  Mysite::$app->config['regester_juanlimit'];// 购物车限制金额下限	
						$juandata['endtime'] = $endtime;// 失效时间	
						$juandata['uid'] = $uid;// 用户ID	
						$juandata['username'] = $arr['username'];// 用户名	
						$juandata['name'] = '注册账号赠送优惠券';//  优惠券名称 
					   $this->mysql->insert(Mysite::$app->config['tablepre'].'juan',$juandata); 
					 
					 }  
					 $member = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where uid='".$uid."' ");
		   
			}  	
			 
			$tjyhj = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."juan where uid='".$uid."' ");
			$member['logo'] = empty($member['logo'])?Mysite::$app->config['siteurl'].Mysite::$app->config['userlogo']:Mysite::$app->config['siteurl'].$member['logo'];
			$member['juancount'] = $tjyhj;
			$channelid = trim(IFilter::act(IReq::get('channelid')));
			$userid = trim(IFilter::act(IReq::get('userid')));
			if(!empty($userid)){
				$checkmid =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."appbuyerlogin where uid='".$uid."' ");
				if(empty($checkmid)){
					$Mdata['channelid'] = $channelid;
					$Mdata['userid'] = $userid;
					$Mdata['uid']=$uid;
					$Mdata['addtime'] = time(); 
					$this->mysql->insert(Mysite::$app->config['tablepre'].'appbuyerlogin',$Mdata);  //插入新数据
				}else{
					if($checkmid['userid'] != $userid){ 
							$Mdata['channelid'] = $channelid;
							$Mdata['userid'] = $userid;
							$Mdata['uid']=$uid;
							$Mdata['addtime'] = time();
							$this->mysql->update(Mysite::$app->config['tablepre'].'appbuyerlogin',$Mdata,"uid='".$backinfo['uid']."'"); 
					}
				}
			}
			$expire = time() + 86400; // 设置24小时的有效期
			setcookie("app_login", "app_login", $expire);
			setcookie("app_loginphone", $phone, $expire); 
			unset($member['password']);
			$this->success($member);
			 
		}else{
			$this->message($phonecls->getError());
		}
	} 
	function fastloginmodifyname(){//快速登录修改用户名
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		 
		$data['username'] =  trim(IFilter::act(IReq::get('username')));
		if(empty($data['username'])) $this->message('新用户名为空');
	    $checkmid =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where username='".$data['username']."' ");
		if(!empty($checkmid)) $this->message('用户名已存在');	
		$this->mysql->update(Mysite::$app->config['tablepre'].'member',$data,"uid='".$backinfo['uid']."'"); 
		 
		$this->success('success'); 
	}
	
	//3
	
	
	
	
	/*
	* 获取店铺列表
	暂无判断  坐标所在店铺
	*/
	function shop(){
		$lat = IFilter::act(IReq::get('lat'));
		$lng = IFilter::act(IReq::get('lng'));
		$showtype = IFilter::act(IReq::get('showtype'));
		$backtype = IFilter::act(IReq::get('backtype'));
		$orderby = in_array($showtype,array('juli','cost','is_com'))?$showtype:'juli';
		$checklat = intval($lat);
		$checklng = intval($lng);
		$lat = empty($checklat)?0:$lat;
		$lng = empty($checklng)?0:$lng;
		$orderarray = array(
			'juli'=>' order by  SQRT((`lat` -'.$lat.') * (`lat` -'.$lat.' ) + (`lng` -'.$lng.' ) * (`lng` -'.$lng.' )) asc ',
			'cost'=>' order by a.limitcost asc ',
			'is_com'=>' order by a.is_com asc '
		);
		$areaid = intval(IFilter::act(IReq::get('areaid')));

		$where = ' where endtime > '.time().' and  SQRT((`lat` -'.$lat.') * (`lat` -'.$lat.' ) + (`lng` -'.$lng.' ) * (`lng` -'.$lng.' )) < `pradius`*0.01094 ';
		if(!empty($areaid)){
			$where = " where b.id in(select shopid from ".Mysite::$app->config['tablepre']."areashop where areaid = ".$areaid." ) ";
			$orderarray = array(
				'juli'=>' order by id asc ',
				'cost'=>' order by a.limitcost asc ',
				'is_com'=>' order by a.is_com asc '
			);
		}else{ 
			if(empty($lat)){
				$this->success(array());
			}
		}
		$where = '';
		$where = $showtype == 'is_com'? $where.' and a.is_com = 1 ':$where;
	

		// print_r($where);

		$this->pageCls->setpage(intval(IReq::get('page')),20);
		$list = $this->mysql->getarr("select a.shopid,b.id,b.shopname,b.is_open,b.starttime,b.pointcount as sellcount,lat,lng,a.is_orderbefore,a.limitcost,b.shoplogo,a.personcost,a.is_hot,a.is_com,a.is_new,a.maketime,a.pradius,b.lat,b.lng,a.sendtype,a.pscost,a.arrivetime from ".Mysite::$app->config['tablepre']."shopfast as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id ".$where."   ".$orderarray[$orderby]."   limit ".$this->pageCls->startnum().", ".$this->pageCls->getsize()."");
		$shopdata = array();
		$nowhour = date('H:i:s',time());
		$nowhour = strtotime($nowhour);
		foreach($list as $key=>$value){
			$value['juli'] =  $this->GetDistance($lat, $lng, $value['lat'], $value['lng']).'米';//'未测距';
			$value['opentype'] = '1';//1营业  0未营业
			$imgurl = empty($value['shoplogo'])? Mysite::$app->config['shoplogo']:$value['shoplogo'];
			$checkinfo = $this->shopIsopen($value['is_open'],$value['starttime'],$value['is_orderbefore'],$nowhour);

			if($checkinfo['opentype'] != 2 && $checkinfo['opentype'] != 3){
				$value['opentype'] = '0';
			}
			if($backtype > 0){
				if($checkinfo['opentype'] != 2 && $checkinfo['opentype'] != 3){
					$value['opentype'] = '0';
				}else{
					$value['opentype'] = $checkinfo['opentype'];
				}
				$checkstr =  $value['starttime'];
				$tempstr = array();
				if(!empty($checkstr)){
					$tempstr = explode('-',$checkstr);
				}
				$value['starttime'] = count($tempstr) > 0 ? $tempstr[0]:'';
			}
			//$items['opentype'] != 2 && $items['opentype'] != 3

			$value['shopimg'] = Mysite::$app->config['siteurl'].$imgurl;
			unset($value['sendset']);
			$shopdata[] = $value;
		}
		$this->success($shopdata);
	}
	function appbuyorder(){
		$statusarr = array('0'=>'新订单','1'=>'待发货','2'=>'待评价','3'=>'已完成','4'=>'关闭','5'=>'关闭');
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		//
		$gettype = trim(IFilter::act(IReq::get('gettype')));
		$gettype = !in_array($gettype,array('wait','waitsend','is_send')) ?'wait':$gettype;
		$newwherearray =array(
			'wait'=> ' status > 0 and status < 2 and is_make = 0',
			'waitsend'=>' status = 1 and is_make = 1',
			'is_send'=>' status > 1 '
		); 
		$todatay = strtotime(date('Y-m-d',time()));
		$todatay = $todatay - 604800;//最近一周订单

		$orderlist =  $this->mysql->getarr("select id,addtime,posttime,dno,allcost,status,is_make,daycode,shopname,is_ping from ".Mysite::$app->config['tablepre']."order where buyeruid = ".$backinfo['uid']."   and addtime > ".$todatay." order by id desc  "); //and ".$newwherearray[$gettype]."
		$backdatalist = array();
		foreach($orderlist as $key=>$value){
			$value['showstatus'] = $statusarr[$value['status']];
			$value['addtime'] = date('Y-m-d H:i:s',$value['addtime']);
			if($value['status'] ==  1){
				if($value['is_make'] == 0){
					$value['showstatus'] = '等待审核';
				}elseif($value['is_make'] ==2){
					$value['showstatus'] = '无效订单';
					$value['status'] = 4;

				}
			}elseif($value['status'] == 3){
				if(empty($value['is_ping'])){
					$value['showstatus'] ='待评价';
				}

			}

			$backdatalist[] = $value;
		}
		$this->success($backdatalist);
	}

	function shopshow(){
		$id = intval(IFilter::act(IReq::get('id')));
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$shopinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where id='".$id."' ");   //店铺基本信息
	 	if(empty($shopinfo)){
	 	 	//需要进行跳转
			echo '店铺获取失败';
			exit;
		}
		$shopdet = array();
		if(empty($shopinfo['shoptype'])){
			$shopdet = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast where shopid='".$id."' ");
		}elseif($shopinfo['shoptype'] == 1){
			$shopdet = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket where shopid='".$id."' ");
		}
		$nowhour = date('H:i:s',time());
		$data['openinfo'] = $this->shopIsopen($shopinfo['is_open'],$shopinfo['starttime'],$shopdet['is_orderbefore'],$nowhour);
		$data['shopinfo'] = $shopinfo;
		$data['shopdet'] = $shopdet;
		$templist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodstype where shopid='".$id."' ");
		$data['goodstype'] = array();
		foreach($templist as $key=>$value){
	 	 	$value['det'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goods where shopid='".$id."' and typeid =".$value['id']." order by good_order asc ");
			$data['goodstype'][]  = $value;
		}
		$shopdet['id'] = $id;
		$shopdet['shoptype']=1;
		$tempinfo =   $this->pscost($shopdet,1);
		$backdata['pstype'] = $tempinfo['pstype'];
		$backdata['pscost'] = $tempinfo['pscost'];
		$data['psinfo'] = $backdata;
		$data['mainattr'] = array();
		$templist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shoptype where  cattype = ".$shopinfo['shoptype']." and parent_id = 0 and is_main =1  order by orderid asc limit 0,1000");
		foreach($templist as $key=>$value){
			$value['det'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shoptype where parent_id = ".$value['id']." order by orderid asc  limit 0,20");
			$data['mainattr'][] = $value;
		}
		$data['shopattr'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shopattr  where  cattype = ".$shopinfo['shoptype']." and shopid = '".$shopinfo['id']."'  order by firstattr asc limit 0,1000");
		$data['cxinfo'] = array();
		$sellrule =new sellrule();
		$sellrule->setdata($id,10000,$shopinfo['shoptype']);
		$ruleinfo = $sellrule->getdata();
		if(isset($ruleinfo['gzdata'])){
			$data['cxinfo'] = $ruleinfo['gzdata'];
		}
		Mysite::$app->setdata($data);
	}
	function cart(){
		$Cart = new smCart;
		$carinfo = $Cart->getMyCart();
		$shopid = intval(IReq::get('shopid'));
		$backdata = array();
		if($shopid  > 0){
			if(isset($carinfo['list'][$shopid]['data'])){
				$backdata['list'] = $carinfo['list'][$shopid]['data'];
				$backdata['sumcount'] =$carinfo['list'][$shopid]['count'];
				$backdata['sum'] =$carinfo['list'][$shopid]['sum'];
				$backdata['bagcost'] =$carinfo['list'][$shopid]['bagcost'];
				$cxclass = new sellrule();
				if($carinfo['list'][$shopid]['shopinfo']['shoptype'] ==1){
					$shopcheckinfo =   $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id where a.shopid = '".$shopid."'    ");
				}else{
					$shopcheckinfo =   $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id where a.shopid = '".$shopid."'    ");
				}
				$cxclass->setdata($shopid,$backdata['sum'],$carinfo['list'][$shopid]['shopinfo']['shoptype']);
				$cxinfo = $cxclass->getdata();
				$backdata['surecost'] = $cxinfo['surecost'];
				$backdata['downcost'] = $cxinfo['downcost'];
				$backdata['gzdata'] = isset($cxinfo['gzdata'])?$cxinfo['gzdata']:array();

				$tempinfo =   $this->pscost($shopcheckinfo,$backdata['sumcount']);
				$backdata['pstype'] = $tempinfo['pstype'];
				$backdata['pscost'] = $cxinfo['nops'] == true?0:$tempinfo['pscost'];
				$backdata['canps'] = $tempinfo['canps'];
				$source =  intval(IFilter::act(IReq::get('source')));
				$ios_waiting =   Mysite::$app->config['ios_waiting'];
				if($source == 1 && $ios_waiting == true){
					$backdata['canps'] = 1;
				}
			}else{
				$this->message(array());
				//  $this->hjson(array('error'=>true,'data'=>array()));
			}

		}else{
			$this->message(array());//$backdata = $carinfo;
		}
		$this->success($backdata);
	}
	function shopcart(){//购物车
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$id = IFilter::act(IReq::get('id'));
		$data['scoretocost'] = Mysite::$app->config['scoretocost'];
		//	id	card 优惠劵卡号	card_password 优惠劵密码	status 状态，0未使用，1已绑定，2已使用，3无效	creattime 制造时间	cost 优惠金额	limitcost 购物车限制金额下限	endtime 失效时间	uid 用户ID	username 用户名	usetime 使用时间	name
		$data['juanlist'] =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."juan  where uid='".$backinfo['uid']."' and endtime > ".time()." and status = 1   "); 
		$shopinfo =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id  where shopid = ".$id."   ");
		if(empty($shopinfo)){
			$shopinfo =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id  where shopid = ".$id."   ");
		}
		$nowtime = time();
		$timelist = array();
		$info = explode('|',$shopinfo['starttime']);
		$info = is_array($info)?$info:array($info);
		$data['is_open'] = 0;
		$dototime = time()+$shopinfo['maketime']*60;
		foreach($info as $key=>$value){
			if(!empty($value)){
				$temptime = explode('-',$value);
				if(count($temptime) == 2){ //开始转换

					$btime = strtotime($temptime[0].':00');
					$etime = strtotime($temptime[1].':00');
					$whtime = $btime;
		     	  	while ($whtime < $etime){
						if($whtime < $etime && $whtime >= $nowtime && $whtime > $dototime ){
							$timelist[] = date('H:i',$whtime);
							$data['is_open'] = 1;
						}
						$whtime +=900;
					}

				}
			}
		}
		if($shopinfo['is_open'] == 0  || $shopinfo['is_pass'] == 0){
			$data['is_open'] = 0;
		}
		$data['timelist'] =$timelist;
		$data['deaddress'] =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."address  where userid = ".$backinfo['uid']." and `default`=1   ");
		$data['appmapname'] = ICookie::get('appmapname');


		$data['domember'] = $backinfo;
		Mysite::$app->setdata($data);
	}
	function makeorder(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$info['shopid'] = intval(IReq::get('shopid'));//店铺ID
		$info['remark'] = IFilter::act(IReq::get('remark'));//备注
		$info['paytype'] =  intval(IFilter::act(IReq::get('payline')));//支付方式
		$info['dikou'] =  intval(IReq::get('dikou'));//抵扣金额
		$info['username'] = IFilter::act(IReq::get('username'));
		$info['mobile'] = IFilter::act(IReq::get('mobile'));
		$info['addressdet'] = IFilter::act(IReq::get('addressdet'));
		$info['senddate'] = date('Y-m-d',time());// IFilter::act(IReq::get('senddate'));
		$info['minit'] = IFilter::act(IReq::get('minit'));
		$info['juanid']  =  intval(IReq::get('juanid'));//优惠劵ID
		$info['ordertype'] = 4;//订单类型
		$peopleNum = IFilter::act(IReq::get('peopleNum'));
		$info['othercontent'] ='';//empty($peopleNum)?'':serialize(array('人数'=>$peopleNum));

		if(empty($info['shopid'])) $this->message('店铺ID错误');
		$Cart = new smCart;
		$carinfo = $Cart->getMyCart();
		if(!isset($carinfo['list'][$info['shopid']]['data'])) $this->message('对应店铺购物车商品为空');
		if($carinfo['list'][$info['shopid']]['shopinfo']['shoptype'] == 1){
			$shopinfo=   $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id where a.shopid = '".$info['shopid']."'    ");
		}else{
			$shopinfo=   $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id where a.shopid = '".$info['shopid']."'    ");
		}
		if(empty($shopinfo))   $this->message('店铺获取失败');
		$checkps = 	 $this->pscost($shopinfo,$carinfo['list'][$info['shopid']]['count']);
		if($checkps['canps'] != 1) $this->message('该店铺不在配送范围内');
		$source =  intval(IFilter::act(IReq::get('source')));
		$ios_waiting =   Mysite::$app->config['ios_waiting'];
		if($source == 1 && $ios_waiting == true){
			$checkps['canps']  = 1;
			$checkps['pscost']  = $shopinfo['pscost'];
		}
		$info['cattype'] = 0;//
		if(empty($info['username'])) 		  $this->message('联系人不能为空');
	  	if(!IValidate::suremobi($info['mobile']))   $this->message('请输入正确的手机号');
		if(empty($info['addressdet'])) $this->message('详细地址为空');

		$info['userid'] = !isset($backinfo['score'])?'0':$backinfo['uid'];
		$info['ipaddress'] = '';
		$ip_l=new iplocation();
		$ipaddress=$ip_l->getaddress($ip_l->getIP());
		if(isset($ipaddress["area1"])){
			$info['ipaddress']  = $ipaddress['ip'].mb_convert_encoding($ipaddress["area1"],'UTF-8','GB2312');//('GB2312','ansi',);
		}
		//area1 二级地址名称	area2 三级地址名称	area3
		$info['areaids'] = '';
		$paytype = $info['paytype'];
		 

	    $senddate = $info['senddate'];
		$minit = $info['minit'];
		$nowpost = strtotime($senddate.' '.$minit.':00'); 
		$settime = time() - (10*60);
		if($settime > $nowpost)  $this->message('提交配送时间和服务器时间相差超过10分钟下单失败');
		$temp = strtotime($minit.':00');
		$is_orderbefore = $shopinfo['is_orderbefore'] == 0?0:$shopinfo['befortime'];
		$tempinfo = $this->checkshopopentime($is_orderbefore,$nowpost,$shopinfo['starttime']);
		if(!$tempinfo) $this->message('配送时间不在有效配送时间范围');
		if($shopinfo['is_open'] != 1) $this->message('店铺暂停营业');
		$info['sendtime'] = $nowpost; 
		$info['shopinfo'] = $shopinfo;
		$info['allcost'] = $carinfo['list'][$info['shopid']]['sum'];
		$info['bagcost'] = $carinfo['list'][$info['shopid']]['bagcost'];
		$info['allcount'] = $carinfo['list'][$info['shopid']]['count'];
		$info['shopps'] = $checkps['pscost'];
		$info['goodslist']   = $carinfo['list'][$info['shopid']]['data'];
		$info['pstype'] = $checkps['pstype'];
		$info['cattype'] = 0;//表示不是预订
		$info['is_goshop']=0;
	    if($shopinfo['limitcost'] > $info['allcost']) $this->message('商品总价低于最小起送价'.$shopinfo['limitcost']);
		$orderclass = new orderclass();
		$orderclass->makenormal($info);
		$orderid = $orderclass->getorder();
		if($info['userid'] ==  0){
	  	   ICookie::set('orderid',$orderid,86400);
		}

		$Cart->delshop($info['shopid']);
		$this->success($orderid);
		exit;
	}
	public static function checkshopopentime($is_orderbefore,$posttime,$starttime){
		$maxnowdaytime = strtotime(date('Y-m-d',time()));
		$daynottime = 24*60*60 -1;
		$findpostime = false;
		for($i=0;$i <= $is_orderbefore;$i++){
			if($findpostime == false){
				$miniday = $maxnowdaytime+$daynottime*$i;
				$maxday = $miniday+$daynottime;
				$tempinfo = explode('|',$starttime);
				foreach($tempinfo as $key=>$value){
					if(!empty($value)){
						$temp2 = explode('-',$value);
						if(count($temp2) > 1){
							$minbijiaotime = date('Y-m-d',$miniday);
							$minbijiaotime = strtotime($minbijiaotime.' '.$temp2[0].':00');

							$maxbijiaotime = date('Y-m-d',$maxday);
							$maxbijiaotime = strtotime($maxbijiaotime.' '.$temp2[1].':00');

							if($posttime > $minbijiaotime && $posttime < $maxbijiaotime){
								$findpostime = true;
								break;
							}
						}
					}
				}

			}

		}
		return $findpostime;
	}
	function showorder(){
		$orderid = intval(IFilter::act(IReq::get('orderid')));
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}else{
			if($backinfo['uid'] == 0){
				ICookie::set('email',$backinfo['email'],86400);
				ICookie::set('memberpwd',ICookie::get('apppwd'),86400);
				ICookie::set('membername',$backinfo['username'],86400);
				ICookie::set('uid',$backinfo['uid'],86400);
			}
		}
			//order="++"&uid="+account+"&pwd="+password+"&mapname="+m.getMapname()+"&lat="+m.getLat()+"&lng="+m.getLng()
		if(!empty($orderid)){

	     	$order = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where buyeruid='".$backinfo['uid']."' and id = ".$orderid."");

	     	if(empty($order)){
	     		$data['order'] = '';
	     		Mysite::$app->setdata($data);
	     	}else{
				$order['ps'] = $order['shopps'];
	     	     // 超市商品总价	 超市配送配送	shopcost 店铺商品总价	shopps 店铺配送费	pstype 配送方式 0：平台1：个人	bagcost
				$orderdet = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."orderdet where order_id='".$order['id']."'");
	            $order['cp'] = count($orderdet);
	            $buyerstatus= array(
					'0'=>'等待处理',
					'1'=>'订餐成功处理中',
					'2'=>'订单已发货',
					'3'=>'订单完成',
					'4'=>'订单已取消',
					'5'=>'订单已取消'
	     	    );
				$paytypelist = array('outpay'=>'货到支付','open_acout'=>'账号余额支付');
				$paylist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."paylist   order by id asc limit 0,50");
				if(is_array($paylist)){
					foreach($paylist as $key=>$value){
						$paytypelist[$value['loginname']] = $value['logindesc'];
					}
	            }
				$paytypearr = $paytypelist;
				$order['surestatus'] = $order['status'];
				$order['status'] = $buyerstatus[$order['status']];
				$order['paytype'] = $paytypearr[$order['paytype']];
				$order['paystatus'] = $order['paystatus']==1?'已支付':'未支付';
				$order['addtime'] = date('Y-m-d H:i:s',$order['addtime']);
				$order['posttime'] = date('Y-m-d H:i:s',$order['posttime']);
				$data['order'] = $order;
				$data['orderdet'] = $orderdet;

				Mysite::$app->setdata($data);

			}
		}else{
			$data['order'] = '';
			Mysite::$app->setdata($data);
		}
	}
	function commentorder(){ 
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}else{
			if($backinfo['uid'] == 0){
				ICookie::set('email',$backinfo['email'],86400);
				ICookie::set('memberpwd',ICookie::get('apppwd'),86400);
				ICookie::set('membername',$backinfo['username'],86400);
				ICookie::set('uid',$backinfo['uid'],86400);
			}
		} 
	    $orderid = intval(IReq::get('orderid'));
	    if(!empty($orderid)){
			$order = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where buyeruid='".$backinfo['uid']."' and id = ".$orderid."");

	     	if(empty($order)){
	     		$data['order'] = '';
	     		Mysite::$app->setdata($data);
	     	}else{
				$data['order'] =$order;
				$orderdet = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."orderdet where order_id='".$order['id']."'");
				$data['orderdet'] = $orderdet;
				$tempcoment = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."comment where orderid='".$order['id']."'");
				$data['comment'] = array();
				foreach($tempcoment as $key=>$value){
					$data['comment'][$value['orderdetid']] = $value;
				}
				//  id		orderdetid	shopid	goodsid	uid	content	addtime	replycontent	replytime	 评分	is_show 0展示，1不展示
				Mysite::$app->setdata($data);

			}
	    }else{
			$data['order'] = '';
			Mysite::$app->setdata($data);
	    } 
	}
	function address(){
		//
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}else{
			if($backinfo['uid'] == 0){
				ICookie::set('email',$backinfo['email'],86400);
				ICookie::set('memberpwd',ICookie::get('apppwd'),86400);
				ICookie::set('membername',$backinfo['username'],86400);
				ICookie::set('uid',$backinfo['uid'],86400);
			}
		}
		$tarelist = $this->mysql->getarr("select *  from ".Mysite::$app->config['tablepre']."address where userid='".$backinfo['uid']."'   order by id asc limit 0,50");
		$arelist = array();
		$data['arealist'] = $tarelist;
		$data['areaarr'] = $arelist;
		Mysite::$app->setdata($data);

	}
	function giftlog(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}else{
			if($backinfo['uid'] == 0){
				ICookie::set('email',$backinfo['email'],86400);
				ICookie::set('memberpwd',ICookie::get('apppwd'),86400);
				ICookie::set('membername',$backinfo['username'],86400);
				ICookie::set('uid',$backinfo['uid'],86400);
			}
		}
		echo '获取礼品记录';
		exit;
	}
	function gift(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		echo '获取所有礼品';
		exit;
	}
	function juan(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$wjuan = array('shuliang'=>0,'list'=>array());
		$ujuan = array('shuliang'=>0,'list'=>array());
		$ojuan = array('shuliang'=>0,'list'=>array());
		$nowtime = time();
		$wjuan['shuliang'] =  $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."juan    where uid = ".$backinfo['uid']." and endtime > ".$nowtime." and status = 1 ");
		$wjuan['list'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."juan where uid = ".$backinfo['uid']." and endtime > ".$nowtime." and status = 1 ");
		$ujuan['shuliang'] =  $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."juan    where uid = ".$backinfo['uid']."  and status = 2 ");
		$ujuan['list'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."juan where uid = ".$backinfo['uid']."   and status = 2 ");

		$ojuan['shuliang'] =  $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."juan    where uid = ".$backinfo['uid']." and endtime < ".$nowtime." and (status = 1 or status =3)");
		$ojuan['list'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."juan where uid = ".$backinfo['uid']." and endtime < ".$nowtime." and (status = 1 or status =3)  ");

		$data['wjuan'] = $wjuan;
		$data['ujuan'] = $ujuan;
		$data['ojuan'] = $ojuan;
		Mysite::$app->setdata($data);
	}
	/**
	 *  @brief 保存通道号
	 *  
	 *  @return Return_Description
	 *  
	 *  @details Details
	 */
	function appbuybaidu(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$channelid = trim(IFilter::act(IReq::get('channelid')));
		$userid = trim(IFilter::act(IReq::get('userid')));
		if(empty($userid)) $this->message('获取失败'); 
		$checkmid =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."appbuyerlogin where uid='".$backinfo['uid']."' ");
  		if(empty($checkmid)){ 
  		    $Mdata['channelid'] = $channelid;
  		    $Mdata['userid'] = $userid;
	        $Mdata['uid']=$backinfo['uid'];
	        $Mdata['addtime'] = time(); 
            $this->mysql->insert(Mysite::$app->config['tablepre'].'appbuyerlogin',$Mdata);  //插入新数据
  		}else{  
			if($checkmid['userid'] != $userid){ 
	           $Mdata['channelid'] = $channelid;
  		       $Mdata['userid'] = $userid;
	           $Mdata['uid']=$backinfo['uid'];
	           $Mdata['addtime'] = time();
			   $this->mysql->update(Mysite::$app->config['tablepre'].'appbuyerlogin',$Mdata,"uid='".$backinfo['uid']."'");  
  			} 
  		} 
		$this->success('操作成功'); 
	}
	function dologin(){
  	 $this->memberCls->login($tname,$password);

	}
	/**
	 *  @brief 买家获取单个订单
	 *  
	 *  @return 
	 *  
	 *  @details Details
	 */
	function appbuyerone(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$statusarr = array('0'=>'取消订单','1'=>'待发货','2'=>'待完成','3'=>'已完成','4'=>'关闭','5'=>'关闭');
		$paytypelist = array('outpay'=>'货到支付','open_acout'=>'账号余额支付');
		$shoptypearr = array(
			'0'=>'外卖',
			'1'=>'超市',
			'2'=>'其他',
	     );
		$paylist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."paylist   order by id asc limit 0,50");
		if(is_array($paylist)){
			foreach($paylist as $key=>$value){
		   	    $paytypelist[$value['loginname']] = $value['logindesc'];
			}
		}
		$backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$orderid = trim(IFilter::act(IReq::get('orderid')));
		if(empty($orderid)){
			$this->message('订单不存在或者不属于您');
		}
		$order= $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where id='".$orderid."' "); //cxids 促销规则ID集	cxcost  yhjcost  bagcost
		if(empty($order)){
			$this->message('订单不存在');
		}
		if($order['buyeruid'] != $backinfo['uid']) $this->message('您不是订单所有者');

		$backdata['dno'] = $order['dno'];
		$backdata['addtime'] = date('Y-m-d H:i:s',$order['addtime']);
		$backdata['id'] = $order['id'];
		$backdata['allcost'] = $order['allcost'];
		$backdata['shopcost'] = $order['shopcost'];
		$backdata['shopname'] = $order['shopname']; 
		$backdata['showstatus'] = $statusarr[$order['status']];
		if($order['status'] ==  1){
			if($order['is_make'] == 0){
				$backdata['showstatus'] = '取消订单';
			}elseif($order['is_make'] ==2){
				$backdata['showstatus'] = '无效订单';
				$backdata['status'] = 4; 
			}
		}elseif($order['status'] == 3){
			if(empty($order['is_ping'])){
				$backdata['showstatus'] ='待评价';
			}
		}
		$backdata['is_ping'] = $order['is_ping'];
		$backdata['is_make'] = $order['is_make'];
		$backdata['status'] = $order['status'];
		$temlist = array();
		$dotem =   empty($order['paystatus'])?'未支付':'已支付';
		$templist[]['mytext'] = '订单编号：'.$order['dno'];
		$templist[]['mytext'] = '买家地址：'.$order['buyeraddress'];
		$templist[]['mytext'] = '联系电话：'.$order['buyerphone'];
		$templist[]['mytext'] = '配送时间：'.date('Y-m-d H:i:s',$order['posttime']);
		$templist[]['mytext'] = '支付类型：'.$paytypelist[$order['paytype']];
		$templist[]['mytext'] = '支付状态：'.$dotem;
		$templist[]['mytext'] = '备注：'.$order['content']; 
		$templist[]['mytext'] = '店铺名：'.$order['shopname'];
		$templist[]['mytext'] = '店铺地址：'.$order['shopaddress'];
		$backdata['itemlist'] = $templist; 
		$templist =   $this->mysql->getarr("select id,order_id,goodsname,goodscost,goodscount from ".Mysite::$app->config['tablepre']."orderdet where order_id='".$orderid."' ");
		$newdatalist = array();
		$shuliang = 0;
		foreach($templist as $key=>$value){
			$value['goodscost'] = $value['goodscost'];
			$newdatalist[] = $value;

			$shuliang += $value['goodscount'];
		} 
		$backdata['det'] = $newdatalist;

		$this->success($backdata);
	}
	/**
	 *  @brief 买家关闭订单
	 *  
	 *  @return Return_Description
	 *  
	 *  @details Details
	 */
	function appbuyerclose(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$orderid = trim(IFilter::act(IReq::get('orderid')));
		if(empty($orderid)){
			$this->message('订单不存在或者不属于您');
		}
		$order= $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where id='".$orderid."' "); //cxids 促销规则ID集	cxcost  yhjcost  bagcost
		if(empty($order)){
			$this->message('订单不存在');
		}
		if($order['buyeruid'] != $backinfo['uid']) $this->message('您不是订单所有者');
		if(empty($order['status']) || $order['status'] == 1){
			if($order['status'] == 1){
				if(!empty($order['is_make'])){
					$this->message('订单状态不可取消');
				}
			}
			if($order['paystatus'] == 1){
     	      $this->message('订单已支付请登录网站申请退款');
			}
			$orderdata['status'] = 5;
			$this->mysql->update(Mysite::$app->config['tablepre'].'order',$orderdata,"id='".$orderid."'");
			if(!empty($order['buyeruid'])){
				$detail = '';
				$memberinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where uid='".$order['buyeruid']."'   ");
					if($order['scoredown']> 0){
		             	$this->mysql->update(Mysite::$app->config['tablepre'].'member','`score`=`score`+'.$order['scoredown'],"uid ='".$order['buyeruid']."' ");
		             	$memberscs = $memberinfo['score']+$order['scoredown'];
		                $this->memberCls->addlog($order['buyeruid'],1,1,$order['scoredown'],'取消订单','用户关闭订单'.$order['dno'].'抵扣积分'.$order['scoredown'].'返回帐号',$memberscs);
		            }
	   	      }

			$this->success('操作成功');

		}else{
			$this->message('订单状态不可取消');
		}
	}
	/**
	 *  @brief 获取所有区域
	 *  
	 *  @return Return_Description
	 *  
	 *  @details Details
	 */
	function appallarea(){ 
		$arealist= $this->mysql->getarr("select id,name,parent_id,lat,lng from ".Mysite::$app->config['tablepre']."area  limit 0,1000 "); 
		$this->success($arealist); 
	}
	/**
	 *  @brief 调用远程打印机
	 *  
	 *  @return Return_Description
	 *  
	 *  @details Details
	 */ 
	function appprint(){
		$orderid = trim(IFilter::act(IReq::get('orderid')));
		if(empty($orderid)) $this->message('订单ID错误');
		$ordercode = trim(IFilter::act(IReq::get('ordercode')));
		$cfkey = trim(IFilter::act(IReq::get('cfkey')));
		$cfcode = trim(IFilter::act(IReq::get('cfcode')));
		$qtkey = trim(IFilter::act(IReq::get('qtkey')));
		$qtcode = trim(IFilter::act(IReq::get('qtcode')));


		$orderinfo =  $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."order  where id= '".$orderid."'   ");
		if(empty($orderinfo)) $this->message('订单信息为空');

			$orderdet =  $this->mysql->getarr("select *  from ".Mysite::$app->config['tablepre']."orderdet  where order_id= '".$orderid."'   ");
			$shopinfo =  $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."shop  where id= '".$orderinfo['shopid']."'   ");
			$payarrr = array('outpay'=>'到付','open_acout'=>'余额支付');
			$orderpastatus = $orderinfo['paystatus'] == 1?'已支付':'未支付';
			$orderpaytype = isset($payarrr[$orderinfo['paytype']])?$payarrr[$orderinfo['paytype']]:'在线支付';
			$temp_content = '';
			foreach($orderdet as $km=>$vc){
                $temp_content .= $vc['goodsname'].'('.$vc['goodscount'].'份) \n ';
			}
$msg = '商家:'.$shopinfo['shopname'].'
订餐热线:'.Mysite::$app->config['litel'].'
订单状态：'.$orderpaytype.',('.$orderpastatus.')
姓名：'.$orderinfo['buyername'].'
电话：'.$orderinfo['buyerphone'].'
地址：'.$orderinfo['buyeraddress'].'
下单时间：'.date('m-d H:i',$orderinfo['addtime']).'
配送时间:'.date('m-d H:i',$orderinfo['posttime']).'
*******************************
'.$temp_content.'
*******************************

配送费：'.$orderinfo['shopps'].'元
合计：'.$orderinfo['allcost'].'元
※※※※※※※※※※※※※※
谢谢惠顾，欢迎下次光临
订单编号'.$orderinfo['dno'].'
备注'.$orderinfo['content'].'
';
		$backdata = array('print_1'=>5,'print_2'=>5);
		if(!empty($cfcode)&&!empty($cfkey)){
			$backdata['print_1'] =  $this->dosengprint($msg,$cfcode,$cfkey);
	    }
	    if(!empty($qtcode)&&!empty($qtkey)){
			$backdata['print_2'] =  $this->dosengprint($msg,$qtcode,$qtkey);
	    }
	    /*
	    cfcode  0 发送成功 ,1发送到队列  2没找到MAC地址,403错误，,4链接出错
	    */
		$this->success($backdata); 
	}
	/**
	 *  @brief 获取店铺商品分类
	 *  
	 *  @return Return_Description
	 *  
	 *  @details Details
	 */
	function getshoptype(){ 
		$is_market = intval(IFilter::act(IReq::get('is_market')));
		$goodstype  = $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."shoptype  where parent_id = 0 and  is_search = 1  and cattype = ".$is_market." and type = 'checkbox'     ");
		if(empty($goodstype)){
			$this->success(array());
		}
		$goodstype = $this->mysql->getarr("select *  from ".Mysite::$app->config['tablepre']."shoptype where parent_id = '".$goodstype['id']."' ");
		$this->success($goodstype);
	}
	 /**
	 *  @brief 获取店铺及商品
	 *  
	 *  @return Return_Description
	 *  
	 *  @details Details
	 */
	function getshopnew(){
		$shopid = trim(IFilter::act(IReq::get('shopid')));
		if(empty($shopid)) $this->message('店铺数据获取失败'); 
		$shopinfo  = $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."shop  where id = '".$shopid."'    ");
		if(empty($shopinfo)) $this->message('店铺数据获取失败'); 
		if($shopinfo['shoptype'] == 0){
			$shopdet  = $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."shopfast  where shopid = '".$shopid."'    ");
		}else{
			$shopdet  = $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."shopmarket  where shopid = '".$shopid."'    ");
		}
		 
		if(empty($shopdet)) $this->message('店铺数未开启');
		$shop = array_merge($shopinfo,$shopdet);
		$data['ptypelist'] = array();
		if($shopinfo['shoptype'] == 0){
			$goodstype =  $this->mysql->getarr("select id,name from ".Mysite::$app->config['tablepre']."goodstype where shopid = ".$shopinfo['id']."   order by orderid asc");
		}else{
			$goodstype =	$this->mysql->getarr("select id,name,parent_id from ".Mysite::$app->config['tablepre']."marketcate where   shopid =".$shopinfo['id']."   order by orderid asc limit 0,100");
			 
		}
		 
		$goodsinfo = array();
		if(is_array($goodstype)){
			foreach($goodstype as $key=>$value){//id	typeid 商品类型	name 商品名称	count 商品数量	costimg 图片地址	pointbagcost
				if($shopinfo['shoptype'] == 0){ 
					$goodsdet = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goods where typeid = ".$value['id']."  and shopid =".$shopinfo['id']."  order by id asc");
					$temparr = array();
					foreach($goodsdet as $k=>$v){
						 
						$cxinfo = $this->goodscx($v);
						$v['is_cx'] = $cxinfo['is_cx'];
						$v['cost'] = $cxinfo['cxcost'];
						$v['zhekou'] = $cxinfo['zhekou'];  
						 
 					
						
						$v['img'] = empty($v['img'])?$v['img']:Mysite::$app->config['siteurl'].$v['img'];
						$v['product_attr'] = !empty($v['product_attr'])?unserialize($v['product_attr']):array();
						if(count($v['product_attr']) > 0){
							$temparray = array();
							foreach($v['product_attr'] as $m=>$e){
								$temparray[] = $e;
							}
							
							$v['product_attr'] = $temparray;
						}
						
						
						
						if($v['have_det'] ==1){
							$v['product'] = $this->mysql->getarr("select id,attrname,attrids,stock,cost  from ".Mysite::$app->config['tablepre']."product where goodsid = ".$v['id']."  and shopid =".$shopinfo['id']."  order by id asc");
						}else{
							$v['product'] = array(); 
						}
						 
						$temparr[] = $v;
					}
					$value['det'] = $temparr;
					 
					$goodsinfo[] = $value;
				}else{
					$ios = trim(IFilter::act(IReq::get('ios')));
					if($ios == 'marketos'){
						 
						if($value['parent_id'] > 0){
							$goodsdet = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goods where typeid = ".$value['id']."  and shopid =".$shopinfo['id']."   order by id asc ");
							$temparr = array();
							foreach($goodsdet as $k=>$v){
								$cxinfo = $this->goodscx($v);
							$v['is_cx'] = $cxinfo['is_cx'];
						$v['cost'] = $cxinfo['cxcost'];
						$v['zhekou'] = $cxinfo['zhekou'];  
								$v['img'] = empty($v['img'])?$v['img']:Mysite::$app->config['siteurl'].$v['img'];
								$v['product_attr'] = !empty($v['product_attr'])?unserialize($v['product_attr']):array();
								if(count($v['product_attr']) > 0){
									$temparray = array();
									foreach($v['product_attr'] as $m=>$e){
										$temparray[] = $e;
									}
									$v['product_attr'] = $temparray;
								}
								if($v['have_det'] ==1){
									$v['product'] = $this->mysql->getarr("select id,attrname,attrids,stock,cost  from ".Mysite::$app->config['tablepre']."product where goodsid = ".$v['id']."  and shopid =".$shopinfo['id']."  order by id asc");
								}else{
									$v['product'] = array(); 
								}
								$temparr[] = $v;
							}
							$value['det'] = $temparr;
							$goodsinfo[] = $value;
						}
					}else{
						if($value['parent_id'] == 0){
							$value['det'] = array();
							$goodsinfo[] = $value;
						}else{
							$goodsdet = $this->mysql->getarr("select id,typeid,name,count,cost,img,point,bagcost,is_cx,sellcount,shopid,have_det,product_attr from ".Mysite::$app->config['tablepre']."goods where typeid = ".$value['id']."  and shopid =".$shopinfo['id']."   order by id asc ");
							$temparr = array();
							foreach($goodsdet as $k=>$v){
								$cxinfo = $this->goodscx($v);
								$v['is_cx'] = $cxinfo['is_cx'];
						$v['cost'] = $cxinfo['cxcost'];
						$v['zhekou'] = $cxinfo['zhekou'];  
								$v['img'] = empty($v['img'])?$v['img']:Mysite::$app->config['siteurl'].$v['img'];
								$v['product_attr'] = !empty($v['product_attr'])?unserialize($v['product_attr']):array();
								if(count($v['product_attr']) > 0){
										$temparray = array();
										foreach($v['product_attr'] as $m=>$e){
											$temparray[] = $e;
										} 
										$v['product_attr'] = $temparray;
								}
								if($v['have_det'] ==1){
									$v['product'] = $this->mysql->getarr("select id,attrname,attrids,stock,cost  from ".Mysite::$app->config['tablepre']."product where goodsid = ".$v['id']."  and shopid =".$shopinfo['id']."  order by id asc");
								}else{
									$v['product'] = array(); 
								}
								$temparr[] = $v;
							}
							$value['det'] = $temparr;
							$goodsinfo[] = $value;
						}
					}
				}
			}
		}
		$backdata['goods'] = $goodsinfo;
		$this->success($goodsinfo); 
	}
	function goodsone(){//获取一个商品信息
		$goodsid = trim(IFilter::act(IReq::get('goodsid')));
		if(empty($goodsid)) $this->message('商品不存在'); 
		
		$goodsinfo = $this->mysql->select_one("select  * from ".Mysite::$app->config['tablepre']."goods where id = ".$goodsid."   order by id asc");
	    
		 $cxinfo = $this->goodscx($goodsinfo);
		$goodsinfo['is_cx'] = $cxinfo['is_cx'];
		$goodsinfo['cost'] = $cxinfo['cxcost'];
		$goodsinfo['zhekou'] = $cxinfo['zhekou'];
		if(empty($goodsinfo)) $this->message('商品不存在');  
		$shopid = $goodsinfo['shopid'];
		$shopinfo  = $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."shop  where id = '".$goodsinfo['shopid']."'    ");
		if(empty($shopinfo)) $this->message('店铺数据获取失败'); 
		if($shopinfo['shoptype'] == 0){
			$shopdet  = $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."shopfast  where shopid = '".$shopid."'    ");
		}else{
			$shopdet  = $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."shopmarket  where shopid = '".$shopid."'    ");
		}
        $goodsinfo['instro'] = strip_tags($goodsinfo['instro']);
		if(empty($shopdet)) $this->message('店铺数未开启');
		$shop = array_merge($shopinfo,$shopdet);
		 $goodsinfo['product_attr'] = !empty($goodsinfo['product_attr'])?unserialize($goodsinfo['product_attr']):array();
		if($goodsinfo['have_det'] ==1){
			if(count($goodsinfo['product_attr']) > 0){
				$temparray = array();
				foreach($goodsinfo['product_attr'] as $m=>$e){
						$temparray[] = $e;
				}
					$goodsinfo['product_attr'] = $temparray;
			}
			
			
			$goodsinfo['product'] = $this->mysql->getarr("select id,attrname,attrids,stock,cost  from ".Mysite::$app->config['tablepre']."product where goodsid = ".$goodsid."  and shopid =".$shopinfo['id']."  order by id asc");
			 
		
		
		
		}else{
			$goodsinfo['product'] = array(); 
		}
	    $goodsinfo['img'] = empty($goodsinfo['img'])?$goodsinfo['img']:Mysite::$app->config['siteurl'].$goodsinfo['img'];
	    			
		$list = $this->mysql->getarr("select a.*,b.username,b.logo,c.name from ".Mysite::$app->config['tablepre']."comment as a left join  ".Mysite::$app->config['tablepre']."member as b on a.uid = b.uid left join ".Mysite::$app->config['tablepre']."goods as c on a.goodsid = c.id  where a.goodsid=".$goodsid." and a.is_show  =0 order by a.id desc   limit 0,10");
		$backdata['comment'] = array();
		foreach($list as $key=>$value){
			$value['logo'] = empty($value['logo'])?Mysite::$app->config['siteurl'].Mysite::$app->config['userlogo']:Mysite::$app->config['siteurl'].$value['logo'];
			$value['addtime'] = date('Y-m-d',$value['addtime']);
			$backdata['comment'][] = $value;
		}
		$backdata['goods'] = $goodsinfo;
		$backdata['shopinfo'] = $shopdet;
		$this->success($backdata); 
	}
	//新店铺列表获取
	function newshop(){
		/*
		参数说明：
           开店类型：shopopentype    0  表示 获取外卖，订台 预订  1表示商城
           店铺类型：shoptype    1对应的店铺ID
           排序类型：ordertype 0默认  1距离  2起送  3推荐
           起送价：   0不限制   1低于 5元  2 5到10元     3   10元以上
           配送区域： areaid    表示配送ID；
           lng:lng坐标;
           lat:lat坐标;

           //shopopentype  0
           is_waimai  外送
           is_goshop  到店消费
           searvalue  起送价
		*/
		/*
			$backinfo = $this->checkappMem();
			$appuid = ICookie::get('appuid');
			if(empty($backinfo['uid'])){
			if(empty($appuid)){
				$this->message('nologin');
			}
		}  */
		//lat  lng
		//and  SQRT((`lat` -'.$lat.') * (`lat` -'.$lat.' ) + (`lng` -'.$lng.' ) * (`lng` -'.$lng.' )) < `pradius` '.$bili.'    //lat  lng   pradius

		$shopopentype = intval(IFilter::act(IReq::get('shopopentype'))); //0,1
		$ordertype = intval(IFilter::act(IReq::get('ordertype'))); //排序类型   0,1,2,3
		//shoptype
		$areaid = intval(IFilter::act(IReq::get('areaid')));//区域ID
		$limitcosttype = intval(IFilter::act(IReq::get('limitcosttype'))); //起送价格类型 0 1 2 3
		$is_waimai = intval(IFilter::act(IReq::get('is_waimai'))); //表示外送
		$is_goshop = intval(IFilter::act(IReq::get('is_goshop'))); //表示到店
		$searvalue = IFilter::act(IReq::get('searchvalue'));
		$lat = IFilter::act(IReq::get('lat'));
		$lng = IFilter::act(IReq::get('lng'));
		$goodstype = intval(IFilter::act(IReq::get('shoptype')));
		$is_com = intval(IFilter::act(IReq::get('is_com')));
		$is_hot = intval(IFilter::act(IReq::get('is_hot')));
		$is_new = intval(IFilter::act(IReq::get('is_new')));
		$lat = empty($lat)?0:$lat;
		$lng =empty($lng)?0:$lng;
		$orderarray = array(
			0 =>'order by  SQRT((`lat` -'.$lat.') * (`lat` -'.$lat.' ) + (`lng` -'.$lng.' ) * (`lng` -'.$lng.' )) asc',
			1=>' order by  SQRT((`lat` -'.$lat.') * (`lat` -'.$lat.' ) + (`lng` -'.$lng.' ) * (`lng` -'.$lng.' )) asc ',
			2=>' order by a.limitcost asc ',
			3=>' order by a.is_com desc '
		);
		$limitarray = array(
			0=>'',
			1=>' and a.limitcost < 6 ',
			2=>' and a.limitcost > 5 and a.limitcost < 11 ',
			3=>' and a.limitcost > 10  ',
		);
		$limitcosttype = in_array($limitcosttype,array(0,1,2,3))?$limitcosttype:0;
		$shopopentype = in_array($shopopentype,array(0,1))?$shopopentype:0;
		//注销掉按距离排序
		//$where = ' where is_open = 1 and endtime > '.time().' and  SQRT((`lat` -'.$lat.') * (`lat` -'.$lat.' ) + (`lng` -'.$lng.' ) * (`lng` -'.$lng.' )) < `pradius`*0.01094 ';
		$where =' where is_open = 1 ';
		if($areaid > 0){
			$where = " where is_open = 1 and b.id in(select shopid from ".Mysite::$app->config['tablepre']."areashop where areaid = ".$areaid." ) ";
		}
		$source =  intval(IFilter::act(IReq::get('source')));
		$ios_waiting =   Mysite::$app->config['ios_waiting'];
		if($source == 1 && $ios_waiting == true){
			
			$where = ' where is_open = 1 and endtime > '.time().' ';
		}
		if($is_waimai == 1){
			$where .= ' and a.is_waimai =  1 ';
		}
		if($is_goshop == 1){
			$where .= ' and a.is_goshop =  1 ';
		}
		if($is_com == 1){
			$where .= ' and is_com = 1';
		}
		if($is_hot == 1){
			//---
			$where .= ' and is_hot = 1';
		}
		if($is_new == 1){
			//---
			$where .= ' and is_new = 1';
		}
		/*
		is_hot	int(1)		 
		is_com	int(1)	 
		is_new
		
		*/
		$shoptype = $shopopentype;
		$where .= $limitarray[$limitcosttype];
		if($goodstype > 0){
			$where .= "  and b.id in((select shopid from ".Mysite::$app->config['tablepre']."shopattr where attrid = ".$goodstype." ))";
		}
		if(!empty($searvalue)){
			$where .= " and shopname like '%".$searvalue."%' ";
		} 
		$this->pageCls->setpage(intval(IReq::get('page')),5);
		if($shopopentype == 0){
			$list = $this->mysql->getarr("select a.shopid,b.id,b.shopname,b.is_open,b.starttime,b.pointcount as sellcount,lat,lng,a.is_orderbefore,a.limitcost,b.shoplogo,a.is_hot,a.is_com,a.is_new,a.maketime,a.pradius,b.lat,b.lng,a.sendtype,a.pscost,a.pradiusvalue,a.arrivetime,b.address,b.point  from ".Mysite::$app->config['tablepre']."shopfast as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id ".$where."   ".$orderarray[$ordertype]."   limit ".$this->pageCls->startnum().", ".$this->pageCls->getsize()."");
		}else{
			$list = $this->mysql->getarr("select a.shopid,b.id,b.shopname,b.is_open,b.starttime,b.pointcount as sellcount,lat,lng,a.is_orderbefore,a.limitcost,b.shoplogo,a.is_hot,a.is_com,a.is_new,a.maketime,a.pradius,b.lat,b.lng,a.sendtype,a.pscost,a.pradiusvalue,a.arrivetime,b.address,b.point  from ".Mysite::$app->config['tablepre']."shopmarket as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id ".$where."   ".$orderarray[$ordertype]."   limit ".$this->pageCls->startnum().", ".$this->pageCls->getsize()."");
		}
		$shopdata = array();
		$nowhour = date('H:i:s',time());
		$nowhour = strtotime($nowhour);
		$sellrule = new sellrule();
		foreach($list as $key=>$value){
			$juli = $this->GetDistance($lat, $lng, $value['lat'], $value['lng']);
			$sellrule->setdata($value['id'],1000,$shoptype);
			$rulist = $sellrule->get_rulelist();
			
			
			//array('1'=>'赠送','2'=>'减费用','3'=>'折扣','4'=>'免配送费')
			// $temprulelist = array();
			// foreach($rulist  as $k=>$v){
				// $temprulelist[$v['controltype']][] = $v['name'];
			// }
		    $tempruleids = array();
			$temprule = array();
			foreach($rulist as $k=>$v){
				if(!in_array($v['controltype'],$tempruleids)){
					$temprule[] = $v;
					$tempruleids[] = $v['controltype'];
				}
			}
			$value['cxinfo'] = $temprule;
			if($juli > 1000){
				$juli = $juli*0.001;
				$juli = round($juli,2);
				$value['juli'] =  $juli.'千米';//'未测距';
			}else{
				$value['juli'] =  $juli.'米';//'未测距
			}
			
			$value['pscost'] = '0';
			$value['canps'] = 0;
			$valuelist = empty($value['pradiusvalue'])? unserialize(Mysite::$app->config['radiusvalue']):unserialize($value['pradiusvalue']);
			$juliceshi = intval($juli/1000);
			if(is_array($valuelist)){
  	       	    foreach($valuelist as $k=>$v){
					if($juliceshi == $k){
						$value['pscost'] = $v;
						$value['canps'] = 1;
					}
  	       	    }
			}
			$source =  intval(IFilter::act(IReq::get('source')));
			$ios_waiting =   Mysite::$app->config['ios_waiting'];
			if($source == 1 && $ios_waiting == true){ 
				$value['canps'] = 1;
			}
			$value['opentype'] = '1';//1营业  0未营业
			$imgurl = empty($value['shoplogo'])? Mysite::$app->config['shoplogo']:$value['shoplogo'];
			$checkinfo = $this->shopIsopen($value['is_open'],$value['starttime'],$value['is_orderbefore'],$nowhour);

			if($checkinfo['opentype'] != 2 && $checkinfo['opentype'] != 3){
				$value['opentype'] = '0';
			}else{
				$value['opentype'] = $checkinfo['opentype'];
			}
			$checkstr =  $value['starttime'];
			$tempstr = array();
			if(!empty($checkstr)){
				$tempstr = explode('-',$checkstr);
			}
			$value['starttime'] = count($tempstr) > 0 ? $tempstr[0]:'';
			//$items['opentype'] != 2 && $items['opentype'] != 3

			$value['shopimg'] = Mysite::$app->config['siteurl'].$imgurl;
			//  unset($value['sendset']);
			$shopdata[] = $value;
		}
		$this->success($shopdata);
	}
	function newsearchshop(){
		$shopopentype = intval(IFilter::act(IReq::get('shopopentype'))); //0,1 
		$areaid = intval(IFilter::act(IReq::get('areaid'))); //0,1 
		$searchvalue = trim(IFilter::act(IReq::get('searchvalue')));
		$lat = IFilter::act(IReq::get('lat'));
		$lng = IFilter::act(IReq::get('lng')); 
		$lat = empty($lat)?0:$lat;
		$lng =empty($lng)?0:$lng;
		$orderarray = array(
			0 =>'order by  SQRT((`lat` -'.$lat.') * (`lat` -'.$lat.' ) + (`lng` -'.$lng.' ) * (`lng` -'.$lng.' )) asc',
			1=>' order by  SQRT((`lat` -'.$lat.') * (`lat` -'.$lat.' ) + (`lng` -'.$lng.' ) * (`lng` -'.$lng.' )) asc ' 
		);
		$limitarray = array(
			0=>'', 
		);  
		$where = ' where is_open = 1 and endtime > '.time().' and  SQRT((`lat` -'.$lat.') * (`lat` -'.$lat.' ) + (`lng` -'.$lng.' ) * (`lng` -'.$lng.' )) < `pradiusa`*0.01094 ';
		if($areaid > 0){
			$where = " where is_open = 1 and b.id in(select shopid from ".Mysite::$app->config['tablepre']."areashop where areaid = ".$areaid." ) ";
		}
		$source =  intval(IFilter::act(IReq::get('source')));
		$ios_waiting =   Mysite::$app->config['ios_waiting'];
		if($source == 1 && $ios_waiting == true){
			
			$where = ' where is_open = 1 and endtime > '.time().' ';
		}  
		if(empty($searchvalue)){
			$this->message('搜索关键字未空');
		}
		$where .= " and shopname like '%".$searchvalue."%' "; 
		$this->pageCls->setpage(intval(IReq::get('page')),5);
 
		$list = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shop  ".$where."   ".$orderarray[0]."   limit ".$this->pageCls->startnum().", ".$this->pageCls->getsize()."");
	 	 
		$shopdata = array();
		$nowhour = date('H:i:s',time());
		$nowhour = strtotime($nowhour);
		$sellrule = new sellrule();
		foreach($list as $key=>$value){  
			$newvalue['id'] = $value['id'];
			$newvalue['shopname'] = $value['shopname'];
			$newvalue['is_open'] = $value['is_open'];
			$newvalue['starttime'] = $value['starttime'];
			$newvalue['pointcount'] = $value['sellcount'];
			$newvalue['lat'] = $value['lat'];
			$newvalue['lng'] = $value['lng']; 
			$newvalue['shoplogo'] = $value['shoplogo']; 
			$newvalue['point'] = $value['point']; 
			$newvalue['address'] = $value['address']; 
			$newvalue['shoptype'] = $value['shoptype']; 
			$newvalue['instro'] = strip_tags($value['instro']);
			$delvalue = array();
			if($value['shoptype'] == 0){
				$delvalue  = $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."shopfast  where shopid = '".$value['id']."'    ");
			}else{
				$delvalue  = $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."shopmarket  where shopid = '".$value['id']."'    ");
			}
			if(isset($delvalue['shopid'])){ 
			        $cvalue= array_merge($newvalue,$delvalue); 
					
					
					$juli = $this->GetDistance($lat, $lng, $cvalue['lat'], $cvalue['lng']); 
					$sellrule->setdata($cvalue['id'],1000,$cvalue['shoptype']);
					$rulist = $sellrule->get_rulelist();
					
					
					//array('1'=>'赠送','2'=>'减费用','3'=>'折扣','4'=>'免配送费')
					// $temprulelist = array();
					// foreach($rulist  as $k=>$v){
						// $temprulelist[$v['controltype']][] = $v['name'];
					// }
					$tempruleids = array();
					$temprule = array();
					foreach($rulist as $k=>$v){
						if(!in_array($v['controltype'],$tempruleids)){
							$temprule[] = $v;
							$tempruleids[] = $v['controltype'];
						}
					}
					$cvalue['cxinfo'] = $temprule;
					$cvalue['juli'] =  $juli.'米';//'未测距';
					$cvalue['pscost'] = '0';
					$cvalue['canps'] = 0;
					$valuelist = empty($cvalue['pradiusvalue'])? unserialize(Mysite::$app->config['radiusvalue']):unserialize($cvalue['pradiusvalue']);
					$juliceshi = intval($juli/1000);
					if(is_array($valuelist)){
						foreach($valuelist as $k=>$v){
							if($juliceshi == $k){
								$cvalue['pscost'] = $v;
								$cvalue['canps'] = 1;
							}
						}
					}
					$source =  intval(IFilter::act(IReq::get('source')));
					$ios_waiting =   Mysite::$app->config['ios_waiting'];
					if($source == 1 && $ios_waiting == true){ 
						$cvalue['canps'] = 1;
					}
					$cvalue['opentype'] = '1';//1营业  0未营业
					$imgurl = empty($cvalue['shoplogo'])? Mysite::$app->config['shoplogo']:$cvalue['shoplogo'];
					$checkinfo = $this->shopIsopen($cvalue['is_open'],$cvalue['starttime'],$cvalue['is_orderbefore'],$nowhour);

					if($checkinfo['opentype'] != 2 && $checkinfo['opentype'] != 3){
						$cvalue['opentype'] = '0';
					}else{
						$cvalue['opentype'] = $checkinfo['opentype'];
					}
					$checkstr =  $cvalue['starttime'];
					$tempstr = array();
					if(!empty($checkstr)){
						$tempstr = explode('-',$checkstr);
					}
					$cvalue['starttime'] = count($tempstr) > 0 ? $tempstr[0]:'';  
					$cvalue['shopimg'] = Mysite::$app->config['siteurl'].$imgurl; 
					$shopdata[] = $cvalue;
			}
		}
		
		$data['shoplist'] = $shopdata; 
		$weekji = date('w'); 
		$goodwhere = '';  
		$goodssearch = $searchvalue;  
		if(!empty($goodssearch)) $goodlistwhere=" and name like '%".$goodssearch."%' ";  
		$goodwhere = empty($goodwhere)?'   and  SQRT((`lat` -'.$lat.') * (`lat` -'.$lat.' ) + (`lng` -'.$lng.' ) * (`lng` -'.$lng.' )) < (`pradiusa`*0.01094) ': $goodwhere.' and SQRT((`lat` -'.$lat.') * (`lat` -'.$lat.' ) + (`lng` -'.$lng.' ) * (`lng` -'.$lng.' )) < (`pradiusa`*0.01094) ';
            	 
		$list =   $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shop where is_pass = 1 ".$goodwhere." ");		
		$nowhour = date('H:i:s',time()); 
		$nowhour = strtotime($nowhour);
		$goodssearchlist = array(); 
				   
		if(is_array($list)){
			foreach($list as $keys=>$vatt){
				if($vatt['id'] > 0){
					if( $vatt['shoptype'] == 1 ){
							$shopdet = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket where  shopid = ".$vatt['id']."   ");
					}else{
							$shopdet = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast where  shopid = ".$vatt['id']."   ");
					}
					$checkinfo = $this->shopIsopen($vatt['is_open'],$vatt['starttime'],$shopdet['is_orderbefore'],$nowhour); 
					$opentype = 1;
					if($checkinfo['opentype'] != 2 && $checkinfo['opentype'] != 3){
						$opentype = 0;
					} 		 
					//"id":"3101","typeid":"248","parentid":"0","name":"\u81ca\u5b50\u9762","count":"400","cost":"16.00","img":"\/upload\/pliang\/20150829103116364.jpg","point":"7","sellcount":"16","shopid":"15","uid":"6","signid":"","pointcount":"1"
					$detaa = $this->mysql->getarr("select id,typeid,name,count,cost,img,is_cx,shopid,point,sellcount,instro from ".Mysite::$app->config['tablepre']."goods where shopid='".$vatt['id']."'  and shoptype = ".$vatt['shoptype']."  and    FIND_IN_SET( ".$weekji." , `weeks` )  ".$goodlistwhere."   order by good_order asc ");
						if(!empty($detaa)){ 
					
							foreach ( $detaa as $keyq=>$valq ){
									if($valq['is_cx'] == 1){
									//测算促销 重新设置金额
										$cxdata = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goodscx where goodsid=".$valq['id']."  ");
										$newdata = getgoodscx($valq['cost'],$cxdata);
										
										$valq['zhekou'] = $newdata['zhekou'];
										$valq['is_cx'] = $newdata['is_cx'];
										$valq['cost'] = $newdata['cost'];
									}
									$valq['opentype'] = $opentype;
									$valq['shoptype'] = $vatt['shoptype']; 
                                    $imgurl = empty($valq['img'])? Mysite::$app->config['shoplogo']:$valq['img'];
							        $valq['img'] = Mysite::$app->config['siteurl'].$imgurl;
									$valq['instro'] = strip_tags($valq['instro']);
									$temparray[] =$valq; 
									$vakk = $temparray;
							}
						
						}	
						$goodssearchlist = $vakk; 
					}
			   } 
		}
		$data['goodslist']   = empty($goodssearchlist)?array():$goodssearchlist; 
		$this->success($data);
	}
	public function searchhotkey(){
		
		$searchwords = Mysite::$app->config['searchwords'];
		$searchwords = empty($searchwords)?array():unserialize($searchwords);
		$temparray = array();
		if(is_array($searchwords)){
			foreach($searchwords as $key=>$value){
				$tempc = array();
				$tempc['id'] = $key;
				$tempc['name'] = $value;
				$temparray[] = $tempc;
			}
		}
		
		 $this->success($temparray);
	}
	
	/**
	 *  @brief 获取店铺信息
	 *  
	 *  @return Return_Description
	 *  
	 *  @details Details
	 */ 
	function newshopinfo(){
		$backinfo = $this->checkappMem();
		$shopid = trim(IFilter::act(IReq::get('shopid')));
		$lat = IFilter::act(IReq::get('lat'));
		$lng = IFilter::act(IReq::get('lng'));
		$lat = empty($lat)?0:$lat;
		$lng = empty($lng)?0:$lng;
		if(empty($shopid)) $this->message('店铺数据获取失败');

		$shopinfo  = $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."shop  where id = '".$shopid."'    ");
		$shopinfo['notice_info']= strip_tags($shopinfo['notice_info']);
		if(empty($shopinfo)) $this->message('店铺数据获取失败');
 
		if($shopinfo['shoptype'] == 0){
			$shopdet  = $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."shopfast  where shopid = '".$shopid."'    ");
		}else{
			$shopdet  = $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."shopmarket  where shopid = '".$shopid."'    ");
		}
		if(empty($shopdet)) $this->message('店铺数未开启');
		$shop = array_merge($shopinfo,$shopdet);
		//2015.3.23修改
		$nowhour = date('H:i:s',time());
		$nowhour = strtotime($nowhour);
		$checkinfo = $this->shopIsopen($shop['is_open'],$shop['starttime'],$shop['is_orderbefore'],$nowhour);
		$shop['opentype'] = $checkinfo['opentype']; 
			
		//2015.3.23修改		
		// unset($shop['intr_info']);
		// unset($shop['cx_info']);
		
		$shop['intr_info'] = strip_tags($shop['intr_info']);
		$shop['cx_info'] = strip_tags($shop['cx_info']);
		$imgurl = empty($shop['shoplogo'])? Mysite::$app->config['shoplogo']:$shop['shoplogo'];
		$shop['shopimg'] = Mysite::$app->config['siteurl'].$imgurl;
		$juli = $this->GetDistance($lat, $lng, $shop['lat'], $shop['lng']);

		$valuelist = empty($shop['pradiusvalue'])? unserialize(Mysite::$app->config['radiusvalue']):unserialize($shop['pradiusvalue']);
		$juliceshi = intval($juli/1000);
		$shop['baidupscost'] = '不在配送区域';
		if(is_array($valuelist)){
			foreach($valuelist as $k=>$v){
  	       	    if($juliceshi == $k){
					$shop['baidupscost'] = $v;
  	       	        $shop['canps'] = 1;
  	       	    }
  	       	}
		}
		$source =  intval(IFilter::act(IReq::get('source')));
		$ios_waiting =   Mysite::$app->config['ios_waiting'];
		if($source == 1 && $ios_waiting == true){
			$shop['baidupscost']  = $shop['pscost'];
		    $shop['canps'] = 1;
		}
		$shop['collect'] = 0;
		if($backinfo['uid'] > 0){ 
			$collect = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."collect where uid ='".$backinfo['uid']."'  and collectid = '".$shopid."' and collecttype  = 0  ");
			if(!empty($collect)){
				$shop['collect'] = 1;
			}
		}
		
		
		
		$cximglist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodssign  where  type = 'cx'  order by id asc limit 0,1000");
		$ruleimg = array();
		foreach($cximglist as $key=>$value){
		    $ruleimg[$value['id']] = $value['imgurl'];
		}
		$cxrule = $this->mysql->getarr("select name,signid  from ".Mysite::$app->config['tablepre']."rule  where shopid = '".$shopid."' and starttime < ".time()."	and endtime  > ".time()."  and  status =1   ");
		$cxinfo = array();
		foreach($cxrule as $key=>$value){
			$value['logo'] = isset($ruleimg[$value['signid']])?Mysite::$app->config['siteurl'].$ruleimg[$value['signid']]:'';
			$cxinfo[] = $value;
		}
		$data['shopinfo'] = $shop;
		$data['cx'] = $cxinfo;
		$this->success($data);
	}
	/**
	 *  @brief 获取店铺10个最近评价
	 *  
	 *  @return Return_Description
	 *  
	 *  @details Details
	 */
	function commentshop(){
		$shopid = intval(IFilter::act(IReq::get('shopid')));
		$list = $this->mysql->getarr("select a.*,b.username,b.logo,c.name from ".Mysite::$app->config['tablepre']."comment as a left join  ".Mysite::$app->config['tablepre']."member as b on a.uid = b.uid left join ".Mysite::$app->config['tablepre']."goods as c on a.goodsid = c.id  where a.shopid=".$shopid." and a.is_show  =0 order by a.id desc   limit 0,10");
		$backdata = array();
		foreach($list as $key=>$value){
			$value['logo'] = empty($value['logo'])?Mysite::$app->config['siteurl'].Mysite::$app->config['userlogo']:Mysite::$app->config['siteurl'].$value['logo'];
			$value['addtime'] = date('Y-m-d',$value['addtime']);
			$backdata[] = $value;
		}
		$this->success($backdata);
	}
	/**
	 *  @brief 获取兑换商品
	 *  
	 *  @return Return_Description
	 *  
	 *  @details Details
	 */
	function giftlist(){
		//获取所有礼品列表
		//score	title	content	typeid	sell_count 销售数量	stock 库存	img
		$page = intval(IFilter::act(IReq::get('page')));
		$pagesize = intval(IFilter::act(IReq::get('pagesize'))); 
		$pagesize = $pagesize > 0? $pagesize:10;
		
		$this->pageCls->setpage(intval(IReq::get('page')),$pagesize); 
		$cximglist = $this->mysql->getarr("select id,score,title,stock,img from ".Mysite::$app->config['tablepre']."gift  order by id asc limit ".$this->pageCls->startnum().", ".$this->pageCls->getsize()."");
		$backdata = array();
		foreach($cximglist as $key=>$value){
			$value['img'] =  empty($value['img'])?Mysite::$app->config['siteurl'].Mysite::$app->config['shoplogo']:Mysite::$app->config['siteurl'].$value['img'];
			$backdata[]= $value;
		}
		$this->success($backdata); 
	}
	/**
	 *  @brief 兑换礼品
	 *  
	 *  @return Return_Description
	 *  
	 *  @details Details
	 */ 
	function exchange(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}

		$lipin_id = intval(IReq::get('id'));
		if(empty($lipin_id)) $this->message("gift_empty");
		$lipininfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."gift where id ='".$lipin_id."'  order by id asc  ");
		if(empty($lipininfo)) $this->message("gift_empty");
		if($lipininfo['stock'] < 1)$this->message("gift_emptystock");
		$moren_addr = intval(IReq::get('address_id'));
		$shuliang = intval(IReq::get('shuliang'));


		if(empty($shuliang)) $this->message('礼品兑换数量错误');
		$data['address'] = IFilter::act(IReq::get('address'));
		$data['contactman'] = IFilter::act(IReq::get('contactname'));
		$data['telphone'] = IFilter::act(IReq::get('phone'));
		if(empty($data['address']))$this->message("emptyaddress");
		if(empty($data['contactman']))$this->message("emptycontact");
		if(empty($data['telphone']))$this->message("errphone");
	    $checkjifen = $lipininfo['score']*$shuliang;
	   	if($backinfo['score'] < $checkjifen)$this->message('member_scoredown');
	   	$ndata['score'] = $backinfo['score'] - $checkjifen;
	   	//更新用户积分
	     $this->mysql->update(Mysite::$app->config['tablepre'].'member',$ndata,"uid='".$backinfo['uid']."'");
	   	$data['giftid'] = $lipininfo['id'];
	   	$data['uid'] = $backinfo['uid'];
	   	$data['addtime'] = time();
	   	$data['status'] = 0;
	   	$data['count'] = $shuliang;
	   	$data['score'] = $checkjifen;
		$this->mysql->insert(Mysite::$app->config['tablepre'].'giftlog',$data);
		$this->memberCls->addlog($backinfo['uid'],1,2,$checkjifen,'兑换礼品','兑换'.$lipininfo['title'].'('.$shuliang.'件)减少'.$lipininfo['score'].'积分',$ndata['score']);
		//更新礼品表
		$lidata['stock'] =  $lipininfo['stock']-$shuliang;
		$lidata['sell_count'] =  $lipininfo['sell_count']+$shuliang;
	   	$this->mysql->update(Mysite::$app->config['tablepre'].'gift',$lidata,"id='".$lipin_id."'");
	    $this->success('success');
	}
    /**
     *  @brief 兑换记录
     *  
     *  @return Return_Description
     *  
     *  @details Details
     */ 
	function exgiftlog(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$page = intval(IFilter::act(IReq::get('page')));
		$pagesize = intval(IFilter::act(IReq::get('pagesize'))); 
		$pagesize = $pagesize > 0? $pagesize:10;
		
		$this->pageCls->setpage(intval(IReq::get('page')),$pagesize);  
		$backdata = array();
		 
		$lipinlist = $this->mysql->getarr("select a.*,b.title,b.img from ".Mysite::$app->config['tablepre']."giftlog  as a left join ".Mysite::$app->config['tablepre']."gift as b on a.giftid = b.id  where a.uid ='".$backinfo['uid']."'   order by a.id desc limit ".$this->pageCls->startnum().", ".$this->pageCls->getsize()." ");
		foreach($lipinlist as $key=>$value){
			$value['img'] =  empty($value['img'])?Mysite::$app->config['siteurl'].Mysite::$app->config['shoplogo']:Mysite::$app->config['siteurl'].$value['img'];
			$value['addtime'] = date('Y-m-d',$value['addtime']);
			$backdata[]= $value;
		}
		$this->success($backdata);
	}
	/**
	 *  @brief 礼品操作
	 *  
	 *  @return Return_Description
	 *  
	 *  @details Details
	 */
	function controlgift(){
		//http://192.168.0.109/index.php?ctrl=app&action=exchange&id=2&controlname=colse&uid=1&pwd=waimairen&datatype=json
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		} 
		$id = intval(IReq::get('id'));
		$controlname = IFilter::act(IReq::get('controlname'));
		if(empty($id)) $this->message('gift_emptygiftlog');
		$info  = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."giftlog where uid ='".$backinfo['uid']."' and id=".$id." ");
		if(empty($info)) $this->message('gift_emptygiftlog');

		$lipininfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."gift where id ='".$info['giftid']."'  order by id asc  ");
		if($controlname == "colse"){
			if($info['status'] != 0)$this->message('gift_cantlogun');
			$titles = isset($lipininfo['title'])? $lipininfo['title']:$info['id'];
			$this->mysql->update(Mysite::$app->config['tablepre'].'giftlog',array('status'=>'4'),"id='".$id."'");
			$ndata['score'] = $backinfo['score'] + $info['score'];
		      //更新用户积分
	        $this->mysql->update(Mysite::$app->config['tablepre'].'member','`score` = `score`+'.$info['score'],"uid='".$backinfo['uid']."'");
	        	//写消息
	        $this->memberCls->addlog($backinfo['uid'],1,1,$info['score'],'取消兑换礼品','取消兑换ID为:'.$id.'的礼品['.$titles.'],帐号积分'.$ndata['score'] ,$ndata['score'] );

			$lidata['stock'] =  $lipininfo['stock']+$info['count'];
			$lidata['sell_count'] =  $lipininfo['sell_count']-$info['count'];
			$this->mysql->update(Mysite::$app->config['tablepre'].'gift',$lidata,"id='".$info['giftid']."'");
	        $this->success('success');
		}else{
	 	    if($info['status'] < 4) $this->message('礼品兑换记录状态不可删除');
	 	    $this->mysql->delete(Mysite::$app->config['tablepre'].'giftlog',"id in($id)");
			$this->success('success');
		}
	}
	/**
	 *  @brief 优惠卷列表
	 *  
	 *  @return Return_Description
	 *  
	 *  @details Details
	 */
	function myjuan(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$page = intval(IFilter::act(IReq::get('page')));
		$pagesize = intval(IFilter::act(IReq::get('pagesize'))); 
		$pagesize = $pagesize > 0? $pagesize:10;
		
		$this->pageCls->setpage(intval(IReq::get('page')),$pagesize);  
		// 状态，0未使用，1已绑定，2已使用，3无效	 制造时间	 优惠金额	 购物车限制金额下限	 失效时间	uid 用户ID	username 用户名	usetime 使用时间	name
		$cximglist = $this->mysql->getarr("select id,status,creattime,endtime,cost,name,limitcost from ".Mysite::$app->config['tablepre']."juan where uid =".$backinfo['uid']." order by id desc   limit ".$this->pageCls->startnum().", ".$this->pageCls->getsize()."");
		$backdata = array();
		//status 状态，0未使用，1已绑定，2已使用，3无效
		foreach($cximglist as $key=>$value){
			if($value['endtime'] < time()){
				$value['status'] = $value['status'] < 2?3:$value['status'];
			}
			$value['endtime'] = date('Y-m-d',$value['endtime']);
			$backdata[] = $value;
		}
		$this->success($backdata);
	}
	/**
	 *  @brief 绑定优惠券
	 *  
	 *  @return Return_Description
	 *  
	 *  @details Details
	 */
	function savejuan(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$card = trim(IFilter::act(IReq::get('card')));
		$password = trim(IFilter::act(IReq::get('cardpwd')));
		if(empty($card)) $this->message('card_emptyjuancard');
		if(empty($password)) $this->message('card_emptyjuanpwd');
		$checkinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."juan where card ='".$card."'  and card_password = '".$password."' and endtime > ".time()." and status = 0");
		if(empty($checkinfo)) $this->message('card_emptyjuan');
		if($checkinfo['uid'] > 0) $this->message('card_juanisuse');

		$arr['uid'] = $backinfo['uid'];
		$arr['status'] =  1;
		$arr['username'] = $backinfo['username'];
		$this->mysql->update(Mysite::$app->config['tablepre'].'juan',$arr,"card='".$card."'  and card_password = '".$password."' and endtime > ".time()." and status = 0 and uid = 0");
		$mess['userid'] = $backinfo['uid'];
		$mess['username']  ='';
		$mess['content'] = '绑定优惠劵'.$checkinfo['card'];
		$mess['addtime'] = time();
		//$this->mysql->insert(Mysite::$app->config['tablepre'].'message',$mess);  //写消息表
		$this->success('success');
	}
	//添加收藏
	function addcollect(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$collectid = intval(IFilter::act(IReq::get('collectid')));
		$collecttype = intval(IFilter::act(IReq::get('collecttype')));
		$collect = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."collect where uid ='".$backinfo['uid']."'  and collectid = '".$collectid."' and collecttype  = '".$collecttype."'  ");
		if(!empty($collectinfo)) $this->message('已收藏该店铺');
		$data['collectid'] = $collectid;
		$data['collecttype'] = $collecttype;
		$data['uid'] = $backinfo['uid'];
		
		if($collecttype == 1){
			$goodsinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goods where id ='".$collectid."'    ");
		    if(empty($goodsinfo)) $this->message('商品不存在');
			$data['shopuid'] = $goodsinfo['uid'];
		}else{
			$shopinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where id ='".$collectid."'    ");
		    if(empty($shopinfo))  $this->message('店铺不存在');
			$data['shopuid'] = $shopinfo['uid'];
		}
		$this->mysql->insert(Mysite::$app->config['tablepre'].'collect',$data);
		$this->success('success!'); 
	}
	function delcollect(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		 
		$collectid = intval(IFilter::act(IReq::get('collectid')));
		$collecttype = intval(IFilter::act(IReq::get('collecttype')));
		$collect = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."collect where uid ='".$backinfo['uid']."'  and collectid = '".$collectid."' and collecttype  = '".$collecttype."'  ");
		if(empty($collect)) $this->message('未收藏');
		 
	    $this->mysql->delete(Mysite::$app->config['tablepre'].'collect',"uid ='".$backinfo['uid']."' and collectid = '".$collectid."' and collecttype  = '".$collecttype."' ");   
	 	$this->success('success!');  
	}
	/**
	 *  @brief 通过店铺ID集获取店铺信息
	 *  
	 *  @return Return_Description
	 *  
	 *  @details Details
	 */
	function collectshop(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
	   $templist = $this->mysql->getarr("select *  from ".Mysite::$app->config['tablepre']."collect  where uid ='".$backinfo['uid']."' and collecttype  = 0   ");  
	   if(count($templist) == 0) {
		   $this->success(array());
	   }
	   $tempids = array();
        foreach($templist as $key=>$value){
		  $tempids[] = $value['collectid'];
	   } 
	   if(count($tempids)> 0){
		   $where = " where id in(".join(',',$tempids).") ";
	   }else{
		    $this->success(array());
	   } 
	   $this->pageCls->setpage(intval(IReq::get('page')),100);  
	   $list = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shop  ".$where."      limit ".$this->pageCls->startnum().", ".$this->pageCls->getsize()."");
	 	 $lat = IFilter::act(IReq::get('lat'));
		  $lng = IFilter::act(IReq::get('lng'));
		$shopdata = array();
		$nowhour = date('H:i:s',time());
		$nowhour = strtotime($nowhour);
		$sellrule = new sellrule();
		foreach($list as $key=>$value){  
			$newvalue['id'] = $value['id'];
			$newvalue['shopname'] = $value['shopname'];
			$newvalue['is_open'] = $value['is_open'];
			$newvalue['starttime'] = $value['starttime'];
			$newvalue['pointcount'] = $value['sellcount'];
			$newvalue['lat'] = $value['lat'];
			$newvalue['lng'] = $value['lng']; 
			$newvalue['shoplogo'] = $value['shoplogo']; 
			$newvalue['point'] = $value['point']; 
			$newvalue['address'] = $value['address']; 
			$newvalue['shoptype'] = $value['shoptype']; 
			$newvalue['sellcount'] = $value['sellcount']; 
			$delvalue = array();
			if($value['shoptype'] == 0){
				$delvalue  = $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."shopfast  where shopid = '".$value['id']."'    ");
			}else{
				$delvalue  = $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."shopmarket  where shopid = '".$value['id']."'    ");
			}
			if(isset($delvalue['shopid'])){ 
			        $cvalue= array_merge($newvalue,$delvalue); 
					
					
					$juli = $this->GetDistance($lat, $lng, $cvalue['lat'], $cvalue['lng']); 
					$sellrule->setdata($cvalue['id'],1000,$cvalue['shoptype']);
					$rulist = $sellrule->get_rulelist();
					
					
					//array('1'=>'赠送','2'=>'减费用','3'=>'折扣','4'=>'免配送费')
					// $temprulelist = array();
					// foreach($rulist  as $k=>$v){
						// $temprulelist[$v['controltype']][] = $v['name'];
					// }
					$tempruleids = array();
					$temprule = array();
					foreach($rulist as $k=>$v){
						if(!in_array($v['controltype'],$tempruleids)){
							$temprule[] = $v;
							$tempruleids[] = $v['controltype'];
						}
					}
					$cvalue['cxinfo'] = $temprule;
					$cvalue['juli'] =  $juli.'米';//'未测距';
					$cvalue['pscost'] = '0';
					$cvalue['canps'] = 0;
					$valuelist = empty($cvalue['pradiusvalue'])? unserialize(Mysite::$app->config['radiusvalue']):unserialize($cvalue['pradiusvalue']);
					$juliceshi = intval($juli/1000);
					if(is_array($valuelist)){
						foreach($valuelist as $k=>$v){
							if($juliceshi == $k){
								$cvalue['pscost'] = $v;
								$cvalue['canps'] = 1;
							}
						}
					}
					$source =  intval(IFilter::act(IReq::get('source')));
					$ios_waiting =   Mysite::$app->config['ios_waiting'];
					if($source == 1 && $ios_waiting == true){ 
						$cvalue['canps'] = 1;
					}
					$cvalue['opentype'] = '1';//1营业  0未营业
					$imgurl = empty($cvalue['shoplogo'])? Mysite::$app->config['shoplogo']:$cvalue['shoplogo'];
					$checkinfo = $this->shopIsopen($cvalue['is_open'],$cvalue['starttime'],$cvalue['is_orderbefore'],$nowhour);

					if($checkinfo['opentype'] != 2 && $checkinfo['opentype'] != 3){
						$cvalue['opentype'] = '0';
					}else{
						$cvalue['opentype'] = $checkinfo['opentype'];
					}
					$checkstr =  $cvalue['starttime'];
					$tempstr = array();
					if(!empty($checkstr)){
						$tempstr = explode('-',$checkstr);
					}
					$cvalue['starttime'] = count($tempstr) > 0 ? $tempstr[0]:'';  
					$cvalue['shopimg'] = Mysite::$app->config['siteurl'].$imgurl; 
					$shopdata[] = $cvalue;
			}
		}
	   
	    $this->success($shopdata);
	}
	/**
	 *  @brief 用户最近2周订单记录
	 *  
	 *  @return Return_Description
	 *  
	 *  @details Details
	 */
	function neworder(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		 
		$ordershowtype = intval(IFilter::act(IReq::get('ordershowtype')));
		$where = "";
		if($ordershowtype > 0){
			//1 外卖
			if($ordershowtype == 1){
				$where = " and shoptype = 0 and is_goshop =0 ";
			}elseif($ordershowtype ==2){
				$where = "  and shoptype = 0 and is_goshop =1  ";
			}elseif($ordershowtype == 3){
				$where = " and shoptype = 1";
			}
			// 2预订
			// 3超市
		}
		$pagesize = intval(IFilter::act(IReq::get('pagesize')));
		$pagesize = empty($pagesize)?10:$pagesize;
		$this->pageCls->setpage(intval(IReq::get('page')),$pagesize);
		$statusarr = array('0'=>'新订单','1'=>'待发货','2'=>'待确认','3'=>'已完成','4'=>'关闭','5'=>'关闭'); 
		$goshoparr = array('0'=>'新订单','1'=>'等待消费','2'=>'等待消费','3'=>'已完成','4'=>'关闭','5'=>'关闭');
		/* 获取订单数:   shopname  id  shoplogo  allcost addtime  status paystatus   paytype  */
		$nowtime = time()-14*24*60*60;
		$orderlist = $this->mysql->getarr("select id,shopname,shopid,allcost,addtime,status,paystatus,paytype,is_reback,is_goshop,is_make,is_ping,pstype from ".Mysite::$app->config['tablepre']."order where  buyeruid = ".$backinfo['uid']." ".$where." order by id desc  limit ".$this->pageCls->startnum().", ".$this->pageCls->getsize()."");
		if(empty($orderlist)) $this->success(array());
		$backdata = array();
		//status 状态，0未使用，1已绑定，2已使用，3无效
		foreach($orderlist as $key=>$value){
			$shoptemp = $this->mysql->select_one("select shoplogo from ".Mysite::$app->config['tablepre']."shop where id = ".$value['shopid']."");
			$imgurl = empty($shoptemp['shoplogo'])? Mysite::$app->config['shoplogo']:$shoptemp['shoplogo'];
			$value['shoplogo'] =  Mysite::$app->config['siteurl'].$imgurl;
			$value['addtime'] = date('Y-m-d H:i:s',$value['addtime']);
			if($value['is_goshop'] == 1){
				$value['seestatus'] = isset($goshoparr[$value['status']])?$goshoparr[$value['status']]:'';
			}else{
				$value['seestatus'] = isset($statusarr[$value['status']])?$statusarr[$value['status']]:'';
			}
			
			$backdata[] = $value;
		}
		$this->success($backdata);
	}
	/**
	 *  @brief 订单详情
	 *  
	 *  @return Return_Description
	 *  
	 *  @details Details
	 */ 
	function neworderdet(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$orderid = IFilter::act(IReq::get('orderid'));
		if(empty($orderid)) $this->message('订单获取失败');
		$orderlist = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where id=".$orderid." and buyeruid = ".$backinfo['uid']." order by id desc limit 0,20");
		if(empty($orderlist)) $this->message('订单为空');
		$orderdet =$this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."orderdet where order_id=".$orderid." order by id desc limit 0,20");
		
		$checksend = Mysite::$app->config['man_ispass']; 
		if($checksend != 1){ 
			if($orderlist['paytype'] == 0){ 
			    if($orderlist['is_print'] == 0){
					//更新数据
					$cdata['is_print'] = 1;
					$this->mysql->update(Mysite::$app->config['tablepre'].'order',$cdata,"id='".$orderid."'");
					$orderclass = new orderclass();
					$orderclass->sendmess($orderid); 
				}
			}else{
				if($orderlist['paystatus'] == 1){
					if($orderlist['is_print'] == 0){
						//更新数据
						$cdata['is_print'] = 1;
						$this->mysql->update(Mysite::$app->config['tablepre'].'order',$cdata,"id='".$orderid."'");
						$orderclass = new orderclass();
						$orderclass->sendmess($orderid); 
					}
				}
			}
		} 
		
		
		 
		
		$orderlist['addtime'] = date('Y-m-d H:i',$orderlist['addtime']);
		$orderlist['posttime'] = date('Y-m-d',$orderlist['posttime']).' '.$orderlist['postdate'];
		$orderlist['sendtime'] = date('Y-m-d H:i',$orderlist['sendtime']);
		$orderlist['suretime'] = date('Y-m-d H:i',$orderlist['suretime']);
		$orderlist['passtime'] = date('Y-m-d H:i',$orderlist['passtime']);
		//id	dno 订单编号	shopuid 店铺UID	shopid 店铺ID	shopname 店铺名称	shopphone 店铺电话	shopaddress 店铺地址	buyeruid 购买用户ID，0未注册用户	buyername 购买热名称	buyeraddress 联系地址
		//	buyerphone 联系电话	status 状态	paytype 支付类型outpay货到支付，open_acout账户余额支付，other调用paylist	paystatus 支付状态1已支付	content 订单备注	ordertype 订餐方式1网站，2电话，3微信，4App	daycode 当天订单序
		//号	area1 二级地址名称	area2 三级地址名称	area3	cxids 促销规则ID集	cxcost 店铺促销优惠金额	yhjcost 优惠劵优惠金额	yhjids 使用优惠劵ID集	ipaddress	scoredown 积分抵扣数	is_ping 是否评价字段 1已评完 0未评	isbefore 0:普通订单，1订台订单
		//marketcost 超市商品总价	marketps 超市配送配送	shopcost 店铺商品总价	shopps 店铺配送费	pstype 配送方式 0：平台1：个人	bagcost 打包费	addtime 制造时间	posttime 配送时间	passtime 审核时间	sendtime 发货时间	suretime 递增 完成时间
		//	allcost 订单实收费	buycode 订台码	othertext 其他说明	is_print	wxstatus 1商家确认，2商家取消	shoptype	is_make	is_reback	areaids	psuid	psusername	psemail	admin_id	is_goshop 0:外送 1订台/到店消费/自取

		//is_reback   is_print  is_goshop  status   paystatus     id,dno
		$ctaiarr = array();
		 $tctaiarr  =  $this->mysql->getarr("select statustitle as name , ststusdesc as  miaoshu,addtime  from ".Mysite::$app->config['tablepre']."orderstatus where orderid=".$orderid." order by addtime asc  limit 0,20");
		if(is_array($tctaiarr)){
			foreach($tctaiarr as $key=>$value){
				$value['time'] = date('m-d H:i',$value['addtime']);
				$ctaiarr[] = $value;
			}
		}
		
		 

		$instrolist = array(); 
		//订单类型
		if( $orderlist['is_goshop'] == 1 ){
			$backdata['ordertype'] = '到店消费';
		}elseif($orderlist['shoptype'] == 100){
			$backdata['ordertype'] = '跑腿';
		}elseif($orderlist['shoptype'] == 1){
			$backdata['ordertype'] = '超市';
		}else{
			$backdata['ordertype'] = '外卖';
		}
		 
	 
		$payarrr = array('0'=>'到付','1'=>'在线支付');
		$orderpastatus = $orderlist['paystatus'] == 1?'已支付':'未支付';
		$orderpaytype = isset($payarrr[$orderlist['paytype']])?$payarrr[$orderlist['paytype']]:'在线支付';
		//支付状态
		$backdata['paystatusintro'] = $orderpaytype.'('.$orderpastatus.')'; 
		//商品总价
		$backdata['shopcost'] = $orderlist['shopcost'];
		
		//配送费
		$backdata['shopps'] = $orderlist['shopps']; 
		//促销优惠
		$backdata['cxcost'] = $orderlist['yhjcost']+$orderlist['cxcost'];
		 
		//订单总价
		$backdata['allcost'] = $orderlist['allcost']; 
        $backdata['shoptype'] = $orderlist['shoptype'];
		$backdata['is_goshop'] = $orderlist['is_goshop'];
		$backdata['shoptype'] = $orderlist['shoptype'];
		$backdata['posttime'] = $orderlist['posttime']; 
		$backdata['buyername'] = $orderlist['buyername']; 
		$backdata['buyerphone'] = $orderlist['buyerphone']; 
		$backdata['buyeraddress'] = $orderlist['buyeraddress']; 
		$backdata['shopid'] = $orderlist['shopid'];
		$backdata['id'] =  $orderlist['id'];
		
		$backdata['shopname'] = $orderlist['shopname']; 
		$backdata['shopaddress'] = $orderlist['shopaddress']; 
		$backdata['shopphone'] = $orderlist['shopphone']; 
		$backdata['content'] = $orderlist['content'];  
		$backdata['dno'] =  $orderlist['dno'];
		$backdata['addtime'] = $orderlist['addtime'];
		$backdata['posttime'] = $orderlist['posttime'];
		$backdata['sendtime'] = $orderlist['sendtime'];
		$backdata['suretime'] = $orderlist['suretime'];
		$backdata['passtime'] = $orderlist['passtime'];
		$backdata['shopname'] = $orderlist['shopname'];
		$backdata['statuslist'] = $ctaiarr;
		 
		
		$backdata['is_reback'] = $orderlist['is_reback'];
		$backdata['is_print'] = $orderlist['is_print'];
		$backdata['is_goshop'] = $orderlist['is_goshop'];
		$backdata['status'] = $orderlist['status'];
		$backdata['is_make'] = $orderlist['is_make']; 
		$backdata['paystatus'] = $orderlist['paystatus'];
		$backdata['paytype'] = $orderlist['paytype'];
		$backdata['is_ping'] = $orderlist['is_ping'];
		$backdata['shopphone'] = $orderlist['shopphone']; 
		$backdata['id'] =  $orderlist['id'];
		if($backdata['shopcost'] > 0){
			$temp['id'] = 0;
			$temp['order_id'] = 0;
			$temp['goodsid'] = 0;
			$temp['goodsname'] = '商品总价';
			$temp['goodscost'] = $backdata['shopcost'];
			$temp['goodscount'] = 0;
			$temp['status'] = 0;
			$temp['is_send'] = 0;
			$temp['shopid'] = 0;
			$orderdet[] = $temp;
		}
		
		//neworderdet  
		if($backdata['shopps'] > 0){
			$temp['id'] = 0;
			$temp['order_id'] = 0;
			$temp['goodsid'] = 0;
			$temp['goodsname'] = '配送费';
			$temp['goodscost'] = $backdata['shopps'];
			$temp['goodscount'] = 0;
			$temp['status'] = 0;
			$temp['is_send'] = 0;
			$temp['shopid'] = 0;
			$orderdet[] = $temp;
		}
		if($backdata['cxcost'] > 0){
			$temp['id'] = 0;
			$temp['order_id'] = 0;
			$temp['goodsid'] = 0;
			$temp['goodsname'] = '优惠金额';
			$temp['goodscost'] = $backdata['cxcost'];
			$temp['goodscount'] = 0;
			$temp['status'] = 0;
			$temp['is_send'] = 0;
			$temp['shopid'] = 0;
			$orderdet[] = $temp;
		}
		if($backdata['allcost'] > 0){
			$temp['id'] = 0;
			$temp['order_id'] = 0;
			$temp['goodsid'] = 0;
			$temp['goodsname'] = '订单实价';
			$temp['goodscost'] = $backdata['allcost'];
			$temp['goodscount'] = 0;
			$temp['status'] = 0;
			$temp['is_send'] = 0;
			$temp['shopid'] = 0;
			$orderdet[] = $temp;
		}
		
		
		
		 	$backdata['gdlist'] = $orderdet;
	
		$this->success($backdata);
	}
	/**
	 *  @brief 买家关闭和完成订单
	 *  
	 *  @return Return_Description
	 *  
	 *  @details Details
	 */
	 
	 
	 
	
	function userdelorder(){
		$this->checkmemberlogin();
		$orderid = intval(IReq::get('orderid'));
		$ordercontrol = new ordercontrol($orderid);
		if(empty($this->member['uid'])) $this->message('member_nologin');
		if($ordercontrol->buyerdelorder($this->member['uid']))
		{
				$this->success('success');
		}else{
				$this->message($ordercontrol->Error());
		}
	}
	 
	 
	function newordercontrol(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$orderid = IFilter::act(IReq::get('orderid'));
        $orderinfo  = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where id='".$orderid."'  ");
        if(empty($orderinfo)){
			$this->message('订单不存在');
		}
		$doname = IFilter::act(IReq::get('doname')); 
		if($doname == 'closeorder'){
		   	$ordercontrol = new ordercontrol($orderid);
		    
			if($ordercontrol->buyerunorder($backinfo['uid']))
			{		
				   $ordCls = new orderclass();
				   if($orderinfo['paytype'] == 0){
						$ordCls->writewuliustatus($orderinfo['id'],12,$orderinfo['paytype']);  // 用户取消订单
				   }
				    $this->mysql->delete(Mysite::$app->config['tablepre'].'orderps',"orderid = '$orderid'");  //写配送订单  
					$this->success('success');
			}else{
					$this->message($ordercontrol->Error());
			}
		}elseif($doname == 'sureorder'){
			$ordercontrol = new ordercontrol($orderid); 
			if($ordercontrol->acceptorder($backinfo['uid']))
			{
					$this->success('success');
			}else{
					$this->message($ordercontrol->Error());
			}
		}elseif($doname =='reback'){  
			$allcost =  $orderinfo['allcost'];  
			$data['reason'] = trim(IFilter::act(IReq::get('reason'))); //退款原因
			$data['content'] = trim(IFilter::act(IReq::get('content'))); //退款详细内容说明
			$typeid = intval(IFilter::act(IReq::get('typeid'))); //支付类型  0支付宝  1  账号余额 
			if(empty($data['reason']))  $this->message('退款原因错误'.$data['reason']); 
			if($orderinfo['allcost'] != $allcost ) $this->message('退款金额错误'); 
			if($orderinfo['buyeruid'] != $backinfo['uid']) $this->message('购买用户和用户不一致'); 
			if($orderinfo['paystatus'] != 1)$this->message('订单未支付');
			if($orderinfo['status'] < 1 && $orderinfo['status'] < 3)$this->message('订单状态不能申请退款');
		 
			if($orderinfo['paytype'] == 0||empty($orderinfo['paytype'])) $this->message('订单不是在线支付');
			if(!empty($orderinfo['is_reback'])) $this->message('已申请退款');
		 
			if(!empty($orderinfo['content'])) $this->message('请填写退款详细内容说明');
		  
	 
			$checklog = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."drawbacklog where orderid='".$orderinfo['id']."' "); 
			if(!empty($checklog)) $this->message('已申请退款');
			$data['orderid'] = $orderinfo['id'];
			$data['shopid'] = $orderinfo['shopid'];
			$data['uid'] = $backinfo['uid'];
			$data['username'] = $backinfo['username'];
			$data['status'] = 0;
			$data['addtime'] = time();
			$data['cost'] = $orderinfo['allcost'];
			$data['admin_id'] = $orderinfo['admin_id'];
			$data['type'] = $typeid;
			$this->mysql->insert(Mysite::$app->config['tablepre'].'drawbacklog',$data);   
			$udata['is_reback'] = 1;
			$this->mysql->update(Mysite::$app->config['tablepre'].'order',$udata,"id='".$orderinfo['id']."'"); 
			$ordCls = new orderclass();
			$ordCls->writewuliustatus($orderinfo['id'],13,$orderinfo['paytype']);  // 管理员 操作配送发货
		 
			$this->success('success');  
		}elseif($doname == 'delorder'){//删除订单
			if($orderinfo['buyeruid'] != $backinfo['uid']) $this->message('购买用户和用户不一致'); 
		    if($orderinfo['status'] < 4) $this->message('订单不能删除');
			$this->mysql->delete(Mysite::$app->config['tablepre'].'order',"id='".$orderinfo['id']."'");   
			$this->mysql->delete(Mysite::$app->config['tablepre'].'orderdet',"order_id='".$orderinfo['id']."'"); 
		    $this->success('success');  
		}
	}
	function rebackreason(){
		 
		$templist = Mysite::$app->config['drawsmlist'];
		$templist = empty($templist)?array():unserialize($templist);
		$drawsmlist = array();
		if(is_array($templist)){
			foreach($templist as $key=>$value){
				$tempc=array();
				$tempc['id'] = $key;
				$tempc['name'] = $value;
				$drawsmlist[] = $tempc;
			}
		} 
		$this->success($drawsmlist);
	}
	/**
	 *  @brief 获取订单评价信息
	 *  
	 *  @return Return_Description
	 *  
	 *  @details Details
	 */
	function neworderpinglist(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$orderid = IFilter::act(IReq::get('orderid'));
		$orderinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where id=".$orderid." and buyeruid = ".$backinfo['uid']." order by id desc limit 0,20");
		if(empty($orderinfo)) $this->message('订单为空');
		if($orderinfo['status'] != 3) $this->message('订单未完成不可评价，请先完成');
		$orderdet =$this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."orderdet where order_id=".$orderid." order by id desc limit 0,20");
		if(empty($orderdet)){
			$data['is_ping'] = 1;
			$this->mysql->update(Mysite::$app->config['tablepre'].'order',$data,"id='".$orderinfo['id']."'");
			$this->message('预订座位订单不无商品不可评价');
		}
		$tempdata = array();
		foreach($orderdet as $key=>$value){
			//order_id	goodsid	goodsname	goodscost	goodscount	status	shopid	is_send
			$goodsinfo = $this->mysql->select_one("select img from ".Mysite::$app->config['tablepre']."goods where id=".$value['goodsid']." ");

			//orderid	orderdetid	shopid	goodsid	uid	content	addtime	replycontent	replytime	point 评分	is_show 0展示，1不展示
			$pingjinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."comment where goodsid=".$value['goodsid']." and orderdetid='".$value['id']."' ");
			if(!empty($pingjinfo)){
				$value['pingcontent'] = $pingjinfo['content'];
				$value['pingtime'] = date('Y-m-d',$pingjinfo['addtime']);;
				$value['point'] = intval($pingjinfo['point']);
			}else{
				$value['pingcontent'] = "";
				$value['pingtime'] = '';
				$value['point'] = '0';
			}
			if(empty($goodsinfo)){
				$value['img'] = Mysite::$app->config['siteurl'].Mysite::$app->config['shoplogo'];
			}else{
				$imgurl = empty($goodsinfo['img'])? Mysite::$app->config['shoplogo']:$goodsinfo['img'];
				$value['img'] =  Mysite::$app->config['siteurl'].$imgurl;
			}
			$tempdata[] = $value;
		}
		$this->success($tempdata);
	}
	//评价订单
	function newpingorder(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$orderdetid = IFilter::act(IReq::get('orderdetid'));
		$point = intval(IFilter::act(IReq::get('point')));
		$pointcontent = trim(IFilter::act(IReq::get('pointcontent')));
		if(empty($orderdetid)) $this->message('订单不存在');
		$orderdet = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."orderdet where id=".$orderdetid." ");
		if(empty($orderdet)) $this->message('订单不存在');
		$orderinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where id=".$orderdet['order_id']."   order by id desc limit 0,20");
		if(empty($orderinfo)) $this->message('订单不存在');
		if($orderinfo['status'] != 3) $this->message('订单状态不能评价');
		if($orderinfo['buyeruid'] != $backinfo['uid']) $this->message('订单不属于您');
		if($orderdet['status'] == 1) $this->message('该条订单记录已评价');
		$data['orderid'] = $orderinfo['id'];
		$data['orderdetid'] = $orderdetid;
		$data['shopid'] = $orderinfo['shopid'];
		$data['goodsid'] = $orderdet['goodsid'];
		$data['uid'] = $backinfo['uid'];
		$data['content'] = $pointcontent;
		$data['point'] = $point;
		$data['addtime'] = time();
		//更新订单详情表数据
		$udata['status'] = 1;
		$this->mysql->update(Mysite::$app->config['tablepre'].'orderdet',$udata,"id='".$orderdetid."'");
		//将评数据写到 数据表中/
		$this->mysql->insert(Mysite::$app->config['tablepre'].'comment',$data);
		/*写日志*/
		$issong = 1;
		if(intval(Mysite::$app->config['commentday']) > 0){//检测是否赠送积分
	     	$uptime = Mysite::$app->config['commentday']*24*60*60;
	     	$uptime = $orderinfo['addtime'] +$uptime;
	     	if($uptime > time()){
	     	    $issong = 1;
	     	}else{
	     	    $issong = 0;
	     	}
		}
		$fscoreadd = 0;
		if(intval(Mysite::$app->config['commenttype']) > 0 && $issong == 1) { //赠送积分 大于0赠送积分到用户帐号  赠送基础积分
			$scoreadd = Mysite::$app->config['commenttype'];
			$checktime = date('Y-m-d',time());
			$checktime = strtotime($checktime);
			$checklog = $this->mysql->select_one("select sum(result) as jieguo from ".Mysite::$app->config['tablepre']."memberlog where type = 1 and   userid = ".$backinfo['uid']." and addtype =1 and  addtime > ".$checktime);
			if(Mysite::$app->config['maxdayscore'] > 0){
				$checkguo = $checklog['jieguo']+$scoreadd;
				if($checkguo < Mysite::$app->config['maxdayscore']){
					//最大值小于当前和
				}elseif(Mysite::$app->config['maxdayscore'] > $checklog['jieguo']){
					//最大指 大于 已增指
					$scoreadd = Mysite::$app->config['maxdayscore'] - $checklog['jieguo'];
				}else{
					$scoreadd = 0;
				}
			}
			if($scoreadd > 0){
				$this->mysql->update(Mysite::$app->config['tablepre'].'member','`score`=`score`+'.$scoreadd,"uid='".$backinfo['uid']."'");
				$fscoreadd =$scoreadd;
				$memberallcost = $backinfo['score']+$scoreadd;
				$this->memberCls->addlog($backinfo['uid'],1,1,$scoreadd,'评价商品','评价商品'.$orderdet['goodsname'].'获得'.$scoreadd.'积分',$memberallcost);
			}
		}
		// 查询子订单是否所有的状态都为 1，  是的话更新订单标志
		$shuliang  = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."orderdet where order_id='".$orderinfo['id']."' and status = 0");
		if($shuliang < 1){//订单已评价完毕
			$this->mysql->update(Mysite::$app->config['tablepre'].'order','`is_ping`=1',"id='".$orderinfo['id']."'");

			if(intval(Mysite::$app->config['commentscore']) > 0 && $issong ==  1){//扩张积分 大于0
				$scoreadd = intval(Mysite::$app->config['commentscore'])*$orderinfo['allcost'];
				$checktime = date('Y-m-d',time());
				$checktime = strtotime($checktime);
				$checklog = $this->mysql->select_one("select sum(result) as jieguo from ".Mysite::$app->config['tablepre']."memberlog where type = 1 and   userid = ".$backinfo['uid']." and addtype =1 and  addtime > ".$checktime);
				if(Mysite::$app->config['maxdayscore'] > 0){
					$checkguo = $checklog['jieguo']+$scoreadd;
					if($checkguo < Mysite::$app->config['maxdayscore']){
	    	           	//最大值小于当前和
					}elseif(Mysite::$app->config['maxdayscore'] > $checklog['jieguo']){
	    		        //最大指 大于 已增指
						$scoreadd = Mysite::$app->config['maxdayscore'] - $checklog['jieguo'];
					}else{
						$scoreadd = 0;
					}
				}
				if($scoreadd > 0){
					$this->mysql->update(Mysite::$app->config['tablepre'].'member','`score`=`score`+'.$scoreadd,"uid='".$backinfo['uid']."'");
					$memberallcost = $backinfo['score']+$scoreadd+$fscoreadd;
					$this->memberCls->addlog($backinfo['uid'],1,1,$scoreadd,'评价完订单','评价完订单'.$orderinfo['dno'].'奖励，'.$scoreadd.'积分',$memberallcost);
				}
			}
		}
		//店铺平分
		$newpoint['point'] = 5;
		$shuliang  = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."comment where shopid='".$orderinfo['shopid']."' ");
		$scorall = $this->mysql->select_one("select sum(point) as allpoint from ".Mysite::$app->config['tablepre']."comment where shopid='".$orderinfo['shopid']."' ");
		if($shuliang > 0){
			$newpoint['point'] = intval($scorall['allpoint']/$shuliang);
		}
		$newpoint['pointcount'] = $shuliang;
	  
		//店铺销售数量
		$chengallshu  = $this->mysql->select_one("select sum(goodscount) as shuliang from ".Mysite::$app->config['tablepre']."orderdet where order_id in (select id from ".Mysite::$app->config['tablepre']."order where status =3 and shopid = '".$orderinfo['shopid']."') ");
		$newpoint['sellcount'] = 0 ;
		if(isset($chengallshu['shuliang'])){
			$newpoint['sellcount']  = intval($chengallshu['shuliang']);
		} 
		$this->mysql->update(Mysite::$app->config['tablepre'].'shop',$newpoint,"id='".$orderinfo['shopid']."'");
		//商品评分
		$newpoint['point'] = 5;
		$shuliang  = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."comment where goodsid='".$orderdet['goodsid']."' ");
		$scorall = $this->mysql->select_one("select sum(point) as allpoint from ".Mysite::$app->config['tablepre']."comment where goodsid='".$orderdet['goodsid']."' ");
		if($shuliang > 0){
			$newpoint['point'] = intval($scorall['allpoint']/$shuliang);
		}
		$newpoint['pointcount'] = $shuliang;
		//pointcount `$key`
		$this->mysql->update(Mysite::$app->config['tablepre'].'goods',$newpoint,"id='".$orderdet['goodsid']."'");
		$this->success('success');

	}
	/**
	 *  @brief Brief
	 *  
	 *  @return Return_Description
	 *  
	 *  @details Details
	 */
	function getcx(){
		//以下为返回数据
		//商品总价
		//配送费
		//打包费
		//订单实价
		//配送时间列表
		//优惠券列表
		//需要提交数据   lng lat  shopid goodsid  goodscount
		logwrite('appcx');
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$shopid = trim(IFilter::act(IReq::get('shopid')));
		$lat = IFilter::act(IReq::get('lat'));
		$lng = IFilter::act(IReq::get('lng'));
		$ids = IFilter::act(IReq::get('ids'));
		$idscount = IFilter::act(IReq::get('idscount'));
		$backdata['allcost'] = 0;
		$backdata['pscost'] = '不在配送区域';
		$backdata['surecost'] = 0;
		$backdata['bagcost'] = 0;
		$backdata['timelist'] = array();
		$backdata['cxlist'] = array();
		$backdata['yhjlist'] = array();
		$lat = empty($lat)?0:$lat;
		$lng = empty($lng)?0:$lng;
		if(empty($shopid)) $this->message('店铺数据获取失败');
		$shopinfo  = $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."shop  where id = '".$shopid."'    ");
		if(empty($shopinfo)) $this->message('店铺数据获取失败');
		if($shopinfo['shoptype'] == 0){
			$shopdet  = $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."shopfast  where shopid = '".$shopid."'    ");
		}else{
			$shopdet  = $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."shopmarket  where shopid = '".$shopid."'    ");
		}
		if(empty($shopdet)) $this->message('店铺数未开启');
		$shop = array_merge($shopinfo,$shopdet); 
		unset($shop['intr_info']);
		unset($shop['cx_info']);
		$shop['canps'] = 0;
		if($shopinfo['is_open'] != 1) $this->message('店铺未开启');
		$juli = $this->GetDistance($lat, $lng, $shop['lat'], $shop['lng']);
		$valuelist = empty($shop['pradiusvalue'])? unserialize(Mysite::$app->config['radiusvalue']):unserialize($shop['pradiusvalue']);
		$juliceshi = intval($juli/1000);
		$shop['baidupscost'] = '不在配送区域';
		if(is_array($valuelist)){
			foreach($valuelist as $k=>$v){
				if($juliceshi == $k){
  	       	        $shop['baidupscost'] = $v;
  	       	        $backdata['pscost'] = $v;
					$shop['canps'] = 1;
				}
			}
		}
		$source =  intval(IFilter::act(IReq::get('source')));
		$ios_waiting =   Mysite::$app->config['ios_waiting'];
	#	if($source == 1 && $ios_waiting == true){
			 $shop['baidupscost'] = $shop['pscost'];
			$shop['canps'] = 1;
	#	}
		$cximglist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodssign  where  type = 'cx'  order by id asc limit 0,1000");
		$ruleimg = array();
		foreach($cximglist as $key=>$value){
		    $ruleimg[$value['id']] = $value['imgurl'];
		} 
		//if(empty($ids)) $this->message('商品ID错误');
		$tempid = explode(',',$ids);
		$tempacout = explode(',',$idscount);
		//if(count($tempid) != count($tempacout)) $this->message('商品ID和商品数量错误');
		$myidlist = array();
		$querids = array();

		foreach($tempid as $key=>$value){
			$checkid = intval($value); 
			if($checkid > 0){
				$myidlist[$checkid] = $tempacout[$key];
				$querids[] = $checkid;
			}
		} 
		//if(count($querids) == 0) $this->message('商品数量错误');
		if(count($querids) > 0){
			$goodslist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goods  where  id in(".join(',',$querids).") order by id asc limit 0,1000");
			foreach($goodslist as $key=>$value){ 
				if($value['count'] < $myidlist[$value['id']]){
					$this->message($value['name'].'库存不足'); 
				}
				$cxinfo = $this->goodscx($value);
				$value['is_cx'] = $cxinfo['is_cx'];
				$value['cost'] = $cxinfo['cxcost'];
				$value['zhekou'] = $cxinfo['zhekou'];
				$backdata['allcost'] += $value['cost']*$myidlist[$value['id']];
				$backdata['bagcost'] += $value['bagcost']*$myidlist[$value['id']]; 
			}
		}
		
		$pids = IFilter::act(IReq::get('pids')); 
		$pnum = IFilter::act(IReq::get('pnum'));
		if(!empty($pids)){
				$temppids = explode(',',$pids);
				$temppnum = explode(',',$pnum);
				$newid = array();
				$pidtonum = array();
				foreach($temppids as $key=>$value){
					if(!empty($value)){
					   $check1 = intval($value);
					   $check2 = intval($temppnum[$key]);
					   if($check1 > 0 && $check2 > 0){
						   $newid[] = $value;
						   $pidtonum[$value] = $check2;
					   }
				  }
			   }
			   $whereid = join(',',$newid);
			   if(!empty($whereid)){
				   $tempclist =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."product where shopid =".$shopid." and id in(".$whereid.") ");
				   foreach($tempclist as $key=>$value){
					   $dosee = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goods where shopid =".$shopid." and id =".$value['goodsid']." ");
					   $dosee['gg'] = $value;
					   $dosee['count'] = $pidtonum[$value['id']];
					   $dosee['cost'] = $value['cost'];
					   $cxinfo = $this->goodscx($dosee);
						$dosee['is_cx'] = $cxinfo['is_cx'];
						$dosee['cost'] = $cxinfo['cxcost'];
						$dosee['zhekou'] =$cxinfo['zhekou'];
					   
					   
					   $backdata['allcost'] += $dosee['cost']*intval($pidtonum[$value['id']]);
					    $backdata['bagcost'] += $dosee['bagcost']*intval($pidtonum[$value['id']]); 
						 
				   }
				   
			   }
			
		}
		
		
		

		$sellrule =new sellrule();
		$sellrule->setdata($shop['shopid'],$backdata['allcost'],$shop['shoptype']);
		$ruleinfo = $sellrule->getdata();
		$downcost = $ruleinfo['downcost'];
		if(!empty($ruleinfo['cxids'])){
			$cxrule = $this->mysql->getarr("select name,signid from ".Mysite::$app->config['tablepre']."rule  where id in(".$ruleinfo['cxids'].")  and starttime < ".time()."	and endtime  > ".time()."  and  status =1   ");
			$cxinfo = array(); 
			foreach($cxrule as $key=>$value){
				$value['logo'] = isset($ruleimg[$value['signid']])?Mysite::$app->config['siteurl'].$ruleimg[$value['signid']]:'';
				$cxinfo[] = $value;
			}
			$backdata['cxlist'] = $cxinfo;
		}
		$backdata['surecost'] = $backdata['allcost']-$downcost;
		$backdata['surecost2'] =  $backdata['surecost'];
		$backdata['pscost'] = $ruleinfo['nops'] == true?0:$backdata['pscost'];
		$backdata['canps'] = $shop['canps'];
	  
		$backdata['surecost'] =  $backdata['surecost2']+$backdata['pscost']+$backdata['bagcost'];
		$backdata['downcost'] = $downcost;

	  //获取所有促销 
	    $nowhout = strtotime(date('Y-m-d',time()));//当天最小linux 时间
		$timelist = !empty($shop['postdate'])?unserialize($shop['postdate']):array();
		$data['timelist'] = array();
		$checknow = time();
		$whilestatic = $shop['befortime'];
		$nowwhiltcheck = 0;
		while($whilestatic >= $nowwhiltcheck){
		    $startwhil = $nowwhiltcheck*86400;
			foreach($timelist as $key=>$value){
				$stime = $startwhil+$nowhout+$value['s'];
				$etime = $startwhil+$nowhout+$value['e'];
				if($stime  > $checknow){
					$tempt = array();
					$tempt['value'] = $value['s']+$startwhil;
					$tempt['s'] = date('H:i',$nowhout+$value['s']);
					$tempt['e'] = date('H:i',$nowhout+$value['e']);
					$tempt['d'] = date('Y-m-d',$stime);
					$tempt['s'] = $tempt['d'].' '.$tempt['s'];
					$tempt['i'] =  $value['i'];
					$data['timelist'][] = $tempt;
				}
			}
		 
			$nowwhiltcheck = $nowwhiltcheck+1;
		}
		
		
		$backdata['timelist'] = $data['timelist'];  
		$yhjlist = $this->mysql->getarr("select id,name,cost,limitcost,endtime  from ".Mysite::$app->config['tablepre']."juan  where uid ='".$backinfo['uid']."'  and status = 1 and endtime > ".time()." and limitcost < ".$backdata['surecost']."  order by id desc limit 0,20");
		foreach($yhjlist as $key=>$value){
			$value['endtime'] = date('Y-m-d H:i:s',$value['endtime']);
			$backdata['yhjlist'][] = $value;
		}
		$backdata['sendtype'] = $shop['sendtype'];
		$this->success($backdata);
	}
	private function goodscx($goodsinfo){
		//将规格转换为真实 数据 
		//array(1=>'买即送',2=>'限时直降',3=>'限时折扣',4=>'限时价');		cxtype
	/* 	 
		 if($valq['is_cx'] == 1){
				//测算促销 重新设置金额
					$cxdata = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goodscx where goodsid=".$valq['id']."  ");
					$newdata = getgoodscx($valq['cost'],$cxdata);
					
					$valq['zhekou'] = $newdata['zhekou'];
					$valq['is_cx'] = $newdata['is_cx'];
					$valq['cost'] = $newdata['cost'];
				} */
		 
		$newarray = array('cxcost'=>$goodsinfo['cost'],'oldcost'=>$goodsinfo['cost'],'zhekou'=>0,'is_cx'=>0);	
		if($goodsinfo['is_cx'] == 1){		
			$cxdata =	$this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goodscx where goodsid=".$goodsinfo['id']."  ");
			$newdata = getgoodscx($goodsinfo['cost'],$cxdata);
			$newarray['oldcost'] = $goodsinfo['cost'];
			$newarray['cxcost'] = $newdata['cost'];
			$newarray['zhekou'] = $newdata['zhekou'];
			$newarray['is_cx'] = $newdata['is_cx'];
		}	  
		return  $newarray;
	}
	function newmakeorder(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$shopid = intval(IReq::get('shopid'));
		$mobile = IFilter::act(IReq::get('mobile'));
		$yhjid = intval(IFilter::act(IReq::get('yhjid')));
		$sendtime = IFilter::act(IReq::get('pstime'));
		$contactname =  IFilter::act(IReq::get('contactname'));
		$addressdet =  IFilter::act(IReq::get('address'));
		$remark = IFilter::act(IReq::get('beizhu'));
		$ids = IFilter::act(IReq::get('ids'));
		$idscount = IFilter::act(IReq::get('idscount'));
		$payline = intval(IFilter::act(IReq::get('payline')));
		$lat = IFilter::act(IReq::get('lat'));
		$lng = IFilter::act(IReq::get('lng'));

		if(!IValidate::suremobi($mobile))   $this->message('errphone');
		if(empty($contactname))   $this->message('emptycontact');
		if(empty($addressdet))   $this->message('emptyaddress');


		$lat = empty($lat)?0:$lat;
		$lng = empty($lng)?0:$lng;
		if(empty($shopid)) $this->message('店铺数据获取失败');
		$shopinfo  = $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."shop  where id = '".$shopid."'    ");
		if(empty($shopinfo)) $this->message('店铺数据获取失败');
		if($shopinfo['shoptype'] == 0){
			$shopdet  = $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."shopfast  where shopid = '".$shopid."'    ");
		}else{
			$shopdet  = $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."shopmarket  where shopid = '".$shopid."'    ");
		}
		if(empty($shopdet)) $this->message('店铺数未开启');
		$shop = array_merge($shopinfo,$shopdet); 
		if($shopinfo['is_open'] != 1) $this->message('店铺未开启');

		$juli = $this->GetDistance($lat, $lng, $shop['lat'], $shop['lng']);

		$valuelist = empty($shop['pradiusvalue'])? unserialize(Mysite::$app->config['radiusvalue']):unserialize($shop['pradiusvalue']);
		$juliceshi = intval($juli/1000);
		$shop['baidupscost'] = '不在配送区域';
		$shop['canps'] = 0;
		if(is_array($valuelist)){
			foreach($valuelist as $k=>$v){
				if($juliceshi == $k){
  	       	        $shop['baidupscost'] = $v;
  	       	        $shop['pscost'] = $v;
					$shop['canps'] = 1;
				}
			}
		} 
		$source =  intval(IFilter::act(IReq::get('source')));
		$ios_waiting =   Mysite::$app->config['ios_waiting'];
		if($source == 1 && $ios_waiting == true){
			$shop['baidupscost'] = $shop['pscost'];
			$shop['canps'] = 1;
		}
		if($shop['canps'] == 0) $this->message('不在配送区'); 
		//if(empty($ids)) $this->message('商品ID错误');
		$tempid = explode(',',$ids);
		$tempacout = explode(',',$idscount);
		//if(count($tempid) != count($tempacout)) $this->message('商品ID和商品数量错误');
		$myidlist = array();
		$querids = array(); 
		foreach($tempid as $key=>$value){
			$checkid = intval($value); 
			if($checkid > 0){
				$myidlist[$checkid] = $tempacout[$key];
				$querids[] = $checkid;
			}
		}
		$allcost = 0;
	 	$bagcost = 0;
	 	$allshu = 0;
		$tempgooddata = array();
		if(count($querids) > 0) {
	 	
			$goodslist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goods  where  id in(".join(',',$querids).") order by id asc limit 0,1000");
			
			foreach($goodslist as $key=>$value){ 
				if($value['count'] < $myidlist[$value['id']]){
					$this->message($value['name'].'库存不足'); 
				}
				 $value['cxinfo'] = $this->goodscx($value);
				 if($value['cxinfo']['is_cx'] == 1 ){
						$value['cost'] = $value['cxinfo']['cxcost']; 
				 }
				
				
				$allcost += $value['cost']*$myidlist[$value['id']];
				$bagcost += $value['bagcost']*$myidlist[$value['id']];
				$allshu  += $myidlist[$value['id']]; 
				$value['count'] = $myidlist[$value['id']];
				$tempgooddata[] = $value; 
			}
		}
	 
		
		$pids = IFilter::act(IReq::get('pids')); 
		$pnum = IFilter::act(IReq::get('pnum'));
		if(!empty($pids)){
				$temppids = explode(',',$pids);
				$temppnum = explode(',',$pnum);
				$newid = array();
				$pidtonum = array();
				foreach($temppids as $key=>$value){
					if(!empty($value)){
					   $check1 = intval($value);
					   $check2 = intval($temppnum[$key]);
					   if($check1 > 0 && $check2 > 0){
						   $newid[] = $value;
						   $pidtonum[$value] = $check2;
					   }
				  }
			   }
			   $whereid = join(',',$newid);
			   
			   if(!empty($whereid)){
				   $tempclist =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."product where shopid =".$shopid." and id in(".$whereid.") ");
			 
				   foreach($tempclist as $key=>$value){
					   $dosee = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goods where shopid =".$shopid." and id =".$value['goodsid']." ");
					   $dosee['gg'] = $value;
					   $dosee['count'] = $pidtonum[$value['id']];
					   $dosee['cost'] = $value['cost'];
					   $value['cxinfo'] = $this->goodscx($dosee);
						 if($value['cxinfo']['is_cx'] == 1 ){
								$value['cost'] = $value['cxinfo']['cxcost']; 
						 }
					   $dosee['gg']['cost'] = $value['cost'];
					   
					   $allcost += $value['cost']*intval($pidtonum[$value['id']]);
					    $bagcost += $dosee['bagcost']*intval($pidtonum[$value['id']]); 
						  $allshu += $dosee['count'];
						    $tempgooddata[] =$dosee;
						 
				   }
				   
			   }
			
		}
		 
		if(count($tempgooddata) == 0)$this->message('无商品在购物车');
	 
		
		
		$info['ipaddress'] = '';
		$ip_l=new iplocation();
		$ipaddress=$ip_l->getaddress($ip_l->getIP());
		if(isset($ipaddress["area1"])){
		   $info['ipaddress']  = $ipaddress['ip'].mb_convert_encoding($ipaddress["area1"],'UTF-8','GB2312');//('GB2312','ansi',);
		}
		 if($shopinfo['is_open'] != 1) $this->message('店铺暂停营业'); 
	  
		$tempdata = $this->getOpenPosttime($shop['is_orderbefore'],$shop['starttime'],$shop['postdate'],$sendtime,$shop['befortime']); 
		if($tempdata['is_opentime'] ==  2) $this->message('选择的配送时间段，店铺未设置');
		if($tempdata['is_opentime'] == 3) $this->message('选择的配送时间段已超时');
		$info['sendtime'] = $tempdata['is_posttime'];
		$info['postdate'] = $tempdata['is_postdate'];
		if($shop['limitcost'] > $allcost) $this->message('商品总价低于最小起送价'.$shop['limitcost']);

	    $userid = $backinfo['uid'];
		$info['paytype'] = $payline == 0? '0':'1';
		$info['dikou'] = 0;
		$info['username'] = $contactname;
		$info['addressdet'] = $addressdet;
		$info['mobile'] = $mobile;
		$info['juanid'] = $yhjid;
		$info['ordertype'] = IFilter::act(IReq::get('ordertype'));//订单类型
		$info['ordertype'] = empty($info['ordertype'])?'4':$info['ordertype'];
		$info['othercontent'] = ''; 
		$info['shopid'] = $shopid;
		$info['remark'] = $remark;//备注 

		$info['cattype'] = 0;//
		$info['userid'] = $userid; 
		$info['areaids'] = '';

		$info['shopinfo'] = $shop;
		$info['allcost'] = $allcost;
		$info['bagcost'] = $bagcost;
		$info['allcount'] = $allshu;
		$info['shopps'] = $shop['pscost'];
		$info['goodslist']   = $tempgooddata;
		$info['pstype'] = $shop['sendtype'];
		$info['cattype'] = 0;//表示不是预订
		$info['is_goshop']=0;
	    if($shop['limitcost'] > $info['allcost']) $this->message('商品总价低于最小起送价'.$shop['limitcost']);
		 
		$orderclass = new orderclass();
		$orderclass->makenormal($info);
	    $orderid = $orderclass->getorder();
	   
		if($backinfo['uid'] ==  0){

		}else{
	      //保持地址数据
			$checkinfo =   $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."address where userid='".$backinfo['uid']."'  ");
			if(empty($checkinfo)){
				$addata['userid'] = $backinfo['uid'];
				$addata['username'] = $backinfo['username'];
				$addata['address'] = $addressdet;
				$addata['phone'] = $mobile;
				$addata['contactname'] = $contactname;
				$addata['default'] = 1;
				$this->mysql->insert(Mysite::$app->config['tablepre'].'address',$addata);
			}
		}
		$this->success($orderid);
		exit;
	}
	/**
	 *  @brief 获取店铺时间列表
	 *  
	 *  @return Return_Description
	 *  
	 *  @details Details
	 */
	function gettimelist(){
		//以下为返回数据
		//商品总价
		//配送费
		//打包费
		//订单实价
		//配送时间列表
		//优惠券列表
		//需要提交数据   lng lat  shopid goodsid  goodscount
		/*
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
		$this->message('未登陆,请先登陆');
		}
		*/
		$shopid = trim(IFilter::act(IReq::get('shopid')));
		$lat = IFilter::act(IReq::get('lat'));
		$lng = IFilter::act(IReq::get('lng'));

		$lat = empty($lat)?0:$lat;
		$lng = empty($lng)?0:$lng;
		if(empty($shopid)) $this->message('店铺数据获取失败');
		$shopinfo  = $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."shop  where id = '".$shopid."'    ");
		if(empty($shopinfo)) $this->message('店铺数据获取失败');
		if($shopinfo['shoptype'] == 0){
			$shopdet  = $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."shopfast  where shopid = '".$shopid."'    ");
		}else{
			$shopdet  = $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."shopmarket  where shopid = '".$shopid."'    ");
		}
		if(empty($shopdet)) $this->message('店铺数未开启');
		$shop = array_merge($shopinfo,$shopdet);

		unset($shop['intr_info']);
		unset($shop['cx_info']);
		$nowhout = strtotime(date('Y-m-d',time()));//当天最小linux 时间
		$timelist = !empty($shop['postdate'])?unserialize($shop['postdate']):array();
		$data['timelist'] = array();
		$checknow = time();
		$whilestatic = $shop['befortime'];
		$nowwhiltcheck = 0;
		while($whilestatic >= $nowwhiltcheck){
		    $startwhil = $nowwhiltcheck*86400;
			foreach($timelist as $key=>$value){
				$stime = $startwhil+$nowhout+$value['s'];
				$etime = $startwhil+$nowhout+$value['e'];
				if($stime  > $checknow){
					$tempt = array();
					$tempt['value'] = $value['s']+$startwhil;
					$tempt['s'] = date('H:i',$nowhout+$value['s']);
					$tempt['e'] = date('H:i',$nowhout+$value['e']);
					$tempt['d'] = date('Y-m-d',$stime);
					$tempt['s'] = $tempt['d'].' '.$tempt['s'];
					$tempt['i'] =  $value['i'];
					$data['timelist'][] = $tempt;
				}
			}
		 
			$nowwhiltcheck = $nowwhiltcheck+1;
		}
		$backdata['timelist'] = $data['timelist']; 
		$this->success($backdata);
	}
	function newmakezorder(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$shopid = intval(IReq::get('shopid'));
		$mobile = IFilter::act(IReq::get('mobile'));
		$sendtime = IFilter::act(IReq::get('pstime'));
		$yhjid = intval(IFilter::act(IReq::get('yhjid')));
		$sendtime = IFilter::act(IReq::get('pstime'));
		$contactname =  IFilter::act(IReq::get('contactname'));


		$remark = IFilter::act(IReq::get('beizhu'));
		$ids = IFilter::act(IReq::get('ids'));
		$idscount = IFilter::act(IReq::get('idscount'));
		$subtype = intval(IReq::get('subtype'));
		$peopleNum = intval(IFilter::act(IReq::get('people')));
		if($peopleNum < 1) $this->message('预订人数不能少于1人');
			$info['othercontent'] = empty($peopleNum)?'':serialize(array('人数'=>$peopleNum));
			$shopinfo=   $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id where a.shopid = '".$shopid."'    ");
			if(empty($shopinfo)) $this->message('店铺不存在');
			/*监测验证码*/ 
			if(empty($contactname)) 		  $this->message('emptycontact');
			if(!IValidate::suremobi($mobile))   $this->message('errphone');
			$info['ipaddress'] = "";
			$ip_l=new iplocation();
			$ipaddress=$ip_l->getaddress($ip_l->getIP());
			if(isset($ipaddress["area1"])){
				$info['ipaddress']  = $ipaddress['ip'].mb_convert_encoding($ipaddress["area1"],'UTF-8','GB2312');//('GB2312','ansi',);
			}

			if($shopinfo['is_open'] != 1) $this->message('店铺暂停营业');
			  $tempdata = $this->getOpenPosttime($shopinfo['is_orderbefore'],$shopinfo['starttime'],$shopinfo['postdate'],$sendtime,$shopinfo['befortime']); 
			if($tempdata['is_opentime'] ==  2) $this->message('选择的配送时间段，店铺未设置');
			if($tempdata['is_opentime'] == 3) $this->message('选择的配送时间段已超时');
			$info['sendtime'] = $tempdata['is_posttime'];
			$info['postdate'] = $tempdata['is_postdate'];
	  
	 
			$info['userid'] = $backinfo['uid'];
			$subtype = intval(IReq::get('subtype'));
			$info['shopid'] = $shopid;//店铺ID
			$info['remark'] = $remark;//备注
			$info['paytype'] = '0';//支付方式
			$info['username'] = $contactname;
			$info['mobile'] = $mobile;
			$info['addressdet'] = '';

			$info['juanid']  = '';//优惠劵ID
			$info['ordertype'] = IFilter::act(IReq::get('ordertype'));//订单类型
			$info['ordertype'] = empty($info['ordertype'])?'4':$info['ordertype'];
			$info['cattype'] = 0;//
			$info['areaids'] = '';
			$info['shopinfo'] = $shopinfo;
		if($subtype == 1){
			$info['allcost'] = 0 ;
			$info['bagcost'] = 0;
			$info['allcount'] = 0;
			$info['goodslist'] = array();
		}else{
			if(empty($info['shopid'])) $this->message('shop_noexit');
			$ids = IFilter::act(IReq::get('ids'));
			$idscount = IFilter::act(IReq::get('idscount'));
			if(empty($ids)) $this->message('商品ID错误');
			$tempid = explode(',',$ids);
			$tempacout = explode(',',$idscount);
			if(count($tempid) != count($tempacout)) $this->message('商品ID和商品数量错误');
			$querids = array();
			foreach($tempid as $key=>$value){
				$checkid = intval($value); 
				if($checkid > 0){
					$myidlist[$checkid] = $tempacout[$key];
					$querids[] = $checkid;
				}
			} 
			if(count($querids) == 0) $this->message('商品数量错误');
			$allcost = 0;
			$bagcost = 0;
			$allnum = 0;
			$goodslist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goods  where  id in(".join(',',$querids).") order by id asc limit 0,1000");
			$tempdata = array();
			foreach($goodslist as $key=>$value){ 
				if($value['count'] < $myidlist[$value['id']]){
					$this->message($value['name'].'库存不足'); 
				}
				$allcost += $value['cost']*$myidlist[$value['id']];
				$value['count'] = $myidlist[$value['id']];
				$allnum += $myidlist[$value['id']];
				$tempdata[] = $value;
			}
			$pids = IFilter::act(IReq::get('pids')); 
			$pnum = IFilter::act(IReq::get('pnum'));
			if(!empty($pids)){
					$temppids = explode(',',$pids);
					$temppnum = explode(',',$pnum);
					$newid = array();
					$pidtonum = array();
					foreach($temppids as $key=>$value){
						if(!empty($value)){
						   $check1 = intval($value);
						   $check2 = intval($temppnum[$key]);
						   if($check1 > 0 && $check2 > 0){
							   $newid[] = $value;
							   $pidtonum[$value] = $check2;
						   }
					  }
				   }
				   $whereid = join(',',$newid);
				   if(!empty($whereid)){
					   $tempclist =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."product where shopid =".$info['shopid']." and id in(".$whereid.") ");
					   foreach($tempclist as $key=>$value){
						   $dosee = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goods where shopid =".$info['shopid']." and id =".$value['goodsid']." ");
						   $dosee['gg'] = $value;
						   $dosee['count'] = $pidtonum[$value['id']];
						   $allcost += $dosee['cost']*intval($pidtonum[$value['id']]); 
							  $allnum += $dosee['count'];
								$tempdata[] =$dosee;
					   }
					   
				   }
				
			}
			$info['allcost'] = $allcost;
			$info['goodslist']   = $tempdata;
			$info['bagcost'] = 0;
			$info['allcount'] = 0;
		}
		$info['shopps'] = 0;
		$info['pstype'] = 0;
		$info['cattype'] = 1;//表示不是预订
		$info['is_goshop']=1;
		$info['subtype'] = $subtype; 
		$orderclass = new orderclass();
		$orderclass->orderyuding($info);
		$orderid = $orderclass->getorder();
	    
		$this->success($orderid); 
		exit;
	}
	function sendregphone(){
		$phone = IFilter::act(IReq::get('phone'));  
	    $type = intval(IFilter::act(IReq::get('type'))); 
		$phonecls = new phonecode($this->mysql,$type,$phone); 
		if($phonecls->sendcode()){
			$this->success($phonecls->getCode());
		}else{
			$this->message($phonecls->getError());
		} 
	}
	function saveask(){
		$backinfo = $this->checkappMem();
	  	$shopid = intval(IReq::get('shopid'));
	  	$data['content'] = trim(IFilter::act(IReq::get('content')));
	  	$type = intval(IReq::get('type'));//留言类型 shop
	    if(empty($data['content'])) $this->message('ask_emptycontent');
	    if(strlen($data['content']) > 200) $this->message('ask_contentlength');
	    $data['shopid'] = empty($shopid)?'0':$shopid;
	    $data['uid'] = $backinfo['uid'];
	    $data['typeid'] = 5;
	    $data['addtime'] = time();
	    $this->mysql->insert(Mysite::$app->config['tablepre'].'ask',$data);
		  $this->success('success');
	}
	function checkupdate(){
		$apptype = trim(IFilter::act(IReq::get('apptype')));
		if($apptype == 'buyer'){
			$data['appvison'] = Mysite::$app->config['appvison1'];
			$data['appdownload'] =  Mysite::$app->config['appdownload1'];
		}elseif($apptype == 'seller'){
			$data['appvison'] = Mysite::$app->config['appvison2'];
			$data['appdownload'] =  Mysite::$app->config['appdownload2'];
		}elseif($apptype == 'psuer'){
			$data['appvison'] = Mysite::$app->config['appvison3'];
			$data['appdownload'] =  Mysite::$app->config['appdownload3'];
		}else{
			$this->message('未来定义的操作');
		}
		$this->success($data);
	}
	 
	function searchmap(){
		$searchvalue = trim(IFilter::act(IReq::get('searchvalue')));
		//http://api.map.baidu.com/place/v2/search?q=饭店&region=北京&output=json&ak=E4805d16520de693a3fe707cdc962045&
	     $content =   file_get_contents('http://api.map.baidu.com/place/v2/search?ak='.Mysite::$app->config['baidumapkey'].'&output=json&query='.$searchvalue.'&page_size=12&page_num=0&scope=1&region='.Mysite::$app->config['cityname']); 
	   echo $content;
	   exit;
	} 
	function updateimg(){
		$backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}   
		$goodsid = intval(IReq::get('goodsid'));
		$goodsinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goods where id='".$goodsid."' ");
		// if(empty($goodsinfo)){
		    // $this->message('商品不存在');
		// }
	/*	if($goodsinfo['uid'] != $backinfo['uid']){
			$this->message('商品不属于您');
		}  */
		 
	   // $_FILES["imgFile"]['type'] = 'jpg';
		
		$json = new Services_JSON();
		$uploadpath = 'upload/goods/';
		$filepath = '/upload/goods/';
		$upload = new upload($uploadpath,array('gif','jpg','jpge','png'));//upload
		$file = $upload->getfile();
		if($upload->errno!=15&&$upload->errno!=0) {
			$this->message($upload->errmsg());

		}else{
			$data['img'] = $filepath.$file[0]['saveName'];
			if(!empty($goodsinfo)){
				$this->mysql->update(Mysite::$app->config['tablepre'].'goods',$data,"id='".$goodsid."'  "); 
			}
			$this->success($data);
		} 
	}
	function shoptj(){
		$backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}  
	//	$BeginDate=date('Y-m-01', strtotime(date("Y-m-d",time())));
	//	$enddata = date('Y-m-d', strtotime("$BeginDate +1 month -1 day"));
	 	$starttime = strtotime(date('Y-m-d',time()));// strtotime($BeginDate);
     	$endtime = strtotime(date('Y-m-d',time()))+86399; 
			
		$where2 = ' and  addtime  > '.$starttime.' and addtime < '.$endtime;
		$yuetj=  $this->mysql->select_one("select  count(id) as shuliang,sum(cxcost) as cxcost,sum(yhjcost) as yhcost, sum(shopcost) as shopcost,sum(scoredown) as score, sum(shopps)as pscost, sum(bagcost) as bagcost,sum(allcost) as doallcost from ".Mysite::$app->config['tablepre']."order  where shopuid = '".$backinfo['uid']."'  and   status < 3 ".$where2."  order by id asc  limit 0,1000");
		 $data['yuecost'] = intval($yuetj['shuliang']); //订单数
		$data['allcost'] = round($yuetj['doallcost'],2);//总金额
		$data['shopcost'] = $backinfo['shopcost'];//账户余额
		$this->success($data); 
	}
	//2016 1.27改
	function newshoptj(){
		$backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}  
		// $BeginDate=date('Y-m-01', strtotime(date("Y-m-d",time())));
		// $enddata = date('Y-m-d', strtotime("$BeginDate +1 month -1 day"));
		// $starttime = strtotime($BeginDate);
		// $endtime = strtotime($enddata)+86399; 
			
		// $where2 = ' and  addtime  > '.$starttime.' and addtime < '.$endtime;
		// $yuetj=  $this->mysql->select_one("select  count(id) as shuliang,sum(cxcost) as cxcost,sum(yhjcost) as yhcost, sum(shopcost) as shopcost,sum(scoredown) as score, sum(shopps)as pscost, sum(bagcost) as bagcost,sum(allcost) as doallcost from ".Mysite::$app->config['tablepre']."order  where shopuid = '".$backinfo['uid']."'  and   status = 3 ".$where2." order by id asc  limit 0,1000");
		// $alltj=  $this->mysql->select_one("select  count(id) as shuliang,sum(cxcost) as cxcost,sum(yhjcost) as yhcost, sum(shopcost) as shopcost,sum(scoredown) as score, sum(shopps)as pscost, sum(bagcost) as bagcost,sum(allcost) as doallcost from ".Mysite::$app->config['tablepre']."order  where shopuid = '".$backinfo['uid']."'  and status = 3   order by id asc  limit 0,1000");
		// $data['yuecost'] = round($yuetj['doallcost'],2);
		// $data['allcost'] = round($alltj['doallcost'],2);
		
		//日统计
		$daytime = date('Y-m-d',time());
		$daytime = strtotime($daytime);
		$enddaytime = $daytime-86399;
		$wheredaytime = ' and  addtime  < '.time().' and addtime > '.$daytime;
		$yuetj=  $this->mysql->select_one("select  count(id) as shuliang,sum(cxcost) as cxcost,sum(yhjcost) as yhcost, sum(shopcost) as shopcost,sum(scoredown) as score, sum(shopps)as pscost, sum(bagcost) as bagcost,sum(allcost) as doallcost from ".Mysite::$app->config['tablepre']."order  where shopuid = '".$backinfo['uid']."'  and   status = 3 ".$wheredaytime." order by id asc  limit 0,1000");
		$alltj=  $this->mysql->counts("select  * from ".Mysite::$app->config['tablepre']."order  where shopuid = '".$backinfo['uid']."'  and status = 3  ".$wheredaytime." order by id asc  limit 0,1000");
		$data['daycost'] = round($yuetj['doallcost'],2);
		$data['daycount'] = $alltj;
		
		//周统计
		$weektime = date('Y-m-d',time());
		$weektime = strtotime($weektime);
		$endweektime = $daytime-86399*7;
		$whereweektime = ' and  addtime  < '.time().' and addtime > '.$endweektime;
		$yuetj=  $this->mysql->select_one("select  count(id) as shuliang,sum(cxcost) as cxcost,sum(yhjcost) as yhcost, sum(shopcost) as shopcost,sum(scoredown) as score, sum(shopps)as pscost, sum(bagcost) as bagcost,sum(allcost) as doallcost from ".Mysite::$app->config['tablepre']."order  where shopuid = '".$backinfo['uid']."'  and   status = 3 ".$whereweektime." order by id asc  limit 0,1000");
		$alltj=  $this->mysql->counts("select  * from ".Mysite::$app->config['tablepre']."order  where shopuid = '".$backinfo['uid']."'  and status = 3  ".$whereweektime." order by id asc  limit 0,1000");
		$data['weekcost'] = round($yuetj['doallcost'],2);
		$data['weekcount'] = $alltj;
		
		//月统计
		$monthtime = date('Y-m-d',time());
		$monthtime = strtotime($monthtime);
		$endmonthtime = $daytime-86399*30;
		$wheremonthtime = ' and  addtime  < '.time().' and addtime > '.$endmonthtime;
		$yuetj=  $this->mysql->select_one("select  count(id) as shuliang,sum(cxcost) as cxcost,sum(yhjcost) as yhcost, sum(shopcost) as shopcost,sum(scoredown) as score, sum(shopps)as pscost, sum(bagcost) as bagcost,sum(allcost) as doallcost from ".Mysite::$app->config['tablepre']."order  where shopuid = '".$backinfo['uid']."'  and   status = 3 ".$wheremonthtime." order by id asc  limit 0,1000");
		$alltj=  $this->mysql->counts("select  * from ".Mysite::$app->config['tablepre']."order  where shopuid = '".$backinfo['uid']."'  and status = 3  ".$wheremonthtime." order by id asc  limit 0,1000");
		$data['monthcost'] = round($yuetj['doallcost'],2);
		$data['monthcount'] = $alltj; 
		//日  周  30天  指定天数 
		$this->success($data); 
	}
	function shoptjsearch(){
		$backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}  
		$startday = IFilter::act(IReq::get('startday'));
		$endday = IFilter::act(IReq::get('endday'));
		$starttime = strtotime($startday);
		$endtime = strtotime($endday)+86399;
		$wheremonthtime = ' and  addtime  > '.$starttime.' and addtime < '.$endtime;
		$yuetj=  $this->mysql->select_one("select  count(id) as shuliang,sum(cxcost) as cxcost,sum(yhjcost) as yhcost, sum(shopcost) as shopcost,sum(scoredown) as score, sum(shopps)as pscost, sum(bagcost) as bagcost,sum(allcost) as doallcost from ".Mysite::$app->config['tablepre']."order  where shopuid = '".$backinfo['uid']."'  and   status = 3 ".$wheremonthtime." order by id asc  limit 0,1000");
		$alltj=  $this->mysql->counts("select  * from ".Mysite::$app->config['tablepre']."order  where shopuid = '".$backinfo['uid']."'  and status = 3  ".$wheremonthtime." order by id asc  limit 0,1000");
		$data['searchcost'] = round($yuetj['doallcost'],2);
		$data['searchcount'] = $alltj; 
		$this->success($data); 
	}

	function subshow(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			 $this->message('nologin');
		}
		$orderid = intval(IReq::get('orderid'));  
		$userid = empty($backinfo['uid'])?0:$backinfo['uid']; 
		$orderid = intval(IReq::get('orderid')); 
		if(empty($orderid)) $this->message('订单获取失败');
		 
	   if($orderid < 1){ 
	  	 echo '订单获取失败';
		 exit;
	   }
	  
		$order = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where buyeruid='".$backinfo['uid']."' and id = ".$orderid."");   
		$order['ps'] = $order['shopps'];
		// 超市商品总价	 超市配送配送	shopcost 店铺商品总价	shopps 店铺配送费	pstype 配送方式 0：平台1：个人	bagc 
		if(empty($order)){ 
			echo '获取定单失败';
		} 
		$orderdet = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."orderdet where order_id='".$order['id']."'");  
		$order['cp'] = count($orderdet); 
		$buyerstatus= array(
		'0'=>'等待处理',
		'1'=>'订餐成功处理中',
		'2'=>'订单已发货',
		'3'=>'订单完成',
		'4'=>'订单已取消',
		'5'=>'订单已取消'
		); 
		$paylist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."paylist  where  type=0 or type=2 order by id asc limit 0,50"); 
		 
		$data['paylist'] = $paylist;
		$order['status'] = $buyerstatus[$order['status']];
		$order['showpaytype'] = $order['paytype'] == 1?'在线支付':'货到支付';
		$order['showpaystatus'] = $order['paystatus']==1?'已支付':'未支付';
		$order['addtime'] = date('Y-m-d H:i:s',$order['addtime']);
		$order['posttime'] = date('Y-m-d H:i:s',$order['posttime']); 
		$data['order'] = $order;
		$data['orderdet'] = $orderdet;
		 
		Mysite::$app->setdata($data); 
	}
	function postmsg(){ 
	    $orderid = intval(IReq::get('orderid'));
		if(empty($orderid)) {
			echo '订单号错误';
			exit;
		} 
		//$info =  mysql_query("SELECT * from `".$cfg['tablepre']."onlinelog` where id = '".$out_trade_no."' ");
	 
		 
		 $orderinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where id='".$orderid."'  ");  
		if(empty($orderinfo)){
		   echo '支付记录不存在';
		   exit;
		}
		if($orderinfo['is_print'] == 0 && $orderinfo['paystatus'] == 1){
				$orderclass = new orderclass();
				$orderclass->sendmess($orderinfo['id']);
				
		        $this->mysql->update(Mysite::$app->config['tablepre'].'order',array('is_print'=>1),"id ='".$orderinfo['id']."' ");
	    }
		
		$this->success('success');
	}
	function gotopay(){
		 
		 $orderid = intval(IReq::get('orderid')); 
	   		$payerrlink = IUrl::creatUrl('app/subshow/orderid/'.$orderid);    
			$errdata = array('paysure'=>false,'reason'=>'','url'=>''); 
	     
		  if(empty($orderid)){
				$backurl = IUrl::creatUrl('wxsite/index');  
				$errdata['url'] = $backurl;
				$errdata['reason'] = '订单获取失败';
				$errdata['paysure'] = false;  
				$this->showpayhtml($errdata);   

		  } 
	 	$userid = empty($this->member['uid'])?0:$backinfo['uid']; 
		if($userid == 0){
			$neworderid = ICookie::get('orderid');
			if($orderid != $neworderid) {
				$errdata['url'] = $payerrlink;
				$errdata['reason'] = '订单操作无权限';
				$errdata['paysure'] = false;  
				$this->showpayhtml($errdata);    
			}
		}
		$orderinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where id=".$orderid."  ");  //获取主单
	//	print_r($orderinfo);
		if(empty($orderinfo)){
			$errdata['url'] = $payerrlink;
				$errdata['reason'] = '订单数据获取失败';
				$errdata['paysure'] = false;  
				$this->showpayhtml($errdata);  
		} 
		if($userid > 0){
			if($orderinfo['buyeruid'] !=  $userid){
				$errdata['url'] = $payerrlink;
				$errdata['reason'] = '订单不属于您';
				$errdata['paysure'] = false;  
				$this->showpayhtml($errdata);  
			} 
		}
		if($orderinfo['paytype'] == 0){
			 
				$errdata['url'] = $payerrlink;
				$errdata['reason'] = '此订单是货到支付订单不可操作';
				$errdata['paysure'] = false;  
				$this->showpayhtml($errdata);  
			  
		}
		if($orderinfo['status']  > 2){
			 
				$errdata['url'] = $payerrlink;
				$errdata['reason'] = '此订单已发货或者其他状态不可操作';
				$errdata['paysure'] = false;  
				$this->showpayhtml($errdata);  
			 
		}
		//
		$paydotype = IFilter::act(IReq::get('paydotype'));
		 
	 
		 $paylist = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."paylist where  loginname = '".$paydotype."'   order by id asc limit 0,50");
		 
		if(empty($paylist)){
			$errdata['url'] = $payerrlink;
				$errdata['reason'] = '不存在的支付类型';
				$errdata['paysure'] = false;  
				$this->showpayhtml($errdata);  
		}			 
		 
		if($orderinfo['paystatus'] == 1){
			$errdata['url'] = $payerrlink;
				$errdata['reason'] = '此订单已支付';
				$errdata['paysure'] = false;  
				$this->showpayhtml($errdata); 
		 
		}
		$paydir = hopedir.'/plug/pay/'.$paydotype;
	 	if(!file_exists($paydir.'/pay.php'))
		{ 
			$errdata['url'] = $payerrlink;
				$errdata['reason'] = '支付方式文件不存在';
				$errdata['paysure'] = false;  
				$this->showpayhtml($errdata); 
			 
		} 
		$dopaydata = array('type'=>'order','upid'=>$orderid,'cost'=>$orderinfo['allcost'],'source'=>3);//支付数据   返回数据为3
		include_once($paydir.'/pay.php');   
		//调用方式  直接调用支付方式
		exit;
	}
	function showpayhtml($data){//定义函数
		$tempcontent = '';
		//array('paysure'=>false,'reason'=>'','url'=>''); 
		 $tempjs = '';
		if($data['paysure'] == true){
		$tempcontent = '<div style="margin-top:50px;background-color:#fff;">
			 <div style="height:30px;width:80%;padding-left:10%;padding-right:10%;padding-top:10%;">
			    <span style="background:url(\'http://'.Mysite::$app->config['siteurl'].'/upload/images/order_ok.png\') left no-repeat;height:30px;width:30px;background-size:100% 100%;display: inline-block;"></span>
				<div style="position:absolute;margin-left:50px;  margin-top: -30px; font-size: 20px;  font-weight: bold;  line-height: 20px;">恭喜您，支付订单成功</div>
				
			    
			</div>
			<div style="width:80%;margin:0px auto;padding-top:10px;"><font style="font-size:12px;">单号:</font><span style="padding-left:20px;font-size:12px;display: inline-block;">'.$data['reason']['dno'].'</span></div>
			<div style="width:80%;margin:0px auto;padding-top:10px;"><font style="font-size:12px;">总价:</font><span style="padding-left:20px;color:red;font-weight:bold;font-size:15px;">￥'.$data['reason']['allcost'].'元</span></div> 
			<div style="width:80%;margin:0px auto;padding-top:30px;text-align:right;"><span style="font-size:20px;color:#fff;padding:5px;background-color:red;" onClick="window.waimai.goCtrl(\'orderdet\',\'0\');">立即返回</span></div>
	   </div>';
	   $tempjs='window.onload=function(){
					 
					window.waimai.goCtrl(\'paydo\',\'0\');
				}';
		}else{//
	   $tempcontent = '<div style="margin-top:50px;background-color:#fff;">
			 <div style="height:30px;width:80%;padding-left:10%;padding-right:10%;padding-top:10%;">
			    <span style="background:url(\''.Mysite::$app->config['siteurl'].'/upload/images/nocontent.png\') left no-repeat;height:30px;width:30px;background-size:100% 100%;display: inline-block;"></span>
				<div style="position:absolute;margin-left:50px;  margin-top: -30px; font-size: 20px;  font-weight: bold;  line-height: 20px;">sorry,支付订单失败</div>
				
			    
			</div>
			<div style="width:80%;margin:0px auto;padding-top:10px;"><font style="font-size:12px;">原因:</font><span style="padding-left:20px;font-size:12px;display: inline-block;">'.$data['reason'].'</span></div> 
			<div style="width:80%;margin:0px auto;padding-top:30px;text-align:right;"><span style="font-size:20px;color:#fff;padding:5px;background-color:red;  cursor: pointer;" onClick="window.waimai.goCtrl(\'closeshow\',\'0\');">立即返回</span></div>
	   </div>';
		} 
		//onClick="windwo.waimai.goCtrl('closeshow','0');"
		$html = '<!DOCTYPE html>
<html>
<head>
   <meta charset="UTF-8">  
  <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
	<title>支付返回信息</title> 
	 
	 
 
 <script>
 	 '.$tempjs.'
</script>

 
</head>
<body style="height:100%;width:100%;margin:0px;"> 
   <div style="max-width:400px;margin:0px;margin:0px auto;min-height:300px;"> '.$tempcontent.'    </div>
	 
</body>
</html>'; 
print_r($html);
exit; 
    }
	
	/*****  2015.6.6 新增加超市分类和超市商品管理***/
    // $listtype = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."marketcate where shopid = '".$shopid."'  order by orderid asc  ");
	// $listtype =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."marketcate where shopid = '".$shopid."' and parent_id =".$newtopid." order by orderid asc  ");  
	/*
	* 超市商家获取一级商品分类
	2015-12-26修改
	*/
	function MarketFgoodstype(){
		$backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$shopinfo= $this->mysql->select_one("select is_open as shopopentype,starttime as opentime,shopname,id,address as shopaddress,phone as shopphone,shoptype from ".Mysite::$app->config['tablepre']."shop where uid='".$backinfo['uid']."' ");
		if(empty($shopinfo)) $this->message('获取店铺资料失败');
		if($shopinfo['shoptype'] !=1) $this->message('店铺不是超市店铺');
		$shoptype =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."marketcate where shopid='".$shopinfo['id']."' and parent_id = 0 order by orderid desc");
		$tempc = array();
		foreach($shoptype as $key=>$value){ 
			$value['shuliang'] = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."goods where shopid='".$shopinfo['id']."' and typeid in(select id from ".Mysite::$app->config['tablepre']."marketcate where parent_id =".$value['id'].") order by id desc"); 
			$tempc[] = $value;
		} 
		
		
		
		$this->success($tempc);
	}
	/*
	* 超市商家删除一级商品分类
	2015-12-26修改
	*/
	function delMarketFgoostype(){
		$backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$shopinfo= $this->mysql->select_one("select is_open as shopopentype,starttime as opentime,shopname,id,address as shopaddress,phone as shopphone,shoptype from ".Mysite::$app->config['tablepre']."shop where uid='".$backinfo['uid']."' ");
		if(empty($shopinfo)) $this->message('获取店铺资料失败');
		if($shopinfo['shoptype'] !=1) $this->message('店铺不是超市店铺');
		$id = intval(IFilter::act(IReq::get('id')));
		if(empty($id)) $this->message('删除ID获取失败');
		//增加个check  判断是否
		$nowtype = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."marketcate where shopid='".$shopinfo['id']."' and id=".$id." order by id desc"); 
		if(empty($nowtype)) $this->message('商品分类不存在');
		if($nowtype['parent_id'] != 0) $this->message('当前分类不是一级分类');
		if($nowtype['parent_id'] == 0){
		   $checkdata =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."marketcate where shopid='".$shopinfo['id']."' and parent_id=".$id." order by id desc"); 
			if(!empty($checkdata)) $this->message('该分类下有下级分类删除失败，删除失败');
		}else{
			$checkdata =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goods where shopid='".$shopinfo['id']."' and typeid=".$id." order by id desc"); 
			if(!empty($checkdata)) $this->message('该分类下有商品，删除失败');
		}
		$this->mysql->delete(Mysite::$app->config['tablepre']."marketcate"," shopid='".$shopinfo['id']."' and id=".$id." ");
		$shoptype =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."marketcate where shopid='".$shopinfo['id']."' and parent_id = 0 order by orderid desc");
		$this->success($shoptype); 
	}
	/*
	* 超市 商家添加一级商品分类 
	2015-12-26修改
	*/
	function addMarketFgoostype(){
		$backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$shopinfo= $this->mysql->select_one("select is_open as shopopentype,starttime as opentime,shopname,id,address as shopaddress,phone as shopphone,shoptype from ".Mysite::$app->config['tablepre']."shop where uid='".$backinfo['uid']."' ");
		if(empty($shopinfo)) $this->message('获取店铺资料失败');
		if($shopinfo['shoptype'] !=1) $this->message('店铺不是超市店铺');
		$id = intval(IFilter::act(IReq::get('id')));
		$name = trim(IFilter::act(IReq::get('name')));
		$orderid = intval(IFilter::act(IReq::get('orderid')));
		//	id	shopid 店铺ID	name 分类名称	orderid	cattype 1外卖 2订台
		if(empty($name)) $this->message('分类名称不能为空');
		$newdata['shopid'] = $shopinfo['id'];
		$newdata['name'] = $name;
		$newdata['orderid'] = $orderid; 
		$newdata['parent_id'] = 0;
/* 		$newdata['bagcost'] = intval(IFilter::act(IReq::get('bagcost')));
		$newdata['uid'] = $backinfo['uid']; */
		if($id > 0){
			$nowtype = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."marketcate where shopid='".$shopinfo['id']."' and id=".$id." order by id desc"); 
			if(empty($nowtype))$this->message('编辑一级分类不存在');
			if($nowtype['parent_id'] > 0) $this->message('当前分类不是一级分类');
			if($nowtype['shopid'] != $nowtype['shopid']) $this->message('当前分类不属于该店铺管理'); 
		}
		if(empty($id)){
			//新增
			$this->mysql->insert(Mysite::$app->config['tablepre']."marketcate",$newdata);
		}else{
			//编辑
			$this->mysql->update(Mysite::$app->config['tablepre'].'marketcate',$newdata,"id='".$id."' and shopid = '".$shopinfo['id']."'");
		}
		$shoptype =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."marketcate where shopid='".$shopinfo['id']."' and parent_id = 0 order by orderid desc");
		$this->success($shoptype);
	}
	/*
	* 超市商家获取二级商品分类
	2015-12-26修改
	*/
	function MarketTgoodstype(){
		$backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$ftype = intval(IFilter::act(IReq::get('ftype')));
		if(empty($ftype)){
		   $this->message('无上级分类获取失败');
		}
		$shopinfo= $this->mysql->select_one("select is_open as shopopentype,starttime as opentime,shopname,id,address as shopaddress,phone as shopphone,shoptype from ".Mysite::$app->config['tablepre']."shop where uid='".$backinfo['uid']."' ");
		if(empty($shopinfo)) $this->message('获取店铺资料失败');
		if($shopinfo['shoptype'] !=1) $this->message('店铺不是超市店铺');
		$shoptype =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."marketcate where shopid='".$shopinfo['id']."' and parent_id = ".$ftype." order by orderid desc");
		$tempc = array();
		foreach($shoptype as $key=>$value){ 
			$value['shuliang'] = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."goods where shopid='".$shopinfo['id']."' and typeid   =".$value['id']."  order by id desc"); 
			$tempc[] = $value;
		}  
		
		$this->success($tempc);
	}
	/*
	* 超市商家删除二级商品分类
	2015-12-26修改
	*/
	function delMarketTgoostype(){
		$backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$ftype = intval(IFilter::act(IReq::get('ftype')));
		if(empty($ftype)){
		   $this->message('无上级分类获取失败');
		}
		$shopinfo= $this->mysql->select_one("select is_open as shopopentype,starttime as opentime,shopname,id,address as shopaddress,phone as shopphone,shoptype from ".Mysite::$app->config['tablepre']."shop where uid='".$backinfo['uid']."' ");
		if(empty($shopinfo)) $this->message('获取店铺资料失败');
		if($shopinfo['shoptype'] !=1) $this->message('店铺不是超市店铺');
		
		$parenttype = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."marketcate where shopid='".$shopinfo['id']."' and id=".$ftype." order by id desc"); 
		if(empty($parenttype)) 			$this->message('上级分类不存在');
		if($parenttype['parent_id'] !=0) $this->message('上级分类不是一级分类'); 
		$id = intval(IFilter::act(IReq::get('id')));
		if(empty($id)) $this->message('删除ID获取失败');
		//增加个check  判断是否
		$nowtype = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."marketcate where shopid='".$shopinfo['id']."' and id=".$id." order by id desc"); 
		if(empty($nowtype)) $this->message('商品分类不存在');
		if($nowtype['parent_id'] ==0) $this->message('商品分类不是二级分类'); 
		if($nowtype['parent_id'] == 0){
		   $checkdata =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."marketcate where shopid='".$shopinfo['id']."' and parent_id=".$id." order by id desc"); 
			if(!empty($checkdata)) $this->message('该分类下有下级分类删除失败，删除失败');
		}else{
			$checkdata =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goods where shopid='".$shopinfo['id']."' and typeid=".$id." order by id desc"); 
			if(!empty($checkdata)) $this->message('该分类下有商品，删除失败');
		}
		$this->mysql->delete(Mysite::$app->config['tablepre']."marketcate"," shopid='".$shopinfo['id']."' and id=".$id." ");
		$shoptype =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."marketcate where shopid='".$shopinfo['id']."' and parent_id = '".$ftype."'  order by orderid desc");
		$this->success($shoptype); 
	}
	/*
	* 超市 商家添加二级商品分类 
	2015-12-26修改
	*/
	function addMarketTgoostype(){
		$backinfo = $this->checkapp();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$ftype = intval(IFilter::act(IReq::get('ftype')));
		if(empty($ftype)){
		   $this->message('无上级分类获取失败');
		}
		$shopinfo= $this->mysql->select_one("select is_open as shopopentype,starttime as opentime,shopname,id,address as shopaddress,phone as shopphone,shoptype from ".Mysite::$app->config['tablepre']."shop where uid='".$backinfo['uid']."' ");
		if(empty($shopinfo)) $this->message('获取店铺资料失败');
		if($shopinfo['shoptype'] !=1) $this->message('店铺不是超市店铺');
		$id = intval(IFilter::act(IReq::get('id')));
		$name = trim(IFilter::act(IReq::get('name')));
		$orderid = intval(IFilter::act(IReq::get('orderid')));
		//	id	shopid 店铺ID	name 分类名称	orderid	cattype 1外卖 2订台
		if(empty($name)) $this->message('分类名称不能为空');
		$newdata['shopid'] = $shopinfo['id'];
		$newdata['name'] = $name;
		$newdata['orderid'] = $orderid; 
		$newdata['parent_id'] = $ftype;
/* 		$newdata['bagcost'] = intval(IFilter::act(IReq::get('bagcost')));
		$newdata['uid'] = $backinfo['uid']; */
		$parenttype = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."marketcate where shopid='".$shopinfo['id']."' and id=".$ftype." order by id desc"); 
		if(empty($parenttype)) 			$this->message('上级分类不存在');
		if($parenttype['parent_id'] !=0) $this->message('上级分类不是一级分类');
		if($id > 0){
			$nowtype = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."marketcate where shopid='".$shopinfo['id']."' and id=".$id." order by id desc"); 
			if($nowtype['parent_id'] == 0) $this->message('当前分类不是二级分类');
			if($nowtype['shopid'] != $nowtype['shopid']) $this->message('当前分类不属于该店铺管理'); 
		}
		if(empty($id)){
			//新增
			$this->mysql->insert(Mysite::$app->config['tablepre']."marketcate",$newdata);
		}else{
			//编辑
			$this->mysql->update(Mysite::$app->config['tablepre'].'marketcate',$newdata,"id='".$id."' and shopid = '".$shopinfo['id']."' and parent_id = '".$ftype."' ");
		}
		$shoptype =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."marketcate where shopid='".$shopinfo['id']."' and parent_id = '".$ftype."' order by orderid desc");
		$this->success($shoptype);
	}
	//获取网站最新配置   ---- 获取网站加载模板
	function getnewconfig(){
		//	name 模块名称--固定写	imgurl 模块图标	is_display 0不在首页展示 1在首页展示	cnname 中文名称（统一录入 无ID 仅name关键字）	ctlname ctlname	is_install 0.APP完
	 
		$config = new config('appset.php',hopedir);   
	    $tempinfo = $config->getInfo(); 
		$data = $tempinfo;
	    $this->success($data);
		//推荐外卖商家
//热卖外卖商家
//新增外卖商家
//推荐超市
//普通超市
//Array ( [appinfo] => Array ( [APPindex] => 5 [apppayacount] => 1 [apppayonline] => 1 [appdataver] => 1 ) )
		//$data['appmodule'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."appmudel where  is_install = 1 order by name desc");
		//$data['appadv'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."marketcate where shopid='".$shopinfo['id']."' and parent_id = '".$ftype."' order by name desc");
	}
	function getnewadv(){
	    $data['appmodule'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."appmudel  order by name asc");
		$data['appadv'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."appadv  order by id asc");
	    $data['ztylist'] =   $this->mysql->getarr("select id,name,indeximg as img,showtype,cx_type,is_custom from ".Mysite::$app->config['tablepre']."specialpage where is_show=1 and cx_type<>9 and ((is_custom =0 ) or (  is_custom =1 and showtype =1) )  order by orderid  asc"); 
		$this->success($data);
	}
	function updateuserimg(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}   
		 
		
		$json = new Services_JSON();
		$uploadpath = 'upload/user/';
		$filepath = '/upload/user/';
		$upload = new upload($uploadpath,array('gif','jpg','jpge','png'));//upload
		$file = $upload->getfile();
		if($upload->errno!=15&&$upload->errno!=0) {
			$this->message($upload->errmsg());

		}else{
			$data['logo'] = $filepath.$file[0]['saveName'];
		    $this->mysql->update(Mysite::$app->config['tablepre'].'member',$data,"uid='".$backinfo['uid']."'  "); 
			$this->success($data);
		} 
	}
	
	
	function foodshow(){  	//菜品详情
		 $shopshowtype = ICookie::get('shopshowtype');
		$sellcount = intval(IReq::get('sellcount') );
		$id = intval( IReq::get('id') );
		$foodshow = $this->mysql->select_one( "select * from  ".Mysite::$app->config['tablepre']."goods where id= ".$id."  " );
		$shopid = $foodshow['shopid'];
		$shopdet  = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id  where a.shopid = ".$shopid."   "); 
		if( empty($shopdet)){
			$shopdet = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id  where a.shopid = ".$shopid."   "); 
		} 
		 
		$data['shopdet'] = $shopdet;
		$data['foodshow']  = $foodshow; 
		$data['sellcount'] = $sellcount;
	#	print_r( $shopdet);
		Mysite::$app->setdata($data);
	#	print_r($foodshow);
   }
   function addresslist(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		} 
		$this->pageCls->setpage(intval(IReq::get('page')),10); 
		/* 获取订单数:   shopname  id  shoplogo  allcost addtime  status paystatus   paytype  */
		$nowtime = time()-14*24*60*60;
		//  从地图选择的地址
 
		$orderlist = $this->mysql->getarr("select id,username,address,phone,contactname,`default`,sex,bigadr,detailadr,lat,lng from ".Mysite::$app->config['tablepre']."address where  userid = ".$backinfo['uid']."   order by id desc  limit ".$this->pageCls->startnum().", ".$this->pageCls->getsize()."");
		  
		if(empty($orderlist)) $this->success(array());
		$backdata = $orderlist; 
		$this->success($backdata);
	}
	function deladdress(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$addresid = intval(IReq::get('addresid'));
	    $this->mysql->delete(Mysite::$app->config['tablepre']."address"," id='".$addresid."' and  userid = '".$backinfo['uid']."'"); 
		$this->success('操作成功');
	}
	function addaddress(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$addresid = intval(IReq::get('addresid'));
		$data['username'] = $backinfo['username'];//隐藏的
		
		$data['phone'] = trim(IReq::get('phone'));
		$data['contactname'] = trim(IReq::get('contactname'));
		$data['default'] = intval(IReq::get('default'));
		$data['sex'] = intval(IReq::get('sex'));
		$data['bigadr'] = trim(IReq::get('bigadr'));
		$data['detailadr'] = trim(IReq::get('detailadr'));
		$data['lat'] = trim(IReq::get('lat'));
		$data['lng'] = trim(IReq::get('lng'));
		$data['address'] = $data['bigadr'].$data['detailadr']; 
		if(empty($data['address'])) $this->message('地区信息不能为空');
		if(!IValidate::suremobi($data['phone'])) $this->message('联系手机不是手机号'); 
		if(empty($data['contactname'])) $this->message('联系人信息不能为空');
		//if(empty($data['bigadr'])) $this->message('未选择所在街道和小区');
		if(empty($data['detailadr'])) $this->message('未录入所在门牌号');
		
	    if($data['default'] ==1){
			$cdata['default'] = 0;
			$this->mysql->update(Mysite::$app->config['tablepre'].'address',$cdata,"  userid = '".$backinfo['uid']."'   ");
		}
		if($addresid == 0){
			//新增
			$data['userid'] =$backinfo['uid'];
			$this->mysql->insert(Mysite::$app->config['tablepre']."address",$data);
			$this->success('新增成功');
		}else{
			//编辑
			$this->mysql->update(Mysite::$app->config['tablepre'].'address',$data,"id='".$addresid."' and userid = '".$backinfo['uid']."'   ");
			$this->success('编辑成功');
		} 
	}
	function setdefaddress(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
	    $addresid = intval(IReq::get('addresid'));
		
			
	    $checkinfo =  $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."address where id= '".$addresid."' and  userid = ".$backinfo['uid']."   order by id desc  limit 0,10");
		if(empty($checkinfo)){
			$this->message('地址信息不存在');
		} 
			$cdata['default'] = 0;
			$this->mysql->update(Mysite::$app->config['tablepre'].'address',$cdata,"  userid = '".$backinfo['uid']."'   ");
			$this->mysql->update(Mysite::$app->config['tablepre'].'address',array('default'=>1)," id='".$addresid."' and  userid = '".$backinfo['uid']."'   ");
       $this->success('操作成功');   
   }
   
   
    
	//充值卡充值
	function exchangcard(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$card = trim(IFilter::act(IReq::get('card')));
		$password = trim(IFilter::act(IReq::get('password')));
		if(empty($card)) $this->message('card_emptycard');
		if(empty($password)) $this->message('card_emptycardpwd');
		$checkinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."card where card ='".$card."'  and card_password = '".$password."' and uid =0 and status = 0");
		if(empty($checkinfo)) $this->message('card_cardiuser');
		$arr['uid'] = $backinfo['uid'];
		$arr['status'] =  1;
		$arr['username'] = $backinfo['username'];
		$this->mysql->update(Mysite::$app->config['tablepre'].'card',$arr,"card ='".$card."'  and card_password = '".$password."' and uid =0 and status = 0");
		//`$key`
		$this->mysql->update(Mysite::$app->config['tablepre'].'member','`cost`=`cost`+'.$checkinfo['cost'],"uid ='".$backinfo['uid']."' ");
		$allcost = $backinfo['cost']+$checkinfo['cost'];
		$this->memberCls->addlog($backinfo['uid'],2,1,$checkinfo['cost'],'充值卡充值','使用充值卡'.$checkinfo['card'].'充值'.$checkinfo['cost'].'元',$allcost);
		$this->success($allcost);
	}
	//资金记录
	function paylog(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		
		 
		$page = intval(IFilter::act(IReq::get('page')));
		$pagesize = intval(IFilter::act(IReq::get('pagesize'))); 
		$pagesize = $pagesize > 0? $pagesize:10;
		
		$this->pageCls->setpage($page,$pagesize);  
		$backdata = array();
	 
		$memberlog = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."memberlog   where userid ='".$backinfo['uid']."' and   type=2   order by id desc limit ".$this->pageCls->startnum().", ".$this->pageCls->getsize()." ");
		
		foreach($memberlog as $key=>$value){
			 $value['addtime'] = date('Y-m-d H:i',$value['addtime']);
			 $backdata[] = $value;
		}
		$this->success($backdata); 
	}
	
	function scorelog(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		
		 
		$page = intval(IFilter::act(IReq::get('page')));
		$pagesize = intval(IFilter::act(IReq::get('pagesize'))); 
		$pagesize = $pagesize > 0? $pagesize:10;
		
		$this->pageCls->setpage(intval(IReq::get('page')),$pagesize);  
		$backdata = array();
		//
		$memberlog = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."memberlog   where userid ='".$backinfo['uid']."' and   type=1   order by id desc limit ".$this->pageCls->startnum().", ".$this->pageCls->getsize()." ");
		
		foreach($memberlog as $key=>$value){
			 $value['addtime'] = date('Y-m-d H:i',$value['addtime']);
			 $backdata[] = $value;
		}
		$this->success($backdata); 
	}
	function pingall(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		
		
		$orderid = intval( IFilter::act(IReq::get('orderid')) );
		$orderinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where buyeruid='".$backinfo['uid']."' and id = ".$orderid."");  
		if($orderinfo['is_ping'] == 1) $this->message('order_isping');		
	    if(empty($orderinfo)) $this->message('获取此订单失败'); 
		$orderdet = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."orderdet where order_id='".$orderinfo['id']."'");  
		$data['orderid'] =   $orderinfo['id']; 
		 
		$data['shopid'] = $orderinfo['shopid'];
		if(empty($backinfo['uid'])) $this->message('获取用户失败');
		$data['uid'] = $backinfo['uid'];
		$data['addtime'] = time();
		$data['is_show'] = 0;
		$shoppointnum =  trim( IFilter::act(IReq::get('shoppointnum')) );
		$shopsudupointnum =  intval( IFilter::act(IReq::get('shopsudupointnum')) ); 
		
		if(empty($shoppointnum)) $this->message('请评论总体评价');
		if(empty($shopsudupointnum)) $this->message('请评论配送服务');
		
	    foreach($orderdet as $key=>$value){
			$data['point'] = intval( IFilter::act(IReq::get('goodsid_'.$value['id'])) );
			$data['content'] =  trim( IFilter::act(IReq::get('content_'.$value['id'])) );
			$data['orderdetid'] = $value['id'];
			$data['goodsid'] =   $value['goodsid'];
			 
			if(!empty($data['point']) || !empty($data['content']) ){
				$this->mysql->insert(Mysite::$app->config['tablepre'].'comment',$data);
				$udata['status'] = 1;
				$this->mysql->update(Mysite::$app->config['tablepre'].'orderdet',$udata,"id='".$value['id']."'");
				
				
						  //商品评分
				  $goodinfo  = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goods where id='".$value['goodsid']."'  "); 
				  $goodpointcount = $goodinfo['pointcount']; 
				  $goodnewpoint['point'] = intval($goodinfo['point']+$data['point']);
				  $goodnewpoint['pointcount'] = intval($goodpointcount+1);
				   $this->mysql->update(Mysite::$app->config['tablepre'].'goods',$goodnewpoint,"id='".$value['goodsid']."'");
					
				
				
				 /*写日志*/
				  $issong = 1;
				  if(intval(Mysite::$app->config['commentday']) > 0){//检测是否赠送积分
						   $uptime = Mysite::$app->config['commentday']*24*60*60;
						   $uptime = $orderinfo['addtime'] +$uptime;
						   if($uptime > time()){
							  $issong = 1;
						   }else{
							  $issong = 0;
						   }
				  }
				  $fscoreadd = 0;
				  if(intval(Mysite::$app->config['commenttype']) > 0 && $issong == 1)
				  { //赠送积分 大于0赠送积分到用户帐号  赠送基础积分
					$scoreadd = Mysite::$app->config['commenttype'];
					$checktime = date('Y-m-d',time());
					$checktime = strtotime($checktime);
					$checklog = $this->mysql->select_one("select sum(result) as jieguo from ".Mysite::$app->config['tablepre']."memberlog where type = 1 and   userid = ".$backinfo['uid']." and addtype =1 and  addtime > ".$checktime);
						if(Mysite::$app->config['maxdayscore'] > 0){
							$checkguo = $checklog['jieguo']+$scoreadd;
							if($checkguo < Mysite::$app->config['maxdayscore']){
								//最大值小于当前和
							}elseif(Mysite::$app->config['maxdayscore'] > $checklog['jieguo']){
								//最大指 大于 已增指
								$scoreadd = Mysite::$app->config['maxdayscore'] - $checklog['jieguo'];
							}else{
								$scoreadd = 0;
							}
						}
						if($scoreadd > 0){
						   $this->mysql->update(Mysite::$app->config['tablepre'].'member','`score`=`score`+'.$scoreadd,"uid='".$backinfo['uid']."'");
						   $fscoreadd =$scoreadd;
						 $memberallcost = $backinfo['score']+$scoreadd;
						 $this->memberCls->addlog($backinfo['uid'],1,1,$scoreadd,'评价商品','评价商品'.$orderdet['goodsname'].'获得'.$scoreadd.'积分',$memberallcost);
					   }
				  }
				  
				  
				  
				  
				
				
				
			}
		}
	
		  
			$this->mysql->update(Mysite::$app->config['tablepre'].'order','`is_ping`=1',"id='".$orderinfo['id']."'");
		  
			$ordCls = new orderclass();
			$ordCls->writewuliustatus($orderinfo['id'],11,$orderinfo['paytype']);  // 用户已评价订单，完成订单
		 

		// 查询子订单是否所有的状态都为 1，  是的话更新订单标志
	  $shuliang  = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."orderdet where order_id='".$orderinfo['id']."' and status = 0");
	  if($shuliang < 1)//订单已评价完毕
	  { 
		  
	     if(intval(Mysite::$app->config['commentscore']) > 0 && $issong ==  1){//扩张积分 大于0
				   $scoreadd = intval(Mysite::$app->config['commentscore'])*$orderinfo['allcost'];
				   $checktime = date('Y-m-d',time());
				 $checktime = strtotime($checktime);
				 $checklog = $this->mysql->select_one("select sum(result) as jieguo from ".Mysite::$app->config['tablepre']."memberlog where type = 1 and   userid = ".$backinfo['uid']." and addtype =1 and  addtime > ".$checktime);
				 if(Mysite::$app->config['maxdayscore'] > 0){
					   $checkguo = $checklog['jieguo']+$scoreadd;
					   if($checkguo < Mysite::$app->config['maxdayscore']){
							//最大值小于当前和
					   }elseif(Mysite::$app->config['maxdayscore'] > $checklog['jieguo']){
							//最大指 大于 已增指
						  $scoreadd = Mysite::$app->config['maxdayscore'] - $checklog['jieguo'];
					   }else{
							$scoreadd = 0;
					   }
				 }
				 if($scoreadd > 0){
					   $this->mysql->update(Mysite::$app->config['tablepre'].'member','`score`=`score`+'.$scoreadd,"uid='".$backinfo['uid']."'");
					 $memberallcost = $backinfo['score']+$scoreadd+$fscoreadd;
				   $this->memberCls->addlog($backinfo['uid'],1,1,$scoreadd,'评价完订单','评价完订单'.$orderinfo['dno'].'奖励，'.$scoreadd.'积分',$memberallcost);
				 }
			 }
		  }
		   
		  $shopinfo  = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where id='".$orderinfo['shopid']."' ");
		  $shuliang = $shopinfo['point'];
		  $pointcount = $shopinfo['pointcount'];
		  $psservicepoint = $shopinfo['psservicepoint'];
		  $psservicepointcount = $shopinfo['psservicepointcount'];
	
		  $newpoint['point'] = intval($shoppointnum+$shuliang);
		  $newpoint['pointcount'] = intval($pointcount+1);
		  $newpoint['psservicepoint'] = intval($psservicepoint+$psservicepointcount);
		  $newpoint['psservicepointcount'] = intval($psservicepointcount+1);
	
		  $this->mysql->update(Mysite::$app->config['tablepre'].'shop',$newpoint,"id='".$orderinfo['shopid']."'");
		
	
		$this->success('success');
	}
	function apppaydata(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		$type = IFilter::act(IReq::get('type')); 
		$cost= IFilter::act(IReq::get('cost'));
		$orderid = IFilter::act(IReq::get('orderid')); 
		$alipaydata = array(
						'paytype'=>'order',
						'orderid'=>$orderid,
						'allcost'=>'',
						'wmrid'=>'',
						'support'=>0,
					  );
		$platedata = array( 
						'acountcost'=>$backinfo['cost'], 
						'support'=>0,
					  );	
		$weixindata = array( 
						'appid'=>'',
						'noncestr'=>'',
						'package'=>'Sign=WXPay',
						'partnerid'=>'',
						'prepayid'=>'',
						'timestamp'=>'',
						'sign'=>'', 
						'support'=>0,
					  );
		$weixindir = hopedir.'/plug/pay/weixinapp/'; 			  
		$weixincheck = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."paylist where  loginname ='weixinapp'   order by id asc limit 0,1");
		$alipaychek = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."paylist where  loginname ='alipayapp'   order by id asc limit 0,1");
		$acountpaychek = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."paylist where  loginname ='open_acout'   order by id asc limit 0,1");
		$allcost = 0;
		 
		$titlename = "在线充值";
		if($type == 'acount'){//余额充值 
		    $allcost = $cost;
			$titlename = "在线充值";
			if(!empty($alipaychek)){
				$dopaydata = array('type'=>'acount','upid'=>$backinfo['uid'],'cost'=>$cost,'source'=>0,'status'=>0,'addtime'=>time());//支付数据  
				$this->mysql->insert(Mysite::$app->config['tablepre'].'onlinelog',$dopaydata);
				$newid = $this->mysql->insertid(); 
				$alipaydata['paytype'] = 'acount';
				$alipaydata['allcost'] = $cost;
				$alipaydata['wmrid']=$newid;
				$alipaydata['support'] = 1;
			}
			if(!empty($weixincheck)){
			 
				require_once $weixindir."lib/WxPay.Api.php";
				//require_once $weixindir."WxPay.JsApiPay.php";   
				$dno = 'acount_'.$backinfo['uid'];
				$dtime = time();
				$acountid = 'acount_'.time(); 
				//②、统一下单
				$input = new WxPayUnifiedOrder();
				$input->SetBody("账号充值的".$backinfo['username']);
				$input->SetAttach($dno);
				$input->SetOut_trade_no($acountid);
				$input->SetTotal_fee($cost*100);
				$input->SetTime_start(date("YmdHis"));
				$input->SetTime_expire(date("YmdHis", time() + 600));
				$input->SetTimeStamp($dtime);
				$input->SetGoods_tag('在线充值');
				$input->SetNotify_url(Mysite::$app->config['siteurl']."/plug/pay/weixinapp/notify.php");
				$input->SetTrade_type("APP");  
				//$input->SetOpenid($openId);
			    
				$ordermm = WxPayApi::unifiedOrder($input); 
				if($ordermm['error'] ==true){
					//2次签名 
					//print_r($ordermm);
					$weixindata= $ordermm['inputdata'];
					$weixindata['support'] = 1; 
					
				}else{
					
					
					
				}
			}
			 
			
		}elseif($type == 'order'){//支付订单
		   
			$orderinfo =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where  id ='".$orderid."'   order by id asc limit 0,1");
			if(empty($orderinfo))$this->message('订单不存在');
			if($orderinfo['paytype'] != 1) $this->message('订单不是在线支付订单');
			if($orderinfo['status'] > 1) $this->message('订单状态不能支付');
			if($orderinfo['is_make'] ==2) $this->message('商家不受理该订单不能支付');
			if($orderinfo['paytstatus'] ==1) $this->message('订单已支付不能重复支付');
			/*
			$checktime = time()-15*60;
			if($orderinfo['addtime'] < $checktime){
				 $newdata['status'] = 4; 
				$this->mysql->update(Mysite::$app->config['tablepre'].'order',$newdata,"id='".$orderid."'");
				$orderCLs = new orderclass(); 
				$orderCLs->writewuliustatus($orderinfo['id'],16,$orderinfo['paytype']);  //在线支付成功状态	 
				$this->message('订单在15分钟内未支付已取消');
			} */
			$titlename = $orderinfo['dno'];
			$allcost = $orderinfo['allcost'];
			if(!empty($alipaychek)){  
				$dopaydata = array('type'=>'order','upid'=>$orderid,'cost'=>$orderinfo['allcost'],'source'=>0,'status'=>0,'addtime'=>time());//支付数据 
				$this->mysql->insert(Mysite::$app->config['tablepre'].'onlinelog',$dopaydata);
				$newid = $this->mysql->insertid(); 
				$alipaydata['allcost'] = $orderinfo['allcost'];
				$alipaydata['wmrid']=$newid;
				$alipaydata['support'] = 1;
			}
			if(!empty($acountpaychek)){
				$platedata['support'] = 1;
				$platedata['acountcost'] = $backinfo['cost'];
			}
			if(!empty($weixincheck)){
				require_once $weixindir."lib/WxPay.Api.php";
				//require_once $weixindir."WxPay.JsApiPay.php"; 
				//②、统一下单
				$dtime = time();
				$input = new WxPayUnifiedOrder();
				$input->SetBody("支付订单".$orderinfo['dno']);
				$input->SetAttach($orderinfo['dno']);
				$input->SetOut_trade_no($orderinfo['id']);
				$input->SetTotal_fee($orderinfo['allcost']*100);
				$input->SetTime_start(date("YmdHis"));
				$input->SetTime_expire(date("YmdHis", time() + 600));
				$input->SetTimeStamp($dtime);
				$input->SetGoods_tag('订餐');
				$input->SetNotify_url(Mysite::$app->config['siteurl']."/plug/pay/weixinapp/notify.php");
				$input->SetTrade_type("APP"); 
				//$url = Mysite::$app->config['siteurl'].'/plug/pay/weixin/jsapi.php';
				//print_r($input); 
				 $ordermm = WxPayApi::unifiedOrder($input); 
				if($ordermm['error'] ==true){
					//2次签名 
					//print_r($ordermm);
					$weixindata= $ordermm['inputdata'];
					$weixindata['support'] = 1; 
					
				}else{
					
					
					
				}
				 
			}
			
			
		}else{
			$this->message('未定义的支付');
		}  
		$backdata=array(
					'alipay'=>$alipaydata,
					'plate'=>$platedata,
					'weixin'=>$weixindata,
					'allcost'=>$allcost,//待支付总金额
					'titlename'=>$titlename
		 );  	 
		$this->success($backdata);
		
	}
	//账号余额支付
	function appacoutpay(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		if($this->checksession()){
			$type = IFilter::act(IReq::get('type')); 
			$cost= IFilter::act(IReq::get('cost'));
			$orderid = IFilter::act(IReq::get('orderid')); 
			if($type == 'order'){//支付订单 
				$orderinfo =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where  id ='".$orderid."'   order by id asc limit 0,1");
				if(empty($orderinfo))$this->message('订单不存在');
				if($orderinfo['paytype'] != 1) $this->message('订单不是在线支付订单');
				if($orderinfo['status'] > 1) $this->message('订单状态不能支付');
				if($orderinfo['is_make'] ==2) $this->message('商家不受理该订单不能支付');
				if($orderinfo['paytstatus'] ==1) $this->message('订单已支付不能重复支付');
				/*
				$checktime = time()-15*60;
				if($orderinfo['addtime'] < $checktime){
					 $newdata['status'] = 4; 
					$this->mysql->update(Mysite::$app->config['tablepre'].'order',$newdata,"id='".$orderid."'");
					$orderCLs = new orderclass(); 
					$orderCLs->writewuliustatus($orderinfo['id'],16,$orderinfo['paytype']);  //在线支付成功状态	 
					$this->message('订单在15分钟内未支付已取消');
				} */
				if(Mysite::$app->config['open_acout'] != 1) $this->message('网站开启支付');
				
				if($backinfo['cost'] < $orderinfo['cost']) $this->messaget('账号余额不足'); 
				$this->mysql->update(Mysite::$app->config['tablepre'].'member','`cost`=`cost`-'.$orderinfo['allcost'],"uid ='".$backinfo['uid']."' ");
				//更新订单数据 
				$orderdata['paystatus'] = 1;
				if($orderinfo['status'] == 0){
				   $orderdata['status'] = 1; 
				} 
				$orderdata['paytype_name'] = 'open_acout';
				$this->mysql->update(Mysite::$app->config['tablepre'].'order',$orderdata,"id ='".$orderid."' ");

				$accost = $backinfo['cost']-$orderinfo['allcost'];
				$this->memberCls->addlog($backinfo['uid'],2,2,$orderinfo['allcost'],'余额支付订单','支付订单'.$orderinfo['dno'].'帐号金额减少'.$orderinfo['allcost'].'元', $accost);
				$this->memberCls->addmemcostlog($orderinfo['buyeruid'],$backinfo['username'],$backinfo['cost'],2,$orderinfo['allcost'],$accost,"下单余额消费",$backinfo['uid'],$backinfo['username']);
				$orderCLs = new orderclass(); 
					 $orderCLs->writewuliustatus($orderinfo['id'],3,$orderinfo['paytype']);  //在线支付成功状态	 
					 if($orderinfo['is_make']  == 1 ){
						 $orderCLs->writewuliustatus($orderinfo['id'],4,$orderinfo['paytype']);  //商家自动确认接单	  
						  $auto_send = Mysite::$app->config['auto_send'];
							  if($auto_send == 1){
								 $orderCLs->writewuliustatus($orderinfo['id'],6,$orderinfo['paytype']);//订单审核后自动 商家接单后自动发货
							  }
					 } 
				$orderCLs->sendmess($orderid); 
				$this->success('支付成功');
			}else{ 
				$this->message('未定义的支付');
			} 
		}else{
			$this->message('请稍后在试');
		}
	}
	function  checksession(){
		 session_start(); 
		 $time = $_SESSION['expire_time'];
		 if(empty($token)){
			 $_SESSION['token'] =time(); 
			 return true;
		 }elseif($time -3 > time()){
			  $_SESSION['token'] =time(); 
			 return true;
		 }else{
			 return false;
		 } 
	} 
           
	function single(){
		$code = trim(IFilter::act(IReq::get('code')));
		$data['single'] =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."single where  code ='".$code."'   order by id asc limit 0,1");
		
	     Mysite::$app->setdata($data); 
	}
	function specialpage(){ //专题页
		$id = intval(IReq::get('id'));
		$data['id'] = $id;
		$lat = trim(IReq::get('lat'));
		$lng = trim(IReq::get('lng'));
		$mapname = trim(IReq::get('mapname'));
		$data['lat'] = $lat;
		$data['lng'] = $lng;
		$data['mapname'] = $mapname;
		ICookie::set('lng',$lng,2592000);  
		ICookie::set('lat',$lat,2592000);  
		ICookie::set('mapname',$mapname,2592000);  
		$ztyinfo = $this->mysql->select_one(" select * from ".Mysite::$app->config['tablepre']."specialpage where id = ".$id."  ");
		$data['ztyinfo'] = $ztyinfo;
		$data['addressname'] = ICookie::get('addressname');
		 
		/* 
								<{if $items['cx_type'] == 6}>【外卖订餐】<{/if}>
								<{if $items['cx_type'] == 7}>【在线超市】<{/if}>
								<{if $items['cx_type'] == 8}>【预定点菜】<{/if}>
								<{if $items['cx_type'] == 9}>【跑腿服务】<{/if}>
		*/
		if($ztyinfo['is_custom'] == 1 && $ztyinfo['showtype'] == 0 && $ztyinfo['cx_type'] > 5 ){
			if($ztyinfo['cx_type'] == 6){
				$link = IUrl::creatUrl('wxsite/waimai'); 
			}
			if($ztyinfo['cx_type'] == 7){
				$link = IUrl::creatUrl('wxsite/marketlist'); 
			}
			if($ztyinfo['cx_type'] == 8){
				$link = IUrl::creatUrl('wxsite/dingtai'); 
			}
			if($ztyinfo['cx_type'] == 9){
				$link = IUrl::creatUrl('wxsite/paotui'); 
			}
			$this->message('',$link);
		}
		$speciallist = $this->getztyshowlist($ztyinfo['is_custom'],$ztyinfo['showtype'],$ztyinfo['cx_type'],$ztyinfo['listids'],$lat,$lng);
		
		#print_r($speciallist);		
		 
		 $data['speciallist'] = $speciallist;
		 
		 
		Mysite::$app->setdata($data);
	}
	function getztyshowlist($is_custom,$showtype,$cx_type,$listids,$lat,$lng){
		$cxsignlist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodssign where type='cx' order by id desc limit 0, 100");
		$cxarray  =  array();
		foreach($cxsignlist as $key=>$value){
		   $cxarray[$value['id']] = $value['imgurl'];
		}
		$weekji = date('w');
		$nowhour = date('H:i:s',time()); 
         $nowhour = strtotime($nowhour);
          $templist = array();
           $cxclass = new sellrule(); 
		    
							$where = '';  
            	  
						 
  //   $where = empty($where)?'   and  SQRT((`lat` -'.$lat.') * (`lat` -'.$lat.' ) + (`lng` -'.$lng.' ) * (`lng` -'.$lng.' )) < (`pradiusa`*0.01094) ': $where.' and SQRT((`lat` -'.$lat.') * (`lat` -'.$lat.' ) + (`lng` -'.$lng.' ) * (`lng` -'.$lng.' )) < (`pradiusa`*0.01094) ';
            	 
            	         
            	       
                 
					  $pageinfo = new page();
					 $pageinfo->setpage(intval(IReq::get('page')),10);  
	  //  limit ".$pageinfo->startnum().", ".$pageinfo->getsize()."
		   
	  
if($showtype == 0){    // 店铺 
       if($is_custom == 0 ){   //自定义专题页情况下
			if(!empty($listids)){
				$list = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shop where id in (".$listids.") ".$where."  order by sort asc limit ".$pageinfo->startnum().", ".$pageinfo->getsize()."  ");   		   
			}else{
				$list = array();
			}
	   }
	   if($is_custom == 1 ){  // 系统默认  cx_type 店铺 1为推荐店铺  2为满减商家 3为打折商家 4免配送费     //  private $rulecontrol = array('1'=>'赠送','2'=>'减费用','3'=>'折扣','4'=>免配送费);
		   switch ($cx_type){ 
			case 1: 
				$ztywhere =  "  and  is_recom = 1   ";
				$list = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shop where is_pass = 1 ".$ztywhere."  ".$where."   order by sort asc limit ".$pageinfo->startnum().", ".$pageinfo->getsize()." ");
				break; 
			case 2:
				$list = $this->getdycxshops(2);
				break;
			case 3:
				$list = $this->getdycxshops(3);
				break;
			case 4:
				$list = $this->getdycxshops(4);
				break;
			case 5:
				$list = $this->getdycxshops(1);
				break;
			default : 
				$list = array();
				break; 
			}  
	   } 
            	   
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
						
										
										
										
										/* 店铺星级计算 */
									$zongpoint = $values['point'];
									$zongpointcount = $values['pointcount'];
									if($zongpointcount != 0 ){
										$values['shopstart'] = intval( round($zongpoint/$zongpointcount) );
									}else{
										$values['shopstart'] = 0;
									}
		 
										if($values['shoptype'] == 1 ){
											$shopdet = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket where  shopid = ".$values['id']."   ");
										}else{
											$shopdet = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast where  shopid = ".$values['id']."   ");
										}
										if(!empty($shopdet)){ 
									 	$values = array_merge($values,$shopdet);
										}
            			    			$values['shoplogo'] = empty($values['shoplogo'])? Mysite::$app->config['imgserver'].Mysite::$app->config['shoplogo']:Mysite::$app->config['imgserver'].$values['shoplogo'];
            			          $checkinfo = $this->shopIsopen($values['is_open'],$values['starttime'],$values['is_orderbefore'],$nowhour); 
            			          $values['opentype'] = $checkinfo['opentype'];
            			          $values['newstartime']  =  $checkinfo['newstartime'];  
            			          $attrdet = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shopattr where  cattype = ".$values['shoptype']." and shopid = ".$values['id']."");
            			          $cxclass->setdata($values['id'],1000,$values['shoptype']); 
								  
								  
            			          $checkps = 	 $this->pscost($values,1,$areaid); 
            			          $values['pscost'] = $checkps['pscost'];
								  
								  
								  	  $mi = $this->GetDistance($lat,$lng, $values['lat'],$values['lng'], 1); 
									$tempmi = $mi;
								  $mi = $mi > 1000? round($mi/1000,2).'km':$mi.'m';
													  
									$values['juli'] = 		$mi;
								  
								    $firstday = strtotime( date('Y-m-01 00:00:00', strtotime(date("Y-m-d H:i:s")))	);   //当月第一天
									$lastday = strtotime( date('Y-m-d 00:00:00',strtotime("$firstday +1 month -1 day"))	);  //当月最后一天
								 
	 $shopcounts = $this->mysql->select_one( "select count(id) as shuliang  from ".Mysite::$app->config['tablepre']."order	 where suretime >= ".$firstday." and suretime <= ".$lastday."  and status = 3 and  shopid = ".$values['id']."" );
								#	print_r(  $shopcounts );
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
								    /* 店铺星级计算 */
								$zongpoint = $values['point'];
								$zongpointcount = $values['pointcount'];
								if($zongpointcount != 0 ){
									$shopstart = intval( round($zongpoint/$zongpointcount) );
								}else{
									$shopstart= 0;
								}
									$values['point'] = 	$shopstart;	
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


if($showtype == 1){    // 加载商品

	 if($is_custom == 0 ){   //自定义专题页情况下
			if(!empty($listids)){
 				$list = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shop where is_pass = 1  ".$where."    order by id desc ");
				 
			}else{
				$list = array();
			}
			 
	   }
	   if($is_custom == 1 ){  // 系统默认  cx_type 商品1为折扣
		   switch ($cx_type){ 
			case 1:  
				$list = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shop where is_pass = 1  ".$where."   order by id desc ");
				break;
			default : 
				$list = array();
				break; 
			}  
	   } 
	  
			if(is_array($list)){
            			    foreach($list as $keys=>$vatt){  
            			     
           if($vatt['id'] > 0){
			   if($is_custom == 0){
					$detaa = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goods where  id in (".$listids.")  and shopid='".$vatt['id']."'  and shoptype = ".$vatt['shoptype']."  and    FIND_IN_SET( ".$weekji." , `weeks` )  ".$goodlistwhere."   order by good_order asc  limit ".$pageinfo->startnum().", ".$pageinfo->getsize()." ");
			
			  }else{
					$detaa = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goods where shopid='".$vatt['id']."'  and shoptype = ".$vatt['shoptype']."  and    FIND_IN_SET( ".$weekji." , `weeks` )  ".$goodlistwhere."   order by good_order asc limit ".$pageinfo->startnum().", ".$pageinfo->getsize()." ");
			   }
			   
					if(!empty($detaa)){
						 
				
					
						foreach ( $detaa as $keyq=>$valq ){
							if($valq['is_cx'] == 1){
							//测算促销 重新设置金额
								$cxdata = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goodscx where goodsid=".$valq['id']."  ");
								$newdata = getgoodscx($valq['cost'],$cxdata);
								
								$valq['zhekou'] = $newdata['zhekou'];
								$valq['is_cx'] = $newdata['is_cx'];
								$valq['cost'] = $newdata['cost'];
							}
							if( $shoptype == 1 ){
								 $shopdet = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket where  shopid = ".$valq['shopid']."   ");
							}else{
								  $shopdet = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast where  shopid = ".$valq['shopid']."   ");
							}
							$checkinfo = $this->shopIsopen($vatt['is_open'],$vatt['starttime'],$shopdet['is_orderbefore'],$nowhour); 
            			    $valq['opentype'] = $checkinfo['opentype'];
            			    $valq['shopname'] = $vatt['shopname'];
						//	print_r($valq['is_cx']);
							if(  $is_custom == 1){	
								if(  $valq['is_cx'] == 1 ){	
									 $templist[]  = $valq;  
								}
							}else{
								$templist[]  = $valq;
							}
						}
						
					 }	
			 
				 
									}
            	       } 
            	    }


} 
	   $data = $templist;
	  
	  return $data;
	   
	}
	function fabupaotui(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		if($backinfo['uid'] == 0)  $this->message('未登陆'); 
		$ptinfoset = $this->mysql->select_one(" select * from ".Mysite::$app->config['tablepre']."paotuiset  "); 
		$demandcontent = trim(IFilter::act(IReq::get('demandcontent')));  // 需求内容
		 // 取货地址： 地址 补充地址  lng lat 
	//	$getaddress = trim(IReq::get('getaddress')); 
		$getdetaddress = trim(IReq::get('getdetaddress'));
		$getlng = trim(IReq::get('getlng'));
		$getlat = trim(IReq::get('getlat'));
		 // 收货地址： 地址 补充地址  lng lat 
	//	$shouaddress = trim(IReq::get('shouaddress'));
		$shouetaddress = trim(IReq::get('shouetaddress'));
		$shoulng = trim(IReq::get('shoulng'));
		$shoulat = trim(IReq::get('shoulat'));

		$getphone = trim(IReq::get('getphone'));  // 取货电话
		$shouphone = trim(IReq::get('shouphone'));  // 收货电话
		$minit = trim(IReq::get('minit'));			// 收/取 货时间
		$ptkg = trim(IReq::get('ptkg'));	// 货 公斤 数 
		$farecost = trim(IReq::get('farecost'));		// 加价（小费） 
		$pttype = trim(IReq::get('pttype'));		// 	1为帮我送 2为帮我买
		$paytype = 1;		//  支付方式，默认为在线支付

		if( empty($demandcontent) )  $this->message("请简要填写需求内容");
		if( empty($getdetaddress) )  $this->message("获取取货地址失败,请重新获取");
		if( empty($getlng) )  $this->message("获取取货地址失败,请重新获取");
		if( empty($getlat) )  $this->message("获取取货地址失败,请重新获取");

		if( empty($shouetaddress) )  $this->message("获取收货地址失败,请重新获取");
		if( empty($shoulng) )  $this->message("获取收货地址失败,请重新获取");
		if( empty($shoulat) )  $this->message("获取收货地址失败,请重新获取");
		
		if($farecost < 0) $this->message("加价格式错误");

		if($pttype==1){
			if( empty($getphone) )  $this->message("请填写取货电话");
			if(!IValidate::suremobi($getphone))   $this->message('请输入正确的手机号'); 	
		}
		if( empty($shouphone) )  $this->message("请填写收货电话");
		if(!IValidate::suremobi($shouphone))   $this->message('请输入正确的手机号'); 	

		if( $minit == 0 ){
			 $data['sendtime'] = time();
			 $data['postdate'] = '立即取货';
		}else{ 
			$tempdata = $this->getOpenPosttime($ptinfoset['is_ptorderbefore'],time(),$ptinfoset['postdate'],$minit,$ptinfoset['pt_orderday']); 
		    if($tempdata['is_opentime'] ==  2) $this->message('选择的配送时间段，店铺未设置');
			if($tempdata['is_opentime'] == 3) $this->message('选择的配送时间段已超时');
			$data['sendtime'] = $tempdata['is_posttime'];
			$data['postdate'] = $tempdata['is_postdate']; 
		}

		$data['pttype'] = $pttype;  // 1为帮我送  2为帮我买

		$data['content'] = $demandcontent;
		$data['shopaddress']  = $getdetaddress;   
		$data['buyeraddress']  = $shouetaddress;  
		if($pttype==1){
			$data['shopphone']  = $getphone;			//取件电话
		}
		$data['buyerphone']  = $shouphone;			//收件电话
		$data['addtime'] = time();
		$data['ordertype'] = 3;//订单类型
		$data['shoptype'] = 100;//订单类型
		$data['paytype'] = $paytype; 
		$data['paystatus'] = 0;  
		$data['ptkg'] = $ptkg;  
		$data['dno'] = time().rand(1000,9999);
		$data['pstype']  = 1;
		$data['buyeruid']  = $backinfo['uid'];
		$data['buyername'] = $backinfo['username']; 
		/* 计算两点之间的距离  并且 判断是否与前台的  千米距离金额是否一致 */
		$juli = $this->GetDistance($getlat,$getlng, $shoulat,$shoulng, 1,1); 
		$tempmi = $juli; 	
		$juli = round($juli/1000,1); 
		$tmpallkmcost =  0;
		if( $juli <= $ptinfoset['km']  ){
			$tmpallkmcost = $ptinfoset['kmcost'];
		}else{
			$addjuli = $juli-$ptinfoset['km'];
			$addnum = floor( ($addjuli/$ptinfoset['addkm']));
			$addcost = $addnum*$ptinfoset['addkmcost'];
			$tmpallkmcost = $ptinfoset['kmcost']+$addcost;
		}  
		$data['ptkm'] = $juli;
		/* 计算重量  并且 判断是否与前台的  公斤金额是否一致 */
		$tmpallkgcost =  0;
		if( $ptkg <= $ptinfoset['kg']  ){
			$tmpallkgcost = $ptinfoset['kgcost'];
		}else{
			$addkg = $ptkg-$ptinfoset['kg'];
			$addkgnum = floor( ($addkg/$ptinfoset['addkg'])); 
			$addkgcost = $addkgnum*$ptinfoset['addkgcost']; 
			$tmpallkgcost = $ptinfoset['kgcost']+$addkgcost; 
		} 
		$data['allkgcost'] = $tmpallkgcost;
		$data['allkmcost'] = $tmpallkmcost;
		$data['farecost'] = $farecost;
		$data['allcost'] = $farecost+$tmpallkgcost+$tmpallkmcost;
		 
		$data['status'] = 0;
		$data['ipaddress'] = "";
		$ip_l=new iplocation(); 
		$ipaddress=$ip_l->getaddress($ip_l->getIP());  
		if(isset($ipaddress["area1"])){
			$data['ipaddress']  = $ipaddress['ip'].mb_convert_encoding($ipaddress["area1"],'UTF-8','GB2312');//('GB2312','ansi',);
		} 
		   
		$this->mysql->insert(Mysite::$app->config['tablepre'].'order',$data);
		$orderid = $this->mysql->insertid();  
		/* 写订单物流 状态 */
		/* 第一步 提交成功 */
		$orderClass = new orderClass();
		$orderClass->writewuliustatus($orderid,1,$data['paytype']); 
		$this->success($orderid);
		
		
	}
	function paotuitime(){
		$ptinfoset = $this->mysql->select_one(" select * from ".Mysite::$app->config['tablepre']."paotuiset  "); 
		  $postdate =  $ptinfoset['postdate'];
		  $befortime = $ptinfoset['pt_orderday'];
		  
		$nowhout = strtotime(date('Y-m-d',time()));//当天最小linux 时间
		$timelist = !empty($postdate)?unserialize($postdate):array();
		$pstimelist = array();
		$checknow = time();
		
		 
		$whilestatic = $befortime;
		$nowwhiltcheck = 0;
		while($whilestatic >= $nowwhiltcheck){
		    $startwhil = $nowwhiltcheck*86400;
			foreach($timelist as $key=>$value){
				$stime = $startwhil+$nowhout+$value['s'];
				$etime = $startwhil+$nowhout+$value['e'];
				if($stime  > $checknow){
					$tempt = array();
					$tempt['value'] = $value['s']+$startwhil;
					$tempt['s'] = date('H:i',$nowhout+$value['s']);
					$tempt['e'] = date('H:i',$nowhout+$value['e']);
					$tempt['d'] = date('Y-m-d',$stime);
					$tempt['i'] =  $value['i'];
					$pstimelist[] = $tempt;
				}
			}
		 
			$nowwhiltcheck = $nowwhiltcheck+1;
		}
		$this->success($pstimelist);
	}
	 
	function paotuiajax(){
		$ptinfoset = $this->mysql->select_one(" select * from ".Mysite::$app->config['tablepre']."paotuiset  "); 
		$getlng = trim(IReq::get('getlng'));
		$getlat = trim(IReq::get('getlat'));
		$shoulng = trim(IReq::get('shoulng'));
		$shoulat = trim(IReq::get('shoulat'));
		$ptkg = intval(IReq::get('ptkg'));	 
	    if( empty($getlng) )  $this->message("获取取货地址失败,请重新获取");
		if( empty($getlat) )  $this->message("获取取货地址失败,请重新获取");
		if( empty($shoulng) )  $this->message("获取收货地址失败,请重新获取");
		if( empty($shoulat) )  $this->message("获取收货地址失败,请重新获取");
		
		$juli = $this->GetDistance($getlat,$getlng, $shoulat,$shoulng, 1,1); 
		$tempmi = $juli; 	
		$juli = round($juli/1000,1); 
		$tmpallkmcost =  0;
		if( $juli <= $ptinfoset['km']  ){
			$tmpallkmcost = $ptinfoset['kmcost'];
		}else{
			$addjuli = $juli-$ptinfoset['km'];
			$addnum = floor( ($addjuli/$ptinfoset['addkm']));
			$addcost = $addnum*$ptinfoset['addkmcost'];
			$tmpallkmcost = $ptinfoset['kmcost']+$addcost;
		}   
		/* 计算重量  并且 判断是否与前台的  公斤金额是否一致 */
		$tmpallkgcost =  0;
		if( $ptkg <= $ptinfoset['kg']  ){
			$tmpallkgcost = $ptinfoset['kgcost'];
		}else{
			$addkg = $ptkg-$ptinfoset['kg'];
			$addkgnum = floor( ($addkg/$ptinfoset['addkg'])); 
			$addkgcost = $addkgnum*$ptinfoset['addkgcost']; 
			$tmpallkgcost = $ptinfoset['kgcost']+$addkgcost; 
		} 
		$backdata['allkgcost'] = $tmpallkgcost;
		$backdata['allkmcost'] = $tmpallkmcost;
		$backdata['juli'] = $juli.'千米';
		$this->success($backdata);
	}
	//2016-3 开始
	//获取 开店类型
	function opentypelist(){
		$catparent = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shoptype  where  type='checkbox' order by cattype asc limit 0,100");
		$catlist = array();
		foreach($catparent as $key=>$value){
			$tempcat   = $this->mysql->getarr("select id,name,cattype from ".Mysite::$app->config['tablepre']."shoptype  where parent_id = '".$value['id']."'  limit 0,100");
			foreach($tempcat as $k=>$v){
				 $v['cattypename'] = $v['cattype'] == 0?'外卖':'超市';
				 $catlist[] = $v;
			}
		} 
		$this->success($catlist);
	}
	function checkopen(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}  
		$checkshopin = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where  uid='".$backinfo['uid']."' "); 
		if(empty($checkshopin)){
			$this->success('waitopen');
		}else{
			$this->success($checkshopin);//is_pass
		}
	}
	function openshop()
	{
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}  
		$sdata['shopname'] = IReq::get('shopname');//店铺名称
		$sdata['address'] = IReq::get('shopaddress');//店铺地址
		$sdata['shoplicense']  = IReq::get('shoplicense');//商家营业执照
		$attrid =  intval(IReq::get('attrid')); 
		
		$checkshopin = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where  uid='".$backinfo['uid']."' ");
		if(!empty($checkshopin)){
			$this->message('商家已开店');
		} 
		if(empty($sdata['shopname']))  $this->message('shop_emptyname');
		if(empty($sdata['shoplicense'])) $this->message('请上传营业执照');
		$shopinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where  shopname='".$sdata['shopname']."'  ");
		if(!empty($shopinfo)) $this->message('shop_repeatname');
		
		
	 
		$sdata['uid'] = $uid;
		$sdata['maphone'] =  $backinfo['phone'];
		$sdata['addtime'] = time();
		$sdata['email'] =  $backinfo['email'];    
		$sdata['admin_id'] = 0;
		$nowday = 24*60*60*365;
		$sdata['endtime'] = time()+$nowday;   
		$checkshoptype =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shoptype where id=".$attrid."  ");
		if(empty($checkshoptype))  $this->message("获取店铺分类失败");

		$sdata['shoptype']  = $checkshoptype['cattype'];   // 店铺大类型 0为外卖 1为超市
		
		$this->mysql->insert(Mysite::$app->config['tablepre'].'shop',$sdata); 
		$shopid = $this->mysql->insertid(); 
		$attrdata['shopid'] = $shopid;
		$attrdata['cattype'] = $checkshoptype['cattype'];
		$attrdata['firstattr'] = $checkshoptype['parent_id'];
		$attrdata['attrid'] = $checkshoptype['id'];
		$attrdata['value'] = $checkshoptype['name']; 

		$this->mysql->insert(Mysite::$app->config['tablepre'].'shopattr',$attrdata); 
		$this->mysql->update(Mysite::$app->config['tablepre'].'member',array('shopid'=>$shopid),"uid='".$this->member['uid']."'"); 
		$this->success('success'); 
	}
	function updatezhizhao(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}    
		$json = new Services_JSON();
		$uploadpath = 'upload/user/';
		$filepath = '/upload/user/';
		$upload = new upload($uploadpath,array('gif','jpg','jpge','png'));//upload
		$file = $upload->getfile();
		if($upload->errno!=15&&$upload->errno!=0) {
			$this->message($upload->errmsg());

		}else{
			$data['logo'] = $filepath.$file[0]['saveName']; 
			$this->success($data);
		} 
	}
	//2016-3 结束 
	function paotuiorder(){
		$backinfo = $this->checkappMem();
		if(empty($backinfo['uid'])){
			$this->message('nologin');
		}
		 $data['pttype'] = $pttype;  // 1为帮我送  2为帮我买
		$ordershowtype = intval(IFilter::act(IReq::get('ordershowtype')));
		$where = " and shoptype = 100 ";
		if($ordershowtype == 1){
			$where .=" and pttype = 1 ";
		}elseif($ordershowtype == 2){
			$where .=" and pttype = 2 ";
		}
		$pagesize = intval(IFilter::act(IReq::get('pagesize')));
		$pagesize = empty($pagesize)?10:$pagesize;
		$this->pageCls->setpage(intval(IReq::get('page')),$pagesize);
		$statusarr = array('0'=>'新订单','1'=>'等待取货','2'=>'确认取货','3'=>'已送达','4'=>'关闭','5'=>'关闭'); 
		$goshoparr = array('0'=>'新订单','1'=>'等待购买','2'=>'等待送货','3'=>'已送达','4'=>'关闭','5'=>'关闭');
		/* 获取订单数:   shopname  id  shoplogo  allcost addtime  status paystatus   paytype  */
		$nowtime = time()-14*24*60*60;
		$orderlist = $this->mysql->getarr("select  * from ".Mysite::$app->config['tablepre']."order where  buyeruid = ".$backinfo['uid']." ".$where." order by id desc  limit ".$this->pageCls->startnum().", ".$this->pageCls->getsize()."");
		if(empty($orderlist)) $this->success(array());
		$backdata = array();
		//status 状态，0未使用，1已绑定，2已使用，3无效
		foreach($orderlist as $key=>$value){ 
			$value['addtime'] = date('Y-m-d H:i:s',$value['addtime']);
		    if($value['pttype'] == 1){
				$value['seestatus'] = isset($statusarr[$value['status']])?$statusarr[$value['status']]:'';
			}else{
				$value['seestatus'] = isset($goshoparr[$value['status']])?$goshoparr[$value['status']]:'';
			} 
			$backdata[] = $value;
		}
		$this->success($backdata);
	}
	 
	
} 
?>