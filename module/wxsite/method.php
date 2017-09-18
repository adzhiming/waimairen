<?php
/*
*   method 方法  包含所有会员相关操作
    管理员/会员  添加/删除/编辑/用户登陆
    用户日志其他相关连的通过  memberclass关联
*/
class method   extends wxbaseclass
{  	
	function postmsg(){ 	  
		$orderid = intval(IReq::get('orderid'));	
		if(empty($orderid)) $this->message('订单ID错误');		
		$orderinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where id='".$orderid."'  ");  		
		if(empty($orderinfo)) $this->message('订单不存在');	  
		$orderclass = new orderclass();		
		$orderclass->sendmess($orderinfo['id']); 	
		$link = IUrl::creatUrlEx($this->mid,'wxsite/subshow/orderid/'.$orderid); 		
		$this->message('',$link);
	}
	 function choice(){ 
	 
	
	
	 
	  	$this->checkwxuser();
	  	$id =IFilter::act(IReq::get('id'));   
	 	  if($id > 0){
	 	
	 	     $checkinfo =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."area where id=".$id."  "); 
	 	     
	 	     if(empty($checkinfo)){
	 	          	$link = IUrl::creatUrlEx($this->mid,'wxsite/choice');
	    	            $this->message('',$link); 
	 	   
	 	     }
	 	     $checkinfo2 =  $this->mysql->select_one("select id,name,parent_id from ".Mysite::$app->config['tablepre']."area where parent_id=".$id."  "); 
	 	     if(empty($checkinfo2)){
	 	                ICookie::set('lng',$checkinfo['lng'],2592000);  
	                	ICookie::set('lat',$checkinfo['lat'],2592000);  
	    	            ICookie::set('mapname',$checkinfo['name'],2592000);  
	    	            ICookie::set('myaddress',$checkinfo['id'],2592000);  
	    	            $cookmalist = ICookie::get('cookmalist');
	    	            $cooklnglist = ICookie::get('cooklnglist');
	    	            $cooklatlist = ICookie::get('cooklatlist'); 

	    	            $link = IUrl::creatUrlEx($this->mid,'wxsite/shoplist/areaid/'.$checkinfo['id']);
	    	            $this->message('',$link); 
	       }
	 	 
	 	 
	    } 
	 	  
	 }
	 function appdown(){
		 
		 
	 }
	 function saveloation(){ 
			$lat = IFilter::act(IReq::get('lat'));   
			$lng = IFilter::act(IReq::get('lng')); 
			$addressname = IFilter::act(IReq::get('addressname')); 
			/* $lat = 34.802461;
			$lng = 113.597715; */
			ICookie::set('lat',$lat);  
			ICookie::set('lng',$lng);  
			ICookie::set('addressname',$addressname); 
		  
			$this->success('success');
	}
	function dwLocation(){  // 定位当前位置
		
		ICookie::clear('lat');
		ICookie::clear('lng');
		ICookie::clear('addressname');
		$link = IUrl::creatUrlEx($this->mid,'wxsite/index');
	    $this->message('',$link);
	}
	
	 function index(){
	 	$mid =$this->mid; 
		$moretypelist = $this->mysql->getarr("select* from ".Mysite::$app->config['tablepre']."appadv where type=2 and mid='".$mid."'  order by orderid  asc  limit 7 ");
	 	$moduleshow =   $this->mysql->getarr("select* from ".Mysite::$app->config['tablepre']."appmudel where FIND_IN_SET( name , 'collect,newuser,gift') and is_display=1  and mid='".$mid."' order by orderid  asc  limit 3 ");
		$data['moduleshow']  = $moduleshow;
		$fourmoduleshow =   $this->mysql->getarr("select* from ".Mysite::$app->config['tablepre']."appmudel where FIND_IN_SET( name , 'waimai,diancai,market,paotui') and is_display=1  and mid='".$mid."' order by orderid  asc  limit 4 ");
		$data['fourmoduleshow']  = $fourmoduleshow;
		$data['moretypelist']  = $moretypelist;
		
		 $lng = ICookie::get('lng');
         $lat = ICookie::get('lat');
         $addressname = ICookie::get('addressname');
		 
		 $lat = empty($lat)?0:$lat;
		 $lng = empty($lng)?0:$lng;
		 
		 if(empty($addressname)){
				 $addressname = '' ;
		 }
		 $data['mid'] = $mid;
		 $data['lat'] = $lat;
		 $data['lng'] = $lng; 
		 $data['addressname'] = $addressname;
		 
		$ztylist =   $this->mysql->getarr("select* from ".Mysite::$app->config['tablepre']."specialpage where is_show=1  and mid='".$mid."' order by orderid  asc");
		$data['ztylist'] = $ztylist;
                
                $cx = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."system_config where mid='".$mid."' ");
                $data['litel'] = $cx['litel'];
                
                $data['notice'] = $this->mysql->select_one("select * from " . Mysite::$app->config['tablepre'] . "information where mid='".$mid."' and type=1 and unix_timestamp(now())>notice_time order by orderid asc");
                
		Mysite::$app->setdata($data); 
                
                
                
//		 if( Mysite::$app->config['mobilemodule']  == 2 ){
//
//			Mysite::$app->setAction('indexsy');
//
//		  }else{
//			 Mysite::$app->setAction('index');
//		  }
	 
	 }
	 function waimai(){
 	 	  ICookie::set('shopshowtype','waimai',2592000);  
		   $typeid = intval(IReq::get('typeid'));
		   if(!empty($typeid)) {
			      $link = IUrl::creatUrlEx($this->mid,'wxsite/shoplist/typelx/wm/typeid/'.$typeid);
		   }else{
			    $link = IUrl::creatUrlEx($this->mid,'wxsite/shoplist/');
		   }	 	
	     $this->message('',$link); 
	 }
	 function dingtai(){
 	 	  ICookie::set('shopshowtype','dingtai',2592000);  
	 	   if(!empty($typeid)) {
			      $link = IUrl::creatUrlEx($this->mid,'wxsite/shoplist/typelx/yd/typeid/'.$typeid);
		   }else{
			    $link = IUrl::creatUrlEx($this->mid,'wxsite/shoplist/');
		   }
	 	
	     $this->message('',$link); 
	 }
	 function marketlist(){
	 	 
	 	  ICookie::set('shopshowtype','market',2592000);  
	 	     $typeid = intval(IReq::get('typeid'));
	 	    if(!empty($typeid)) {
			      $link = IUrl::creatUrlEx($this->mid,'wxsite/shoplist/typelx/mk/typeid/'.$typeid);
		   }else{
			    $link = IUrl::creatUrlEx($this->mid,'wxsite/shoplist/');
		   }
	 	
	     $this->message('',$link); 
	 }

	 function wmrtest(){
	 	$typelx = IFilter::act(IReq::get('typelx')); 
		
		 if(!empty($typelx)){
			 if($typelx == 'wm'){
				 ICookie::set('shopshowtype','waimai',2592000); 
				 $shopshowtype = 'waimai';
			 }
			 if($typelx == 'mk'){
				 ICookie::set('shopshowtype','market',2592000); 
				 $shopshowtype = 'market';
			 }
			  if($typelx == 'yd'){
				 ICookie::set('shopshowtype','dingtai',2592000); 
				 $shopshowtype = 'dingtai';
			 }
		 }else{
			 
			 $shopshowtype = ICookie::get('shopshowtype');
			 
		 }
			 
		 
	 	  if(!in_array($shopshowtype,array('waimai','market','dingtai'))){
	 	     ICookie::set('shopshowtype','waimai',2592000);  
	 	     $shopshowtype = 'waimai';
	 	  }
	 	  $areaid = IFilter::act(IReq::get('areaid'));  
		  if( $areaid <= 0 ){
				ICookie::clear('myaddress');
			}
	 	  $data['typeid'] = IFilter::act(IReq::get('typeid'));  
	 	  if($shopshowtype == 'market'){
	 	  	 $templist = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shoptype where  cattype = 1 and parent_id = 0 and is_search = 1 and type ='checkbox'  order by orderid asc limit 0,1000"); 
		     $data['caipin'] = array();
	       if(!empty($templist)){
		 	      $data['caipin']  = $this->mysql->getarr("select id,name from ".Mysite::$app->config['tablepre']."shoptype where parent_id='".$templist['id']."'  ");
		     }
	 	  }else{
	    
	 	     $templist = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shoptype where  cattype = 0 and parent_id = 0 and is_search = 1 and type ='checkbox'  order by orderid asc limit 0,1000"); 
	   
		   $data['caipin'] = array();
	       if(!empty($templist)){
		 	      $data['caipin']  = $this->mysql->getarr("select id,name from ".Mysite::$app->config['tablepre']."shoptype where parent_id='".$templist['id']."'  ");
		     }
		  }
		
		  $data['shopshowtype'] = $shopshowtype;
		  $shopsearch = IFilter::act(IReq::get('search_input')); 
		  $data['search_input'] = $shopsearch;
		  
		  $data['areaid'] = $areaid;  
		  Mysite::$app->setdata($data);  
	 } 
	 
	 function shoplist(){
		 
		 
		 if( !strpos($_SERVER["HTTP_USER_AGENT"],'MicroMessenger')    ){    //判断是微信浏览器不
			 $data['wxType'] = 2;
		 }else{
			 $data['wxType'] = 1;
		 }
		 
		 
	 	$typelx = IFilter::act(IReq::get('typelx')); 
		
		 if(!empty($typelx)){
			 if($typelx == 'wm'){
				 ICookie::set('shopshowtype','waimai',2592000); 
				 $shopshowtype = 'waimai';
			 }
			 if($typelx == 'mk'){
				 ICookie::set('shopshowtype','market',2592000); 
				 $shopshowtype = 'market';
			 }
			  if($typelx == 'yd'){
				 ICookie::set('shopshowtype','dingtai',2592000); 
				 $shopshowtype = 'dingtai';
			 }
		 }else{
			 
			 $shopshowtype = ICookie::get('shopshowtype');
			 
		 }
			 
		
		#  print_r($shopshowtype);
	 	  if(!in_array($shopshowtype,array('waimai','market','dingtai'))){
	 	     ICookie::set('shopshowtype','waimai',2592000);  
	 	     $shopshowtype = 'waimai';
	 	  }
	 	  $areaid = IFilter::act(IReq::get('areaid'));  
		  if( $areaid <= 0 ){
				ICookie::clear('myaddress');
			}
	 	  $data['typeid'] = IFilter::act(IReq::get('typeid'));  
	 	  if($shopshowtype == 'market'){
	 	  	 $templist = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shoptype where  cattype = 1 and parent_id = 0 and is_search = 1 and type ='checkbox'  order by orderid asc limit 0,1000"); 
		     $data['caipin'] = array();
	       if(!empty($templist)){
		 	      $data['caipin']  = $this->mysql->getarr("select id,name from ".Mysite::$app->config['tablepre']."shoptype where parent_id='".$templist['id']."'  ");
		     }
	 	  }else{
	    
	 	     $templist = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shoptype where  cattype = 0 and parent_id = 0 and is_search = 1 and type ='checkbox'  order by orderid asc limit 0,1000"); 
	   
		   $data['caipin'] = array();
	       if(!empty($templist)){
		 	      $data['caipin']  = $this->mysql->getarr("select id,name from ".Mysite::$app->config['tablepre']."shoptype where parent_id='".$templist['id']."'  ");
		     }
		  }
		
		  $data['shopshowtype'] = $shopshowtype;
		  $shopsearch = IFilter::act(IReq::get('search_input')); 
		  $data['search_input'] = $shopsearch;
		  
		  $data['areaid'] = $areaid;  
		  Mysite::$app->setdata($data);  
	 }
	 function shoplistdata(){
		$typelx = IFilter::act(IReq::get('typelx')); 
		
		 if(!empty($typelx)){
			 if($typelx == 'wm'){
				 ICookie::set('shopshowtype','waimai',2592000); 
				 $shopshowtype = 'waimai';
			 }
			 if($typelx == 'mk'){
				 ICookie::set('shopshowtype','market',2592000); 
				 $shopshowtype = 'market';
			 }
			  if($typelx == 'yd'){
				 ICookie::set('shopshowtype','dingtai',2592000); 
				 $shopshowtype = 'dingtai';
			 }
		 }else{
			 
			 $shopshowtype = ICookie::get('shopshowtype');
			 
		 } 
 		$cxsignlist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodssign where type='cx' order by id desc limit 0, 100");
		$cxarray  =  array();
		foreach($cxsignlist as $key=>$value){
		   $cxarray[$value['id']] = $value['imgurl'];
		}
	#	print_r($shopshowtype);
	 	 if($shopshowtype == 'market'){
            	 	 	$where = '';  
            	   $shopsearch = IFilter::act(IReq::get('search_input')); 
            	   $shopsearch = urldecode($shopsearch); 
            	   if(!empty($shopsearch)) $where=" and b.shopname like '%".$shopsearch."%' "; 
            	   $areaid= intval(IFilter::act(IReq::get('areaid'))); //  ICookie::get('myaddress');  
            	   $catid = intval(IReq::get('catid'));
            	   $order = intval(IReq::get('order'));
            	   $order = in_array($order,array(1,2,3))? $order:0;  
				   $qsjid = intval(IReq::get('qsjid'));
            	   $qsjid = in_array($qsjid,array(1,2,3))? $qsjid:0;  		
             	   //构造菜品查询
            	   $where2 = ''; 
            	   if($catid > 0) $where2 = 'where sh.second_id = '.$catid; 
            	   $checkareaid = $areaid;
            	if($checkareaid > 0) {
					   $where2 = empty($where2) ?' where  ard.areaid = '.$checkareaid:$where2.' and  ard.areaid = '.$checkareaid;   	        	
					  $where = empty($where2)? $where:$where." and b.id in(select ard.shopid from ".Mysite::$app->config['tablepre']."areashop as ard left join ".Mysite::$app->config['tablepre']."shopsearch  as sh  on ard.shopid = sh.shopid   ".$where2."  group by shopid  ) "; 
	        	   }else{					   
					$where = empty($where2)? $where:$where." and b.id in(select sh.shopid from  ".Mysite::$app->config['tablepre']."shopsearch  as sh    ".$where2."  group by shopid  ) "; 
					} 
            	         $lng = 0;
            	         $lat = 0;
            	         if($checkareaid > 0){
            	              $areainfo =    $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."area where id=".$checkareaid."  ");
            	              if(!empty($areainfo)){
            	                $lng = $areainfo['lng'];
            	                $lat = $areainfo['lat']; 
            	                
            	              } 
            	         }else{
            	            $lng = ICookie::get('lng');
            	            $lat = ICookie::get('lat');
						    $lng = empty($lng)?0:$lng;
							$lat =empty($lat)?0:$lat;
        	//         $where = empty($where)?'   and  SQRT((`lat` -'.$lat.') * (`lat` -'.$lat.' ) + (`lng` -'.$lng.' ) * (`lng` -'.$lng.' )) < (`pradius`*0.01094) ': $where.' and SQRT((`lat` -'.$lat.') * (`lat` -'.$lat.' ) + (`lng` -'.$lng.' ) * (`lng` -'.$lng.' )) < (`pradius`*0.01094) ';
            	         }
            	         
            	         $lng = trim($lng);
            	         $lat = trim($lat);
            	          $lng = empty($lng)?0:$lng;
							$lat =empty($lat)?0:$lat;
             
                        $orderarray = array(
                  //     '0'=>' (`lat` -'.$lat.') * (`lat` -'.$lat.' ) + (`lng` -'.$lng.' ) * (`lng` -'.$lng.' ) ASC       ',
                       '0'=>'  sort ASC       ',
                       '1'=>' (`lat` -'.$lat.') * (`lat` -'.$lat.' ) + (`lng` -'.$lng.' ) * (`lng` -'.$lng.' ) ASC  ',
                       '2'=>' limitcost asc     ',
					   '3'=>' is_com desc '
                       );
                        $qsjarray = array(
                       '0'=>'  ',
                       '1'=>' and limitcost < 5  ',
                       '2'=>' and limitcost >= 5 and limitcost <= 10 ',
					   '3'=>' and limitcost > 10 '
                       );
                  
                  
            	     $templist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shoptype where  cattype = 1 and parent_id = 0    order by orderid asc limit 0,1000"); 
            			 $attra['input'] = 0;
            			 $attra['img'] = 0;
            			 $attra['checkbox'] = 0; 
            			 foreach($templist as $key=>$value){
            			 	  if($value['type'] == 'input'){
            			 	   $attra['input'] =  $attra['input'] > 0?$attra['input']:$value['id'];
            			 	  }elseif($value['type'] == 'img'){
            			 	  	 $attra['img'] =  $attra['img'] > 0?$attra['img']:$value['id'];
            			 	  }elseif($value['type'] == 'checkbox'){
            			 	  	$attra['checkbox'] =  $attra['checkbox'] > 0?$attra['checkbox']:$value['id'];
            			 	  }
            			 } 
            			 /*获取店铺*/
            		  $pageinfo = new page();
            		  $pageinfo->setpage(intval(IReq::get('page'))); 
					   $where .= $qsjarray[$qsjid];
					      $where .= $qsjarray[$qsjid];
 		 
			           $where .= ' and shoptype=1 ';	 
					    $where = Mysite::$app->config['plateshopid'] > 0? $where.' and a.shopid != '.Mysite::$app->config['plateshopid'] .' ':$where;
					$temb = array(); 
				 $temb[] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shopmarket as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id  where  b.is_pass = 1 and b.is_recom = 1 ".$where."    order by ".$orderarray[$order]." limit ".$pageinfo->startnum().", ".$pageinfo->getsize()."");
				  $temb[] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shopmarket as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id  where  b.is_pass = 1 and b.is_recom != 1 ".$where."    order by ".$orderarray[$order]." limit ".$pageinfo->startnum().", ".$pageinfo->getsize()."");
				
            			$nowhour = date('H:i:s',time()); 
                  $nowhour = strtotime($nowhour);
                  $templist = array();
                   $cxclass = new sellrule();  
				   foreach($temb as $kc=>$list){ 
					  if(is_array($list)){
								foreach($list as $keys=>$values){  
								 
										if($values['id'] > 0){
											$values['shoplogo'] = empty($values['shoplogo'])? Mysite::$app->config['imgserver'].Mysite::$app->config['shoplogo']:Mysite::$app->config['imgserver'].$values['shoplogo'];
									  $checkinfo = $this->shopIsopen($values['is_open'],$values['starttime'],$values['is_orderbefore'],$nowhour); 
									  $values['opentype'] = $checkinfo['opentype'];
									  $values['newstartime']  =  $checkinfo['newstartime'];  
									  $attrdet = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shopattr where  cattype = 1 and shopid = ".$values['id']."");
									  $cxclass->setdata($values['id'],1000,$values['shoptype']); 
									  $checkps = 	 $this->pscost($values,1); 
									  $values['pscost'] = $checkps['pscost'];
									  
										  $mi = $this->GetDistance($lat,$lng, $values['lat'],$values['lng'], 1); 
										 $tempmi = $mi;
										$mi = $mi > 1000? round($mi/1000,2).'km':$mi.'m'; 
										$values['juli'] = 		$mi; 
									  
		 $shopcounts = $this->mysql->select_one( "select count(id) as shuliang  from ".Mysite::$app->config['tablepre']."order	 where   status = 3 and  shopid = ".$values['id']."" );
									#	print_r(  $shopcounts );
										if(empty( $shopcounts['shuliang']  )){
											$values['ordercount'] = 0;
										}else{
											$values['ordercount']  = $shopcounts['shuliang'];
										} 
										$values['ordercount'] = $values['ordercount']+$values['virtualsellcounts'];	
										  $cxinfo = $this->mysql->getarr("select name,id,signid from ".Mysite::$app->config['tablepre']."rule where   shopid = ".$values['id']." and status = 1 and starttime  < ".time()." and endtime > ".time()." ");
									  $values['cxlist'] = array();
									  
										foreach($cxinfo as $k1=>$v1){
										if(isset($cxarray[$v1['signid']])){
											 $v1['imgurl'] = $cxarray[$v1['signid']];
											 $values['cxlist'][] = $v1;
										}
									  }

											//可预订情况下第一个配送时间
											$nowhout = strtotime(date('Y-m-d',time()));//当天最小linux 时间
											$timelist = !empty($values['postdate'])?unserialize($values['postdate']):array();
											$values['pstime'] = date('H:i',$nowhout+$timelist[0]['s']);


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
            	    $data  = $templist;
	 	}else{
            	 	 $where = '';  
            	   $shopsearch = IFilter::act(IReq::get('search_input')); 
            	   $shopsearch = urldecode($shopsearch); 
            	   if(!empty($shopsearch)) $where=" and b.shopname like '%".$shopsearch."%' "; 
            	   $areaid= intval(IFilter::act(IReq::get('areaid'))); //  ICookie::get('myaddress');  
            	   $catid = intval(IReq::get('catid'));
            	   $order = intval(IReq::get('order'));
            	   $order = in_array($order,array(1,2,3))? $order:0;
				   $qsjid = intval(IReq::get('qsjid'));
            	   $qsjid = in_array($qsjid,array(1,2,3))? $qsjid:0;  				   
             	   //构造菜品查询
            	   $where2 = ''; 
            	   if($catid > 0) $where2 = 'where sh.second_id = '.$catid; 
            	   $checkareaid = $areaid;
            	   if($checkareaid > 0) {
					   $where2 = empty($where2) ?' where  ard.areaid = '.$checkareaid:$where2.' and  ard.areaid = '.$checkareaid;   	        	
					  $where = empty($where2)? $where:$where." and b.id in(select ard.shopid from ".Mysite::$app->config['tablepre']."areashop as ard left join ".Mysite::$app->config['tablepre']."shopsearch  as sh  on ard.shopid = sh.shopid   ".$where2."  group by shopid  ) "; 
	        	   }else{					   
					  $where = empty($where2)? $where:$where." and b.id in(select sh.shopid from  ".Mysite::$app->config['tablepre']."shopsearch  as sh    ".$where2."  group by shopid  ) "; 
					}
                 
            	        $lng = 0;
            	         $lat = 0;
            	         if($checkareaid > 0){
            	              $areainfo =    $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."area where id=".$checkareaid."  ");
            	              if(!empty($areainfo)){
            	                $lng = $areainfo['lng'];
            	                $lat = $areainfo['lat']; 
            	                
            	              } 
            	         }else{
            	            $lng = ICookie::get('lng');
            	            $lat = ICookie::get('lat');
						    $lng = empty($lng)?0:$lng;
							$lat =empty($lat)?0:$lat;
  //       $where = empty($where)?'   and  SQRT((`lat` -'.$lat.') * (`lat` -'.$lat.' ) + (`lng` -'.$lng.' ) * (`lng` -'.$lng.' )) < (`pradius`*0.01094) ': $where.' and SQRT((`lat` -'.$lat.') * (`lat` -'.$lat.' ) + (`lng` -'.$lng.' ) * (`lng` -'.$lng.' )) < (`pradius`*0.01094) ';
            	         }
            	         
            	         $lng = trim($lng);
            	         $lat = trim($lat);
            	          $lng = empty($lng)?0:$lng;
							$lat =empty($lat)?0:$lat;
                    
                       $orderarray = array(
					   '0'=>'  sort ASC       ',
              //         '0'=>' (`lat` -'.$lat.') * (`lat` -'.$lat.' ) + (`lng` -'.$lng.' ) * (`lng` -'.$lng.' ) ASC  ',
                       '1'=>' (`lat` -'.$lat.') * (`lat` -'.$lat.' ) + (`lng` -'.$lng.' ) * (`lng` -'.$lng.' ) ASC   ',
                       '2'=>' limitcost asc ',
					   '3'=>' is_com desc '
                       );
                        $qsjarray = array(
                       '0'=>'  ',
                       '1'=>' and limitcost < 5 ',
                       '2'=>' and limitcost >= 5 and limitcost <= 10 ',
					   '3'=>' and limitcost > 10 '
                       );
              
                  
            	     $templist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shoptype where  cattype = 0 and parent_id = 0 and is_main =1  order by orderid asc limit 0,1000"); 
            			 $attra['input'] = 0;
            			 $attra['img'] = 0;
            			 $attra['checkbox'] = 0; 
            			 foreach($templist as $key=>$value){
            			 	  if($value['type'] == 'input'){
            			 	   $attra['input'] =  $attra['input'] > 0?$attra['input']:$value['id'];
            			 	  }elseif($value['type'] == 'img'){
            			 	  	 $attra['img'] =  $attra['img'] > 0?$attra['img']:$value['id'];
            			 	  }elseif($value['type'] == 'checkbox'){
            			 	  	$attra['checkbox'] =  $attra['checkbox'] > 0?$attra['checkbox']:$value['id'];
            			 	  }
            			 } 
            			 /*获取店铺*/
            		  $pageinfo = new page();
            		  $pageinfo->setpage(intval(IReq::get('page'))); 
					  $where .= $qsjarray[$qsjid];
             		   $tempwhere = $shopshowtype == 'dingtai'?' and is_goshop =1 ':' and is_waimai =1 ';
					   $where .= " and shoptype =  0  ";
					   
					   
					    $where = Mysite::$app->config['plateshopid'] > 0? $where.' and a.shopid != '.Mysite::$app->config['plateshopid'] .' ':$where;
					$teempd = array();
            	    $teempd[] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shopfast as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id  where  b.is_pass = 1 and b.is_recom = 1  ".$tempwhere." ".$where."    order by ".$orderarray[$order]." limit ".$pageinfo->startnum().", ".$pageinfo->getsize()."");
            	       $teempd[] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shopfast as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id  where  b.is_pass = 1 and b.is_recom != 1  ".$tempwhere." ".$where."    order by ".$orderarray[$order]." limit ".$pageinfo->startnum().", ".$pageinfo->getsize()."");
            			$nowhour = date('H:i:s',time()); 
                  $nowhour = strtotime($nowhour);
                  $templist = array();
                   $cxclass = new sellrule();  
				   foreach($teempd as $kv=>$list){
					  if(is_array($list)){
								foreach($list as $keys=>$values){  
								 
										if($values['id'] > 0){
											$values['shoplogo'] = empty($values['shoplogo'])? Mysite::$app->config['imgserver'].Mysite::$app->config['shoplogo']:Mysite::$app->config['imgserver'].$values['shoplogo'];
									  $checkinfo = $this->shopIsopen($values['is_open'],$values['starttime'],$values['is_orderbefore'],$nowhour); 
									  $values['opentype'] = $checkinfo['opentype'];
									  $values['newstartime']  =  $checkinfo['newstartime'];  
									  $attrdet = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shopattr where  cattype = 0 and shopid = ".$values['id']."");
									  $cxclass->setdata($values['id'],1000,$values['shoptype']); 
									  
									 
									  
									  $checkps = 	 $this->pscost($values,1,$areaid); 
									  if( $shopshowtype == 'dingtai'){
										$values['pscost'] =0;
									  }else{
										$values['pscost'] = $checkps['pscost'];
									  }
									  
										  $mi = $this->GetDistance($lat,$lng, $values['lat'],$values['lng'], 1); 
										$tempmi = $mi;
									  $mi = $mi > 1000? round($mi/1000,2).'km':$mi.'m';
														  
										$values['juli'] = 		$mi;
									   
		 $shopcounts = $this->mysql->select_one( "select count(id) as shuliang  from ".Mysite::$app->config['tablepre']."order	 where  status = 3 and  shopid = ".$values['id']."" );
									#	print_r(  $shopcounts );
										if(empty( $shopcounts['shuliang']  )){
											$values['ordercount'] = 0;
										}else{
											$values['ordercount']  = $shopcounts['shuliang'];
										} 
										$values['ordercount'] = $values['ordercount']+$values['virtualsellcounts'];		  
										  $cxinfo = $this->mysql->getarr("select name,id,signid from ".Mysite::$app->config['tablepre']."rule where   shopid = ".$values['id']." and status = 1 and starttime  < ".time()." and endtime > ".time()." ");
									  $values['cxlist'] = array();
									  
										foreach($cxinfo as $k1=>$v1){
										if(isset($cxarray[$v1['signid']])){
											 $v1['imgurl'] = $cxarray[$v1['signid']];
											 $values['cxlist'][] = $v1;
										}
									  }

											//可预订情况下第一个配送时间
											$nowhout = strtotime(date('Y-m-d',time()));//当天最小linux 时间
											$timelist = !empty($values['postdate'])?unserialize($values['postdate']):array();
											$values['pstime'] = date('H:i',$nowhout+$timelist[0]['s']);


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
            	    $data  = $templist;
	  }
	  
	  $data['shopshowtype'] = $shopshowtype;
	   $data['shoplist']  = $templist; 
					
					 Mysite::$app->setdata($data); 
	 /*  $datas = json_encode($data);
	  echo 'showmoreshop('.$datas.')';
      exit; 
	    $this->success($data); */
	 } 
	 
	 
	  function indexshoplistdata(){		// 首页获取附近商家列表（外卖和超市）
	  	$mid = $this->mid;
		$typelx = IFilter::act(IReq::get('typelx')); 
		
		 if(!empty($typelx)){
			 if($typelx == 'wm'){
				 ICookie::set('shopshowtype','waimai',2592000); 
				 $shopshowtype = 'waimai';
			 }
			 if($typelx == 'mk'){
				 ICookie::set('shopshowtype','market',2592000); 
				 $shopshowtype = 'market';
			 }
			  if($typelx == 'yd'){
				 ICookie::set('shopshowtype','dingtai',2592000); 
				 $shopshowtype = 'dingtai';
			 }
		 }else{
			 
			 $shopshowtype = ICookie::get('shopshowtype');
			 
		 }
	  
		$cxsignlist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodssign where mid='".$this->mid."' and type='cx' order by id desc limit 0, 100");
	     
		$cxarray  =  array();
		foreach($cxsignlist as $key=>$value){
		   $cxarray[$value['id']] = $value['imgurl'];
		}
 
		$where = '';  

		$lng = 0;
		$lat = 0;

		$lng = ICookie::get('lng');
		$lat = ICookie::get('lat');


		$lng = empty($lng)?0:$lng;
		$lat =empty($lat)?0:$lat;
		//   $where = empty($where)?'   and  SQRT((`lat` -'.$lat.') * (`lat` -'.$lat.' ) + (`lng` -'.$lng.' ) * (`lng` -'.$lng.' )) < (`pradiusa`*0.01094) ': $where.' and SQRT((`lat` -'.$lat.') * (`lat` -'.$lat.' ) + (`lng` -'.$lng.' ) * (`lng` -'.$lng.' )) < (`pradiusa`*0.01094) ';

		$lng = trim($lng);
		$lat = trim($lat);
		$lng = empty($lng)?0:$lng;
		$lat =empty($lat)?0:$lat;

		$orderarray = array(
		//'0'=>' (`lat` -'.$lat.') * (`lat` -'.$lat.' ) + (`lng` -'.$lng.' ) * (`lng` -'.$lng.' ) ASC       ',                       
		'0'=>'   sort asc      ',                       
		); 

		/*获取店铺*/
		$pageinfo = new page();
		$pageinfo->setpage(intval(IReq::get('page'))); 
		// $where .= $qsjarray[$qsjid];
		// $where .= $qsjarray[$qsjid];
		$where = Mysite::$app->config['plateshopid'] > 0? $where.' and  id != '.Mysite::$app->config['plateshopid'] .' and mid='.$mid.'' :$where;
		$tempdd = array();
		$tempdd[] =   $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shop where is_pass = 1 and mid='$mid' and is_open = 1 and is_recom = 1 and endtime > ".time()."  ".$where."    order by ".$orderarray[0]." limit ".$pageinfo->startnum().", ".$pageinfo->getsize()."  ");		
		$tempdd[] =   $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shop where is_pass = 1 and mid='$mid' and is_open = 1 and is_recom != 1 and endtime > ".time()."  ".$where."    order by ".$orderarray[0]." limit ".$pageinfo->startnum().", ".$pageinfo->getsize()."  ");		
		$nowhour = date('H:i:s',time()); 
		$nowhour = strtotime($nowhour);
		$templist = array();
		$cxclass = new sellrule();  
				foreach($tempdd as $kv=>$list){
				  if(is_array($list)){
							foreach($list as $keys=>$values){  
							 
									if($values['id'] > 0){
										
						$templist111 = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shoptype where cattype = ".$values['shoptype']." and mid='.$mid.' and  parent_id = 0    order by orderid asc limit 0,1000"); 
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
						 
										if($values['shoptype'] == 1 ){
											$shopdet = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket where  shopid = ".$values['id']."   ");
										}else{
											$shopdet = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast where  shopid = ".$values['id']."   ");
										}
									if(!empty($shopdet)){
										$values = array_merge($values,$shopdet);
								  $values['shoplogo'] = empty($values['shoplogo'])? Mysite::$app->config['imgserver'].Mysite::$app->config['shoplogo']:Mysite::$app->config['imgserver'].$values['shoplogo'];
								  
								  $checkinfo = $this->shopIsopen($values['is_open'],$values['starttime'],$values['is_orderbefore'],$nowhour); 
								  $values['opentype'] = $checkinfo['opentype'];
								  $values['newstartime']  =  $checkinfo['newstartime'];  
								  
							


							$attrdet = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shopattr where  cattype = ".$values['shoptype']." and shopid = ".$values['id']."");
								  $cxclass->setdata($values['id'],1000,$values['shoptype']); 
						  
								  $checkps = 	 $this->pscost($values,1,0); 
								  $values['pscost'] = $checkps['pscost'];
								  
								  
									  $mi = $this->GetDistance($lat,$lng, $values['lat'],$values['lng'], 1); 
									$tempmi = $mi;
								  $mi = $mi > 1000? round($mi/1000,2).'km':$mi.'m';
													  
									$values['juli'] = 		$mi;
								  
								  
	 $shopcounts = $this->mysql->select_one( "select count(id) as shuliang  from ".Mysite::$app->config['tablepre']."order	 where  status = 3 and  shopid = ".$values['id']."" );
 
									if(empty( $shopcounts['shuliang']  )){
										$values['ordercount'] = 0;
									}else{
										$values['ordercount']  = $shopcounts['shuliang'];
									} 
									$values['ordercount'] = $values['ordercount']+$values['virtualsellcounts'];		  
									  $cxinfo = $this->mysql->getarr("select name,id,signid from ".Mysite::$app->config['tablepre']."rule where   shopid = ".$values['id']." and status = 1 and starttime  < ".time()." and endtime > ".time()." ");
								  $values['cxlist'] = array();
								  
									foreach($cxinfo as $k1=>$v1){
									if(isset($cxarray[$v1['signid']])){
										 $v1['imgurl'] = $cxarray[$v1['signid']];
										 $values['cxlist'][] = $v1;
									}
								  }


										//可预订情况下第一个配送时间
										$nowhout = strtotime(date('Y-m-d',time()));//当天最小linux 时间
										$timelist = !empty($values['postdate'])?unserialize($values['postdate']):array();
										$values['pstime'] = date('H:i',$nowhout+$timelist[0]['s']);


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
								  
						#		 print_r($values['attrdet']);
								 
								  $templist[] = $values;
							 }
					   } 
				}
		   
		   
				}
		   
		}
		$data['shoplist']  = $templist; 
		
		 Mysite::$app->setdata($data);  
	} 
	 
	 function shopdetails(){ 
         
        $storeid = $shopid = IReq::get('id')? IReq::get('id'): ICookie::get('shopid');
 
        if(ICookie::get('shopid')){
            ICookie::set('shopid',intval($storeid),86400);
        } 
                
	 	$lng = ICookie::get('lng');
	 	$lat = ICookie::get('lat');
	 	$addressname = ICookie::get('addressname');
	 		
	 	$lat = empty($lat)?0:$lat;
	 	$lng = empty($lng)?0:$lng;
	 	$wxclass = new wx_s();

	 	$signPackage = $wxclass->getSignPackage($this->mid);
	 	
	 	$data['signPackage'] = $signPackage;
	 	if(empty($addressname)){
	 		$addressname = '' ;
	 	}

	 	$where = " and id ='".$storeid."' ";
	 	$tempdd[] =   $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where is_pass = 1  and is_open = 1   ".$where."    ");
	 	$tempdd = $tempdd[0]; 

	 	$data['wifi'] = unserialize($tempdd['wifi_info']);	
        //var_dump($tempdd);
	 	$data['shopinfo'] = $tempdd;
	 	$data['addressname'] = $addressname;
	 	//$data['mid'] = $mid;
	 	$data['lat'] = $lat;
	 	$data['lng'] = $lng;
	 	$data['storeid'] = $storeid;
	 	$mi = $this->GetDistance($lat,$lng, $data['shopinfo']['lat'],$data['shopinfo']['lng'], 1);
	 	$tempmi = $mi;
	 	$mi = $mi > 1000? round($mi/1000,2).'km':$mi.'m';
	 	$data['juli'] = 		$mi;
                $data['shopid'] = $shopid; 
        
        //判断用户是否已取号
        $uid = $this->member['uid'];
        $data['cxinfo'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."table_krdl where mid = '".$this->mid."' and shopid='".$shopid."' and uid='".$uid."' and to_days(addtime) = to_days(now()) order by id desc");

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

		//获取 是否会员卡
		if (!empty($this->member['card_openid'])) {
			$params = array();
			$params ['mid'] = $this->mid;
			$params ['openid'] = $this->member['card_openid'];
			
			if (api_post_isMemberOfMerchant( $params, $return_code, $return_msg, $return_data )) {
				if ($return_code==1 && $return_data) {
					$couponList = array();
					$data['is_member']   = $return_data['is_member'];
					$data['activie_member_card']   = $return_data['activie_member_card'];
				}
			}
		}
// 		$data['is_member']   =1;
// 		$data['activie_member_card']   =array("1"=>"2");
		
		
		//获取会员卡、卡券信息
		$params = array();
		$params ['mid'] = $this->mid;
		$params ['coupon_type'] = "CASH,DISCOUNT";
		
		if (api_post_getCouponListOfMerchant ( $params, $return_code, $return_msg, $return_data )) {
			if ($return_code==1 && $return_data) {
				$couponList = array();
				$needCount = 0;
				foreach($return_data as $cardInfo) {
					$arrTmp = array();
					$arrTmp['id'] =  $cardInfo['id'];
					$arrTmp['title'] =  $cardInfo['name'];
					$arrTmp['expire'] =  "有效期:".$cardInfo['deadline'];
					if ($cardInfo['type'] == 'CASH') {
						$arrTmp['discount'] =  "￥".round($cardInfo['amount']/100);
					}
					else if ($cardInfo['type'] == 'DISCOUNT') {
						if ( $cardInfo['discount'] >= 100) {
							$cardInfo['discount'] = 0;
						}
						$arrTmp['discount'] =  ($cardInfo['discount']*10).' 折';
					}
					$couponList[] = $arrTmp;
					$needCount++;
					if ($needCount > 2) {
						break;
					}
				}
				$data['couponlist']   = $couponList;

			}
		}
	 
	 	Mysite::$app->setdata($data);
	 }
	 
	 function uupdateUserGetCoupon() {
	 	$storeid = $shopid = IReq::get('shopid');
	 	$this->checkwxuser();
// 	 	if( $this->checkbackinfo() ){
// 	 		if($this->member['uid'] == 0||empty($this->mid))  $this->message('未登陆');
// 	 	}
	 
	 
// 	 	if(empty($this->member['card_openid'])){
// 	 		$this->message('未关联卡券openid');
// 	 	}
	 	$card_member_id = IFilter::act(IReq::get('card_member_id'));
	 	if (empty($card_member_id)) {
	 		$this->message('参数错误');
	 	}	 	
	 	//获取微信卡券
	 	$params = array();
	 	$params ['mid'] = $this->mid;
	 	$params ['openid'] = $this->member['card_openid'];
	 	$params ['card_member_id'] = $card_member_id;

	 
	 	if (api_post_uupdateUserGetCoupon ( $params, $return_code, $return_msg, $return_data )) {
	 		if ($return_code==1 && $return_data) {
	 			$this->success($return_data);
	 			return;
	 		}
	 	}
	 	$return_msg=empty($return_msg)?'更新卡券失败':$return_msg;
	 	$this->message($return_msg);
	 }
	 function userGetCoupon() {
	 	$storeid = $shopid = IReq::get('shopid');
	 	$this->checkwxuser();
	 	if( $this->checkbackinfo() ){
	 		if($this->member['uid'] == 0||empty($this->mid))  $this->message('未登陆');
	 	}
	 
	 
	 	if(empty($this->member['card_openid'])){
	 		$this->message('未关联卡券openid');
	 	}
	 	$couponid = IFilter::act(IReq::get('couponid'));
	 	if (empty($couponid)) {
	 		$this->message('参数错误');
	 	}
	 	//获取微信卡券
	 	$params = array();
	 	$params ['mid'] = $this->mid;
	 	$params ['openid'] = $this->member['card_openid'];
	 	$params ['cid'] = $couponid;
	 
	 
	 	if (api_post_userGetCoupon ( $params, $return_code, $return_msg, $return_data )) {
	 		if ($return_code==1 && $return_data) {
	 			$this->success($return_data);
	 			return;
	 		}
	 	}
	 	$return_msg=empty($return_msg)?'领取卡券失败':$return_msg;
	 	$this->message($return_msg);
	 }

	 function shopmemcardlist(){
	 	$storeid = $shopid = IReq::get('shopid');
	 	$data['shopid']   = $shopid;
	 	 
	 	if (!empty($this->member['card_openid'])) {

		 	$wxclass = new wx_s();
		 	$signPackage = $wxclass->getSignPackage($this->mid);
		 	$data['signPackage'] = $signPackage;
		 	 
		 	//获取会员卡信息
		 	$params = array();
		 	$params ['mid'] = $this->mid;
		 	$params ['openid'] = $this->member['card_openid'];
		 	if (api_post_getMemberCards ( $params, $return_code, $return_msg, $return_data )) {
		 		if ($return_code==1 && $return_data) {
		 			$data['membercardlist']   = $return_data;
		 			 
		 		}
		 	}
	 	}
	 	Mysite::$app->setdata($data);
	 }
	 
	 function shopcouponlist(){
	 	$storeid = $shopid = IReq::get('shopid');
	 	$data['shopid']   = $shopid;
	 	
	 	$wxclass = new wx_s();
	 	$signPackage = $wxclass->getSignPackage($this->mid);
	 	$data['signPackage'] = $signPackage;
	 	
	 	//获取卡券信息
	 	$params = array();
	 	$params ['mid'] = $this->mid;
	 	$params ['coupon_type'] = "CASH,DISCOUNT";
	 	
	 	if (api_post_getCouponListOfMerchant ( $params, $return_code, $return_msg, $return_data )) {
	 		if ($return_code==1 && $return_data) {
	 			$couponList = array();
	 			foreach($return_data as $cardInfo) {
	 				$arrTmp = array();
	 				$arrTmp['id'] =  $cardInfo['id'];
	 				$arrTmp['title'] =  $cardInfo['name'];
	 				$arrTmp['expire'] =  "有效期:".$cardInfo['deadline'];
	 				if ($cardInfo['type'] == 'CASH') {
	 					$arrTmp['discount'] =  "￥".round($cardInfo['amount']/100);
	 				}
	 				else if ($cardInfo['type'] == 'DISCOUNT') {
	 					if ( $cardInfo['discount'] >= 100) {
	 						$cardInfo['discount'] = 0;
	 					}
	 					$arrTmp['discount'] =  ($cardInfo['discount']*10).' 折';
	 				}
	 				$couponList[] = $arrTmp;
	 			}
	 			$data['couponlist']   = $couponList;
	 	
	 		}
	 	}
	 	Mysite::$app->setdata($data);
	 }
	//前台预定 by yanyh
	function shoppreorder(){
		$mid 		 = $this->mid;
		$data['mid'] = $mid;
		$id 		 = intval(IReq::get('id')) ?intval(IReq::get('id')) : intval(ICookie::get('shopid')); 
      	$data['id']  = $id; 
      	 	 
      	$shopinfo 			= $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where id='".$id."' ");   //店铺基本信息

      	$data['position'] = $shopinfo['lng'].",".$shopinfo['lat'];
      	$data['address']  = $shopinfo['address'];
      	$data['shopinfo'] = $shopinfo;
        $data['remark'] = nl2br($shopinfo['reserve_remarks']);

      	//缓存位置信息
      	ICookie::set('position',$data['position'],86400);
		ICookie::set('address',$data['address'],86400);
 
	 	//统计该店铺的销量
		$sellcnt 		 = $this->mysql->select_one( "select sum(sellcount+virtualsellcount) sellcnt from  ".Mysite::$app->config['tablepre']."goods where shopid= ".$id."  " );
		$data['sellcnt'] = $sellcnt['sellcnt'];
 
	 	Mysite::$app->setdata($data); 
   	} 
	//商家导航 by yanyh
   	function shopbaidumap(){
 		
	 	$position  = IReq::get('position');
	 	$address  = IReq::get('addr');

	 	$data['position'] = $position; 
	 	$data['address']  = $address; 
	 	Mysite::$app->setdata($data); 
  
   	} 
   	//预约界面 by yanyh
	function shoppreonline(){
 		$shopid	 = intval(IReq::get('id')) ? intval(IReq::get('id')) : ICookie::get('shopid');//店铺ID
 		$mid     = $this->mid;
 		$time 	 = time();

		$areasql = "select distinct(table_area),sid from ".Mysite::$app->config['tablepre']."pre_time_slot where shopid = ".$shopid."";
	 	$prearea = $this->mysql->getarr($areasql);
	 	//可预约日期
	 	$shopSql  = "SELECT can_reserve_day,date_of_appointment  FROM ".Mysite::$app->config['tablepre']."shop WHERE id = ".$shopid." AND mid = ".$mid."";
	 	$shop     = $this->mysql->select_one($shopSql);
	 	$daynum  = intval($shop['can_reserve_day']);
	 	$timenum = intval($shop['date_of_appointment']);
	 	$yd_date = [];

	 	for ($i=0; $i < $daynum ; $i++) { 
	 		$yd_date[] = date('Y-m-d',$time+86400*$i);
	 	}
	 	$data['yd_date'] = $yd_date;

	 	$data['timenum']    = $timenum;
	 	$data['mid']        = $mid;
	 	$data['shopid']     = $shopid;
   		$data['position']	= ICookie::get('position');
   		$data['address']	= ICookie::get('address');
 
 		$data['prearea'] = $prearea;
 		 
		ICookie::set('shopid',$shopid,86400);
		ICookie::set('mid',$mid,86400);

	 	Mysite::$app->setdata($data); 
   	} 
	//已预约列表 by yanyh
   	function shoppreonlined(){
 		
	 	$uid    = !isset($this->member['score'])?'0':$this->member['uid'];
	 	$shopid = ICookie::get('shopid');
	 	$mid    = ICookie::get('mid');
	 	$data['position']	= ICookie::get('position');
   		$data['address']	= ICookie::get('address');
	 	$time   = time();
 
   		$sql 			= "SELECT * FROM ".Mysite::$app->config['tablepre']."order WHERE  buyeruid = ".$uid." AND shopid = ".$shopid." AND isbefore = 2 AND is_goshop = 2 ORDER BY id desc";
  		$shoppreonlined = $this->mysql->getarr($sql);
 
  		//超过40分钟未确认则取消
  		foreach ($shoppreonlined as $key => $value) { 
  			$is_lost = $time - $value['addtime'];
  			if($value['ydstatus'] == 0 && $is_lost > 40*60){ 
  				$res['ydstatus']  = 3; 
	     		$this->mysql->update(Mysite::$app->config['tablepre'].'order',$res,"id='".$value['id']."'");
	  		}
  		}
 
  		foreach ($shoppreonlined as $key => $value) {
  			$shoppreonlined[$key]['booktime'] = date('Y-m-d H:i:s',$value['booktime']); 	
  		}

  		$data['shoppreonlined'] = $shoppreonlined ;
 
	 	Mysite::$app->setdata($data); 
   	}
   	//在线预定表单提交 by yanyh
   	function premakeorder(){ 
	    $subtype 				= 1; //1为只定桌台
		$info['shopid'] 		= intval(IReq::get('shopid'));//店铺ID
		$info['mid']			= intval(IReq::get('mid'));
		$info['remark'] 		= IFilter::act(IReq::get('remark'));//备注
	 	$info['username']   	= IFilter::act(IReq::get('yd_name')); 
		$info['mobile'] 		= IFilter::act(IReq::get('yd_phone'));
		$info['yd_date']      	= IFilter::act(IReq::get('yd_date'));  
		$info['yd_time']      	= IFilter::act(IReq::get('yd_time'));  
		$info['sex']      		= IFilter::act(IReq::get('sex')); //性别
 		$info['booktime']       = strtotime($info['yd_date']." ".$info['yd_time']);
 		$info['table_area']     = IFilter::act(IReq::get('yd_area'));
 		$info['table_type']     = IFilter::act(IReq::get('yd_type'));
 		//$info['tableid']     	= IFilter::act(IReq::get('tableid'));
 		$info['bookpay']     	= IFilter::act(IReq::get('bookpay'));//定金

		$info['ordertype']  	= 3;//订单类型 
		$peopleNum 				= IFilter::act(IReq::get('yd_type'));  
		$info['othercontent'] 	= empty($peopleNum)?'':serialize(array('人数'=>$peopleNum));  
		$info['userid'] 	   	= !isset($this->member['score'])?'0':$this->member['uid'];

	   	if(Mysite::$app->config['allowedguestbuy'] != 1){
	     	if($info['userid']==0) $this->message('member_nologin');
	   	} 
		$shopinfo  =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id where a.shopid = '".$info['shopid']."'    ");   
		if(empty($shopinfo)) $this->message('店铺不存在');
		 /*监测验证码*/
		$checksend = Mysite::$app->config['ordercheckphone'];
	    if($checksend == 1){
	    	if(empty($this->member['uid'])){
	    	  	$checkphone = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."mobile where phone ='".$info['mobile']."'   order by addtimdesc limit 0,50");
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
	    if(empty($info['username']))   $this->message('emptycontact'); 
		  	if(!IValidate::suremobi($info['mobile']))   $this->message('errphone'); 
	    $info['ipaddress'] = "";
	    $ip_l=new iplocation(); 
	    $ipaddress=$ip_l->getaddress($ip_l->getIP());  
	    if(isset($ipaddress["area1"])){
			$info['ipaddress']  = $ipaddress['ip'].mb_convert_encoding($ipaddress["area1"],'UTF-8','GB2312');//('GB2312','ansi',);
		} 
	    $info['cattype'] = 0;//
	   
		// if($shopinfo['is_open'] != 1) $this->message('店铺暂停营业'); 
		// $tempdata = $this->getOpenPosttime($shopinfo['is_orderbefore'],$shopinfo['starttime'],$shopinfo['postdate'],$info['minit'],$shopinfo['befortime']); 
		// if($tempdata['is_opentime'] ==  2) $this->message('选择的配送时间段，店铺未设置');
		// if($tempdata['is_opentime'] == 3) $this->message('选择的配送时间段已超时');
		// $info['sendtime'] = $tempdata['is_posttime'];
		// $info['postdate'] = $tempdata['is_postdate'];
				
				
				
		$info['paytype'] = $info['paytype']==1?1:0;
		$info['areaids'] = '';  
		$info['shopinfo'] = $shopinfo;
		if($subtype == 1){
		   	$info['allcost'] = 0 ;
		   	$info['bagcost'] = 0;
		   	$info['allcount'] = 0; 
		   	$info['goodslist'] = array();
		}else{
		  
		}
		$info['shopps'] = 0;  
		$info['pstype'] = 0;
		$info['cattype'] = 1;//表示不是预订 
		$info['is_goshop']=2;    
		$info['subtype'] = $subtype; 
		$orderclass = new orderclass();
		$orderclass->orderyuding($info);
		$orderid = $orderclass->getorder();
		if($info['userid'] ==  0){ 
		    ICookie::set('orderid',$orderid,86400);
		}
		if($subtype == 2){
		    $smardb->DelShop($info['shopid']);
		} 
                
//                if($info['bookpay']>0){
//                    $link = IUrl::creatUrlEx($this->mid,'wxsite/subshow/orderid/'.$orderid); 
//		    $this->success('预订该桌台需要交预付金,请完成支付',$link); 
//                }
		
		//$this->success('提交成功，等待商家确定！');
		//exit;
                
                //发送预订微信信息
//                $user = $this->mysql->select_one("select openid from ".Mysite::$app->config['tablepre']."wxuser where uid=".$info['userid']);
//                if(sendCustomMsg($this->mid, $this->SendWxMsg_yd($info['userid'],$info['shopid'],$orderid),$user['openid'])){
//                   //第一次通知
//                   $checkinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where id='".$orderid."' ");
//                   $datas['wx_notice'] = $checkinfo['wx_notice'] +1;
//
//                   $this->mysql->update(Mysite::$app->config['tablepre'].'order',$datas,"id='".$checkinfo['id']."'");
//                }
                
		$link = IUrl::creatUrlEx($this->mid,'wxsite/shoppreonlined/sid/'.$info['shopid']); 
		$this->success('提交成功，等待商家确定!',$link); 	 
	}

   	//ajax各开放预约区域下的桌子by yanyh
   	function getpreordertable(){ 
		
	 	$area = IReq::get('yd_area'); 
	 	$mid = IReq::get('mid'); 
	 	$shopid = IReq::get('shopid'); 
  
 		$sql = "select distinct(table_type) from ".Mysite::$app->config['tablepre']."pre_time_slot where shopid = '".$shopid."'  and mid = '".$mid."' and table_area = '".$area."' order by table_type asc";
	 	$preordertable = $this->mysql->getarr($sql);
 
	   	if(empty($preordertable)){
	   		$this->message('未开放！');
	   	}

	    $this->success($preordertable); 
	} 
	
   	//ajax各开放预约桌子下的时间by yanyh
	function getpreordertime(){ 
		
	 	$type = IReq::get('yd_type'); 
	 	$mid = IReq::get('mid'); 
	 	$shopid = IReq::get('shopid'); 
 	
 		$sql = "select * from ".Mysite::$app->config['tablepre']."pre_time_slot where  shopid = '".$shopid."'  and mid = '".$mid."' and table_type = '".$type."'";
	 	$preordertime = $this->mysql->getarr($sql);
 
	   	if(empty($preordertime)){
	   		$this->message('未开放！');
	   	}

	   	//可预约时间
 		$today    = date('Y-m-d',time());
 		$tomorrow = date('Y-m-d',time()+86400);
	   	$booktime = [];
	   	$booktimes = [];
	   	foreach ($preordertime as $key => $value) {

	   		$start     = date('H:i:s',$value['start_booktime']);
	   		$starttime = strtotime($today." ".$start);
	   		$end       = date('H:i:s',$value['end_booktime']);
	   		$endtime   = strtotime($tomorrow." ".$end);

	   		if($end < $start){
	   			$num1 = intval(($endtime - strtotime($tomorrow))/600);
		   		$num2 = intval((strtotime($tomorrow) - $starttime)/600);
 
		   		for ($i=0; $i < $num1+1 ; $i++) { 
		   			$booktime[$i] = date('H:i:s',strtotime($tomorrow)+600*$i);
		   		}
		   		for ($i=$num1+1; $i < $num1+$num2+2 ; $i++) { 
		   			$booktime[$i] = date('H:i:s',$starttime +600*($i-$num1-1));
		   		}

	   		}else{
		  		$num = ($value['end_booktime']-$value['start_booktime'])/600;
//		   		for ($i=0; $i < $num+1 ; $i++) { 
                                for ($i=0; $i < $num ; $i++) { 
		   			$booktime[$i] = date('H:i:s',$value['start_booktime']+600*$i);
		   		}
			}
			
			$booktimes[] = $booktime;
	   	}

	   	$arr = [];
	   	foreach ($booktimes as $key => $value) {
	   		foreach ($value as $key => $value) {
	   			$arr[] = $value;
	   		}
	   	}

	   	$arr = array_unique($arr);
	   	sort($arr);
	
	    $this->success($arr); 
	}
      

    //我要取号 by yanyh
	function shopqueue(){  
	 	
	 	$shopid = intval(IReq::get('shopid'));
        if($shopid){
            ICookie::set('shopid',$shopid,86400);
        }else{
            $shopid = ICookie::get('shopid');
        }
        
    	$phsz_id     = IFilter::act(IReq::get('phsz_id'));
	 	$storeid     = $shopid;
	 	$data['shopid'] = $storeid; 

	 	//商店信息
	 	$where = " and id ='".$storeid."' ";
	 	$tempdd[] =   $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where is_pass = 1  and is_open = 1   ".$where."    ");
	 	$tempdd = $tempdd[0];
	 	$data['shopinfo'] = $tempdd;
  		
  		//餐桌信息
	 	$mid = $this->mid;
	 	$sql = "SELECT * FROM ".Mysite::$app->config['tablepre']."table_phsz WHERE mid = ".$mid." and shopid = ".$shopid." and status=1 order by id asc";  
	 	$tables = $this->mysql->getarr($sql);

	 	//过滤非营业时间
	 	$t = date('H:i:s',time());
	 	foreach ($tables as $key => $value) {
	 		$s = date('H:i:s',$value['starttime']);
	 		$e = date('H:i:s',$value['endtime']);  
	 		if($t < $s || $t > $e){//非营业
	 			unset($tables[$key]);
	 		}
	 	}

	 	//容纳人数	
 	    //对桌子进行人数排序
        if(!empty($tables)) {
            $arrSort = array();  
            foreach($tables as $uniqid => $row){  
                foreach($row as $key=>$value){  
                    $arrSort[$key][$uniqid] = $value;  
                }  
            }  
            array_multisort($arrSort['table_type'], SORT_ASC, $tables);     
        }

  		$i = [];
        foreach ($tables as $key => $value) {
        	$i[$key] = $tables[$key]['table_type'] + 1;
        	if($key == 0){
        		$tables[$key]['contain'] = '1-'.$tables[$key]['table_type'].'人';
        	}else{
        		$tables[$key]['contain'] = $i[$key-1].'-'.$tables[$key]['table_type'].'人';
        	}
        }

	 	foreach ($tables as $key => $value) {

	 		//当前餐桌号
		  	$current_num = $this->mysql->getarr("select qhms from ".Mysite::$app->config['tablepre']."table_krdl where phsz_id = ".$value['id']." and to_days(addtime) = to_days(now()) and status = 1 order by id asc limit 1");  
	  	   	$tables[$key]['current_num'] = $current_num[0]['qhms']? $current_num[0]['qhms'] : 0 ; 
	  	   	//当前排队人数
	  	   	$current_count = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."table_krdl where shopid=".$shopid." and phsz_id = ".$value['id']." and status = 1 and to_days(addtime) = to_days(now())");
            $tables[$key]['current_count'] = $current_num[0]['qhms']? $current_count : 0; 

	 	}
	  
	 	$data['tables'] = $tables;
   
	 	Mysite::$app->setdata($data);
	 }
         
    //插入，立即排号
	 function shopqueuedetail(){  

	 	$phsz_id  = IFilter::act(IReq::get('phsz_id'));
	 	$customer = intval(IReq::get('customer'));
	 	$phone    = intval(IReq::get('phone'));
	 	$shopid   = intval(IReq::get('shopid'));
	   	$uid      = $this->member['uid'];	
	  
   
	    if($customer){

	 		$data['mid']         	   = $this->mid;
	 		$data['uid']    		   = $uid;	
	 		$data['shopid']            = $shopid;
		 	//$data['name']        	   = IReq::get('dl_name');
		 	$data['addtime']     	   = date('Y-m-d H:i:s',time());
		 	$data['guest_numer'] 	   = $customer;
		 	$data['tel']         	   = $phone;
            $data['start_queue_time']  = time();
            $data['status'] 		   = 1;
            //$data['phsz_id'] 		   = intval(IReq::get('phsz_id'));
            $data['wxname']            = $this->member['username'];

            //根据人数系统获取后台的管理员输入的数量自动分配几人桌
            $phsz_arr =$this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."table_phsz where status=1 and sid=".$shopid." order by table_type asc");
            $ids = array();
            foreach($phsz_arr as $key=>$val){
                $starttime = strtotime(date('Y-m-d',time()).' '.date('H:i',$val['starttime']));
                $endtime = strtotime(date('Y-m-d',time()).' '.date('H:i',$val['endtime']));
               
                if( time()>=$starttime  && time()<=$endtime ){
                     //$this->message(date('Y-m-d H:i',$starttime));
                    if($customer<=$val["table_type"]){
                        $data['name']    = $val["dlname"];
                        $data['phsz_id'] = $val["id"];
                    }
                    if($data['name']){
                        break;
                    }
                }
             }

             if(empty($data['name']) && empty($data['phsz_id'])){
                 $link =IUrl::creatUrlEx($this->mid,'wxsite/shopqueue'); 
                 $this->message('你的人数太多，请直接联系我们',$link);
             }
     
            
            //写入当前取号码,只统计当天的排号
            $qhms_count =$this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."table_krdl where shopid=".$shopid." and to_days(addtime) = to_days(now())");
            $qhms_count = $qhms_count+1;    
            
            //判断是否加上排号前缀
            
            $phsz_arr =$this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."table_phsz where id=".$data['phsz_id']." ");

            if($phsz_arr['dlphqz']){
               $data['qhms'] = $phsz_arr['dlphqz'].'00'.$qhms_count;  //当前取号码,前面加上二个零 
            }else{
                $data['qhms'] = '00'.$qhms_count;  //当前取号码,前面加上二个零 
            }


                    $return_str = $this->mysql->insert(Mysite::$app->config['tablepre']."table_krdl",$data);

                    if($return_str){
                            $sql    = "select openid from ".Mysite::$app->config['tablepre']."wxuser where uid=".$uid." and mid = ".$data['mid']."";
                            $user = $this->mysql->select_one($sql);
                            $openid = $user['openid'];

                        //发送微信信息
                        if(sendCustomMsg($this->mid, $this->SendWxMsg($uid,$shopid),$openid)){
                           //第一次通知
                           $checkinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."table_krdl where uid='".$uid."' order by id desc");
                           $datas['is_notice'] = $checkinfo['is_notice'] +1;
                         
                           $this->mysql->update(Mysite::$app->config['tablepre'].'table_krdl',$datas,"id='".$checkinfo['id']."'");
                        }
                    }
                
                $link =IUrl::creatUrlEx($this->mid,'wxsite/shopdetails/id/'.$shopid);                
	        $this->message('排号成功,你的排号是:'.$data['qhms'],$link); 
		}
                
	 }
	//预约满判断  by yanyh
	function is_ydopen(){ 
    	$area 		= IReq::get('yd_area'); 
    	$type 		= IReq::get('yd_type');
    	$click_unix = IReq::get('click_unix'); 
	 	$mid 		= IReq::get('mid'); 
	 	$shopid 	= IReq::get('shopid'); 
 
	 	//查找对比点击的时间是否在某预约段
 		$sql = "select * from ".Mysite::$app->config['tablepre']."pre_time_slot where  shopid = '".$shopid."'  and mid = '".$mid."' and table_area = '".$area."' and table_type = '".$type."'";	
 		$preordertime = $this->mysql->getarr($sql);

 		foreach ($preordertime as $key => $value) {
 			$sbooktime = strtotime(date('Y-m-d',$click_unix)." ".date('H:i:s',$value['start_booktime']));
 			$ebooktime = strtotime(date('Y-m-d',$click_unix)." ".date('H:i:s',$value['end_booktime']));
 
 			if($click_unix >= $sbooktime && $click_unix <= $ebooktime){
 				//定金
 				$bookpay = $value['bookpay'];
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
			   		$this->message('该时间段座位已满,请重新选择！');
			   	}

			   	$tableid = array_rand(array_flip($arr));
  				$data = array($tableid,$bookpay);

			    $this->success($data); 
 			}

 		 
 		}
   
    }

	 function shopshow(){//店铺详情
	 	//清空购物车
	 	$smardb = new newsmcart(); 
		$smardb->setdb($this->mysql)->ClearCart();
				 
		 $areaid = ICookie::get('myaddress');
		 $mid = $this->mid;
		 $data['mid'] = $mid;
	 	 $typelx = IFilter::act(IReq::get('typelx'));
	 	// $ordertype = IFilter::act(IReq::get('ot'));
	 	 //$ordertype = empty($ordertype)?1:$ordertype;
		 if(!empty($typelx)){
			 if($typelx == 'wm'){
				 ICookie::set('shopshowtype','waimai',2592000); 
				 $shopshowtype = 'waimai';
			 }
			 if($typelx == 'mk'){
				 ICookie::set('shopshowtype','market',2592000); 
				 $shopshowtype = 'market';
			 }
			  if($typelx == 'yd'){//订台
			  	 $tableid = IFilter::act(IReq::get('tableid'));
				 ICookie::set('shopshowtype','dingtai',2592000);
				
				 
				 $shopshowtype = 'dingtai';
			 }
		 }else{
			 
			 $shopshowtype = ICookie::get('shopshowtype');
			 
		 }
		 if( !empty($typelx) ){
			 $data['typelx'] = $typelx;			 
		 }else{
			 if( $shopshowtype == 'waimai' ){
				 $data['typelx'] = 'wm';
			 }
			if( $shopshowtype == 'market' ){
				 $data['typelx'] = 'mk';
			 } 
			if( $shopshowtype == 'dingtai' ){
				 $data['typelx'] = 'yd';
			 } 			 
		 }
	 
		$weekji = date('w');
	 	 if($shopshowtype == 'market'){
	 	 	      $id = intval(IReq::get('id')); 
      	 	 $data['id'] = $id;  
      	 	 $shopinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where id='".$id."' ");   //店铺基本信息
      	 	 if(empty($shopinfo)){
      	 	 	//需要进行跳转
      	 	 	 $link =IUrl::creatUrlEx($this->mid,$this->mid,'wxsite/index'); 
      	     $this->message('',$link); 
      	 	 } 
			 $checid = intval(Mysite::$app->config['plateshopid']);
			 if($checid == $id){
				 $link = IUrl::creatUrlEx($this->mid,'wxsite/index'); 
				 $this->message('',$link); 
			 }
			 
			 	 
      	 	  
      	 	 $shopdet = array();
      	 	 $shopdet = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket where shopid='".$id."' "); 
      	 	 
      	 	 $nowhour = date('H:i:s',time()); 
      	 	 $data['openinfo'] = $this->shopIsopen($shopinfo['is_open'],$shopinfo['starttime'],$shopdet['is_orderbefore'],$nowhour); 
			$data['collect'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."collect where  collecttype = 0 and uid = ".$this->member['uid']." and collectid  = '".$shopinfo['id']."' ");//收藏
		   
      	 	 
      	 	 $data['shopinfo'] = $shopinfo;
      	 	 $data['shopdet'] = $shopdet;
      	 
			 	$goodstype=  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."marketcate where shopid = ".$shopinfo['id']."   and parent_id = 0 order by orderid asc");
	 
				$data['goodstype'] = array();
				$tempids = array();
				foreach($goodstype as $key=>$value){
					$soncate = array();
					$soncatearray = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."marketcate where shopid = ".$id."   and parent_id = ".$value['id']."  order by orderid asc");
					foreach($soncatearray as $key1=>$val){
						$val['det'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goods where shopid='".$id."' and    FIND_IN_SET( ".$weekji." , `weeks` )   and typeid =".$val['id']." order by good_order asc ");
 						$tempids[] = $val['id'];
						$soncate[] = $val;
					}
					
					$value['tempids']  = implode(',',$tempids);
					$value['soncate']  = $soncate;
					$data['goodstype'][] =$value;
				}
			 
		 
      	 	 $shopdet['id'] = $id; 
      	 	 $shopdet['shoptype']=1;
      	 		$newshoparray = array_merge($shopinfo,$shopdet);
      	 	 $tempinfo =   $this->pscost($newshoparray,1,$areaid); 
                      $backdata['pstype'] = $tempinfo['pstype'];
                      $backdata['pscost'] = $tempinfo['pscost'];
           $data['psinfo'] = $backdata;
           $data['mainattr'] = array(); 
            
      	 	 $data['shopattr'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shopattr  where  cattype = ".$shopinfo['shoptype']." and shopid = '".$shopinfo['id']."'  order by firstattr asc limit 0,1000");
      	 	  
      	 	 
	 	 }else{
      	 	 $id = intval(IReq::get('id')); 
      	 	 $data['id'] = $id; 
      	 	  
      	 	 $shopinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where id='".$id."' ");   //店铺基本信息
		     $shopimage = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shoprealimg where shopid='".$id."' and mid = '".$mid."'"); //店铺图片
      	 	 $data['shopimage'] = $shopimage;
      	 	 if(empty($shopinfo)){
      	 	 	//需要进行跳转
      	 	 	 $link = IUrl::creatUrlEx($this->mid,'wxsite/shoplist'); 
      	     $this->message('',$link); 
      	 	 } 
      	 	 $shopdet = array();
      	 	 if(empty($shopinfo['shoptype'])){
      	 	 	 $shopdet = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast where shopid='".$id."' "); 
      	 	 }elseif($shopinfo['shoptype'] == 1){
      	 	 	 $shopdet = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket where shopid='".$id."' "); 
      	 	 }
      	 	 $nowhour = date('H:i:s',time()); 
      	 	 $data['openinfo'] = $this->shopIsopen($shopinfo['is_open'],$shopinfo['starttime'],$shopdet['is_orderbefore'],$nowhour); 
       
      	   $data['collect'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."collect where  collecttype = 0 and uid = ".$this->member['uid']." and collectid  = '".$shopinfo['id']."' ");//收藏
		  
      	 	 $data['shopinfo'] = $shopinfo;
      	 	 $data['shopdet'] = $shopdet;
      	 	  $templist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodstype  where shopid='".$id."' order by orderid asc  ");
      	 	  $data['goodstype'] = array();
		
			$wheretype  = '';
			if( $shopshowtype == 'dingtai' ){				
			  $wheretype = "and is_dingtai = 1 and    FIND_IN_SET( ".$weekji." , `weeks` )    ";
			  
			}else{
				 $wheretype = "and is_waisong = 1 and    FIND_IN_SET( ".$weekji." , `weeks` )    ";
			  
			}
			  
      	 	 foreach($templist as $key=>$value){
      	 	 	$value['det'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goods where shopid='".$id."'  ".$wheretype."  and typeid =".$value['id']."  order by good_order asc");
      	 	 	if($value['det']){
      	 	 		$data['goodstype'][]  = $value; 
      	 	 	}
      	 	    
      	 	 }
			
      	 	 $shopdet['id'] = $id; 
      	 	 $shopdet['shoptype']=1;
      	 		$newshoparray = array_merge($shopinfo,$shopdet);
      	 	 $tempinfo =   $this->pscost($newshoparray,1,$areaid); 
                      $backdata['pstype'] = $tempinfo['pstype'];
					  
					   if( $shopshowtype == 'dingtai'){
									$backdata['pscost'] =0;
								  }else{
									$backdata['pscost'] = $tempinfo['pscost'];
								  }
                      
           $data['psinfo'] = $backdata;
           $data['mainattr'] = array(); 
           $templist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shoptype where  cattype = ".$shopinfo['shoptype']." and parent_id = 0 and is_main =1  order by orderid asc limit 0,1000");
      		 foreach($templist as $key=>$value){
      	  	 $value['det'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shoptype where parent_id = ".$value['id']." order by orderid asc  limit 0,20");  
			 		 
      	  	 $data['mainattr'][] = $value;
      	 	 } 
      	 	 $data['shopattr'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shopattr  where  cattype = ".$shopinfo['shoptype']." and shopid = '".$shopinfo['id']."'  order by firstattr asc limit 0,1000");
	 	}
		 $data['shopshowtype'] = $shopshowtype;
		 $data['weekji']  =$weekji ;
		 
		 //统计该店铺的销量
		 $sellcnt = $this->mysql->select_one( "select sum(sellcount+virtualsellcount) sellcnt from  ".Mysite::$app->config['tablepre']."goods where shopid= ".$id."  " );
		 $data['sellcnt'] = $sellcnt['sellcnt'];
		 ///获取评论
		 if( $shopinfo['pointcount'] != 0){
		 	$zongtistart = round( $shopinfo['point']/$shopinfo['pointcount'] ); // 总体评分  // 12 / 3 = 54
		 	$zonghefen = round( $shopinfo['point']/$shopinfo['pointcount'],1 ); // 综合评分
		 }else{
		 	$zongtistart = 0;
		 	$zonghefen = 0;
		 }
		 if( $shopinfo['pointcount'] != 0){
		 	$psfuwustart = round( $shopinfo['psservicepoint']/$shopinfo['psservicepointcount'] ); // 配送服务
		 }else{
		 	$psfuwustart = 0;
		 }
		 $data['zonghefen'] = $zonghefen;
		 $data['zongtistart'] = $zongtistart;
		 $data['psfuwustart'] = $psfuwustart;
		 
		 $pageinfo = new page();
		 $pageinfo->setpage(intval(IReq::get('page')),5);
		  
		 $temparray = $this->mysql->getarr("select * from  ".Mysite::$app->config['tablepre']."comment where  shopid=".$id." order by addtime desc  limit ".$pageinfo->startnum().", ".$pageinfo->getsize()." ");
		 $commentlist = array();
		 foreach($temparray as $key=>$value){
		 	$memberinfo = $this->mysql->select_one("select * from  ".Mysite::$app->config['tablepre']."member where uid = ".$value['uid']." ");
		 	$goodinfo = $this->mysql->select_one("select * from  ".Mysite::$app->config['tablepre']."goods where id = ".$value['goodsid']." ");
		 	$value['username'] =$memberinfo['username'];
		 	$value['shotusername'] =substr($memberinfo['username'],2)."***";
		 	$value['userlogo'] =$memberinfo['logo'];
		 	$value['goodname'] =$goodinfo['name'];
		 	$value['goodpoint'] =$goodinfo['point'];
		 	$value['addtime'] = date("Y-m-d H:i:s",$value['addtime']);
		 	$html ="";
		 	for($i=0;$i<(int)$value['point'];$i++){
		 		$html = $html . "<i class='fa fa-star'></i>";
		 	}
		 	$value['startpoin'] = $html;
		 	$commentlist[] = $value;
		 }
		 $data['commentlist'] = $commentlist;
		 //var_dump($commentlist);
		 $shuliang = $this->mysql->select_one("select count(id) as zongshu , sum(point) as pointzongshu from  ".Mysite::$app->config['tablepre']."comment where   shopid=".$id." order by addtime desc  ");
		 $zongshu =  $shuliang['zongshu'];
		 $pointzongshu =  $shuliang['pointzongshu'];
		 if($pointzongshu != 0){
		 	$haoping = round( $zongshu/$pointzongshu,3 )*100;
		 }else{
		 	$haoping = 0;
		 }
		 $data['haoping'] = $haoping;   // 计算好评率
		// session_start();
		// $_SESSION['otype'] = $ordertype; //订单类型，默认为外卖1，2，堂食
	 	 Mysite::$app->setdata($data); 
		 
	  if($shopinfo['shoptype'] == 1){
		Mysite::$app->setAction('mkshopshow');
	  }else{ 
		Mysite::$app->setAction('shopshow');
		//Mysite::$app->setAction('mkshopcard');
	  }
		 
		 
   } 
   /* 8.2 改变函数 */
  function foodshow(){  	//菜品详情
		$shopshowtype = ICookie::get('shopshowtype');
		$data['shopshowtype'] = $shopshowtype;
		$id = intval( IReq::get('id') );
        $data['goodsid']=$id;
		$foodshow = $this->mysql->select_one( "select * from  ".Mysite::$app->config['tablepre']."goods where id= ".$id."  " );
		$shopid = $foodshow['shopid'];
		$data['shopinfo'] = $this->mysql->select_one( "select * from  ".Mysite::$app->config['tablepre']."shop where id= ".$shopid."  " );
		
		if( $shopshowtype == 'market' ){
			$shopdet = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id  where a.shopid = ".$shopid."   "); 
		}else{
			$shopdet  = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id  where a.shopid = ".$shopid."   "); 
		}
		 
		$nowhour = date('H:i:s',time()); 
        $nowhour = strtotime($nowhour);
		$checkinfo = $this->shopIsopen($data['shopinfo']['is_open'],$data['shopinfo']['starttime'],$shopdet['is_orderbefore'],$nowhour); 
        $data['opentype'] = $checkinfo['opentype'];
		 /* 商品评价 */
		$data['pointcount'] = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."comment   where shopid = ".$shopid." and  goodsid  = ".$id."   "); 
		 if($foodshow['is_cx'] == 1){
				//测算促销 重新设置金额
					$cxdata = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goodscx where goodsid=".$foodshow['id']."  ");
					$newdata = getgoodscx($foodshow['cost'],$cxdata);
					
					$foodshow['zhekou'] = $newdata['zhekou'];
					$foodshow['is_cx'] = $newdata['is_cx'];
					$foodshow['cost'] = $newdata['cost'];
		 }
		$foodshow['sellcount'] = $foodshow['sellcount']+$foodshow['virtualsellcount'];

		$data['shopdet'] = $shopdet;
		$data['foodshow']  = $foodshow;
		
		/* 配送费 */
		$newshoparray = array_merge($data['shopinfo'],$shopdet);
      	 	 $tempinfo =   $this->pscost($newshoparray,1,$areaid); 
                      $backdata['pstype'] = $tempinfo['pstype'];
                      $backdata['pscost'] = $tempinfo['pscost'];
           $data['psinfo'] = $backdata;
		
		
		
		$data['productinfo'] = !empty($foodshow)?unserialize($foodshow['product_attr']):array(); 
		
		$smardb = new newsmcart(); 
		 $smardb->setdb($this->mysql)->SetShopId($this->mid,$shopid);
		$data['nowselect'] = array();
		if($foodshow['have_det'] == 0){
			$tempinfo = $smardb->SetGoodsType(1)->productone($id);
		 
			$data['carnum'] =  $tempinfo;
		}else{
			$nowselect =$smardb->FindInproduct($id);
			$data['nowselect'] = $nowselect;
			$data['carnum'] = $nowselect['count'];
		}
	 

		//获取product 在goodsid中的商品
		$data['attrids'] = array();
		if(!empty($nowselect)){
			$data['attrids'] = explode(',',$nowselect['attrids']);
		}
		
		$productlist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."product where goodsid=".$id." and shopid=".$shopid."");
		$data['productlist'] = $productlist;
		 
		$temparray = $this->mysql->getarr("select * from  ".Mysite::$app->config['tablepre']."comment where goodsid=".$id." and shopid=".$shopid." order by addtime desc limit 10 ");
		$commentlist = array();
		foreach($temparray as $key=>$value){
			$memberinfo = $this->mysql->select_one("select * from  ".Mysite::$app->config['tablepre']."member where uid = ".$value['uid']." ");
			$value['username'] =$memberinfo['username'];
			$commentlist[] = $value;
		}
		$data['commentlist'] = $commentlist;
		$data['mid'] = $this->mid;
		$shuliang = $this->mysql->select_one("select count(id) as zongshu , sum(point) as pointzongshu from  ".Mysite::$app->config['tablepre']."comment where goodsid=".$id." and shopid=".$shopid." order by addtime desc  ");
	 
		$zongshu =  $shuliang['zongshu']; 
		$pointzongshu =  $shuliang['pointzongshu'];
		if($pointzongshu != 0){
			$haoping = round( $zongshu/$pointzongshu,3 )*5*100;
		}else{
			$haoping = 0;
		}
	    $data['haoping'] = $haoping;   // 计算好评率
		Mysite::$app->setdata($data);
	  
	
   }
   
    /* 8.2新增函数 */
  function showshoprealimg(){
        $parent_id= IFilter::act(IReq::get('parent_id'));
        $shoprealimg = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shoprealimg where  parent_id = ".$parent_id);//商家实景分类图片
        $data['shoprealimg']=$shoprealimg;
        Mysite::$app->setdata($data);
    }

  /* 8.2 改变函数 */
   function getdetailinfo(){
	   	$typelx = IFilter::act(IReq::get('typelx')); 
			$data['typelx'] = $typelx;
	   $shopid = IFilter::act(IReq::get('shopid'));
	   $shopinfo = $this->mysql->select_one("select *  from  ".Mysite::$app->config['tablepre']."shop  where id = ".$shopid." ");
	   if(empty($shopinfo)) $this->message('获取店铺数据失败');
	   $data['collect'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."collect where  collecttype = 0 and uid = ".$this->member['uid']." and collectid  = '".$shopinfo['id']."' ");//收藏
       $shopreal = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shopreal where  shopid = ".$shopid." limit 0,4");//商家实景分类
       $data['shopreal']=array();
       foreach($shopreal as $key=>$val){
           $shoprealimg = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shoprealimg where  parent_id = ".$val['id']);//商家实景分类图片
           $imgcount = $this->mysql->select_one("select count(id) as count from ".Mysite::$app->config['tablepre']."shoprealimg where  parent_id = ".$val['id']);//商家实景分类图片总数
           $val['shoprealimg']=$shoprealimg;
           $val['imgcount']=$imgcount['count'];

           $data['shopreal'][]=$val;
       }
#print_r($data['shopreal']);
	   $shopshowtype = $shopinfo['shoptype'];
	   if( $shopshowtype == 1 ){
			$shopdet = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id  where a.shopid = ".$shopid."   "); 
		}else{
			$shopdet  = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id  where a.shopid = ".$shopid."   "); 
		}
		/* 店铺星级计算 */
		$zongpoint = $shopinfo['point'];
		$zongpointcount = $shopinfo['pointcount'];
		if($zongpointcount != 0 ){
			$shopstart = intval( round($zongpoint/$zongpointcount) );
		}else{
			$shopstart= 0;
		}
		
		$cxsignlist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodssign where mid='".$this->mid."' and type='cx' order by id desc limit 0, 100");
		$cxarray  =  array();
		foreach($cxsignlist as $key=>$value){
		   $cxarray[$value['id']] = $value['imgurl'];
		}
		 		
		 $cxinfo = $this->mysql->getarr("select name,id,signid from ".Mysite::$app->config['tablepre']."rule where   shopid = ".$shopinfo['id']." and status = 1 and starttime  < ".time()." and endtime > ".time()." ");
		 $cxlist = array();
								  
		 foreach($cxinfo as $k1=>$v1){
			 if(isset($cxarray[$v1['signid']])){
				 $v1['imgurl'] = $cxarray[$v1['signid']];
				 $cxlist[] = $v1;
			 }
		 }
		$data['cxlist'] = $cxlist;
	 
		$areaid = ICookie::get('myaddress');
	 
		$newshoparray = array_merge($shopinfo,$shopdet);
      	 $tempinfo =   $this->pscost($newshoparray,1,$areaid); 
                      $backdata['pstype'] = $tempinfo['pstype'];
                      $backdata['pscost'] = $tempinfo['pscost'];
           $data['psinfo'] = $backdata;
           
	 
		$data['shopstart'] = $shopstart;
	   $data['shopinfo'] = $shopinfo;
	   $data['shopdet'] = $shopdet;
	   Mysite::$app->setdata($data);
   }
   function getshopcomment(){
	   $typelx = IFilter::act(IReq::get('typelx')); 
			$data['typelx'] = $typelx;
	   $shopid = IFilter::act(IReq::get('shopid'));
	    $shopinfo = $this->mysql->select_one("select *  from  ".Mysite::$app->config['tablepre']."shop  where id = ".$shopid." "); 
		$data['shopinfo'] = $shopinfo;
		 $shopshowtype = $shopinfo['shoptype'];
	   if( $shopshowtype == 1 ){
			$shopdet = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id  where a.shopid = ".$shopid."   "); 
		}else{
			$shopdet  = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id  where a.shopid = ".$shopid."   "); 
		}
		$data['shopdet'] = $shopdet;
	   if(empty($shopinfo)) $this->message('获取店铺数据失败');
	    $data['collect'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."collect where  collecttype = 0 and uid = ".$this->member['uid']." and collectid  = '".$shopinfo['id']."' ");//收藏
		  
	   if( $shopinfo['pointcount'] != 0){
			$zongtistart = round( $shopinfo['point']/$shopinfo['pointcount'] ); // 总体评分  // 12 / 3 = 54
			$zonghefen = round( $shopinfo['point']/$shopinfo['pointcount'],1 ); // 综合评分 
		}else{
			$zongtistart = 0;
			$zonghefen = 0;
		}
		if( $shopinfo['pointcount'] != 0){
			$psfuwustart = round( $shopinfo['psservicepoint']/$shopinfo['psservicepointcount'] ); // 配送服务  
		}else{
			$psfuwustart = 0;
		}
		$data['zonghefen'] = $zonghefen;
		$data['zongtistart'] = $zongtistart;
		$data['psfuwustart'] = $psfuwustart;
		
		  $pageinfo = new page();
	  $pageinfo->setpage(intval(IReq::get('page')),5); 
	  
	   $temparray = $this->mysql->getarr("select * from  ".Mysite::$app->config['tablepre']."comment where  shopid=".$shopid." order by addtime desc  limit ".$pageinfo->startnum().", ".$pageinfo->getsize()." ");
		$commentlist = array();
		foreach($temparray as $key=>$value){
			$memberinfo = $this->mysql->select_one("select * from  ".Mysite::$app->config['tablepre']."member where uid = ".$value['uid']." ");
			$goodinfo = $this->mysql->select_one("select * from  ".Mysite::$app->config['tablepre']."goods where id = ".$value['goodsid']." ");
			$value['username'] =$memberinfo['username'];
			$value['userlogo'] =$memberinfo['logo'];
			$value['goodname'] =$goodinfo['name'];
			$value['goodpoint'] =$goodinfo['point']; 
			$commentlist[] = $value;
		}
		$data['commentlist'] = $commentlist;
 
		$shuliang = $this->mysql->select_one("select count(id) as zongshu , sum(point) as pointzongshu from  ".Mysite::$app->config['tablepre']."comment where   shopid=".$shopid." order by addtime desc  ");
		$zongshu =  $shuliang['zongshu']; 
		$pointzongshu =  $shuliang['pointzongshu'];
		if($pointzongshu != 0){
			$haoping = round( $zongshu/$pointzongshu,3 )*100;
		}else{
			$haoping = 0;
		}
	    $data['haoping'] = $haoping;   // 计算好评率
		
	 
		
	#   print_r( $data['commentlist'] );
	   Mysite::$app->setdata($data);
   }
    function getshopmorecomment(){
	   	$typelx = IFilter::act(IReq::get('typelx')); 
			$data['typelx'] = $typelx;
	   $shopid = IFilter::act(IReq::get('shopid'));
	   $goodid = IFilter::act(IReq::get('goodid'));
	    $shopinfo = $this->mysql->select_one("select *  from  ".Mysite::$app->config['tablepre']."shop  where id = ".$shopid." "); 
		$data['shopinfo'] = $shopinfo;
	
		
		  $pageinfo = new page();
	  $pageinfo->setpage(intval(IReq::get('page')),5); 
	 if( !empty($goodid) ){ 
	   $temparray = $this->mysql->getarr("select * from  ".Mysite::$app->config['tablepre']."comment where  shopid=".$shopid." and is_show = 0 and goodsid = ".$goodid." order by addtime desc  limit ".$pageinfo->startnum().", ".$pageinfo->getsize()." ");
	 }else{
		  $temparray = $this->mysql->getarr("select * from  ".Mysite::$app->config['tablepre']."comment where  shopid=".$shopid." and is_show = 0 order by addtime desc  limit ".$pageinfo->startnum().", ".$pageinfo->getsize()." ");
	 }
		$commentlist = array();
		foreach($temparray as $key=>$value){
			$memberinfo = $this->mysql->select_one("select * from  ".Mysite::$app->config['tablepre']."member where uid = ".$value['uid']." ");
			$goodinfo = $this->mysql->select_one("select * from  ".Mysite::$app->config['tablepre']."goods where id = ".$value['goodsid']." "); 
			if(empty($goodinfo)){
				$goodinfo = $this->mysql->select_one("select * from  ".Mysite::$app->config['tablepre']."product where goodsid  = ".$value['goodsid']." ");
				$value['goodname'] =$goodinfo['goodsname'];
			}else{
				$value['goodname'] =$goodinfo['name'];
			}
			$value['username'] =$memberinfo['username'];
			
			if( !empty($value['virtualname']) ){
				$value['username'] = $value['virtualname'];
				$goodsinfo =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goods   where id = '".$value['goodsid']."'   ");
				if( empty($goodsinfo) ){
					$goodsinfo =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."product   where goodsid = '".$value['goodsid']."'   ");
					$value['goodsname'] = $goodsinfo['goodsname'].'【'.$attrname.'】'.'【刷】';
				}else{
					$value['goodsname'] = $goodsinfo['name'].'【刷】';
				}
				
			}
			
			$value['userlogo'] =$memberinfo['logo'];
			
			$value['goodpoint'] =$goodinfo['point']; 
			$value['addtime'] = date('Y-m-d H:i',$value['addtime']);  
			$value['huifutime'] = date('Y-m-d H:i',$value['replytime']);  
			$commentlist[] = $value;
		}
		$data['commentlist'] = $commentlist;
 #  print_r($data['commentlist']);
		
	/* 	$datas = json_encode($data);
		  echo 'showmoreorder('.$datas.')';
		  exit; 
			 */
		
	#   print_r( $data['commentlist'] );
	   Mysite::$app->setdata($data);
   }
   
   function gowei(){
   	 
   	 $id = IFilter::act(IReq::get('id'));    

   	$data['scoretocost'] = Mysite::$app->config['scoretocost']; 
   	//	id	card 优惠劵卡号	card_password 优惠劵密码	status 状态，0未使用，1已绑定，2已使用，3无效	creattime 制造时间	cost 优惠金额	limitcost 购物车限制金额下限	endtime 失效时间	uid 用户ID	username 用户名	usetime 使用时间	name
   	$data['juanlist'] =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."juan  where uid='".$this->member['uid']."' and endtime > ".time()." and status = 1   ");
     
   	 $shopinfo =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id  where shopid = ".$id."   ");  
		 if(empty($shopinfo)){
		 	 $shopinfo =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id  where shopid = ".$id."   ");  
		 } 
		 
		$nowtime = time();
		 $timelist = array();
		 $info = explode('|',$shopinfo['starttime']);
		 $info = is_array($info)?$info:array($info);
		 $data['is_open'] = 0;
	  
     if($shopinfo['is_open'] == 0  || $shopinfo['is_pass'] == 0){
		 	  $data['is_open'] = 0;
		 }
		 
		 
		 
		$nowhout = strtotime(date('Y-m-d',time()));//当天最小linux 时间
		$timelist = !empty($shopinfo['postdate'])?unserialize($shopinfo['postdate']):array();
		$data['timelist'] = array();
		$checknow = time();
		$whilestatic = $shopinfo['befortime'];
		$nowwhiltcheck = 0;
		while($whilestatic >= $nowwhiltcheck){
		    $startwhil = $nowwhiltcheck*86400;
			foreach($timelist as $key=>$value){
				$stime = $startwhil+$nowhout+$value['s'];
				$etime = $startwhil+$nowhout+$value['e'];
				if($etime  > $checknow){
					$tempt = array();
					$tempt['value'] = $value['s']+$startwhil;
					$tempt['s'] = date('H:i',$nowhout+$value['s']);
					$tempt['e'] = date('H:i',$nowhout+$value['e']);
					$tempt['d'] = date('Y-m-d',$stime);
				//	$tempt['s'] = $tempt['d'].' '.$tempt['s'];
					$tempt['s'] = $tempt['s'];
					$tempt['i'] =  $value['i'];
					$tempt['cost'] =  isset($value['cost'])?$value['cost']:0;
					
					$tempt['name'] = $stime > $checknow?$tempt['s'].'-'.$tempt['e']:'立即配送';
					$data['timelist'][] = $tempt;
				}
			}
		 
			$nowwhiltcheck = $nowwhiltcheck+1;
		}  
		 	
		  $data['lat'] = ICookie::get('lat');
		  $data['lng'] = ICookie::get('lng');
     $data['deaddress'] =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."address  where userid = ".$this->member['uid']." and `default`=1   "); 
   	 $data['shopinfo'] = $shopinfo;
	
    	Mysite::$app->setdata($data); 
  }
  function goweishop(){//购物车 
   	 $id = IFilter::act(IReq::get('id'));    
   	$data['scoretocost'] = Mysite::$app->config['scoretocost']; 
   	//	id	card 优惠劵卡号	card_password 优惠劵密码	status 状态，0未使用，1已绑定，2已使用，3无效	creattime 制造时间	cost 优惠金额	limitcost 购物车限制金额下限	endtime 失效时间	uid 用户ID	username 用户名	usetime 使用时间	name
   	$data['juanlist'] =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."juan  where uid='".$this->member['uid']."' and endtime > ".time()." and status = 1   ");
     
   	 $shopinfo =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id  where shopid = ".$id."   ");  
		 if(empty($shopinfo)){
		 	 $shopinfo =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id  where shopid = ".$id."   ");  
		 }  
		 $nowtime = time();
		 $timelist = array();
		 $info = explode('|',$shopinfo['starttime']);
		 $info = is_array($info)?$info:array($info);
		 $data['is_open'] = 0;
	  
     if($shopinfo['is_open'] == 0  || $shopinfo['is_pass'] == 0){
		 	  $data['is_open'] = 0;
		 }
		 
		 
		 
		$nowhout = strtotime(date('Y-m-d',time()));//当天最小linux 时间
		$timelist = !empty($shopinfo['postdate'])?unserialize($shopinfo['postdate']):array();
		$data['timelist'] = array();
		$checknow = time();
		$whilestatic = $shopinfo['befortime'];
		$nowwhiltcheck = 0;
		while($whilestatic >= $nowwhiltcheck){
		    $startwhil = $nowwhiltcheck*86400;
			foreach($timelist as $key=>$value){
				$stime = $startwhil+$nowhout+$value['s'];
				$etime = $startwhil+$nowhout+$value['e'];
				if($etime  > $checknow){
					$tempt = array();
					$tempt['value'] = $value['s']+$startwhil;
					$tempt['s'] = date('H:i',$nowhout+$value['s']);
					$tempt['e'] = date('H:i',$nowhout+$value['e']);
					$tempt['d'] = date('Y-m-d',$stime);
				//	$tempt['s'] = $tempt['d'].' '.$tempt['s'];
					$tempt['s'] =  $tempt['s'];
					$tempt['i'] =  $value['i'];
					$tempt['cost'] =  isset($value['cost'])?$value['cost']:0;
					
					$tempt['name'] = $stime > $checknow?$tempt['s'].'-'.$tempt['e']:'立即配送';
					$data['timelist'][] = $tempt;
				}
			}
		 
			$nowwhiltcheck = $nowwhiltcheck+1;
		} 
	    	
		  $data['lat'] = ICookie::get('lat');
		  $data['lng'] = ICookie::get('lng');
     $data['deaddress'] =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."address  where userid = ".$this->member['uid']." and `default`=1   ");  
	 $data['shopinfo'] = $shopinfo;
    	Mysite::$app->setdata($data); 
   }
   function shopgoodslist(){//点菜
   	 
	 	 $id = IFilter::act(IReq::get('id'));    
   	 $shopinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where id='".$id."' ");
   	 $data['shopinfo'] = $shopinfo;
   	 if(empty($shopinfo)){
	 	 	//需要进行跳转
	 	 	 $link = IUrl::creatUrlEx($this->mid,'wxsite/shoplist'); 
	     $this->message('',$link); 
	 	 } 
	 	 $data['goodstype'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodstype where shopid='".$id."' ");
	 	 $data['goodslist'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goods where shopid='".$id."' ");
   	 Mysite::$app->setdata($data); 
   }
   
 private function getCardDownAmount($card_openid, $totalamount, $membercardcode, $couponcode) {
	$cardDownCost = 0;
 	
   	if(empty($card_openid)){
		return $cardDownCost;
   	}
   	if(empty($membercardcode) && empty($couponcode)){
   		return $cardDownCost;
   	}

   	


   	if (empty($membercardcode)) $membercardcode ='';
   	if (empty($couponcode)) $couponcode ='';
   	
   	//获取微信卡券
   	$params = array();
   	$params ['mid'] = $this->mid;
   	$params ['openid'] = $this->member['card_openid'];
   	$params ['code'] = $membercardcode;
   	$params ['totalamount'] = $totalamount*100;
   	$params ['couponcode'] = $couponcode;
   	
   	if (api_post_getPreferentiaInfoOfCard ( $params, $return_code, $return_msg, $return_data )) {
   		if ($return_code==1 && $return_data) {
   			return round($return_data['downcost']/100,2);
   		}
   	}
   	return 0;
 }
 function getCouponListByMemberAndAmount() {
 	$this->checkwxuser();
 	if( $this->checkbackinfo() ){
 		if($this->member['uid'] == 0||empty($this->mid))  $this->message('未登陆');
 	}
 
 
 	if(empty($this->member['card_openid'])){
 		$this->message('未关联卡券openid');
 	}
 	$membercardcode = IFilter::act(IReq::get('membercardcode'));
 	$totalamount = IFilter::act(IReq::get('totalamount'));
 
 	if (empty($membercardcode)) $membercardcode = '';
 	//获取微信卡券
 	$params = array();
 	$params ['mid'] = $this->mid;
 	$params ['openid'] = $this->member['card_openid'];
 	$params ['code'] = $membercardcode;
 	$params ['totalamount'] = $totalamount*100;
 
 	if (api_post_getCouponListByMemberAndAmount ( $params, $return_code, $return_msg, $return_data )) {
 		if ($return_code==1 && $return_data) {
 			$this->success($return_data);
 			return;
 		}
 	}
 	$return_msg=empty($return_msg)?'获取卡券列表失败':$return_msg;
 	$this->message('未关联卡券openid');
 }  
   
   function shopcart(){//购物车
   	 $id = IFilter::act(IReq::get('id'));  
         $shopid = intval(IReq::get('id'));
         
         //判断购物车是否为空
         $smardb = new newsmcart();
         $carinfo = array();
         if($smardb->setdb($this->mysql)->SetShopId($this->mid,$shopid)->OneShop()){
            $carinfo = $smardb->getdata(); 			 
            if(empty($carinfo['goodslist'])){
                //$link = IUrl::creatUrl($this->mid,'shopcenter/usershopcxnotice'); 
                //$this->message('当前购物车为空');
                echo "<script>alert('当前购物车为空');window.history.go(-1);</script>";
            }
         }
         
   	$data['scoretocost'] = Mysite::$app->config['scoretocost']; 
   	//	id	card 优惠劵卡号	card_password 优惠劵密码	status 状态，0未使用，1已绑定，2已使用，3无效	creattime 制造时间	cost 优惠金额	limitcost 购物车限制金额下限	endtime 失效时间	uid 用户ID	username 用户名	usetime 使用时间	name
   	$data['juanlist'] =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."juan  where uid='".$this->member['uid']."' and endtime > ".time()." and status = 1   ");
//id	juanid	fafangtime	uid 顾客uid	username 顾客姓名	juanname 优惠卷名称	juancost 面值	juanlimitcost 限制金额	endtime 过期时间	lqstatus 默认0是未领取 1是用户已领取	status 状态 0未使，1已使用，2无效	juanshu 优惠卷数量	usetime 优惠卷使用时间    
   	$data['wxjuanlist'] =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."wxuserjuan  where uid='".$this->member['uid']."' and endtime > ".time()." and lqstatus = 1 and status = 0   ");
    
  # 	print_r($data['wxjuanlist']);
   	
   	 $shopinfo =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id  where shopid = ".$id."   ");  
		 if(empty($shopinfo)){
		 	 $shopinfo =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id  where shopid = ".$id."   ");  
		 } 
		 $nowtime = time();
		 $timelist = array();
		 $info = explode('|',$shopinfo['starttime']);
		 $info = is_array($info)?$info:array($info);
		 $data['is_open'] = 0;
	  
         if($shopinfo['is_open'] == 0  || $shopinfo['is_pass'] == 0){
		 	  $data['is_open'] = 0;
		 }
	
		$nowhout = strtotime(date('Y-m-d',time()));//当天最小linux 时间
		$timelist = !empty($shopinfo['postdate'])?unserialize($shopinfo['postdate']):array();
		$data['timelist'] = array();
		$checknow = time();
		$whilestatic = $shopinfo['befortime'];
		$nowwhiltcheck = 0;
		while($whilestatic >= $nowwhiltcheck){
		    $startwhil = $nowwhiltcheck*86400;
			foreach($timelist as $key=>$value){
				$stime = $startwhil+$nowhout+$value['s'];
				$etime = $startwhil+$nowhout+$value['e']; 
				if($etime  > $checknow){
					$tempt = array();
					$tempt['value'] = $value['s']+$startwhil;
					$tempt['s'] = date('H:i',$nowhout+$value['s']);
					$tempt['e'] = date('H:i',$nowhout+$value['e']);
					$tempt['d'] = date('Y-m-d',$stime);
					$tempt['i'] =  $value['i'];
					$tempt['cost'] =  isset($value['cost'])?$value['cost']:'0';
					
					$tempt['name'] = $stime > $checknow?$tempt['s'].'-'.$tempt['e']:'立即配送';
					$tempt['mid'] = $this->mid;
					$data['timelist'][] = $tempt;
				}
			}
		 
			$nowwhiltcheck = $nowwhiltcheck+1;
		}
 

if(  empty($this->member['uid']) || $this->member['uid'] ==  0){	 
			$data['deaddress']  = array();
				$cdata['id'] = 0;
				$cdata['default'] = 1;
				$cdata['contactname'] = ICookie::get('wxguest_username');
				$cdata['phone'] = ICookie::get('wxguest_phone');
				$cdata['address']  = ICookie::get('wxguest_address');
				if(empty($cdata['contactname'])){
					$data['deaddress'] = array();
				}else{
					$data['deaddress'] = $cdata;
				}
			}else{
		$data['deaddress'] =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."address  where userid = ".$this->member['uid']." and `default`=1   "); 
   		if (!empty($this->member['card_openid'])) {
   			//获取微信会员卡
   			$params = array();
   			$params ['mid'] = $this->mid;
   			$params ['openid'] = $this->member['card_openid'];

   			
   			if (api_post_getMemberCards ( $params, $return_code, $return_msg, $return_data )) {
   				if ($return_code==1 && $return_data) {
   					$data['membercardlist'] = $return_data;
   				}
   			}
   		}
	} 

	 	
		  $data['lat'] = ICookie::get('lat');
		  $data['lng'] = ICookie::get('lng');
		  $data['shopshowtype'] = ICookie::get('shopshowtype');
                  $data['tableid'] = ICookie::get('tableid');
                  $data['shopid'] = $shopid;
   	      $data['shopinfo'] = $shopinfo;
    	  Mysite::$app->setdata($data); 
   }
   function getjuan(){
	
		$this->checkwxuser();
	 $id = intval( IReq::get('id') );
	  $wxuserjuan = $this->mysql->select_one("select a.*,b.cartdesrc from ".Mysite::$app->config['tablepre']."wxuserjuan as a left join ".Mysite::$app->config['tablepre']."wxjuan as b on a.juanid = b.id  where a.id='".$id."' and a.uid='".$this->member['uid']."'  ");
	  
	  if(empty($wxuserjuan)) $this->message('获取用户失败！');
	  $data['wxuserjuan']  = $wxuserjuan;
	
	  Mysite::$app->setdata($data); 
	
   }
   
    function wxgetjuan(){
		
	
		$this->checkwxuser();
		
		
	 $id = intval( IReq::get('id') );
	
	 $wxuserjuan = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."wxuserjuan where id='".$id."'");
	 
	   if(empty($wxuserjuan)) $this->message('获取优惠卷失败！');
	 
	   $data['lqstatus']  = 1;
	    $this->mysql->update(Mysite::$app->config['tablepre'].'wxuserjuan',$data,"id='".$id."'");

		
	   $this->success('success'); 
	}
	
   
   function member(){//用户中心
   	
   	//$this->checkwxweb();
   	//$this->checkwxuser();
    $wxuser = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."wxuser where mid='".$this->mid."' and uid='".$this->member['uid']."'");
	//echo "select * from ".Mysite::$app->config['tablepre']."wxuser where mid='".$this->mid."' and uid='".$this->member['uid']."'"; 
    $data['wxuserbangd'] =  $wxuser['is_bang'];
		   
	/* if(empty($wxuser['is_bang'])){
		$link = IUrl::creatUrlEx($this->mid,'wxsite/bangdmem');
		$this->message('',$link);
	}
 */

   	 $data['juanshu'] = $this->mysql->counts("select *  from ".Mysite::$app->config['tablepre']."juan where mid='".$this->mid."' and  uid='".$this->member['uid']."'  and status = 1 order by id asc limit 0,50"); 
   	 $data['wxjuanshu'] = $this->mysql->counts("select *  from ".Mysite::$app->config['tablepre']."wxuserjuan where mid='".$this->mid."' and  uid='".$this->member['uid']."'  and lqstatus = 1 order by id asc limit 0,50");
  
   	 Mysite::$app->setdata($data); 
   	
   }
     function bangdmem(){
	   
	       $wxuser = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."wxuser where  mid='".$this->mid."' and  uid='".$this->member['uid']."'");
		  	$link = IUrl::creatUrlEx($this->mid,'wxsite/member');
		if(empty($wxuser)) $this->message('未关注我们，不可绑定帐号',$link);
		if($wxuser['is_bang'] == 1){
			
			$link = IUrl::creatUrlEx($this->mid,'wxsite/member');
			$this->message('',$link);
		}
	   
   }
   function wxbangduser(){
	   $wxbanguser = trim(IReq::get('wxbanguser'));
	   $wxbangpsw = trim(IReq::get('wxbangpsw'));
	   
	   $wxuser = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."wxuser where mid='".$this->mid."' and  uid='".$this->member['uid']."'");
		if(empty($wxuser)) $this->message('未关注我们，不可绑定帐号');
		if($wxuser['is_bang'] == 1) $this->message('已绑订帐号不可重复绑定');
	   
	    if(empty($wxbanguser)) $this->message('绑定帐号失败,帐号为空');
	   if(empty($wxbangpsw )) $this->message('绑定帐号失败,密码为空');
	   

		//已 注册用户绑定		
		$info =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where mid='".$this->mid."' and  (email='".$wxbanguser."' or username='".$wxbanguser."') ");
			 if(empty($info)) $this->message('绑定帐号失败,帐号未查找到');
			  if(!empty($info['is_bang'])) $this->message('帐号已绑定其他帐号');
			  if( $info['password'] != md5($wxbangpsw) ) $this->message('帐号绑订失败,密码错误');//怎么样绑订定微信号
			 
			 $data['uid'] = $info['uid'];
			 $data['is_bang'] = 1;
			 $this->mysql->update(Mysite::$app->config['tablepre'].'wxuser',$data,"uid='".$this->member['uid']."'");  
			 
			 
			 //删除默认绑定帐号
			 $temuser  = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where mid='".$this->mid."' and  uid='".$wxuser['uid']."' ");
			 $all['score'] = $temuser['score']+$info['score'];
			 $all['cost'] =  $temuser['cost'] +$info['cost']; 
			 $all['is_bang'] = 1;
			 $this->mysql->update(Mysite::$app->config['tablepre'].'member',$all,"mid='".$this->mid."' and  uid='".$info['uid']."'");  
			 //合并积分
			 $this->mysql->delete(Mysite::$app->config['tablepre'].'member',"mid='".$this->mid."' and  uid ='".$wxuser['uid']."'");    
			 $this->success('绑定帐号成功');
			
	 
		#	print_r($this->member['uid']);
		
	//绑定未注册的用户   插入用户信息	
	/*
	 $arr['username'] = $tname;
     $arr['phone'] = $phone;
     $arr['address'] = $address;
     $arr['password'] = md5($password);
     $arr['email'] = $email;
     $arr['creattime'] = time(); 
     $arr['score']  = $score == 0?Mysite::$app->config['regesterscore']:$score;
     $arr['logintime'] = time(); 
     $arr['logo'] = $userlogo;
     $arr['loginip'] = IClient::getIp();
     $arr['group'] = $group;
     $arr['cost'] = $cost; 
     $arr['parent_id'] = intval(ICookie::get('logincode'));  
     $this->mysql->insert(Mysite::$app->config['tablepre'].'member',$arr);   
     #$this->uid = $this->mysql->insertid();
     $this->uid =  $wxuser['uid'];
	
     if($arr['score'] > 0){
        $this->addlog($this->uid,1,1,$arr['score'],'注册送积分','注册送积分'.$arr['score'],$arr['score']);
     }
     
     
     $logintype = ICookie::get('adlogintype');
	 	 $token = ICookie::get('adtoken');
	 	 $openid = ICookie::get('adopenid'); 
	 	 if(!empty($logintype)){
	 	 	   $apiinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."otherlogin where loginname='".$logintype."'  ");
	 	 	   if(!empty($apiinfo)){
	 	 	   	//更新
	 	 	   	  $tempuid = $this->uid;
	 	 	   	  $this->mysql->update(Mysite::$app->config['tablepre'].'oauth',array('uid'=>$tempuid),"openid='".$openid."' and type = '".$logintype."'"); 
	          ICookie::set('logintype',$logintype,86400);  
	 	 	   }
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
       $juandata['uid'] = $this->uid;// 用户ID	
       $juandata['username'] = $arr['username'];// 用户名	
       $juandata['name'] = '注册账号赠送优惠券';//  优惠券名称 
	 	   $this->mysql->insert(Mysite::$app->config['tablepre'].'juan',$juandata); 
	 	 
	 	 }
	 */	 
		
		
		
		
   }
   function paycart(){
	   
   }
   
   	function payonline(){
		//在线支付
		$this->checkmemberlogin();
		$paytype='alimobile';
	 	$cost = intval(IReq::get('cost'));
	 	if($cost < 10) $this->message('card_limit');
	 	$paylist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."paylist   order by id asc limit 0,50");
		if(is_array($paylist)){
		  foreach($paylist as $key=>$value){
			   $paytypelist[] =$value['loginname'];		 
		  }
	  }
		if(!in_array($paytype,$paytypelist)){
		  $this->message('other_nodefinepay');
		} 
		$paydir = hopedir.'/plug/pay/'.$paytype;
	 	if(!file_exists($paydir.'/pay.php'))
    { 
      	$this->message('other_notinstallapi');
    } 
	 	$dopaydata = array('type'=>'acount','upid'=>$this->member['uid'],'cost'=>$cost);//支付数据
    include_once($paydir.'/pay.php');  
	}
	
	/* 8.3新增 */
	function rechargepayonline(){ 
		//余额充值用手机支付宝在线支付
		$this->checkmemberlogin();
		$cost = intval(IFilter::act( IReq::get('cost') ));
	 	$paytype =  'alimobile';
  	 	 
  	 	$paylist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."paylist   order by id asc limit 0,50");
		if(is_array($paylist)){
		  foreach($paylist as $key=>$value){
			   $paytypelist[] =$value['loginname'];		 
		  }
	    }
		if(!in_array($paytype,$paytypelist)){
		  $this->message('other_nodefinepay');
		} 
		$paydir = hopedir.'/plug/pay/'.$paytype;
	
	 	if(!file_exists($paydir.'/pay.php'))
    { 
      	$this->message('other_notinstallapi');
    } 
	 	$dopaydata = array('type'=>'acount','upid'=>$this->member['uid'],'cost'=>$cost);//支付数据
    include_once($paydir.'/pay.php');  
	}
	
	
   	function exchangcard(){		//充值卡充值
		$this->checkmemberlogin();
		$card = trim(IFilter::act(IReq::get('card')));
		$password = trim(IFilter::act(IReq::get('password')));
		if(empty($card)) $this->message('card_emptycard');
		if(empty($password)) $this->message('card_emptycardpwd');
		$checkinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."card where card ='".$card."'  and card_password = '".$password."' and uid =0 and status = 0");
		if(empty($checkinfo)) $this->message('充值卡不存在,请在核对下');
		$arr['uid'] = $this->member['uid'];
		$arr['status'] =  1;
		$arr['username'] = $this->member['username'];
		$this->mysql->update(Mysite::$app->config['tablepre'].'card',$arr,"card ='".$card."'  and card_password = '".$password."' and uid =0 and status = 0");
		//`$key`
		$this->mysql->update(Mysite::$app->config['tablepre'].'member','`cost`=`cost`+'.$checkinfo['cost'],"uid ='".$this->member['uid']."' ");
		$allcost = $this->member['cost']+$checkinfo['cost'];
       $this->memberCls->addlog($this->member['uid'],2,1,$checkinfo['cost'],'充值卡充值','使用充值卡'.$checkinfo['card'].'充值'.$checkinfo['cost'].'元',$allcost);
	   
	   
	   $this->memberCls->addmemcostlog($this->mid,$this->member['uid'],$this->member['username'],$this->member['cost'],1,$checkinfo['cost'],$allcost,$this->member['username']."使用充值卡充值",$this->member['uid'],$this->member['username']);
 	   
 		$this->success('success');
	}
    function memcard(){
        $this->checkwxweb();
        $this->checkwxuser();
	 	$link = IUrl::creatUrlEx($this->mid,'wxsite/shoplist'); 
	  if($this->member['uid'] == 0)  $this->message('',$link); 
	  $tarelist = $this->mysql->getarr("select *  from ".Mysite::$app->config['tablepre']."address where userid='".$this->member['uid']."'   order by id asc limit 0,50");
	  $arelist = array();
	  $areaid=array();
	  foreach($tarelist as $key=>$value){
	     $areaid[] = $value['areaid1'];
	     $areaid[] = $value['areaid3'];
	     $areaid[] = $value['areaid2'];  
	  }
	  if(count($areaid) > 0){ 
	     $areaarr = $this->mysql->getarr("select id,name from ".Mysite::$app->config['tablepre']."area  where id in(".join(',',$areaid).")  order by id asc limit 0,1000"); 
	     foreach($areaarr as $key=>$value){
	  	    $arelist[$value['id']] = $value['name'];
	     } 
	  }
	  $data['arealist'] = $tarelist;
	  $data['areaarr'] = $arelist;
	  
	  /* 8.3新增 */
	    
	  $rechargelist = $this->mysql->getarr("select *  from ".Mysite::$app->config['tablepre']."rechargecost where cost > 0 order by orderid asc limit 0,10000");
	  $data['rechargelist'] = array();
	  foreach($rechargelist as $key=>$value ){
		  $totalsendcost = '';
		  if( $value['is_sendcost'] == 1 ){
			  $totalsendcost = $totalsendcost+$value['sendcost'];
		  }
		  if( $value['is_sendjuan'] == 1 ){
			  $totalsendcost = $totalsendcost+$value['sendjuancost'];
		  }
		  $value['totalsendcost'] = $totalsendcost;
		  $data['rechargelist'][] = $value;
	  }
	  
	  
	  
	  Mysite::$app->setdata($data); 
   }
   function address(){
       $this->checkwxweb();
       $this->checkwxuser();
   	$link = IUrl::creatUrlEx($this->mid,'wxsite/shoplist'); 
	  if($this->member['uid'] == 0)  $this->message('',$link); 
	  $tarelist = $this->mysql->getarr("select *  from ".Mysite::$app->config['tablepre']."address where userid='".$this->member['uid']."'   order by id asc limit 0,50");
	  $arelist = array();
	  $areaid=array();
	  foreach($tarelist as $key=>$value){
	     $areaid[] = $value['areaid1'];
	     $areaid[] = $value['areaid3'];
	     $areaid[] = $value['areaid2'];  
	  }
	  if(count($areaid) > 0){ 
	     $areaarr = $this->mysql->getarr("select id,name from ".Mysite::$app->config['tablepre']."area  where id in(".join(',',$areaid).")  order by id asc limit 0,1000"); 
	     foreach($areaarr as $key=>$value){
	  	    $arelist[$value['id']] = $value['name'];
	     } 
	  }
	  $data['arealist'] = $tarelist;
	  $data['areaarr'] = $arelist;
	  Mysite::$app->setdata($data); 
   }
   function editaddress(){
		$addressid = intval(IReq::get('id'));
		$data['addressid'] = $addressid;
    	$link = IUrl::creatUrlEx($this->mid,'wxsite/index'); 
    	$data['backtype'] = IFilter::act(IReq::get('backtype')); 
    	$data['shopid'] = IFilter::act(IReq::get('shopid'));     
 	   $data['addressid'] = IFilter::act(IReq::get('id'));
		  $data['lat'] = ICookie::get('lat');
		  $data['lng'] = ICookie::get('lng');
		#  print_r($data);
		   Mysite::$app->setdata($data); 
	   if($this->member['uid'] == 0)  $this->message('',$link); 
   }
   function bkaddress(){ 
   	 $link = IUrl::creatUrlEx($this->mid,'wxsite/index'); 
	    if($this->member['uid'] == 0)  $this->message('',$link); 
	    $data['shopid'] = IFilter::act(IReq::get('shopid'));   
	    $data['backtype'] =  IFilter::act(IReq::get('backtype'));   
	    $tarelist = $this->mysql->getarr("select *  from ".Mysite::$app->config['tablepre']."address where userid='".$this->member['uid']."'   order by id asc limit 0,50");
	    $arelist = array(); 
	    $data['arealist'] = $tarelist; 
	    Mysite::$app->setdata($data);  
   }
   	//ajax获取地址by yanyh
   	function myajaxadlist(){   
		if( $this->checkbackinfo() ){
			if($this->member['uid'] == 0)  $this->message('请先登录！'); 
	    }
		$data['shopid']   = IFilter::act(IReq::get('shopid'));   
	    $data['backtype'] =  IFilter::act(IReq::get('backtype'));   
 
		if( empty($this->member['uid']) || $this->member['uid'] ==  0){	 
			$tarelist 				= array();
			$cdata['id'] 			= 0;
			$cdata['default'] 		= 1;
			$cdata['contactname'] 	= ICookie::get('wxguest_username');
			$cdata['phone'] 		= ICookie::get('wxguest_phone');
			$cdata['address']  		= ICookie::get('wxguest_address');
			if(empty($cdata['contactname'])){
				$tarelist = array();
			}else{
				$tarelist[] = $cdata;
			}
		}else{
	    	$tarelist = $this->mysql->getarr("select *  from ".Mysite::$app->config['tablepre']."address where userid='".$this->member['uid']."'    order by id asc limit 0,50");  
	    	//判断配送距离by yanyh
		 	$shopid   = $data['shopid'];
		 	$shopinfo = $this->mysql->select_one("select lat,lng from ".Mysite::$app->config['tablepre']."shop where id = ".$shopid."");
		 	$miset    = $this->mysql->select_one("select pradius from ".Mysite::$app->config['tablepre']."shopfast where shopid=".$shopid."");
		 	$mi_limit = $miset['pradius'];
 
			foreach ($tarelist as $key => $value) {

  				if(empty($value['lng']) && empty($value['lat'])){  
  					$address  = $value['address'];
					$url      ='http://api.map.baidu.com/geocoder/v2/?address='.$address.'&output=json&ak=FbzOyQ4YujPrZsxiQKoB07aB';  
		            if($result = file_get_contents($url))  
		            {  
		                $res= explode(',"lat":', substr($result, 40,36));  
		            }

		            $mi = $this->GetDistance($res[1],$res[0], $shopinfo['lat'],$shopinfo['lng'], 1); 
					$mi = round($mi/1000,2);

					if($mi > $mi_limit){
						$tarelist[$key]['is_tolong'] = 1;
					}else{
						$tarelist[$key]['is_tolong'] = 0;
					}

  				}else{ 
  					$mi = $this->GetDistance($value['lat'],$value['lng'], $shopinfo['lat'],$shopinfo['lng'], 1); 
					$mi = round($mi/1000,2);
		
					if($mi > $mi_limit){
						$tarelist[$key]['is_tolong'] = 1;
					}else{
						$tarelist[$key]['is_tolong'] = 0;
					}

  				}

	          
			}


		} 	  

	  	$this->success($tarelist);
   	}
   function savemyaddress(){
			//	if( $this->checkbackinfo() ){
			if($this->member['uid'] == 0) {
				 $username = IFilter::act(IReq::get('contactname'));
				 $phone = IFilter::act(IReq::get('phone'));
				 $address =  IFilter::act(IReq::get('add_new'));
				 
				ICookie::set('wxguest_username',$username,86400);
				ICookie::set('wxguest_phone',$phone,86400);
				ICookie::set('wxguest_address',$address,86400);
				#	$this->message(ICookie::get('wxguest_username'));
				  $this->success('success');
			}
	//	 }
       	 $addressid = intval(IReq::get('addressid'));
		 if(empty($addressid))
		 {
		 	 $checknum = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."address where userid='".$this->member['uid']."' ");
		 if(Mysite::$app->config['addresslimit'] < $checknum)$this->message('member_addresslimit');
       
	     $arr['userid'] = $this->member['uid'];
	     $arr['username'] = $this->member['username'];

	     $arr['address'] =  IFilter::act(IReq::get('add_new'));
	     $arr['phone'] = IFilter::act(IReq::get('phone'));
	     $arr['otherphone'] = '';
	     $arr['contactname'] = IFilter::act(IReq::get('contactname'));
	     $arr['sex'] =  IFilter::act(IReq::get('sex'));
	     $arr['default'] = 1;
	     if(!(IValidate::len(IFilter::act(IReq::get('add_new')),3,50)))$this->message('member_addresslength');
	     if(!(IValidate::phone($arr['phone'])))$this->message('errphone');
	     if(!empty($arr['otherphone'])&&!(IValidate::phone($arr['otherphone'])))$this->message('errphone');
	     if(!(IValidate::len($arr['contactname'],2,6)))$this->message('contactlength');
		  $this->mysql->update(Mysite::$app->config['tablepre'].'address',array('default'=>0),'userid = '.$this->member['uid'].' ');
	     $this->mysql->insert(Mysite::$app->config['tablepre'].'address',$arr);
	     $this->success('success');
		 }else{
	     $arr['address'] =  IFilter::act(IReq::get('add_new'));
	     $arr['phone'] = IFilter::act(IReq::get('phone'));
	     $arr['otherphone'] = '';
	     $arr['contactname'] = IFilter::act(IReq::get('contactname'));
	     $arr['sex'] =  IFilter::act(IReq::get('sex'));
		  $arr['default'] = 1;
	      if(!(IValidate::len(IFilter::act(IReq::get('add_new')),3,50)))$this->message('member_addresslength');
	     if(!(IValidate::phone($arr['phone'])))$this->message('errphone');
	     if(!empty($arr['otherphone'])&&!(IValidate::phone($arr['otherphone'])))$this->message('errphone');
	     if(!(IValidate::len($arr['contactname'],2,6)))$this->message('contactlength');
		    $this->mysql->update(Mysite::$app->config['tablepre'].'address',array('default'=>0),'userid = '.$this->member['uid'].' ');
	     $this->mysql->update(Mysite::$app->config['tablepre'].'address',$arr,'userid = '.$this->member['uid'].' and id='.$addressid.'');
	     $this->success('success');
		 }
		$this->success('success');
   }
   function setmydefadid(){
	     if( $this->checkbackinfo() ){
			if($this->member['uid'] == 0)  $this->message('未登陆获取地区信息失败'); 
		 }
       	 $addressid = intval(IReq::get('addressid'));
		 if(empty($addressid)) $this->message('默认值错误');
		 $checkdata =   $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."address where id='".$addressid."' and userid = '".$this->member['uid']."' ");  
		 if(empty($checkdata)) $this->message('该地址不属于你该账号');
		    $this->mysql->update(Mysite::$app->config['tablepre'].'address',array('default'=>0),'userid = '.$this->member['uid'].' ');
	     $this->mysql->update(Mysite::$app->config['tablepre'].'address',array('default'=>1),'userid = '.$this->member['uid'].' and id='.$addressid.'');
		 $this->success('success');
		 
   }
   function order(){
	   $this->checkwxweb();
   		$link = IUrl::creatUrlEx($this->mid, 'wxsite/index'); 
	   if($this->member['uid'] == 0)  $this->message('',$link); 
	
		
   }
   function userorder(){
			$link = IUrl::creatUrlEx($this->mid,'wxsite/index'); 
	 if($this->member['uid'] == 0)  $this->message('',$link); 
	  $pageinfo = new page();
	  $pageinfo->setpage(intval(IReq::get('page')),5);  
	  // 
		$datalist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."order where buyeruid='".$this->member['uid']."' and shoptype != 100  order by id desc limit ".$pageinfo->startnum().", ".$pageinfo->getsize()."");   
	  $temparray = array('0'=>'外卖','1'=>'超市','2'=>'其他','100'=>'跑腿订单');
	  $backdata = array();
	  foreach($datalist as $key=>$value){  
				$listdet = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."orderdet where order_id='".$value['id']."'");  
				$value['det'] = '';
				foreach($listdet as $k=>$v){
					$value['det'] .= $v['goodsname'].',';
				}
				if($value['is_goshop'] == 1){
					$value['ordertitle'] = "堂食";
				}
				else 
				{
					$value['ordertitle'] = "外卖";
				}	
				$shopinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where id='".$value['shopid']."'");  
				$value['shoplogo'] = $shopinfo['shoplogo'];
			//	$value['shoptype'] = $temparray[$value['shoptype']];
					
		$orderwuliustatus = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."orderstatus where   orderid = ".$value['id']." order by id desc limit 0,1 ");
		$value['orderwuliustatus'] = $orderwuliustatus['statustitle'];
				$value['addtime'] = date('Y-m-d H:i',$value['addtime']);
				$backdata[] =$value;
		}
		$data['orderlist'] = $backdata;
		Mysite::$app->setdata($data);
	/* 	
	$datas = json_encode($backdata);
	  echo 'showmoreorder('.$datas.')';
      exit; 
	    $this->success($data);
		
	 print_r($backdata);
		exit; 
		$this->success($backdata); */
	}
	function ordershow(){
		$link = IUrl::creatUrlEx($this->mid,'wxsite/index'); 
		if($this->member['uid'] == 0)  $this->message('未登陆',$link); 
		$orderid = intval(IReq::get('orderid')); 
		
		
		
		$wxclass = new wx_s();
		$signPackage = $wxclass->getSignPackage($this->mid);
 		$data['signPackage'] = $signPackage;
		
		$shareinfo = $this->mysql->select_one("select title,img,`describe`  from ".Mysite::$app->config['tablepre']."juanshowinfo where type=2 order by orderid asc  ");
		if( empty($shareinfo) ){
			$shareinfo['title'] = Mysite::$app->config['sitename'];
			$shareinfo['img'] = Mysite::$app->config['sitelogo'];
			$shareinfo['describe'] = Mysite::$app->config['sitename'];
		}
		$data['shareinfo'] = $shareinfo;
		
		$where = "  where type=2 and addtime < ".time()." and endtime > ".time()." and is_open = 1 and juannum > 0 ";
 		$checkinfosendjuan = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."juanrule ".$where." order by orderid asc ");
		$data['checkinfosendjuan'] = $checkinfosendjuan;
		
		$orderwuliustatus = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."orderstatus where   orderid = ".$orderid." order by addtime asc limit 0,10 ");
		foreach ($orderwuliustatus as $k=>$v){
			$orderwuliustatus[$k]['addtime'] = date('Y-m-d H:i:s',$v['addtime']);
		}
		$data['orderwuliustatus'] = $orderwuliustatus;
	 
		if(!empty($orderid)){
	  	 
	     	$order = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where buyeruid='".$this->member['uid']."' and id = ".$orderid."");   	     	
	     	if($order['paytype'] == 1 && $order['paystatus'] == 0 && $order['status'] < 3){
	     		$this->syncOrderPayStatus($order);
	     		$order = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where buyeruid='".$this->member['uid']."' and id = ".$orderid."");
	     	}
	     	if(empty($order)){
	     		$data['order'] = '';
	     		Mysite::$app->setdata($data);
	     	}else{
                $scoretocost =Mysite::$app->config['scoretocost'];
                $order['scoredown'] =  $order['scoredown']/$scoretocost;//抵扣积分
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
	     	     $paytypelist = array(0=>'货到支付',1=>'在线支付');  
	     	      
	     	     $paytypearr = $paytypelist; 
	     	      $order['is_acceptorder'] = $order['is_acceptorder'];
	     	      $order['surestatus'] = $order['status'];
	     	      $order['basetype'] = $order['paytype'];
	     	      $order['basepaystatus'] =$order['paystatus'];
	     	   #  $order['status'] = $buyerstatus[$order['status']];
	     	     $order['paytype'] =  $order['paytype'];
	     	  #   $order['paystatus'] = $order['paystatus']==1?'已支付':'未支付';
	     	     $order['paystatus'] = $order['paystatus'] ;
	     	     $order['addtime'] = date('Y-m-d H:i:s',$order['addtime']);
	     	     $order['posttime'] = date('Y-m-d H:i:s',$order['posttime']);
	     	  
	     	   $data['mid'] = $this->mid;  
	     	   $data['order'] = $order;
	           $data['orderdet'] = $orderdet;
 
	           $data['table'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."table where id = '".$order['tableid']."'" );
	        
	           Mysite::$app->setdata($data);
	           
	       }
	  }else{
	  	$data['order'] = '';
	  	Mysite::$app->setdata($data);
	  }

	  
	  Mysite::$app->setAction('ordershow');
	}
	/*评价订单*/
	function commentorder(){
	  	$link = IUrl::creatUrlEx($this->mid,'wxsite/index'); 
	    if($this->member['uid'] == 0)  $this->message('未登陆',$link); 
	    $link = IUrl::creatUrlEx($this->mid,'wxsite/order'); 
	    $orderid = intval(IReq::get('orderid'));  
	    if(!empty($orderid)){
	  	 
	     	 $order = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where buyeruid='".$this->member['uid']."' and id = ".$orderid."");   
	     
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
	//积分操作
	function gift(){
        $this->checkwxweb();
        $this->checkwxuser();
		if($this->member['uid'] == 0)  $this->message('未登陆',$link); 
	  	$link = IUrl::creatUrlEx($this->mid,'wxsite/index'); 
		$giftlog = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."memberlog where type = 1 and userid = '".$this->member['uid']."' order by addtime desc ");  //积分增减记录
	    $data['giftlog'] = $giftlog;
		Mysite::$app->setdata($data);
	}
	//积分记录
	function giftlog(){
		$data['logstat'] = array('0'=>'待处理','1'=>'已处理，配送中','2'=>'已发货','3'=>'兑换成功','4'=>'已取消兑换'); 
		 Mysite::$app->setdata($data);
	}
	function dhgift(){
		$giftid = intval(IReq::get('giftid'));
		$giftinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."gift where id = ".$giftid."    ");   
		$data['giftinfo']  = $giftinfo;
		Mysite::$app->setdata($data);
	}
	//兑换产品列表
	function giftlist(){
        $this->checkwxweb();
        $this->checkwxuser();
	}
	function juan(){
        $this->checkwxweb();
        $this->checkwxuser();
		$wjuan = array('shuliang'=>0,'list'=>array());
		$ujuan = array('shuliang'=>0,'list'=>array());
		$ojuan = array('shuliang'=>0,'list'=>array());
		$nowtime = time();
		$data['nowtime']  = $nowtime;
		$wjuan['shuliang'] =  $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."juan    where uid = ".$this->member['uid']." and endtime > ".$nowtime." and status = 1 ");
		$wjuan['list'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."juan where uid = ".$this->member['uid']." and endtime > ".$nowtime." and status = 1 ");   
		$ujuan['shuliang'] =  $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."juan    where uid = ".$this->member['uid']."  and status = 2 ");
		$ujuan['list'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."juan where uid = ".$this->member['uid']."   and status = 2 ");     
		
		$ojuan['shuliang'] =  $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."juan    where uid = ".$this->member['uid']." and endtime < ".$nowtime." and (status = 1 or status =3)");
		$ojuan['list'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."juan where uid = ".$this->member['uid']." and endtime < ".$nowtime." and (status = 1 or status =3)  ");  
		
		
		$pcjuan = array('shuliang'=>0,'list'=>array());
		$pcjuan['list'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."juan where status = 1 and  uid = ".$this->member['uid']." ");   
		$pcjuan['shuliang'] =  $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."juan   where status = 1 and   uid = ".$this->member['uid']."  ");
		
		$wxujuan = array('shuliang'=>0,'list'=>array());
		$wxujuan['shuliang'] =  $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."wxuserjuan    where uid = ".$this->member['uid']." and endtime > ".$nowtime." and lqstatus = 1 ");
		$wxujuan['list'] = $this->mysql->getarr("select a.*,b.cartdesrc from ".Mysite::$app->config['tablepre']."wxuserjuan as a left join ".Mysite::$app->config['tablepre']."wxjuan as b on a.juanid = b.id where a.uid = ".$this->member['uid']." and a.endtime > ".$nowtime." and a.lqstatus = 1 ");  
		$data['wxujuan'] = $wxujuan;
		$data['pcjuan'] = $pcjuan;
		
	#	print_r($data['wxujuan']);
		
		
		$data['wjuan'] = $wjuan;
		$data['ujuan'] = $ujuan;
		$data['ojuan'] = $ojuan; 
		Mysite::$app->setdata($data);
	}
  	function cart(){  	   
		$shopid 		= intval(IReq::get('shopid'));  
		$membercardcode = IReq::get('membercardcode');
		$couponcode 	= IReq::get('couponcode');
		
		$shopshowtype 		= ICookie::get('shopshowtype');
		$backdata 			= array();
		$backdata['title'] 	= '';
		$smardb  = new newsmcart();
		$carinfo = array();
		if($smardb->setdb($this->mysql)->SetShopId($this->mid,$shopid)->OneShop()){
		    $carinfo = $smardb->getdata(); 			 
		    $backdata['list'] 		= $carinfo['goodslist'];
    	    $backdata['sumcount'] 	= $carinfo['count'];
    	    $backdata['sum'] 		= $carinfo['sum'];

    	    //此处判断菜品是否可以用会员卡优惠卷
    	    $backdata['noyouhuicost'] = 0;
			foreach ($carinfo['goodslist'] as $key => $value) {
				if($value['is_membercard'] != 1){//不能优惠的价格
					$backdata['noyouhuicost'] += $value['cost']*$value['count'];
				} 
			}

	      	if($carinfo['shopinfo']['shoptype'] ==1){
	          	    $shopcheckinfo =   $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket as a left join ".	Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id where a.shopid = '".$shopid."'    ");  
          	}else{
          	    $shopcheckinfo =   $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id where a.shopid = '".$shopid."'    ");  
          	}
		   
		    if($shopshowtype == 'dingtai'){
				$backdata['bagcost'] = 0;
				$backdata['title'] = '堂食';
			}else{
				$backdata['bagcost'] =$carinfo['bagcost'];
			}
    	     
			   
			   
			$cxclass = new sellrule();  
	//			   $cxclass->setdata($shopid,$carinfo['sum'],$carinfo['shopinfo']['shoptype']);
	        if( !strpos($_SERVER["HTTP_USER_AGENT"],'MicroMessenger')    ){    //判断是微信浏览器不
	            $platform=3;//触屏
	        }else{
	            $platform=2;//微信
	        }
	        $paytype = intval(IReq::get('paytype'));
	        $cxclass->setdata($shopid,$carinfo['sum'],$carinfo['shopinfo']['shoptype'],$this->member['uid'],$platform,$paytype);
			$cxinfo = $cxclass->getdata(); 
		//	$carinfo['cx'] = $cxinfo;  
			$backdata['surecost'] = $cxinfo['surecost'];
    	    $backdata['downcost'] = $cxinfo['downcost'];
    	    $backdata['gzdata']   = isset($cxinfo['gzdata'])?$cxinfo['gzdata']:array(); 
			
			$areaid = ICookie::get('myaddress');
    	      
            $tempinfo =   $this->pscost($shopcheckinfo,$backdata['sumcount'],$areaid); 
            $backdata['pstype'] = $tempinfo['pstype'];
			$areaid = ICookie::get('myaddress');
		  	if($shopshowtype == 'dingtai'){
			 	$backdata['pscost'] = 0;
			 	$backdata['title'] = '堂食';
		  	}else{
			 	$backdata['pscost'] = $cxinfo['nops'] == true?0:$tempinfo['pscost'];
		  	}
			$backdata['canps'] = $tempinfo['canps']; 
			
			#	print_r($backdata);
			$card_openid='';
			if (isset($this->member['card_openid'])) {
				$card_openid = $this->member['card_openid'];
			}
			$backdata['surecost'] = $backdata['surecost'] - $backdata['noyouhuicost'];//减去不能优惠的价钱
			$cardDownCost = $this->getCardDownAmount($card_openid, $backdata['surecost'], $membercardcode, $couponcode);
			$backdata['carddowncost'] = $cardDownCost;
			$backdata['surecost']= round($backdata['surecost'] - $cardDownCost,2);
			$this->success($backdata);
		}else{
			$this->message(array());
		} 
	
		  
	}


	/* 发布跑腿 start   */
	function fabupaotui(){
		$this->checkwxuser();
		if($this->member['uid'] == 0)  $this->message('未登陆');
		
		$ptinfoset = $this->mysql->select_one(" select * from ".Mysite::$app->config['tablepre']."paotuiset  ");
	 
 		$demandcontent = trim(IFilter::act(IReq::get('demandcontent')));  // 需求内容
		 // 取货地址： 地址 补充地址  lng lat 
		$getaddress = trim(IReq::get('getaddress')); 
		$getdetaddress = trim(IReq::get('getdetaddress'));
		$getlng = trim(IReq::get('getlng'));
		$getlat = trim(IReq::get('getlat'));
		 // 收货地址： 地址 补充地址  lng lat 
		$shouaddress = trim(IReq::get('shouaddress'));
		$shouetaddress = trim(IReq::get('shouetaddress'));
		$shoulng = trim(IReq::get('shoulng'));
		$shoulat = trim(IReq::get('shoulat'));
		
		$getphone = trim(IReq::get('getphone'));  // 取货电话
		$shouphone = trim(IReq::get('shouphone'));  // 收货电话
 		$minit = trim(IReq::get('minit'));			// 收/取 货时间
		$ptkg = trim(IReq::get('ptkg'));	// 货 公斤 数
		$ptkm = trim(IReq::get('ptkm'));	//  收取货 地址 两地 距离 km
 		$allkgcost = trim(IReq::get('allkgcost'));		// 重量价格
 		$allkmcost = trim(IReq::get('allkmcost'));		// 距离价格
 		$farecost = trim(IReq::get('farecost'));		// 加价（小费）
 		$allcost = trim(IReq::get('allcost'));		// 总价格
		$pttype = trim(IReq::get('pttype'));		// 	1为帮我送 2为帮我买
		$paytype = trim(IReq::get('paytype'));		//  支付方式，默认为在线支付
	 
		if( empty($demandcontent) )  $this->message("请简要填写需求内容");
		if( empty($getaddress) )  $this->message("获取取货地址失败,请重新获取");
 		if( empty($getlng) )  $this->message("获取取货地址失败,请重新获取");
		if( empty($getlat) )  $this->message("获取取货地址失败,请重新获取");
		
		if( empty($shouaddress) )  $this->message("获取收货地址失败,请重新获取");
		if( empty($shoulng) )  $this->message("获取收货地址失败,请重新获取");
		if( empty($shoulat) )  $this->message("获取收货地址失败,请重新获取");
		
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
		   $info['addpscost'] =  $tempdata['cost'];
	   
		}
	   
 		$data['pttype'] = $pttype;  // 1为帮我送  2为帮我买
		
		$data['content'] = $demandcontent;
		$data['shopaddress']  = $getaddress;   
		$data['buyeraddress']  = $shouaddress;  
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
		 $data['ptkm'] = $ptkm;
		 $data['allkgcost'] = $allkgcost;
		 $data['allkmcost'] = $allkmcost;
		 $data['farecost'] = $farecost;
		 $data['allcost'] = $allcost;
		  $data['dno'] = time().rand(1000,9999);
		  $data['pstype']  = 1;
		  $data['buyeruid']  = $this->member['uid'];
		$data['buyername'] = $this->member['username'];
	
		
		/* 计算两点之间的距离  并且 判断是否与前台的  千米距离金额是否一致 */
		$juli = $this->GetDistance($getlat,$getlng, $shoulat,$shoulng, 1,1); 
		$tempmi = $juli;
	#	$juli = $juli > 1000? round($juli/1000,1).'km':$juli.'m';		
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
		/* print_r($ptkm);
		echo "<br />";
		print_r($juli); */
		if($juli != $ptkm )$this->message("获取距离错误");
		if($tmpallkmcost != $allkmcost )$this->message("获取距离总金额错误");
		
			
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
		 
 		if($tmpallkgcost != $allkgcost )$this->message("获取重量总金额错误");
		
		
		

			$panduan = Mysite::$app->config['man_ispass'];
		$data['status'] = 0;
		if($panduan != 1 && $data['paytype'] == 0){
			$data['status'] = 1;
		} 
			
			
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
	   
		   
		  if($panduan != 1)
				{ 
				
					  $orderclass = new orderclass();
				
				$orderclass->sendmess($orderid);
				
			  }
		  $this->success($orderid);
		
		
	}
	 function paotui(){
		 $this->checkwxweb();
   		 $link =IUrl::creatUrlEx($this->mid,'wxsite/index'); 
		
	    if($this->member['uid'] == 0)  $this->message('',$link); 
   }
	 function paotuiorder(){
 
		$link = IUrl::creatUrlEx($this->mid,'wxsite/index'); 
	 if($this->member['uid'] == 0)  $this->message('',$link); 
	  $pageinfo = new page();
	  $pageinfo->setpage(intval(IReq::get('page')),20);  
	  // 
		$datalist = $this->mysql->getarr("select id,shopname,pttype,is_goshop,allcost,content,addtime,status,is_ping,shoptype,is_reback from ".Mysite::$app->config['tablepre']."order where buyeruid='".$this->member['uid']."' and shoptype=100 order by id desc limit ".$pageinfo->startnum().", ".$pageinfo->getsize()."");
	  $temparray = array('0'=>'外卖','1'=>'超市','2'=>'其他');
	  $backdata = array();
	  foreach($datalist as $key=>$value){  
				$listdet = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."orderdet where order_id='".$value['id']."'");  
				$value['det'] = '';
				foreach($listdet as $k=>$v){
					$value['det'] .= $v['goodsname'].',';
				}
                if($value['pttype'] == 1){
                    $value['pttype']="帮我送";
                }elseif($value['pttype'] == 2){
                    $value['pttype']="帮我买";
                }
				$value['shoptype'] = $temparray[$value['shoptype']];
//				$value['addtime'] = date('Y-m-d H:i',$value['addtime']);
          $value['addtime'] = date('Y-m-d',$value['addtime']);
				$backdata[] =$value;
		}
		$this->success($backdata);
	}


    function paotuidetail(){//跑腿详情

        $link = IUrl::creatUrlEx($this->mid,'wxsite/index');
        if($this->member['uid'] == 0)  $this->message('',$link);
        $orderid=intval(IReq::get('orderid'));
        $pageinfo = new page();
        $pageinfo->setpage(intval(IReq::get('page')),20);
        $datalist = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where id=".$orderid." order by id desc");
        $temparray = array('0'=>'外卖','1'=>'超市','2'=>'其他');
            $listdet = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."orderdet where order_id='".$datalist['id']."'");
            $datalist['det'] = '';
                $datalist['det'] .= $listdet['goodsname'].',';
            $datalist['shoptype'] = $temparray[$datalist['shoptype']];
            $datalist['addtime'] = date('Y-m-d H:i',$datalist['addtime']);
            $data['detail'] =$datalist;
        Mysite::$app->setdata($data);
    }
	function mypaotui(){
   		$link = IUrl::creatUrlEx($this->mid,'wxsite/index'); 
	    if($this->member['uid'] == 0)  $this->message('',$link); 
   }
	
    /* 发布跑腿 end   */
	
	
	

	function makeorder(){  
   		$this->checkwxuser();
		if( $this->checkbackinfo() ){
			if($this->member['uid'] == 0||empty($this->mid))  $this->message('未登陆'); 
	    } 
	    $paymethod 		= IReq::get('paymethod');
	    $membercardcode = IReq::get('membercardcode');
	    $membercardinfo = IReq::get('membercardinfo');
	    $couponcode     = IReq::get('couponcode');
	    $couponinfo     = IReq::get('couponinfo');
	    $membercardcode = $membercardcode ? $membercardcode :'';
	    $couponcode		= $couponcode ? $couponcode :'';
	    if (!empty($paymethod) && $paymethod = "membercard" && empty($membercardcode) ) {
	    	$this->message('请选择会员卡');
	    }
	       
	    $shopshowtype = ICookie::get('shopshowtype');
		
		if(empty($this->member['uid']) || $this->member['uid'] ==  0){	 
			$addressinfo  			= null;
			$cdata['id'] 			= 0;
			$cdata['default'] 		= 1;
			$cdata['contactname'] 	= ICookie::get('wxguest_username');
			$cdata['phone'] 		= ICookie::get('wxguest_phone');
			$cdata['address']  		= ICookie::get('wxguest_address');
			if(empty($cdata['contactname'])){
				
			}else{
				$addressinfo = $cdata;
			}
		}else{
		  	$addressinfo =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."address  where userid = ".$this->member['uid']." and `default`=1   "); 
   		} 
		 
		$info['mid'] = $this->mid;
		 //如果订单为订台(堂食)，则下单时不用设置地址，
		if($shopshowtype !='dingtai'){ //外卖
		 	if(empty($addressinfo)) $this->message('未设置默认地址');
		 	$info['is_goshop']=0;

		 	//判断配送距离by yanyh
		 	$shopid   = intval(IReq::get('shopid'));
		 	$shopinfo = $this->mysql->select_one("select lat,lng from ".Mysite::$app->config['tablepre']."shop where id = ".$shopid."");
		 	$miset    = $this->mysql->select_one("select pradius from ".Mysite::$app->config['tablepre']."shopfast where shopid=".$shopid."");
		 	$mi_limit = $miset['pradius'];
 
		 	if(empty($addressinfo['lng']) && empty($addressinfo['lat'])){
  				$address  = $addressinfo['address']; 
				$url      ='http://api.map.baidu.com/geocoder/v2/?address='.$address.'&output=json&ak=FbzOyQ4YujPrZsxiQKoB07aB';  
	            if($result = file_get_contents($url))  
	            {  
	               $res= explode(',"lat":', substr($result, 40,36));  
	            }
	           	$mi = $this->GetDistance($res[1],$res[0], $shopinfo['lat'],$shopinfo['lng'], 1); 
				$mi = round($mi/1000,2);
				
				if($mi > $mi_limit){
				 	$this->message('距离太远可能无法配送！');
				}
  			}else{
  				$mi = $this->GetDistance($addressinfo['lat'],$addressinfo['lng'], $shopinfo['lat'],$shopinfo['lng'], 1); 
				$mi = round($mi/1000,2);
				if($mi > $mi_limit){
				 	$this->message('距离太远可能无法配送！');
				}
  			}
    
	 	}else{//堂食
		 	$addressinfo['address'] = "堂食";
		 	$info['is_goshop']		= 1;
		 	$info['tableid']		= ICookie::get('tableid');
		 	if( $info['tableid'] !=''){
		 		$searchdata = array();
		 		$searchdata['id'] = $info['tableid'];
		 		$url = Mysite::$app->config['banklay_url'].'/getShopTableInfo/table/eat_table';
		 		$tableinfo = https_request($url,$searchdata);
		 		// if($tableinfo){
		 		// 	$tableinfo = json_decode($tableinfo,true);
		 		// 	$addressinfo['address'] ="堂食(".$tableinfo['name'].")";
		 		// }
		 	}
		}

		$info['username'] 	= $addressinfo['contactname']; 
		$info['mobile'] 	= $addressinfo['phone'];
		$info['addressdet'] = $addressinfo['address'];
	
		$info['shopid'] 	= intval(IReq::get('shopid'));//店铺ID
		$info['remark'] 	= IFilter::act(IReq::get('remark'));//备注
		$info['paytype'] 	= IFilter::act(IReq::get('paytype'));//支付方式
		$info['dikou'] 		= intval(IReq::get('dikou'));//抵扣金额
	 	
	//	$info['senddate'] = date('Y-m-d',time());// IFilter::act(IReq::get('senddate'));
		$info['minit'] 		= IFilter::act(IReq::get('minit')); 
		$info['juanid']  	= intval(IReq::get('juanid'));//优惠劵ID
		if($this->checkbackinfo()){
			$info['ordertype'] = 3;//订单类型 
		}else{
		 	$info['ordertype'] = 5;
		}
		$peopleNum 			  = IFilter::act(IReq::get('peopleNum'));  
		$info['othercontent'] ='';//empty($peopleNum)?'':serialize(array('人数'=>$peopleNum)); 
		 
		if(empty($info['shopid'])) $this->message('店铺ID错误');
		
		
		$smardb  = new newsmcart();
		$carinfo = array();
		if($smardb->setdb($this->mysql)->SetShopId($this->mid,$info['shopid'])->OneShop()){
		    $carinfo = $smardb->getdata(); 

		    //此处判断菜品是否可以用会员卡优惠卷
    	    $backdata['noyouhuicost'] = 0;
			foreach ($carinfo['goodslist'] as $key => $value) {
				if($value['is_membercard'] != 1){//不能优惠的价格
					$backdata['noyouhuicost'] += $value['cost']*$value['count'];
				} 
			}
		}else{ 
		    $this->message($smardb->getError());
		} 
		
		

		if(count($carinfo['goodslist'])==0) $this->message('对应店铺购物车商品为空');
		if($carinfo['shopinfo']['shoptype'] == 1){
			$shopinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id where a.shopid = '".$info['shopid']."'    ");   
		}else{
	        $shopinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id where a.shopid = '".$info['shopid']."'    ");   
	    }
	  
		if(empty($shopinfo))   		$this->message('店铺获取失败');
		$areaid  = ICookie::get('myaddress');
		$checkps =  $this->pscost($shopinfo,$carinfo['count'],$areaid); 
		//if($checkps['canps'] != 1) 	$this->message('该店铺不在配送范围内');  
		$info['cattype'] = 0;
		if($shopshowtype != 'dingtai'){
		  	if(empty($info['username'])) 		  		$this->message('联系人不能为空');
		  	if(!IValidate::suremobi($info['mobile']))   $this->message('请输入正确的手机号');
		  	if(empty($info['addressdet'])) 				$this->message('详细地址为空');
		}
	  	
	   	$info['userid'] = !isset($this->member['score'])?'0':$this->member['uid'];
	    if(Mysite::$app->config['allowedguestbuy'] != 1){
	     	if($info['userid']==0) $this->message('禁止游客下单');
	   	}
	   	$info['ipaddress'] 	= "";
	   	$ip_l 				= new iplocation(); 
     	$ipaddress			= $ip_l->getaddress($ip_l->getIP());  
     	if(isset($ipaddress["area1"])){
		   	$info['ipaddress']  = $ipaddress['ip'].mb_convert_encoding($ipaddress["area1"],'UTF-8','GB2312');//('GB2312','ansi',);
	   	} 
	  //area1 二级地址名称	area2 三级地址名称	area3
	   
	  	$info['areaids'] = '';
	  
	#  logwrite($info['postdate']);
	    if($shopinfo['is_open'] != 1) 		$this->message('店铺暂停营业'); 
	   	$tempdata = $this->getOpenPosttime($shopinfo['is_orderbefore'],$shopinfo['starttime'],$shopinfo['postdate'],$info['minit'],$shopinfo['befortime']); 
	   	// if($tempdata['is_opentime'] ==  2) 	$this->message('选择的配送时间段，店铺未设置');
	   	// if($tempdata['is_opentime'] == 3) 	$this->message('选择的配送时间段已超时');
	   	$info['sendtime'] 	= $tempdata['is_posttime'];
	   	$info['postdate'] 	= $tempdata['is_postdate'];
	    $info['addpscost'] 	= $tempdata['cost'];
	 
	  	$checksend = Mysite::$app->config['ordercheckphone'];
    	if($checksend == 1){
    	  	if(empty($this->member['uid'])){
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
		foreach($info['goodslist'] as $key=>$value){  
	        if($value['stock'] < $value['count']){
			 	$this->message('商品库存不足');
		 	}
	    }

    	if( !strpos($_SERVER["HTTP_USER_AGENT"],'MicroMessenger')    ){    //判断是微信浏览器不
        	$info['platform'] = 3;//触屏
    	}else{
        	$info['platform'] = 2;//微信
    	}

	    if (isset($this->member['card_openid'])) {
	    	$card_openid 				= $this->member['card_openid'];
	    	$info['wx_membercard_code'] = $membercardcode;
	    	$info['wx_card_code'] 		= $couponcode;
	    	$carinfo['sum'] = $carinfo['sum'] - $backdata['noyouhuicost'];//减去不能优惠的价钱
	    	$cardDownCost = $this->getCardDownAmount($card_openid, $carinfo['sum'], $membercardcode, $couponcode);
	    	$info['wx_card_downcost'] 	= $cardDownCost;
	    }

    
	    if($shopinfo['limitcost'] > $info['allcost']) $this->message('商品总价低于最小起送价'.$shopinfo['limitcost']);   
        
        //判断是否先吃后付订单
        $ptype = IFilter::act(IReq::get('ptype'));        
        if($ptype == 'xchf'){
	   	    $info['is_preeat'] = 1;
	   	}
	    
	    $info['wx_membercard_name'] = $membercardinfo;
	    $info['wx_card_name'] = $couponinfo;

        $orderclass = new orderclass();
	   	$orderclass->makenormal($info);
	   	$orderid = $orderclass->getorder();

	    //堂食阿里推送 
        if($shopshowtype =='dingtai'){
	 		$sns = $this->mysql->getarr("select tqs.sn,t.name from ".Mysite::$app->config['tablepre']."table_qrcode_set  tqs  LEFT JOIN ".Mysite::$app->config['tablepre']."table t on tqs.tableid = t.id  where tableid = '{$info["tableid"]}'");
	 	 
 			if($sns){
 				foreach ($sns as $list){
 						$sn .= $sn? $sn.",".$list['sn'] : $list['sn'];
 						$name = $list['name'];
 				}
 				$title = $name.'堂食下单';
 				$call_type = 1;
 			}

	 		$res = $this->callService($sn,$info['tableid'],$info['shopid'],$title,$call_type,$name,$back='back');
		}
	   	
	   	if($info['userid'] ==  0){ 
	  	   	ICookie::set('orderid',$orderid,86400);
	   	}    

	   	$smardb->DelShop($info['shopid']);
	   	//add by rain will 
	   	$paymethod 				 = 'W';
	   	$paymethod 				 = 'membercard';
	   	$params    				 = array();
	   	$params ['out_trade_no'] = ''.$orderid;
	   	$params['body'] 		 = "外卖下单";
	   	$params ['mid'] 		 = $this->mid;
	   	$params ['order_type'] 	 = 1;
	   	$params ['attach'] =  "1;";
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
 				
				if(!empty($couponcode)) {
					// 核销卡券
					$paramsTmp = array ();
					$paramsTmp ['mid'] = $this->mid;
					$paramsTmp ['couponcode'] = $couponcode;
					if (api_post_cardConsume ( $paramsTmp, $return_code1, $return_msg1, $return_data1 )) {
						if ($return_code1 == 1) {
						}
					}
				}
				if (  $params ['pay_amount'] == 0) {
					//支付金额为0时候处理订单状态
					$payTypeAndName = getPayTypeAndName('O');
					$di= array();
					$di['paystatus']=1;
					$di['status']=1;
					$di['paytype_name']="weixin";
					$di['paytype'] = $payTypeAndName['paytype'];
					$di['paytype_name'] = $payTypeAndName['paytype_name'];
	
					$this->mysql->update(Mysite::$app->config['tablepre'].'order',$di,"id='".$orderid."'");
					$info = file_get_contents(Mysite::$app->config['siteurl']."/index.php/".$this->mid."?ctrl=site&action=postmsgbypay&orderid=".$orderid);
				}
                                
	            //先吃后付到此结束返回
	            if($orderid && $ptype == 'xchf'){
	                    $this->success('xchf');
	            }
				
				$this->success($orderid);  
 				return; 
			}
		}
                
	   	$opMsg=empty($return_msg)?'统一支付下单失败':$return_msg;	   
	   	$this->message($opMsg);
		exit; 
	}

	function memberPay() {
		$this->checkwxuser();
		if( $this->checkbackinfo() ){
			if($this->member['uid'] == 0||empty($this->mid))  $this->message('未登陆');
		}
		$orderid = intval(IReq::get('orderid'));
		if(empty($orderid)) $this->message('参数错误');


		$order = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where buyeruid='".$this->member['uid']."' and id = ".$orderid."");
		if(empty($order)) $this->message('获取订单失败');
		
		
		if (isset($this->member['card_openid'])) {
			$card_openid = $this->member['card_openid'];
		}
		

		$params =array();
		$params ['mid'] = $this->mid;
		$params ['order_id'] = $order['uni_order_id'];
		$params ['membercardcode'] = $order['wx_membercard_code'];
		$params ['openid'] = $card_openid;

		$return_msg = "会员卡支付失败";
		if (api_post_memberCardPay ( $params, $return_code, $return_msg, $return_data )) {
			if ($return_code==1 && $return_data) {
                                //会员卡支付成功后加上发微信通知信息
                                if($order['is_goshop']==0){
                                    $orderclass = new orderclass();		
                                    $orderclass->sendmess($orderid); 
                                }
                                
				$retUrl =  IUrl::creatUrlEx($this->mid,'wxsite/payOver/orderid/'.$orderid);
				$this->success($retUrl);
                
				exit;
			}
		}
		$this->message($return_msg);
		exit;
	}
	private function syncOrderPayStatus($order) {
		if($order['paytype'] == 1 && $order['paystatus'] == 0 && $order['status'] < 3){
			$return_code='';
			$return_msg='';
			$return_data= null;
			$params =array();
			$params ['order_id'] = $order['uni_order_id'];
			if (queryOrder ( $params, $return_code, $return_msg, $return_data )) {
				if ($return_code==1 && $return_data&&$return_data['pay_status'] >0) {
					$transaction_id = $return_data['transaction_id'];
					if (empty($transaction_id)) {
						$transaction_id='';
					}
					$payTypeAndName = getPayTypeAndName($return_data['pay_type']);
						
					$diTmp = array();
					$diTmp['paystatus'] = $return_data['pay_status'];
					$diTmp['trade_no'] = $transaction_id;
					$diTmp['paytype_name'] = 'weixin';
					$diTmp['status'] = 1;
					$diTmp['paytype'] = $payTypeAndName['paytype'];
					$diTmp['paytype_name'] = $payTypeAndName['paytype_name'];
						
					$this->mysql->update(Mysite::$app->config['tablepre'].'order',$diTmp,"id='".$order['id']."' and `uni_order_id`=".$return_data['order_id']."");
			
					//$order = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where buyeruid='".$this->member['uid']."' and id = ".$order['id']."");
			
						
				}
			}
		}
		
	}
	function payOver(){
		$orderid = intval(IReq::get('orderid'));
		$userid = empty($this->member['uid'])?0:$this->member['uid'];
		$orderid = intval(IReq::get('orderid'));
		if(empty($orderid)) $this->message('订单获取失败');
		if($userid == 0){
			$neworderid = ICookie::get('orderid');
			if($orderid != $neworderid) $this->message('订单无查看权限1');
		}
		if($orderid < 1){
			$this->message('订单获取失败');
		}
		$order = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where buyeruid='".$this->member['uid']."' and id = ".$orderid."");


                
		if($order['paytype'] == 1 && $order['paystatus'] == 0 && $order['status'] < 3){
			$this->syncOrderPayStatus($order);
			$order = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where buyeruid='".$this->member['uid']."' and id = ".$orderid."");
		}
		
		if($order['paytype'] == 1 && $order['paystatus'] == 0 && $order['status'] < 3){
			$checktime = time() - $order['addtime'];
			if($checktime > 900){
				//说明该订单可以关闭
				$cdata['status'] = 4;
				$this->mysql->update(Mysite::$app->config['tablepre'].'order',$cdata,"id='".$orderid."'");
				$this->mysql->delete(Mysite::$app->config['tablepre'].'orderps',"orderid = '$orderid' and status != 3");
				/*更新订单 状态说明*/
				$statusdata['orderid']     =  $orderid;
				$statusdata['addtime']     =  $order['addtime']+900;
				$statusdata['statustitle'] =  "自动关闭订单";
				$statusdata['ststusdesc']  =  "在线支付订单，未支付自动关闭";
				$this->mysql->insert(Mysite::$app->config['tablepre'].'orderstatus',$statusdata);
				$order['status'] = 4;
		
			}
		}
		
		$this->ordershow();
	}

	function makeorder2(){
		$this->checkwxuser();
		if( $this->checkbackinfo() ){
			if($this->member['uid'] == 0||empty($this->mid))  $this->message('未登陆'); 
	    } 
			if(  empty($this->member['uid']) || $this->member['uid'] ==  0){	 
				$addressinfo  = null;
				$cdata['id'] = 0;
				$cdata['default'] = 1;
				$cdata['contactname'] = ICookie::get('wxguest_username');
				$cdata['phone'] = ICookie::get('wxguest_phone');
				$cdata['address']  = ICookie::get('wxguest_address');
				if(empty($cdata['contactname'])){
					
				}else{
					$addressinfo = $cdata;
				}
			}else{
		  $addressinfo =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."address  where userid = ".$this->member['uid']." and `default`=1   "); 
   	
			} 
		 
			$info['mid'] = $this->mid;
			
			 //如果订单为订台，则下单时不用设置地址，
		 if($shopshowtype !='dingtai'){
		 	if(empty($addressinfo)) $this->message('未设置默认地址');
		 	$info['is_goshop']=0;
		 }
		 else
		 {
		 	$addressinfo['address'] ="堂食";
		 	$info['is_goshop']=1;
		 }
		 $info['username'] = $addressinfo['contactname']; 
		 $info['mobile'] = $addressinfo['phone'];
		 $info['addressdet'] = $addressinfo['address'];
		 $subtype = intval(IReq::get('subtype'));
	   $info['shopid'] = intval(IReq::get('shopid'));//店铺ID
		 $info['remark'] = IFilter::act(IReq::get('content'));//备注
		 $info['paytype'] = IFilter::act(IReq::get('paytype'));//'outpay';//支付方式 
		if( $info['paytype'] == '' ) $this->message('未开启任何支付方式，请联系管理员！');
	// $info['senddate'] =  IFilter::act(IReq::get('senddate'));
		 $info['minit'] = IFilter::act(IReq::get('minit')); 
		 $info['juanid']  = intval(IReq::get('juanid'));//优惠劵ID
		 if($this->checkbackinfo()){
			$info['ordertype'] = 3;//订单类型 
		 }else{
			 $info['ordertype'] = 5;
		 }
		 $peopleNum = IFilter::act(IReq::get('personcount'));  
		 if($peopleNum < 1) $this->message('选择消费人数');
		 $info['othercontent'] = empty($peopleNum)?'':serialize(array('人数'=>$peopleNum));  
		 $info['userid'] = !isset($this->member['score'])?'0':$this->member['uid'];
	   if(Mysite::$app->config['allowedguestbuy'] != 1){
	     if($info['userid']==0) $this->message('member_nologin');
	   } 
		 $shopinfo=   $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id where a.shopid = '".$info['shopid']."'    ");   
		 if(empty($shopinfo)) $this->message('店铺不存在');
		 /*监测验证码*/
		 
    if(empty($info['username'])) 		  $this->message('emptycontact'); 
	  if(!IValidate::suremobi($info['mobile']))   $this->message('errphone'); 
    $info['ipaddress'] = "";
    $ip_l=new iplocation(); 
     $ipaddress=$ip_l->getaddress($ip_l->getIP());  
     if(isset($ipaddress["area1"])){
		   $info['ipaddress']  = $ipaddress['ip'].mb_convert_encoding($ipaddress["area1"],'UTF-8','GB2312');//('GB2312','ansi',);
	   } 
     $info['cattype'] = 0;//
     
	  
	  	   if($shopinfo['is_open'] != 1) $this->message('店铺暂停营业'); 
	   $tempdata = $this->getOpenPosttime($shopinfo['is_orderbefore'],$shopinfo['starttime'],$shopinfo['postdate'],$info['minit'],$shopinfo['befortime']); 
	   if($tempdata['is_opentime'] ==  2) $this->message('选择的配送时间段，店铺未设置');
	   if($tempdata['is_opentime'] == 3) $this->message('选择的配送时间段已超时');
	   $info['sendtime'] = $tempdata['is_posttime'];
	   $info['postdate'] = $tempdata['is_postdate'];
	   $info['addpscost'] = $tempdata['cost'];
			
		if($info['paytype'] == 'undefined') 	$this->message("未开启任何支付方式，请联系管理员！");	  
		
			
	   $info['paytype'] = $info['paytype'] == 1?1:0;
	   
	   $info['areaids'] = '';  
	   $info['shopinfo'] = $shopinfo;
	   if($subtype == 1){
	   	$info['allcost'] = 0 ;
	   	$info['bagcost'] = 0;
	   	$info['allcount'] = 0; 
	   	$info['goodslist'] = array();
	   }else{
	   	 
	      if(empty($info['shopid'])) $this->message('shop_noexit');
		  
		  
		    $smardb = new newsmcart();
		 $carinfo = array();
		 if($smardb->setdb($this->mysql)->SetShopId($this->mid,$info['shopid'])->OneShop()){
			   $carinfo = $smardb->getdata(); 
		 }else{ 
		     $this->message($smardb->getError()); 
		 }
		 
		 if(count($carinfo['goodslist'])==0) $this->message('对应店铺购物车商品为空');
		   
		 $info['allcost'] = $carinfo['sum']; 
	   $info['goodslist']   = $carinfo['goodslist'];
		    
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
	    
	   if($info['userid'] ==  0){ 
	  	  ICookie::set('orderid',$orderid,86400);
	   }
	   if($subtype == 2){
	      $smardb->delshop($info['shopid']);
	   } 
	   
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
   function subshow(){
	    $orderid = intval(IReq::get('orderid'));  
		$userid  = empty($this->member['uid'])?0:$this->member['uid']; 

		if(empty($orderid)) $this->message('订单获取失败');
		if($userid == 0){ 
			$neworderid = ICookie::get('orderid'); 
			if($orderid != $neworderid) $this->message('订单无查看权限1');
		} 
	  	if($orderid < 1){ 
	  	 	$this->message('订单获取失败');
	  	}
		$order = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where buyeruid='".$this->member['uid']."' and id = ".$orderid."");   
	 
		$wxclass = new wx_s();
		$signPackage = $wxclass->getSignPackage($this->mid);
		$data['signPackage'] = $signPackage;
	 
	 	if($order['paytype'] == 1 && $order['paystatus'] == 0 && $order['status'] < 3){
            //判断订单如果已超过15分钟未支付的自动取消，但不能是堂食的订单，堂食订单不限支付时间
			$checktime = time() - $order['addtime'];
			if($checktime > 900 && $order['is_preeat']==0){
				//说明该订单可以关闭
				$cdata['status'] = 4;
				$this->mysql->update(Mysite::$app->config['tablepre'].'order',$cdata,"id='".$orderid."'");
			    $this->mysql->delete(Mysite::$app->config['tablepre'].'orderps',"orderid = '$orderid' and status != 3");
				/*更新订单 状态说明*/
				$statusdata['orderid']     =  $orderid;
				$statusdata['addtime']     =  $order['addtime']+900;
				$statusdata['statustitle'] =  "自动关闭订单";
				$statusdata['ststusdesc']  =  "在线支付订单，15分钟未支付自动关闭"; 		
				$this->mysql->insert(Mysite::$app->config['tablepre'].'orderstatus',$statusdata); 
				$order['status'] = 4;
			} 
		}
	 	
		$order['ps'] = $order['shopps'];
		// 超市商品总价	 超市配送配送	shopcost 店铺商品总价	shopps 店铺配送费	pstype 配送方式 0：平台1：个人	bagc 
	  	if(empty($order)){ 
	  	 	$this->message('订单获取失败');
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
		 
// 		$paylist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."paylist where type = 0 or type=2  order by id asc limit 0,50");
// 		if(is_array($paylist)){
// 		  foreach($paylist as $key=>$value){
// 			    $paytypelist[$value['loginname']] = $value['logindesc'];
// 		  }
// 	  }
	  $paylist =array();
	  $retUrl =  IUrl::creatUrlEx($this->mid,'wxsite/payOver/orderid/'.$orderid);
	  $url = getWxJSApiPayUrl ($order['uni_order_id'],$retUrl);
	  $payitem =array('code'=>'W','name'=>'微信支付','pay_url'=>$url,'check'=>1);
	  $paylist[] = $payitem;
	  	
	  if (!empty($order['wx_membercard_code'])) {
		  $url =IUrl::creatUrlEx($this->mid,'wxsite/memberPay/orderid/'.$orderid); 
		  $payitem =array('code'=>'membercard','name'=>'会员卡支付','pay_url'=>$url,'check'=>0);
		  $paylist[] = $payitem;
	  }
	  
	  $data['paylist'] = $paylist;
	  
	  $data['order'] = $order;
	  if($order['allcost']>0){
	     Mysite::$app->setdata($data); 
          }else{
              $link = IUrl::creatUrlEx($this->mid,'wxsite/ordershow/orderid/'.$order['id']);
	      $this->message('',$link); 
          }
/*	   	if($this->checkbackinfo()){
		 	Mysite::$app->setAction('subshow');
	  	}else{
		 	Mysite::$app->setAction('mobilesubshow'); 
	  	}
	  */
	}
	function shop(){
			$link = IUrl::creatUrlEx($this->mid,'wxsite/index'); 
	    if($this->member['uid'] == 0)  $this->message('未登陆',$link);  
	    	$nowdata = date('Y-m-d',time());
	  $mintime = strtotime($nowdata);
	  $maxtime = strtotime($nowdata.' 23:59:59');
	  $where = ' and  posttime > '.$mintime.' and posttime < '.$maxtime;//发货时间
	  
	   $tjlist = $this->mysql->getarr("select count(id) as shuliang,status from ".Mysite::$app->config['tablepre']."order where shopuid=".$this->member['uid']." ".$where."  group by status order by id asc limit 0,50");
	  $data['tj'] = array();
	  foreach($tjlist as $key=>$value){
	    $data['tj'][$value['status']] = $value['shuliang'];
	  }
	   Mysite::$app->setdata($data);
	}
	function shopordert(){
	   //shopuid
	    
	}
	function shopordertoday(){
		$nowdata = date('Y-m-d',time());
	  $mintime = strtotime($nowdata);
	  $maxtime = strtotime($nowdata.' 23:59:59');
	  $where = '  posttime > '.$mintime.' and posttime < '.$maxtime;//发货时间
	  $status  = intval(IFilter::act(IReq::get('status')));
	  $status  =  in_array($status,array(1,2,3))? $status:1; 
	  $where .=' and status ='.$status;
	  $where .=' and shopuid ='.$this->member['uid']; 
	  $buyerstatus= array(
		'0'=>'等待处理',
		'1'=>'等待发货',
		'2'=>'已发货，待完成',
		'3'=>'订单完成',
		'4'=>'订单已取消',
		'5'=>'订单已取消'
		);
		$data['buyerstatus'] = $buyerstatus;
		$data['where'] = $where;
		$arraystatus = array(
		'1'=>'今日待发货订单',
		'2'=>'今日已发货订单',
		'3'=>'今日完成订单'
		);
		$data['orderbt'] = $arraystatus[$status]; 
	  Mysite::$app->setdata($data); 
	}
	function shopshoworder(){
		$this->checkwxuser();
		$link = IUrl::creatUrlEx($this->mid,'wxsite/index'); 
	 if($this->member['uid'] == 0)  $this->message('未登陆',$link); 
	  $orderid = intval(IReq::get('id'));  
	  if(!empty($orderid)){
	  	 
	     	$order = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where shopuid='".$this->member['uid']."' and id = ".$orderid."");   
	     
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
		'1'=>'等待发货',
		'2'=>'已发货，待完成',
		'3'=>'订单完成',
		'4'=>'订单已取消',
		'5'=>'订单已取消'
	     	     );
	     	     $paytypelist = array(0=>'货到支付',1=>'在线支付','weixin'=>'微信支付');  
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
	function shopcontrol(){
		$this->checkmemberlogin();
		$controlname =trim(IFilter::act(IReq::get('controlname')));
		$orderid = intval(IReq::get('orderid')); 
		$ordertempinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where id = ".$orderid."");
		if($ordertempinfo['shopuid'] != $this->member['uid']) $this->message('您不能操作此订单');
		$shopid = $ordertempinfo['shopid']; 
		$shopinfo = $this->mysql->select_one("select uid from ".Mysite::$app->config['tablepre']."shop where id = ".$shopid."");
		switch($controlname)
		{
			case 'unorder': 
			 
	     $reason = trim(IFilter::act(IReq::get('reason')));
	     if(empty($reason)) $this->message('关闭理由不能为空');
	   	 $ordercontrol = new ordercontrol($orderid);
	   	 if($ordercontrol->sellerunorder($shopinfo['uid'],$reason))
	   	 {
				 $this->success('操作成功');
	     }else{
				  $this->message($ordercontrol->Error());
		   }  
			break;
			case 'sendorder': 
		  $ordercontrol = new ordercontrol($orderid);
		  if($ordercontrol->sendorder($shopinfo['uid']))
		  {
				$this->success('操作成功');
		  }else{
				 $this->message($ordercontrol->Error());
		  } 
			break;
			case 'shenhe': 
		  $ordercontrol = new ordercontrol($orderid);
		  if($ordercontrol->shenhe($shopinfo['uid']))
		  {
					$this->success('操作成功');
		  }else{
				 $this->message($ordercontrol->Error());
		  }
			break;
			case 'delorder':
			$ordercontrol = new ordercontrol($orderid);
		  if($ordercontrol->sellerdelorder($shopinfo['uid']))
		  {
				$this->success('操作成功');
		  }else{
			   $this->message($ordercontrol->Error());
		  } 
			break;
			case 'domake':
			if($ordertempinfo['status'] != 1){
			  $this->message('订单状态不可操作是否制作');
			} 		
			if(!empty($ordertempinfo['is_make'])){
				 $this->message('订单已设置过是否制作，如要取消 请联系网站客服');
			}
			$newdata['is_make'] = 1;
			$this->mysql->update(Mysite::$app->config['tablepre'].'order',$newdata,"id='".$orderid."'");
			$this->success('操作成功');
			break;
			case 'unmake':
			if($ordertempinfo['status'] != 1){
			  $this->message('订单状态不可操作是否制作');
			} 		
			if(!empty($ordertempinfo['is_make'])){
				 $this->message('订单已设置过是否制作，如要取消 请联系网站客服');
			}
			$newdata['is_make'] = 2;
			$this->mysql->update(Mysite::$app->config['tablepre'].'order',$newdata,"id='".$orderid."'");
			$this->success('操作成功');
			break;
			default:
			$this->message('未定义的操作');
			break;
		}
	}
	function ajaxlocation(){
		
	 	$lat = IFilter::act(IReq::get('lat'));   
	 	$lng = IFilter::act(IReq::get('lng'));  
	  
	 	$content =   file_get_contents('http://api.map.baidu.com/geoconv/v1/?coords='.$lng.','.$lat.'&&from=1&to=5&ak='.Mysite::$app->config['baidumapkey']);
	 	$backinfo = json_decode($content,true);
	 	//Array ( [status] => 0 [result] => Array ( [0] => Array ( [x] => 113.6778066454 [y] => 34.799780450303 ) ) )
	 	if($backinfo['status'] == 0){
	 	   $data['lat'] = $backinfo['result'][0]['y'];
	 	   $data['lng'] = $backinfo['result'][0]['x'];
	 	   ICookie::set('lat',$backinfo['result'][0]['y'],2592000);  
	     ICookie::set('lng',$backinfo['result'][0]['x'],2592000);  
	 	   $this->success($data);
	 	}else{
	 		$this->message('失败');
	 	} 
	 	 
	}
	function locationshop(){
		 ICookie::clear('myaddress');
		 $link = IUrl::creatUrlEx($this->mid,'wxsite/shoplist');
	    $this->message('',$link); 
	}
		function getwxuaerlocation(){
		
	 	$lat = IFilter::act(IReq::get('lat'));   
	 	$lng = IFilter::act(IReq::get('lng'));  
	 # $lng = 113.6778066454;
	 #  $lat = 34.799780450303;
	  //http://api.map.baidu.com/geocoder/v2/?ak=E4805d16520de693a3fe707cdc962045&callback=renderReverse&location=39.983424,116.322987&output=json&pois=1
	 	$content =   file_get_contents('http://api.map.baidu.com/geocoder/v2/?ak='.Mysite::$app->config['baidumapkey'].'&location='.$lat.','.$lng.'&output=json&pois=0&coordtype=wgs84ll');
	 	$backinfo = json_decode($content,true);
	 	//Array ( [status] => 0 [result] => Array ( [0] => Array ( [x] => 113.6778066454 [y] => 34.799780450303 ) ) )
		print_r($backinfo['result']['addressComponent']);
		
	 	if($backinfo['status'] == 0){
	 	   $data['cityname'] = $backinfo['result']['addressComponent']['city'];
	 	   $data['areaname'] = $backinfo['result']['addressComponent']['district'];
	 	   $data['streetname'] = $backinfo['result']['addressComponent']['street'];		  
	 	   $this->success($data);
	 	}else{
	 		$this->message('失败');
	 	} 
	 	 
	}
	
	function checkwxuser(){
		/*
	  $logintype = ICookie::get('logintype');  
	  if($logintype == 'wx'){
	  }else{
	  	$this->message('Not allowed');
	  }*/
	  if(Mysite::$app->config['wxLoginType'] == 1 && $this->member['uid'] <= 0 ){
		   $link = IUrl::creatUrlEx($this->mid,'wxsite/login');
		  $this->message('',$link);
	  }
	  
	  
	}
	function showpayhtml($data){
		$tempcontent = '';
		//array('paysure'=>false,'reason'=>'','url'=>''); 
		 
		if($data['paysure'] == true){
		$tempcontent = '<div style="margin-top:50px;background-color:#fff;">
			 <div style="height:30px;width:80%;padding-left:10%;padding-right:10%;padding-top:10%;">
			    <span style="background:url(\'http://'.Mysite::$app->config['siteurl'].'/upload/images/order_ok.png\') left no-repeat;height:30px;width:30px;background-size:100% 100%;display: inline-block;"></span>
				<div style="position:absolute;margin-left:50px;  margin-top: -30px; font-size: 20px;  font-weight: bold;  line-height: 20px;">恭喜您，支付订单成功</div>
				
			    
			</div>
			<div style="width:80%;margin:0px auto;padding-top:10px;"><font style="font-size:12px;">单号:</font><span style="padding-left:20px;font-size:12px;display: inline-block;">'.$data['reason']['dno'].'</span></div>
			<div style="width:80%;margin:0px auto;padding-top:10px;"><font style="font-size:12px;">总价:</font><span style="padding-left:20px;color:red;font-weight:bold;font-size:15px;">￥'.$data['reason']['allcost'].'元</span></div> 
			<div style="width:80%;margin:0px auto;padding-top:30px;text-align:right;"><a href="'.$data['url'].'"><span style="font-size:20px;color:#fff;padding:5px;background-color:red;">立即返回</span></a></div>
	   </div>';
		}else{
	   $tempcontent = '<div style="margin-top:50px;background-color:#fff;">
			 <div style="height:30px;width:80%;padding-left:10%;padding-right:10%;padding-top:10%;">
			    <span style="background:url(\''.Mysite::$app->config['siteurl'].'/upload/images/nocontent.png\') left no-repeat;height:30px;width:30px;background-size:100% 100%;display: inline-block;"></span>
				<div style="position:absolute;margin-left:50px;  margin-top: -30px; font-size: 20px;  font-weight: bold;  line-height: 20px;">sorry,支付订单失败</div>
				
			    
			</div>
			<div style="width:80%;margin:0px auto;padding-top:10px;"><font style="font-size:12px;">原因:</font><span style="padding-left:20px;font-size:12px;display: inline-block;">'.$data['reason'].'</span></div> 
			<div style="width:80%;margin:0px auto;padding-top:30px;text-align:right;"><a href="'.$data['url'].'"><span style="font-size:20px;color:#fff;padding:5px;background-color:red;  cursor: pointer;">立即返回</span></a></div>
	   </div>';
		} 
		$html = '<!DOCTYPE html>
<html>
<head>
   <meta charset="UTF-8">  
  <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
	<title>支付返回信息</title> 
	 
	 
 
 <script>
 	 
</script>

 
</head>
<body style="height:100%;width:100%;margin:0px;"> 
   <div style="max-width:400px;margin:0px;margin:0px auto;min-height:300px;"> '.$tempcontent.'    </div>
	 
</body>
</html>'; 
print_r($html);
exit;
      
    }
	function gotopay(){
		
	   	$orderid = intval(IReq::get('orderid')); 
	   		$payerrlink = IUrl::creatUrlEx($this->mid,'wxsite/subshow/orderid/'.$orderid);    
			$errdata = array('paysure'=>false,'reason'=>'','url'=>''); 
	     
		  if(empty($orderid)){
				$backurl = IUrl::creatUrlEx($this->mid,'wxsite/index');  
				$errdata['url'] = $backurl;
				$errdata['reason'] = '订单获取失败';
				$errdata['paysure'] = false;  
				$this->showpayhtml($errdata);   

		  } 
	 	$userid = empty($this->member['uid'])?0:$this->member['uid']; 
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
		 
	 
		 $paylist = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."paylist where  loginname = '".$paydotype."' and (type = 0 or type=2) order by id asc limit 0,50");
	
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
		$dopaydata = array('type'=>'order','upid'=>$orderid,'cost'=>$orderinfo['allcost'],'source'=>2,'paydotype'=>$paydotype);//支付数据 
		include_once($paydir.'/pay.php');   
		//调用方式  直接调用支付方式
		exit;
	}
	function drawbacklog(){
                $mid = $this->mid;
		$link = IUrl::creatUrlEx($this->mid,'wxsite/index'); 
		if($this->member['uid'] == 0)  $this->message('未登陆',$link); 
		$orderid = intval(IReq::get('orderid'));  
		if(!empty($orderid)){ 
	       	$order = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where buyeruid='".$this->member['uid']."' and id = ".$orderid."");   
	        $data['order'] = $order;
			if($order['is_reback'] > 0){
				$drawbacklog =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."drawbacklog where orderid='".$order['id']."'  ");   
				 $data['drawbacklog'] = $drawbacklog;
			}   
	        Mysite::$app->setdata($data); 
		}else{
			$data['order'] = '';
			Mysite::$app->setdata($data);
		}
	}
	function savedrawbacklog(){
		if(empty($this->member['uid'])){
			$this->message('member_nologin');
		}
	 	 
		$drawbacklog = new drawbacklog($this->mid,$this->mysql,$this->memberCls,$this->appid);
		 
		$check = $drawbacklog->save();
		if($check == true){
			$this->success('success');  
		}else{
			 $msg = $drawbacklog->GetErr();
			$this->message($msg);
		} 
	}
	//	一起说 列表
	function togethersay(){
		$this->checkwxuser();
		
		$wxclass = new wx_s();
		$signPackage = $wxclass->getSignPackage($this->mid);
		#print_r( $signPackage );
		$data['signPackage'] = $signPackage;
		
		
		$togethersaylist1 = $this->mysql->getarr(" select * from ".Mysite::$app->config['tablepre']."wxcomment as a left join ".Mysite::$app->config['tablepre']."member  as b  on a.uid = b.uid where a.is_top=0  and is_show=1   order by addtime desc ");
	#	print_r($togethersaylist);
		$togethersaylist = array();
		foreach($togethersaylist1 as $key=>$value){
			$value['pingjiazongshu'] =  $this->mysql->counts(" select * from ".Mysite::$app->config['tablepre']."wxreplycomment where parentid  = ".$value['id']."  ");
			$value['zongzanshu'] =  $this->mysql->counts(" select * from ".Mysite::$app->config['tablepre']."wxpjzan where commentid  = ".$value['id']."  ");
			$wxuserimages = $value['userimg'];
			$value['wxuserimgarr']  = explode('@',$wxuserimages);
			$togethersaylist[] = $value;
		}
		$data['togethersaylist'] = $togethersaylist;
		
	#	print_r($togethersaylist);
		
		$togethersaylist2 = $this->mysql->getarr(" select * from ".Mysite::$app->config['tablepre']."wxcomment as a left join ".Mysite::$app->config['tablepre']."admin  as b  on a.uid = b.uid where a.is_top=1  and is_show=1   order by addtime desc ");
	#	print_r($togethersaylist);
		$togethersaycomlist = array();
		foreach($togethersaylist2 as $key=>$value){
			$value['pingjiazongshu'] =  $this->mysql->counts(" select * from ".Mysite::$app->config['tablepre']."wxreplycomment where parentid  = ".$value['id']."  ");
			$value['zongzanshu'] =  $this->mysql->counts(" select * from ".Mysite::$app->config['tablepre']."wxpjzan where commentid  = ".$value['id']."  ");
				$wxuserimages = $value['userimg'];
			$value['wxuserimgarr']  = explode('@',$wxuserimages);
			$togethersaycomlist[] = $value;
		}
		$data['togethersaycomlist'] = $togethersaycomlist;
		/* print_r( $data['togethersaycomlist'] );
		exit; */
		
		Mysite::$app->setdata($data);
	}
	function commentwxuser(){
		$this->checkwxuser();
		$id = intval(IFilter::act(IReq::get('id')));
		$data['id'] = $id;
		$checkinfo  = $this->mysql->select_one(" select * from ".Mysite::$app->config['tablepre']."wxcomment where id = ".$id."   ");
		if($checkinfo['is_top']==0){			
			$wxcommentone = $this->mysql->select_one(" select * from ".Mysite::$app->config['tablepre']."wxcomment as a left join ".Mysite::$app->config['tablepre']."member  as b  on a.uid = b.uid where a.id = ".$id."  order by addtime desc "); //获取单独的评论
			
		}else{
			$wxcommentone = $this->mysql->select_one(" select * from ".Mysite::$app->config['tablepre']."wxcomment as a left join ".Mysite::$app->config['tablepre']."admin  as b  on a.uid = b.uid where a.id = ".$id."  order by addtime desc "); //获取单独的评论
		}
		$data['userimages'] = explode('@',$wxcommentone['userimg']);
		
		$wxreplylist = $this->mysql->getarr(" select * from ".Mysite::$app->config['tablepre']."wxreplycomment as a left join ".Mysite::$app->config['tablepre']."member  as b  on a.uid = b.uid where a.parentid = ".$wxcommentone['id']."  order by addtime desc "); //获取其它微信用户回复的评论
	#	print_r($wxreplylist);
		$data['zongzanshu']  = $this->mysql->counts(" select * from ".Mysite::$app->config['tablepre']."wxpjzan where commentid  = ".$wxcommentone['id']."  ");
		$data['wxreplylist'] = $wxreplylist;
		$data['wxcommentone'] = $wxcommentone;
		
		$wxclass = new wx_s();
		$signPackage = $wxclass->getSignPackage($this->mid);
		#print_r( $signPackage );
		$data['signPackage'] = $signPackage;
		$data['pingjiazongshu'] =  $this->mysql->counts(" select * from ".Mysite::$app->config['tablepre']."wxreplycomment where parentid  = ".$id."  ");
		Mysite::$app->setdata($data);
	}
	//微信用户本人留言
	 function saveuserpmes(){
		$this->checkwxuser();
	   $uid = $this->member['uid'];	
	   $media_ids = trim(IFilter::act(IReq::get('media_ids')));
	 
	   $wxclass = new wx_s();
	   	$accessToken = $wxclass->gettoken();
	   $mediaarr = explode(',',$media_ids);
	   $filename = array();
	   if(!empty($media_ids)){
		   if( is_array($mediaarr) ){
			   foreach ( $mediaarr as $key=>$value ){
				   $url = 'http://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.$accessToken.'&media_id='.$value;
				   $upwxfilename = $wxclass->saveMedia($url);
				   $filename[] = $upwxfilename;			
				}	
				$filename = $filename;
				$data['userimg'] = implode('@',$filename);			
		   }else{
				   $url = 'http://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.$accessToken.'&media_id='.$media_ids;
				   $upwxfilename = $wxclass->saveMedia($url);
				   $data['userimg'] = $upwxfilename;
		   }
	   }else{
		   
		   $data['userimg']  = '';
		   
	   }
	   $data['usercontent'] = trim(IFilter::act(IReq::get('message')));
	   $data['cityname'] = trim(IFilter::act(IReq::get('cityname')));
	   $data['areaname'] = trim(IFilter::act(IReq::get('areaname')));
	   $data['streetname'] = trim(IFilter::act(IReq::get('streetname')));
	   $data['uid'] = $uid;
	   $data['addtime'] = time();
	  $this->mysql->insert(Mysite::$app->config['tablepre'].'wxcomment',$data);
	   $this->success('success');
	 }
	//微信用户本人留言
	 function savehuifupj(){
		$this->checkwxuser();
	   $uid = $this->member['uid'];	
	   $data['content'] = trim(IFilter::act(IReq::get('message')));
	   $data['parentid'] = intval(IFilter::act(IReq::get('parentid')));
	   $data['cityname'] = trim(IFilter::act(IReq::get('cityname')));
	   $data['areaname'] = trim(IFilter::act(IReq::get('areaname')));
	   $data['streetname'] = trim(IFilter::act(IReq::get('streetname')));
	   $data['kejian'] = intval(IFilter::act(IReq::get('kejianvalue')));
	   $data['uid'] = $uid;
	   $data['addtime'] = time();
	   if(empty($data['content'])) $this->message('评价内容不能为空');
	   $this->mysql->insert(Mysite::$app->config['tablepre'].'wxreplycomment',$data);
	   $this->success('success');
	 }
	//微信用户点赞
	function saveuserzanjia(){
	   $data['uid'] = intval(IFilter::act(IReq::get('uid')));
	   $data['commentid'] = intval(IFilter::act(IReq::get('commentid')));
	   $pingjiaone = $this->mysql->select_one(" select * from ".Mysite::$app->config['tablepre']."wxcomment where id =".$data['commentid']." ");
	   if(empty($pingjiaone)) $this->message('获取评价对象错误'); 
	   $this->mysql->insert(Mysite::$app->config['tablepre'].'wxpjzan',$data);
	    $this->success('success');
	}
		//微信用户取消点赞
	function saveuserzanjian(){
	   $data['uid'] = intval(IFilter::act(IReq::get('uid')));
	   $data['commentid'] = intval(IFilter::act(IReq::get('commentid')));
	   $pingjiaone = $this->mysql->select_one(" select * from ".Mysite::$app->config['tablepre']."wxcomment where id =".$data['commentid']." ");
	   if(empty($pingjiaone)) $this->message('获取评价对象错误'); 
	   $this->mysql->insert(Mysite::$app->config['tablepre'].'wxpjzan',$data);
	   	 $this->mysql->delete(Mysite::$app->config['tablepre'].'wxpjzan',"commentid ='".$data['commentid']."' and uid = '".$data['uid']."' ");   
	    $this->success('success');
	}
	//微信用户举报
	function savejubaowxuser(){
	   $data['uid'] = intval(IFilter::act(IReq::get('uid')));
	   $data['commentid'] = intval(IFilter::act(IReq::get('jubaoid')));
	   $pingjiaone = $this->mysql->select_one(" select * from ".Mysite::$app->config['tablepre']."wxcomment where id =".$data['commentid']." ");
	   if(empty($pingjiaone)) $this->message('获取评价对象错误'); 
	   $getjubaowxuser = $this->mysql->select_one(" select * from ".Mysite::$app->config['tablepre']."wxuserjubao where uid =".$data['uid']." and commentid = ".$data['commentid']." ");
	   if(!empty($getjubaowxuser)) $this->message('你已经举报过啦~'); 
	   $this->mysql->insert(Mysite::$app->config['tablepre'].'wxuserjubao',$data);
	    $this->success('success');
	}
	//微信用户删除
	function saveshanchuwxuser(){
	   $uid = intval(IFilter::act(IReq::get('uid')));
	   $shanchuid = intval(IFilter::act(IReq::get('shanchuid')));
	   $pingjiaone = $this->mysql->select_one(" select * from ".Mysite::$app->config['tablepre']."wxcomment where id =".$shanchuid." and uid= ".$uid." ");
	   if(empty($pingjiaone)) $this->message('获取评价对象错误'); 
	    $this->mysql->delete(Mysite::$app->config['tablepre'].'wxcomment',"uid ='".$uid."' and id = '".$shanchuid."' ");    
	    $this->success('success');
	}
	function wxmsglist(){
		$uid = $this->member['uid'];	
		$togethersaylist1 = $this->mysql->getarr(" select * from ".Mysite::$app->config['tablepre']."wxcomment as a left join ".Mysite::$app->config['tablepre']."member  as b  on a.uid = b.uid where a.is_top=0 and a.uid = '".$uid."'  order by addtime desc ");
		$togethersaylist = array();
		foreach($togethersaylist1 as $key=>$value){
			$value['pingjiazongshu'] =  $this->mysql->counts(" select * from ".Mysite::$app->config['tablepre']."wxreplycomment where parentid  = ".$value['id']."  ");
			$value['zongzanshu'] =  $this->mysql->counts(" select * from ".Mysite::$app->config['tablepre']."wxpjzan where commentid  = ".$value['id']."  ");
			$togethersaylist[] = $value;
		}
		$data['togethersaylist'] = $togethersaylist;
		
		
		$systemsaylist = $this->mysql->getarr(" select * from ".Mysite::$app->config['tablepre']."wxcomment as a left join ".Mysite::$app->config['tablepre']."member  as b  on a.uid = b.uid where a.is_top=1 order by addtime desc ");
		$systemmsg = array();
		foreach($systemsaylist as $key=>$value){
			$value['pingjiazongshu'] =  $this->mysql->counts(" select * from ".Mysite::$app->config['tablepre']."wxreplycomment where parentid  = ".$value['id']."  ");
			$value['zongzanshu'] =  $this->mysql->counts(" select * from ".Mysite::$app->config['tablepre']."wxpjzan where commentid  = ".$value['id']."  ");
			$systemmsg[] = $value;
		}
		$data['systemmsg'] = $systemmsg;
		
		Mysite::$app->setdata($data);
		
	}
	//发表主题页面
	function fabiaozhuti(){
		$wxclass = new wx_s();
		$signPackage = $wxclass->getSignPackage($this->mid);
		#print_r( $signPackage );
		$data['signPackage'] = $signPackage;
		Mysite::$app->setdata($data);
		
	}

	
	
		//微信收藏商家
		function collectshopdata(){		// 首页获取附近商家列表（外卖和超市）
		$typelx = IFilter::act(IReq::get('typelx')); 
		
		 if(!empty($typelx)){
			 if($typelx == 'wm'){
				 ICookie::set('shopshowtype','waimai',2592000); 
				 $shopshowtype = 'waimai';
			 }
			 if($typelx == 'mk'){
				 ICookie::set('shopshowtype','market',2592000); 
				 $shopshowtype = 'market';
			 }
			  if($typelx == 'yd'){
				 ICookie::set('shopshowtype','dingtai',2592000); 
				 $shopshowtype = 'dingtai';
			 }
		 }else{
			 
			 $shopshowtype = ICookie::get('shopshowtype');
			 
		 }
	  
		
		$cxsignlist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodssign where mid='".$this->mid."' and type='cx' order by id desc limit 0, 100");
		$cxarray  =  array();
		foreach($cxsignlist as $key=>$value){
		   $cxarray[$value['id']] = $value['imgurl'];
		}
 
            	  $where = '';  
            
				       $lng = 0;
            	         $lat = 0;
            	          
            	            $lng = ICookie::get('lng');
            	            $lat = ICookie::get('lat');
						    $lng = empty($lng)?0:$lng;
							$lat =empty($lat)?0:$lat;
  //   $where = empty($where)?'   and  SQRT((`lat` -'.$lat.') * (`lat` -'.$lat.' ) + (`lng` -'.$lng.' ) * (`lng` -'.$lng.' )) < (`pradiusa`*0.01094) ': $where.' and SQRT((`lat` -'.$lat.') * (`lat` -'.$lat.' ) + (`lng` -'.$lng.' ) * (`lng` -'.$lng.' )) < (`pradiusa`*0.01094) ';
            	         
            	         $lng = trim($lng);
            	         $lat = trim($lat);
            	         $lng = empty($lng)?0:$lng;
						 $lat =empty($lat)?0:$lat;
						 
                        $orderarray = array(
						 	//'0'=>' (`lat` -'.$lat.') * (`lat` -'.$lat.' ) + (`lng` -'.$lng.' ) * (`lng` -'.$lng.' ) ASC       ',                       
						 	'0'=>'   sort asc      ',                       
                          ); 
				   
            			 /*获取店铺*/
            		  $pageinfo = new page();
            		  $pageinfo->setpage(intval(IReq::get('page'))); 
					   $where .= $qsjarray[$qsjid];
					      $where .= $qsjarray[$qsjid];
            	  $list =   $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shop where is_pass = 1  and is_open = 1  and endtime > ".time()."  ".$where."    order by ".$orderarray[0]." limit ".$pageinfo->startnum().", ".$pageinfo->getsize()."  ");		
			 
            			$nowhour = date('H:i:s',time()); 
                  $nowhour = strtotime($nowhour);
                  $templist = array();
                   $cxclass = new sellrule();  
                  if(is_array($list)){
            			    foreach($list as $keys=>$values){  
            			     
            			    		if($values['id'] > 0){
				 
				 $values['collect'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."collect where  collecttype = 0 and uid = ".$this->member['uid']." and collectid  = '".$values['id']."' ");//收藏
				 if(!empty($values['collect'])){		 
									
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
						#		print_r($attra);
						#		echo("11111111");								
										
										if($values['shoptype'] == 1 ){
											$shopdet = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket where  shopid = ".$values['id']."   ");
										}else{
											$shopdet = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast where  shopid = ".$values['id']."   ");
										}
									if(!empty($shopdet)){
									 	$values = array_merge($values,$shopdet);
										 
            			    	  $values['shoplogo'] = empty($values['shoplogo'])? Mysite::$app->config['imgserver'].Mysite::$app->config['shoplogo']:Mysite::$app->config['imgserver'].$values['shoplogo'];
            			          $checkinfo = $this->shopIsopen($values['is_open'],$values['starttime'],$values['is_orderbefore'],$nowhour); 
            			          $values['opentype'] = $checkinfo['opentype'];
            			          $values['newstartime']  =  $checkinfo['newstartime'];  
								  
							


							$attrdet = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shopattr where  cattype = ".$values['shoptype']." and shopid = ".$values['id']."");
            			          $cxclass->setdata($values['id'],1000,$values['shoptype']); 
						#		 print_r($attrdet); 
							  
            			          $checkps = 	 $this->pscost($values,1,$areaid); 
            			          $values['pscost'] = $checkps['pscost'];
								  
								  
								  	  $mi = $this->GetDistance($lat,$lng, $values['lat'],$values['lng'], 1); 
									$tempmi = $mi;
								  $mi = $mi > 1000? round($mi/1000,2).'km':$mi.'m';
													  
									$values['juli'] = 		$mi;
								  
								   
	 $shopcounts = $this->mysql->select_one( "select count(id) as shuliang  from ".Mysite::$app->config['tablepre']."order	 where   status = 3 and  shopid = ".$values['id']."" );
 
									if(empty( $shopcounts['shuliang']  )){
										$values['ordercount'] = 0;
									}else{
										$values['ordercount']  = $shopcounts['shuliang'];
									} 
								  	$values['ordercount']  = $values['ordercount']+$values['virtualsellcounts'];
									
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
								  
						#		 print_r($values['attrdet']);
								 
            			          $templist[] = $values;
            			     }
							 
				 } 
            	       } 
					   
					   
							}
					   
            	    }
            	    $data  = $templist; 
 #print_r($data);
	  $datas = json_encode($data);
	  echo 'showmoreshop('.$datas.')';
      exit; 
	    $this->success($data);
	 } 
	 
		
	
	 function saveshangjia(){
		$regagree = IFilter::act(IReq::get('regagree'));
		if(empty($regagree))	$this->message('请阅读入驻协议后勾选接受！');
		$username = IFilter::act(IReq::get('username'));  	
		$mobile = IFilter::act(IReq::get('mobile'));  
		$qq = IFilter::act(IReq::get('qq'));  
		 $resname = IFilter::act(IReq::get('resname')); 
		 $addr = IFilter::act(IReq::get('addr'));    
		if(empty($username))   $this->message('姓名不能为空！');
		if(!(IValidate::len($username,1,50)))$this->message('member_addresslength');	
		if(empty($mobile))   $this->message('手机号不能为空！');
		 if(!(IValidate::phone($mobile)))$this->message('errphone');  	
		if(empty($resname))   $this->message('店铺名称不能为空！');
		if(!(IValidate::len($resname,1,50)))$this->message('shop_shopnamelenth');	
		if(empty($addr))   $this->message('店铺的详细地址不能为空！');
		 if(!(IValidate::len($addr,1,255)))$this->message('shop_addresslenth');     	
				if(Mysite::$app->config['allowedcode'] == 1)
				 {
					   $Captcha = IFilter::act(IReq::get('Captcha'));
					   if(empty($Captcha) || $Captcha=="输入验证码" )   $this->message('验证码不能为空！');
					  if($Captcha != ICookie::get('Captcha')) 	$this->message('member_codeerr'); 
				 }		   
		
		  $arr['username'] = $username;
		 $arr['phone'] = $mobile; 
		
		 if(empty($qq) || $qq == "请输入您的QQ(选填)"){
			$arr['qq'] = '' ;   
		 }else{		 
			 $arr['qq'] = $qq;		 
		 }
			 
		 $arr['shopname'] = $resname;
		 $arr['shopaddress'] = $addr;
		  $arr['addtime'] = time(); 
		 $arr['is_pass'] = '0'; 	 
		 $this->mysql->insert(Mysite::$app->config['tablepre'].'messages',$arr);  
		  
		 $this->success('shangjiasuccess');
	 }
	 
	function login(){
		if( $this->member['uid'] > 0 ){
						$link = IUrl::creatUrlEx($this->mid,'wxsite/member');
	    	            $this->message('',$link); 
		}
	}
	function reg(){
		$data['mid'] = $this->mid;
		if( $this->member['uid'] > 0 ){
						$link = IUrl::creatUrlEx($this->mid,'wxsite/member');
	    	            $this->message('',$link); 
		}
		Mysite::$app->setdata($data);
	}
	function loginout(){
		  $this->memberCls->loginout(); 
      $link = IUrl::creatUrlEx($this->mid,'wxsite/index');
      $this->message('',$link);  
	} 
	function checkwxweb(){
		if( !strpos($_SERVER["HTTP_USER_AGENT"],'MicroMessenger')    ){    //判断是微信浏览器不
			if( $this->member['uid'] <= 0 ){
				 	$link = IUrl::creatUrlEx($this->mid,'wxsite/login');
	    	            $this->message('',$link); 
			}
		}
	}
	function checkbackinfo(){
		if( strpos($_SERVER["HTTP_USER_AGENT"],'MicroMessenger')    ){    //判断是微信浏览器不
			return true;
		}else{
			return false;
		}
	}
	/* 闪惠 */
	
	
	
	 
	  function shophui(){
	 	$this->checkwxuser();
		  $shopsearch = IFilter::act(IReq::get('search_input')); 
		  $data['search_input'] = $shopsearch;
		  Mysite::$app->setdata($data);  
	 }
    //8.3修改闪惠商家列表  lzh 2016-6-6
    function shophuilistdata(){

        $cxsignlist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodssign where type='cx' order by id desc limit 0, 100");
        $cxarray  =  array();
        foreach($cxsignlist as $key=>$value){
            $cxarray[$value['id']] = $value['imgurl'];
        }

        $where = '';
        $shopsearch = IFilter::act(IReq::get('search_input'));
        $shopsearch = urldecode($shopsearch);
        if(!empty($shopsearch)) $where=" and shopname like '%".$shopsearch."%' ";
        $areaid= intval(IFilter::act(IReq::get('areaid')));
        $catid = intval(IReq::get('catid'));
        $order = intval(IReq::get('order'));
        $order = in_array($order,array(1,2,3))? $order:0;
        $qsjid = intval(IReq::get('qsjid'));
        $qsjid = in_array($qsjid,array(1,2,3))? $qsjid:0;
        logwrite('获取店铺数');
        //构造菜品查询
        $orderarray = array(
            '0'=>' sort asc ',
            '1'=>' sort asc ',
            '2'=>'limitcost asc',
            '3'=>'is_com desc'
        );
        $qsjarray = array(
            '0'=>'  ',
            '1'=>'and limitcost < 5 ',
            '2'=>'and limitcost >= 5 and limitcost <= 10',
            '3'=>'and limitcost > 10'
        );
        
        $templist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shoptype where  cattype = 0 and parent_id = 0 and is_main =1  order by orderid asc limit 0,1000");
        $attra['input'] = 0;
        $attra['img'] = 0;
        $attra['checkbox'] = 0;
        foreach($templist as $key=>$value){
            if($value['type'] == 'input'){
                $attra['input'] =  $attra['input'] > 0?$attra['input']:$value['id'];
            }elseif($value['type'] == 'img'){
                $attra['img'] =  $attra['img'] > 0?$attra['img']:$value['id'];
            }elseif($value['type'] == 'checkbox'){
                $attra['checkbox'] =  $attra['checkbox'] > 0?$attra['checkbox']:$value['id'];
            }
        }
        /*获取店铺*/
        $pageinfo = new page();
        $pageinfo->setpage(intval(IReq::get('page')));
        $shopxinxi = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shop where is_pass = 1 and ".time()." < endtime and is_open =1  ".$where." limit ".$pageinfo->startnum().", ".$pageinfo->getsize()."");
        $list = array();
        foreach ($shopxinxi as $key=>$value){
            $shoplists = array();
            if($value['shoptype'] == 0){
                $shopfast =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast where shopid = ".$value['id']." and  is_hui=1 and is_shophui=1 ");

                if(!empty($shopfast)){
                    $shoplists = array_merge( $value , $shopfast);
                    $list[] = $shoplists;
                }


            }else{
                $shopmarket =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket where shopid = ".$value['id']." and  is_hui=1 and is_shophui=1");
                if(!empty($shopmarket)){
                    $shoplists = array_merge( $value , $shopmarket);
                    $list[] = $shoplists;
                }
            }
        }
        $nowhour = date('H:i:s',time());
        $nowhour = strtotime($nowhour);
        $templist = array();
        $cxclass = new sellrule();
        if(is_array($list)){
            foreach($list as $keys=>$values){
                if($values['id'] > 0){
                     $shopcounts = $this->mysql->select_one( "select count(id) as shuliang  from ".Mysite::$app->config['tablepre']."order	 where   status = 3 and  shopid = ".$values['id']."" );
                    if(empty( $shopcounts['shuliang']  )){
                        $values['ordercount'] = 0;//月销量
                    }else{
                        $values['ordercount']  = $shopcounts['shuliang'];
                    }
					$values['ordercount']  = $values['ordercount']+$values['virtualsellcounts'];
                    $lng = ICookie::get('lng');
                    $lat = ICookie::get('lat');
                    $lng = empty($lng)?0:$lng;
                    $lat =empty($lat)?0:$lat;
                    $mi = $this->GetDistance($lat,$lng, $values['lat'],$values['lng'], 1);
                    $mi = $mi > 1000? round($mi/1000,2).'km':$mi.'m';
                    $values['juli'] = $mi;//店铺距离
                    $values['shoplogo'] = empty($values['shoplogo'])? Mysite::$app->config['imgserver'].Mysite::$app->config['shoplogo']:Mysite::$app->config['imgserver'].$values['shoplogo'];
                    $checkinfo = $this->shopIsopen($values['is_open'],$values['starttime'],$values['is_orderbefore'],$nowhour);
                    $values['opentype'] = $checkinfo['opentype'];
                    $values['newstartime']  =  $checkinfo['newstartime'];
                    $attrdet = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shopattr where  cattype = 0 and shopid = ".$values['id']."");
                    $cxclass->setdata($values['id'],1000,$values['shoptype']);

                    $checkps = 	 $this->pscost($values,1,$areaid);
                    $values['pscost'] = $checkps['pscost'];
                    $values['shopshui'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shophui where  status = 1 and shopid = ".$values['id']."");
                    $weeknum = date("w"); //今天星期几
                    $nowtime = time();
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
//        $data  = $templist;
		
		   $data['shoplist']  = $templist; 
			Mysite::$app->setdata($data);
		
     //   $this->success($data);
    }
	 function subpayhui(){
//		$userid = empty($this->member['uid'])?0:$this->member['uid'];
		$orderid = intval(IReq::get('orderid')); 
		if(empty($orderid)) $this->message('闪慧买单获取失败');
		
	  if($orderid < 1){ 
	  	 $this->message('订单获取失败');
	  }
		$order = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shophuiorder where uid='".$this->member['uid']."' and id = ".$orderid."");   

		// 超市商品总价	 超市配送配送	shopcost 店铺商品总价	shopps 店铺配送费	pstype 配送方式 0：平台1：个人	bagc 
	  if(empty($order)){ 
	  	 $this->message('订单获取失败');
	  } 
	
	  $data['order'] = $order;
	
	
	
	if( $this->checkbackinfo() && $order['paystatus'] != 1  ){
	   $wxopenid = $this->getCookieKeyOfWxOpenid();
	   $weixindir = hopedir.'/plug/pay/weixin/'; 
	   require_once $weixindir."lib/WxPay.Api.php";
       require_once $weixindir."WxPay.JsApiPay.php";
	   
	   
	   $tools = new JsApiPay();
$openId = $tools->GetOpenid();
	logwrite('微信openid：'.$openId);
//②、统一下单
$input = new WxPayUnifiedOrder();
$input->SetBody("支付闪慧买单");
$input->SetAttach('a');
$input->SetOut_trade_no('a_'.$orderid);
$input->SetTotal_fee($order['sjcost']*100);
$input->SetTime_start(date("YmdHis"));
$input->SetTime_expire(date("YmdHis", time() + 600));
$input->SetTimeStamp(time());
$input->SetGoods_tag('闪慧');
$input->SetNotify_url(Mysite::$app->config['siteurl']."/plug/pay/weixin/notify.php");
$input->SetTrade_type("JSAPI");
$input->SetOpenid($wxopenid);
 //$url = Mysite::$app->config['siteurl'].'/plug/pay/weixin/jsapi.php';
$ordermm = WxPayApi::unifiedOrder($input);
//

$jsApiParameters = $tools->GetJsApiParameters($ordermm);
		$data['wxdata']  = $jsApiParameters;

/* {     "appId":"wx252c7ddb87971418",
		"nonceStr":"elp5is5mebhjdgrtsptptuel0z37rf62",
		"package":"prepay_id=wx20151109163411c5a9c269ed0695027503",
		"signType":"MD5",
		"timeStamp":"\"1447058052\"",
		"paySign":"FCED56BB6B00DEA6C4ED23CA3BAB5CF3"} */

}

	  Mysite::$app->setdata($data);

         if($this->checkbackinfo()){
             Mysite::$app->setAction('subpayhui');
         }else{
             Mysite::$app->setAction('mobilesubpayhui');
         }
	}
	//闪惠商家详情  8.3更新   lzh  2016-6-6
    function shophuishow(){
        $shopid = IFilter::act(IReq::get('id'));
        $shopinfo = $this->mysql->select_one("select *  from  ".Mysite::$app->config['tablepre']."shop  where id = ".$shopid." ");
        if(empty($shopinfo)) $this->message('获取店铺数据失败');
        $data['collect'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."collect where  collecttype = 0 and uid = ".$this->member['uid']." and collectid  = '".$shopinfo['id']."' ");//收藏
        $shopreal = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shopreal where  shopid = ".$shopid." limit 0,4");//商家实景分类
        $data['shopreal']=array();
        foreach($shopreal as $key=>$val){
            $shoprealimg = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shoprealimg where  parent_id = ".$val['id']);//商家实景分类图片
            $imgcount = $this->mysql->select_one("select count(id) as count from ".Mysite::$app->config['tablepre']."shoprealimg where  parent_id = ".$val['id']);//商家实景分类图片总数
            $val['shoprealimg']=$shoprealimg;
            $val['imgcount']=$imgcount['count'];

            $data['shopreal'][]=$val;
        }
        $shopshowtype = $shopinfo['shoptype'];
        if( $shopshowtype == 1 ){
            $shopdet = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id  where a.shopid = ".$shopid."   ");
        }else{
            $shopdet  = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast as a left join ".Mysite::$app->config['tablepre']."shop as b  on a.shopid = b.id  where a.shopid = ".$shopid."   ");
        }
        /* 店铺星级计算 */
        $zongpoint = $shopinfo['point'];
        $zongpointcount = $shopinfo['pointcount'];
        if($zongpointcount != 0 ){
            $shopstart = intval( round($zongpoint/$zongpointcount) );
        }else{
            $shopstart= 0;
        }

        $cxsignlist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodssign where type='cx' order by id desc limit 0, 100");
        $cxarray  =  array();
        foreach($cxsignlist as $key=>$value){
            $cxarray[$value['id']] = $value['imgurl'];
        }

        $cxinfo = $this->mysql->getarr("select name,id,signid from ".Mysite::$app->config['tablepre']."rule where   shopid = ".$shopinfo['id']." and status = 1 and starttime  < ".time()." and endtime > ".time()." ");
        $cxlist = array();
        $data['shophui'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shophui where  status = 1 and shopid = ".$shopinfo['id']."");
        foreach($cxinfo as $k1=>$v1){
            if(isset($cxarray[$v1['signid']])){
                $v1['imgurl'] = $cxarray[$v1['signid']];
                $cxlist[] = $v1;
            }
        }
        $data['cxlist'] = $cxlist;

        $areaid = ICookie::get('myaddress');

        $newshoparray = array_merge($shopinfo,$shopdet);
        $tempinfo =   $this->pscost($newshoparray,1,$areaid);
        $backdata['pstype'] = $tempinfo['pstype'];
        $backdata['pscost'] = $tempinfo['pscost'];
        $data['psinfo'] = $backdata;
        $data['shopstart'] = $shopstart;
        $data['shopinfo'] = $shopinfo;
        $data['shopdet'] = $shopdet;
        Mysite::$app->setdata($data);
    }
	 
	 //闪惠买单
	function huisubshow(){
		$id = intval(IReq::get('id')); 
		$list = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where id='".$id."'");
		$data['shopid'] = $list['id'];
		if(empty($list)) $this->message("获取商家失败");
		if( $list['shoptype'] == 0 ){
			$shopinfo  = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop as a left join ".Mysite::$app->config['tablepre']."shopfast as b on a.id = b.shopid where a.id='".$id."'");
		}else{
			$shopinfo  = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop as a left join ".Mysite::$app->config['tablepre']."shopmarket as b on a.id = b.shopid where a.id='".$id."'");
		}
		#print_r($shopinfo);
		
		$weeknum = date("w"); //今天星期几
		$nowtime = time();
        if( $shopinfo['is_shophui']==1 && $shopinfo['is_hui']==1 ){
			$shophuiinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shophui where shopid = '".$shopinfo['id']."' and status=1  and starttime <= ".$nowtime." and endtime >=".$nowtime);
			
			#print_r($shophuiinfo);
			if(!empty($shophuiinfo)){
				if( !empty($shophuiinfo['limitweek']) &&  !empty($shophuiinfo['limittimes']) ){
							$weekarray = explode(',',$shophuiinfo['limitweek']);
					
					
								$datey = date('Y-m-d',$nowtime);
								   $info =explode(',',$shophuiinfo['limittimes']);
								   #print_r($info);
								   $find = false;
								   foreach($info as $kc=>$val)
								   {
									  if(!empty($val))
									  {
										$checkinfo = explode('-',$val);
										if(!empty($checkinfo[1]))
										{
											   $time1 = strtotime($datey.' '.$checkinfo[0].':00');
											   $time2 = strtotime($datey.' '.$checkinfo[1].':00');
											   if($nowtime > $time1 && $nowtime < $time2)
											   {
												   $find = true;
												   break;
											   }
										}
									  }
								   }
					
					
							if( in_array($weeknum,$weekarray)  &&  $nowtime >= $shophuiinfo['starttime']  &&  $nowtime <= $shophuiinfo['endtime'] && $find==true ){
								$is_shophui = 1;  	// 当前时间有闪慧
							}else{
								$is_shophui = 0;
							}
				}else{
					$is_shophui  = 1;
				}
			}else{
				
				$shophuiinfo = '';
				$is_shophui  = 0;
				
			}
		}else{
			$shophuiinfo = '';
			$is_shophui  = 0;
		}
        $paylist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."paylist where type = 0 or type=2  order by id asc limit 0,50");
        if(is_array($paylist)){
            foreach($paylist as $key=>$value){
                $paytypelist[$value['loginname']] = $value['logindesc'];
            }
        }
        $data['paylist'] = $paylist;
		$data['is_shophui'] = $is_shophui;
		$data['shophuiinfo'] = $shophuiinfo;
		$data['shopinfo'] = $shopinfo;
		#print_r($shopinfo);
		  Mysite::$app->setdata($data); 
		
	}	
	 /* 
	 
	 id	name 规则名称	\
	 limittype 是否指定具体时间1否2指定星期3指定小时	
	 limitweek 具体时间：周几	
	 limittimes 限制每天具体时间	
	 mjlimitcost 每满费用金额	
	 limitzhekoucost 折扣限制金额	
	 controltype 规则类型：1赠，3折扣，2减费用	
	 controlcontent 限制内容填写赠品ID，折扣率，费用等大于0	
	 starttime 开始时间	
	 endtime 结束时间	
	 status 状态1有效 2无效	
	 shopid 店铺id	
	 
	 
		
-- 
-- 表的结构 `xiaozu_shophuiorder`
-- 

CREATE TABLE `xiaozu_shophuiorder` (
  `id` int(11) NOT NULL auto_increment,
  `uid` int(11) NOT NULL COMMENT '用户uid',
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `dno` varchar(25) NOT NULL COMMENT '买单号',
  `shopid` int(11) NOT NULL COMMENT '店铺ID',
  `shopname` varchar(255) NOT NULL COMMENT '店铺名称',
  `xfcost` decimal(10,2) NOT NULL COMMENT '消费金额',
  `yhcost` decimal(10,2) NOT NULL COMMENT '优惠金额',
  `sjcost` decimal(10,2) NOT NULL COMMENT '实际支付金额',
  `huiid` int(11) NOT NULL COMMENT '闪慧ID',
  `huiname` varchar(255) NOT NULL COMMENT '闪慧名称',
  `huitype` int(1) NOT NULL COMMENT '2是每满减 3是折扣',
  `huilimitcost` decimal(10,2) NOT NULL COMMENT '最低达到金额限制',
  `huicost` decimal(10,2) NOT NULL COMMENT '减金额',
  `paytype` int(11) NOT NULL COMMENT '1是微信支付',
  `paystatus` int(1) NOT NULL default '0' COMMENT '0是未付1是已付',
  `status` int(1) NOT NULL default '0' COMMENT '0是未完成是已完成',
  `addtime` int(11) NOT NULL COMMENT '创建时间',
  `completetime` int(11) NOT NULL default '0' COMMENT '支付买单完成时间',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


	 */
	 function makeshophuiorder(){
		 $uid = $this->member['uid'];
	
		 if( $uid > 0 ){
			 $memberinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where uid='".$uid."' ");
			 $username = $memberinfo['username'];			
		 }
		 
		 $shopid = intval( IFilter::act(IReq::get('shopid')) ); 
		 $huiid =  intval(IFilter::act(IReq::get('huiid')) ); 
		 $xfcost =  IFilter::act(IReq::get('xfcost')) ;  //消费金额
//		 $buyorderphone = trim(IFilter::act(IReq::get('buyorderphone')));		 // 买单人 联系号
		 $yhcost =  0 ;  //优惠金额
		 $sjcost =  0 ;  //实际支付金额
		
		 $paytype =  intval(IFilter::act(IReq::get('paytype')) ); 
		  if(empty($xfcost)) $this->message('消费金额为空');
		  if($xfcost > 0){
			  
		  }else{
			   $this->message('消费金额不能为0');
		  }
//		  if(empty($buyorderphone)) $this->message('买单人联系电话不能为空');
//		  if(!(IValidate::suremobi($buyorderphone)))  $this->message('买单人联系电话错误');
		
		
		 
								 
		 
		 $shopone = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where id='".$shopid."' "); // 店铺信息\
		 if( empty($shopone) ) $this->message("获取商户信息失败");
		 if($shopone['shoptype'] == 0){
			$shopinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop as a left join ".Mysite::$app->config['tablepre']."shopfast as b on a.id = b.shopid where a.id='".$shopid."' "); // 店铺信息
		 }else{
			$shopinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop as a left join ".Mysite::$app->config['tablepre']."shopmarket as b on a.id = b.shopid where a.id='".$shopid."' "); // 店铺信息
		 }
		if( $shopinfo['is_shophui'] == 1 ){
			 if( $huiid > 0 ){  
				$shophuiinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shophui where id='".$huiid."' ");  //闪慧信息
				if(!empty($shophuiinfo) && $shophuiinfo['shopid'] == $shopid) {
					if( $shophuiinfo['controltype'] == 2 ){
						$checkcost = $shophuiinfo['mjlimitcost']; // 每满费用金额
						if( $xfcost >= $checkcost  ){
							$yhcost = floor($xfcost/$checkcost)*$shophuiinfo['controlcontent']; 
						} 
						$data['huilimitcost'] = $shophuiinfo['mjlimitcost'];
						
					}
					if( $shophuiinfo['controltype'] == 3 ){
						$checkcost = $shophuiinfo['limitzhekoucost']; // 打折金额限制
						if( $xfcost >= $checkcost  ){
							$yhcost = $xfcost*((100-$shophuiinfo['controlcontent'])/100);
							 
						}else{
							$this->messqge("消费金额未达到条件");
						}
						
						$data['huilimitcost'] = $shophuiinfo['limitzhekoucost'];
						
					} 
					$data['huiid'] = $shophuiinfo['id'];
					$data['huiname'] = $shophuiinfo['name'];
					$data['huitype'] = $shophuiinfo['controltype'];					
					$data['huicost'] = $shophuiinfo['controlcontent']; 
				}else{
					$data['huiid'] = '';
					$data['huiname'] ='';
					$data['huitype'] = '';
					$data['huilimitcost'] = '';
					$data['huicost'] = '';
				}
			 }
		 }
		 if($yhcost > $xfcost){
			  $this->message('优惠金额不能大于消费金额');
		 }
		 $sjcost = $xfcost-$yhcost;
		 $data['uid'] = $uid;
		 $data['username'] = $username;
		 $data['dno'] = time().rand(1000,9999);
		 $data['shopid'] = $shopid;
		 $data['shopname'] = $shopinfo['shopname'];
		 $data['xfcost'] = $xfcost;
		 $data['buyorderphone'] = 0;
		 $data['yhcost'] = $yhcost;
		 $data['sjcost'] = $sjcost;
		 if( $shopinfo['is_shgift'] == 1 ){
			 $data['givejifen'] = floor($sjcost/$shopinfo['sendgift']);
		 }else{
			 $data['givejifen'] =  0;
		 }
			 
		 $data['paytype'] = $paytype;
		 $data['paystatus'] = 0;
		 $data['status'] = 0;
		 $data['addtime'] = time();
		 $data['completetime'] =0; 
		$this->mysql->insert(Mysite::$app->config['tablepre'].'shophuiorder',$data);    
		$orderid = $this->mysql->insertid(); 
		$this->success($orderid);  
	 }
	function locationshop1111111111(){
	    ICookie::clear('myaddress');
	    $link = IUrl::creatUrlEx($this->mid,'wxsite/shoplist');
	    $this->message('',$link); 
	}
  
	  
	function getsearmap(){
		//{"error":false,"msg":[{"datatype":"3","parent_id":"31","datacode":"henanshengzhengzhoushierqiqumianfanglu","datacontent":"\u6cb3\u5357\u7701\u90d1\u5dde\u5e02\u4e8c\u4e03\u533a\u68c9\u7eba\u8def","lat":"34.76177","lng":"113.637355"},{"datatype":"3","parent_id":"35","datacode":"henanshengzhengzhoushijinshuiqubeicangzhongli1hao","datacontent":"\u6cb3\u5357\u7701\u90d1\u5dde\u5e02\u91d1\u6c34\u533a\u5317\u4ed3\u4e2d\u91cc1\u53f7","lat":"34.776774","lng":"113.654387"},{"datatype":"3","parent_id":"39","datacode":"henanshengzhengzhoushijinshuiqujinshuilu612hao","datacontent":"\u6cb3\u5357\u7701\u90d1\u5dde\u5e02\u91d1\u6c34\u533a\u91d1\u6c34\u8def6-12\u53f7","lat"
		 
	/* Array ( 
	[status] => 0 
	[message] => ok 
	[total] => 160 
	[results] => Array ( 
		[0] => Array ( 
			[name] => 郑州大学(新校区) 
			[location] => Array ( 
					[lat] => 34.822975 
					[lng] => 113.542962 
					) 
					[address] => 河南省郑州市高新区科学大道100号 
					[street_id] => fc7675243777ff844f776ea6 
					[telephone] => (0371)67783111 
					[detail] => 1 
					[uid] => fc7675243777ff844f776ea6 
						) 
		)
	)					 */
		 
		$searchvalue = trim(IFilter::act(IReq::get('searchvalue')));
		//http://api.map.baidu.com/place/v2/search?q=饭店&region=北京&output=json&ak=E4805d16520de693a3fe707cdc962045&
	   $content =   file_get_contents('http://api.map.baidu.com/place/v2/search?ak='.Mysite::$app->config['baidumapkey'].'&output=json&query='.$searchvalue.'&page_size=20&page_num=0&scope=1&region='.Mysite::$app->config['cityname']); 
	   $list = json_decode($content,true);
	   $backdata = array();
	   if($list['message'] == 'ok'){
		  
	   	  if($list['total'] >= 1){
	   	  	foreach($list['results'] as $key=>$value){
	   	  	    $temp['address']    =  $value['name'];
	   	  	    $temp['detaddress'] =  $value['address'];
	   	  	    $temp['lng'] =  $value['location']['lng'];
	   	  	    $temp['lat'] =  $value['location']['lat'];
	   	  	    $temp['parent_id'] = 0;
	   	  	    $backdata[] = $temp;
	   	  	}	   	     
	   	  }
	    
	   }
	  // print_r($list);
	  
	  
	   
	  $datas = json_encode($backdata);
	  echo 'showaddresslist('.$datas.')';
      exit; 
	  
	  
	   $this->success($backdata);
	   
	  
	   
	}
	// 找回密码 验证手机号
	function forgetpwd(){
		   $regestercode = Mysite::$app->config['regestercode'];
		   $checkcode =    ICookie::get('regphonecode');
		   $checkphone =   ICookie::get('regphone');
		   $checktime =   ICookie::get('regtime'); 
      if(empty($regestercode)){
    	 	echo 'noshow(\'不需要验证CODE\')';
    	 	exit;
    	} 
      if(!empty($checkcode)){
      	  $backtime = $checktime-time();
		  	 if($backtime > 0){ 
		  	   echo 'showsend(\''.$checkphone.'\','.$backtime.')';
		  	   exit;
		  	 }
		  } 
    	if(!empty($this->member['uid'])){
    	  echo 'noshow(\'已登陆\')';
    	  exit;
    	} 
      $phone = IFilter::act(IReq::get('phone')); 
	  if(empty($phone)){
		   echo 'noshow(\'请填写手机号\')';
    	  exit;
	  }
      if(!(IValidate::suremobi($phone))){
      		echo  'noshow(\'手机格式错误\')';
      		exit;
      }
	  $userinfoarray = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."member where phone='".$phone."' ");
	  if( count($userinfoarray) > 1 )
      {
        	 echo 'noshow(\'此手机号绑定多个用户！\')';
        	 exit;
      }
      $userinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where phone='".$phone."' ");
      if(empty($userinfo))
      {
        	 echo 'noshow(\'未找到此手机号的用户！\')';
        	 exit;
      } 
      $makecode =  mt_rand(10000,99999);
	   $contents =  '您的验证码为：'.$makecode; 
       $phonecode = new phonecode($this->mysql,0,$phone);
		 $phonecode->sendother($contents);
      
      ICookie::set('getbackphonecode',$makecode,90);
      ICookie::set('getbackphone',$phone,90);
      $longtime = time()+90;
      ICookie::set('regtime',$longtime,90);
      echo 'showsend(\''.$phone.'\',90,\''.$userinfo['uid'].'\')';
      exit; 
	}
	function fornextzhpwd(){  
		
		$pwdyzm = intval( IFilter::act(IReq::get('pwdyzm')) );
		$phoneyan =  IFilter::act(IReq::get('phone')) ;
	 
		$datauid = intval( IFilter::act(IReq::get('datauid')) );
		if(empty($phoneyan)) $this->message('请输入您的手机号');
		$userinfoarray = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."member where phone='".$phoneyan."' ");
		  if( count($userinfoarray) > 1 )
		  {
			$this->message('此手机号绑定多个用户！');
		  }
		  $userinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where phone='".$phoneyan."'   ");
		  if(empty($userinfo))
		  {
			$this->message('未找到此手机号的用户！');
		  } 
		
		if(empty($pwdyzm)) $this->message('请输入您收到的验证码');
		 if(!empty($phoneyan)){
		    	   	    $checkcode =    ICookie::get('getbackphonecode');
						
		    	   	    if($pwdyzm != $checkcode) $this->message('验证码错误');
		 }
		 
		  $lastuserinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where uid='".$datauid."'   ");
		   if(empty($lastuserinfo))
		  {
			$this->message('未找到此用户！');
		  } 
		$this->success($lastuserinfo['uid']);
		
		
	} 
	function forgetnextpwd(){ //手机验证 
		
		$uid = intval( IFilter::act(IReq::get('id')) ) ;
		$data['uid'] = $uid;
		$userinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where uid='".$uid."'   ");
		 if(empty($userinfo))
		  {
			$this->message('获取用户信息失败！');
		  }
		Mysite::$app->setdata($data);
		 
	}
	function updatepwd(){   //手机验证 重新设置密码 
		
		$uid = intval( IFilter::act(IReq::get('uid')) ) ;
		$userinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where uid='".$uid."'   ");
		 if(empty($userinfo))
		  {
			$this->message('获取用户信息失败！');
		  }
		$pwd =  IFilter::act(IReq::get('pwd'))  ;
		$repwd =  IFilter::act(IReq::get('repwd'))  ;
		 if(!(IValidate::len($pwd,6,20)))
		{
			$this->message('member_pwdlen6to20');
		}  
    
		 if($pwd != $repwd){
		     $this->message('member_twopwdnoequale');
		 }
		 
		 $data['password'] = md5($pwd);
	 
		$this->mysql->update(Mysite::$app->config['tablepre'].'member',$data,"uid='".$uid."'");
		$this->success('success');
	}
	function costlog(){  //余额明细
		$uid = $this->member['uid'];
		if(empty($uid)) $this->message('获取用户信息失败');
		$costloglist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."memberlog where userid = ".$uid." and  type = 2  order by addtime desc ");
		$data['costloglist'] = $costloglist;
		Mysite::$app->setdata($data);
	}
	function subbalancepay(){ // 余额在线支付页面
	
		$cost = intval(IFilter::act( IReq::get('cost') ));
		$data['cost'] = $cost;
		
		
		
				 
		 /* 8.3新增 */
		 
		$rechargeone = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."rechargecost where cost  =".$cost." order by orderid asc  ");
	
		$sendname = '';
		if( !empty($rechargeone) ){
			if($rechargeone['is_sendcost']  == 0 && $rechargeone['is_sendjuan'] == 1  ){
				$sendname .= '充值'.$rechargeone['cost'].'元赠送';
			}
			if($rechargeone['is_sendcost']  == 1 ){
				$sendname .= '充值'.$rechargeone['cost'].'元赠送'.$rechargeone['sendcost'].'元';
			}
			if($rechargeone['is_sendcost']  == 1 && $rechargeone['is_sendjuan'] == 1   ){
				$sendname .= '+';
			}
			if(  $rechargeone['is_sendjuan']  == 1  ){
				$sendname .= $rechargeone['sendjuancost'].'优惠券';
			}
		}
 		$data['sendname'] = $sendname;
		
		
		
		
	  $paylist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."paylist where type = 0 or type=2  order by id asc limit 0,50");
		if(is_array($paylist)){
		  foreach($paylist as $key=>$value){
			    $paytypelist[$value['loginname']] = $value['logindesc'];
		  }
	  }
	  $data['paylist'] = $paylist;
 
  
	if( $this->checkbackinfo() ){

	   $wxopenid = $this->getCookieKeyOfWxOpenid();
	   $weixindir = hopedir.'/plug/pay/weixin/'; 
	   require_once $weixindir."lib/WxPay.Api.php";
       require_once $weixindir."WxPay.JsApiPay.php";
	   
	   
	   $tools = new JsApiPay();
$openId = $tools->GetOpenid();

$dno = 'acount_'.$this->member['uid'];
$acountid = 'acount_'.time();

//②、统一下单
$input = new WxPayUnifiedOrder();
$input->SetBody("支付订单".$dno);
$input->SetAttach($dno);
$input->SetOut_trade_no($acountid);
$input->SetTotal_fee($cost*100);
$input->SetTime_start(date("YmdHis"));
$input->SetTime_expire(date("YmdHis", time() + 600));
$input->SetTimeStamp(time());
$input->SetGoods_tag('在线充值');
$input->SetNotify_url(Mysite::$app->config['siteurl']."/plug/pay/weixin/notify.php");
$input->SetTrade_type("JSAPI");
$input->SetOpenid($wxopenid);
 //$url = Mysite::$app->config['siteurl'].'/plug/pay/weixin/jsapi.php';
$ordermm = WxPayApi::unifiedOrder($input);
// 
 
$jsApiParameters = $tools->GetJsApiParameters($ordermm);
		
		$data['wxdata'] = $jsApiParameters;
		 
} 
		
		Mysite::$app->setdata($data);
	}
	function catefoods(){	//外卖点击分类ajax获取分类下的所有商品
	    $weekji = date('w');
		$shopid = intval( IFilter::act(IReq::get('shopid')) ) ;
		$parentid = intval( IFilter::act(IReq::get('parentid')) ) ;
		$curcateid = intval( IFilter::act(IReq::get('curcateid')) ) ;
		$shoptype = intval( IFilter::act(IReq::get('shoptype')) ) ;
		$shopshowtype = ICookie::get('shopshowtype');
		if($shoptype == 1 ){
			$cateinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."marketcate where id = ".$curcateid." and shopid = ".$shopid." ");
			$shopinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where id=".$cateinfo['shopid']." ");
			$shopdet = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket where shopid = ".$shopinfo['id']." ");
		}else{ 
			$cateinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goodstype where id = ".$curcateid." ");
			$shopinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where id=".$cateinfo['shopid']." ");		
			$shopdet = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast where shopid = ".$shopinfo['id']." ");			
		}
		
		$data['shopinfo'] = $shopinfo;
	#	print_r($data['shopinfo']);
		$nowhour = date('H:i:s',time()); 
        $nowhour = strtotime($nowhour);
		$checkinfo = $this->shopIsopen($shopinfo['is_open'],$shopinfo['starttime'],$shopdet['is_orderbefore'],$nowhour); 
        $data['opentype'] = $checkinfo['opentype'];
	 
		$type = intval( IFilter::act(IReq::get('type')) ) ;  // type 商品展示模板 1表示默认模板 
		$catefoodslist = array();
		if($shopshowtype == 'dingtai'){//堂食
			$detaa = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goods where typeid='".$curcateid."' and is_dingtai = 1 and is_live = 1 and shopid = ".$shopid." and    FIND_IN_SET( ".$weekji." , `weeks` )    order by good_order asc ");
		}else{//外送
			$detaa = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goods where typeid='".$curcateid."' and is_waisong = 1 and is_live = 1 and shopid = ".$shopid." and    FIND_IN_SET( ".$weekji." , `weeks` )    order by good_order asc ");
 		}
			 foreach ( $detaa as $keyq=>$valq ){
				if($valq['is_cx'] == 1){
				//测算促销 重新设置金额
					$cxdata = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goodscx where goodsid=".$valq['id']."  ");
					$newdata = getgoodscx($valq['cost'],$cxdata);
					
					$valq['zhekou'] = $newdata['zhekou'];
					$valq['is_cx'] = $newdata['is_cx'];
					$valq['cost'] = $newdata['cost'];
                                        
				}
				
				$valq['sellcount'] = $valq['sellcount']+$valq['virtualsellcount'];
                                
                                //判断如果主图没图，如果多图中有图就拿第一张
                                if(empty($valq['img'])){
                                    $goodsimg = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goodsimg where goodsid = '".$valq['id']."' order by id asc ");
                                    $valq['img'] = $goodsimg['imgurl'];
                                
                                }
                                
				$catefoodslist[] =$valq; 
			}
		
		$data['cateinfo'] = $cateinfo;
		$data['shopdet'] = $shopdet;
	 
		$data['catefoodslist'] = $catefoodslist;
 #	print_r($data['catefoodslist']);
#   	 print_r($data['shopdet']);
		Mysite::$app->setdata($data);
	}
	function mkcatefoods(){	//超市点击分类ajax获取分类下的所有商品
	    $weekji = date('w');		 
		$shopid = intval( IFilter::act(IReq::get('shopid')) ) ;
		$curcateid = intval( IFilter::act(IReq::get('curcateid')) ) ;
		$shoptype = intval( IFilter::act(IReq::get('shoptype')) ) ;
		$type = intval( IFilter::act(IReq::get('type')) ) ;  // type 商品展示模板 1表示默认模板 
		if($shoptype == 1 ){
			$parentid = intval( IFilter::act(IReq::get('parentid')) ) ;
		 
 			if(!empty($curcateid)){				
				$where = " and  id = ".$curcateid."   ";
			}else{
				$where = "";				
			}
			$soncatelist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."marketcate where parent_id = ".$parentid."  ".$where." order by orderid asc ");	
			 
				foreach($soncatelist as $key=>$value){
					$temparray = array();
					$detaa = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goods where typeid='".$value['id']."'  and shoptype = ".$shoptype."  and shopid = ".$shopid."  and    FIND_IN_SET( ".$weekji." , `weeks` )    order by good_order asc ");
				 
					 foreach ( $detaa as $keyq=>$valq ){
						if($valq['is_cx'] == 1){
						//测算促销 重新设置金额
							$cxdata = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goodscx where goodsid=".$valq['id']."  ");
							$newdata = getgoodscx($valq['cost'],$cxdata);
							
							$valq['zhekou'] = $newdata['zhekou'];
							$valq['is_cx'] = $newdata['is_cx'];
							$valq['cost'] = $newdata['cost'];
						}
						$valq['sellcount'] = $valq['sellcount']+$valq['virtualsellcount'];
						$temparray[] =$valq; 
						
						$value['det'] = $temparray;
					}
					$catefoodslist[] = $value;
				}
		
				
		 	$parentcateinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."marketcate where id = ".$parentid." ");
		#	print_r($catefoodslist);
		 	$cateinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."marketcate where id = ".$curcateid." ");
		 	$shopinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where id=".$parentcateinfo['shopid']." ");
		 	$shopdet = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket where shopid = ".$shopinfo['id']." ");
		} 
		$data['shopinfo'] = $shopinfo;
		$nowhour = date('H:i:s',time()); 
        $nowhour = strtotime($nowhour);
		$checkinfo = $this->shopIsopen($shopinfo['is_open'],$shopinfo['starttime'],$shopdet['is_orderbefore'],$nowhour); 
        $data['opentype'] = $checkinfo['opentype'];
	 
	
		
		$data['cateinfo'] = $cateinfo;
		$data['shopdet'] = $shopdet;
	 
		$data['catefoodslist'] = $catefoodslist;
#   	 print_r($data['shopdet']);
		Mysite::$app->setdata($data);
	}
	function search(){  //搜索 商家和商品页面
	$searchname = IFilter::act(IReq::get('searchname'))   ; 
	$data['searchname'] = $searchname;
		$uid = $this->member['uid'];
        $searchArr=ICookie::get('searchlist');
		if($uid > 0){
			$searchloglist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."searchlog where uid = ".$uid." order by searchtime desc limit 0,10 ");
            //删除两个数组中重复的数据
            if(!empty($searchArr)){
                foreach($searchloglist as $k => $v){
                    foreach($searchArr as $key => $val){
                        if($v['searchval'] == $val['searchval']){
                            unset($searchArr[$key]);
                        }
                    }
                }
                $searchloglist = array_merge($searchloglist,$searchArr);
            }
			$data['searchloglist'] = $searchloglist;
		}else{
            if(!empty($searchArr)){
                $data['searchloglist'] = $searchArr;
            }
        }
		Mysite::$app->setdata($data);
	}
	function searchresult(){   //ajax搜索 商家和商品结果
		$searchname = IFilter::act(IReq::get('searchname'))   ;
		$uid = $this->member['uid'];
        $searchlist = ICookie::get('searchlist');
        if(empty($searchlist)){
            $searchlist = array();
            array_unshift($searchlist,array('searchval'=>$searchname));
        }else{
            foreach($searchlist as  $val){
                $temp[]=$val['searchval'];
            }
            if(!in_array($searchname,$temp)){
                array_unshift($searchlist,array('searchval'=>$searchname));
            }
        }
        ICookie::set("searchlist",$searchlist);
		
		
		if($uid > 0){
			$sdata['uid'] = $uid;
			$sdata['searchval'] = $searchname;
			$sdata['searchtime'] = time();
			$checksearch = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."searchlog where searchval = '".$searchname."' ");
		 
			if(empty($checksearch)){
				 $this->mysql->insert(Mysite::$app->config['tablepre'].'searchlog',$sdata);   // 插入用户 搜索记录 
			}
		} 
			
		$cxsignlist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goodssign where type='cx' order by id desc limit 0, 100");
		$cxarray  =  array();
		foreach($cxsignlist as $key=>$value){
		   $cxarray[$value['id']] = $value['imgurl'];
		}
				

/* 搜索店铺 结果  */
				$where = '';  
            	   $shopsearch = IFilter::act(IReq::get('searchname')); 
            	   $shopsearch		 = urldecode($shopsearch); 
            	   if(!empty($shopsearch)) $where=" and shopname like '%".$shopsearch."%' "; 
            	
            	        $lng = 0;
            	         $lat = 0;
            	   
            	            $lng = ICookie::get('lng');
            	            $lat = ICookie::get('lat');
						    $lng = empty($lng)?0:$lng;
							$lat =empty($lat)?0:$lat;
   //  $where = empty($where)?'   and  SQRT((`lat` -'.$lat.') * (`lat` -'.$lat.' ) + (`lng` -'.$lng.' ) * (`lng` -'.$lng.' )) < (`pradiusa`*0.01094) ': $where.' and SQRT((`lat` -'.$lat.') * (`lat` -'.$lat.' ) + (`lng` -'.$lng.' ) * (`lng` -'.$lng.' )) < (`pradiusa`*0.01094) ';
            	 
            	         
            	         $lng = trim($lng);
            	         $lat = trim($lat);
            	          $lng = empty($lng)?0:$lng;
							$lat =empty($lat)?0:$lat;
                      
            			 /*获取店铺*/
            		  $pageinfo = new page();
            		  $pageinfo->setpage(intval(IReq::get('page'))); 
					  
					$tempdd = array();
					$tempdd[] =   $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shop where is_pass = 1  and is_open = 1  and is_recom = 1 and endtime > ".time()."  ".$where." ");		
					$tempdd[] =   $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shop where is_pass = 1  and is_open = 1  and is_recom != 1 and endtime > ".time()."  ".$where." ");
				 
            			$nowhour = date('H:i:s',time()); 
                  $nowhour = strtotime($nowhour);
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
											
											if($values['shoptype'] == 1 ){
												$shopdet = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket where  shopid = ".$values['id']."   ");
											}else{
												$shopdet = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast where  shopid = ".$values['id']."   ");
											}
											
											$values = array_merge($values,$shopdet);
										
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
            	    $data['shopsearchlist']   = $templist;
		
	 
	#	print_r($data['shopsearchlist']);
	
	
 
					
	/* 搜索商品列表 */
		$weekji 	 = date('w');
		$goodwhere   = '';  
        $goodssearch = IFilter::act(IReq::get('searchname')); 
        $goodssearch = urldecode($goodssearch); 

	    if(!empty($goodssearch)) $goodlistwhere=" and name like '%".$goodssearch."%' "; 

/*            $lng = ICookie::get('lng');
            $lat = ICookie::get('lat');
		    $lng = empty($lng)?0:$lng;
			$lat = empty($lat)?0:$lat;
     		$goodwhere = empty($goodwhere)?'   and  SQRT((`lat` -'.$lat.') * (`lat` -'.$lat.' ) + (`lng` -'.$lng.' ) * (`lng` -'.$lng.' )) < (`pradiusa`*0.01094) ': $goodwhere.' and SQRT((`lat` -'.$lat.') * (`lat` -'.$lat.' ) + (`lng` -'.$lng.' ) * (`lng` -'.$lng.' )) < (`pradiusa`*0.01094) ';
            	        
	        $lng = trim($lng);
	        $lat = trim($lat);
	        $lng = empty($lng)?0:$lng;
			$lat = empty($lat)?0:$lat;
                      
            $templist11 = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shoptype where  cattype = 0 and parent_id = 0 and is_main =1  order by orderid asc limit 0,1000"); 

			$attra['input'] = 0;
			$attra['img'] = 0;
			$attra['checkbox'] = 0; 
			foreach($templist11 as $key=>$value){
				  if($value['type'] == 'input'){
				   $attra['input'] =  $attra['input'] > 0?$attra['input']:$value['id'];
				  }elseif($value['type'] == 'img'){
				  	 $attra['img'] =  $attra['img'] > 0?$attra['img']:$value['id'];
				  }elseif($value['type'] == 'checkbox'){
				  	$attra['checkbox'] =  $attra['checkbox'] > 0?$attra['checkbox']:$value['id'];
				  }
			} 
    		*/
    		/*获取店铺*/
    		$pageinfo = new page();
    		$pageinfo->setpage(intval(IReq::get('page'))); 

			$list =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shop where is_pass = 1  and is_open = 1  and endtime > ".time()."  ".$goodwhere." ");		
				 	 	      
				 
            $nowhour = date('H:i:s',time()); 
            $nowhour = strtotime($nowhour);
            $goodssearchlist = array();
            $cxclass = new sellrule();
				   
            if(is_array($list)){
            	foreach($list as $keys=>$vatt){  
            			     
            	if($vatt['id'] > 0){
					$detaa = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goods where shopid='".$vatt['id']."'  and shoptype = ".$vatt['shoptype']."  and    FIND_IN_SET( ".$weekji." , `weeks` )  ".$goodlistwhere."   order by good_order asc ");

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
							
							$temparray[] =$valq; 
							$vakk = $temparray;
						}
						
					 }	$goodssearchlist = $vakk;
				
							#			$vatt['goodsdet'] = $this->mysql->getarr("  ");
						 
								#		$templist11[] = $vatt;
									}
            	       } 
            	    }
					

            	    $data['goodssearchlist']   = $goodssearchlist;
		
#	  print_r($data['goodssearchlist']);
	
	 
	
		Mysite::$app->setdata($data);
	}
	
	function qkmemsearchlog(){  //清空会员搜索记录
		
		$uid = $this->member['uid'];
		if($uid > 0){
			 $this->mysql->delete(Mysite::$app->config['tablepre'].'searchlog',"uid ='".$uid."'");
            ICookie::set('searchlist',array());
			 $this->success('success');
		}else{
            ICookie::set('searchlist',array());
			$this->success('success');
		}
		
	}
	
	function pthelpme(){  // 跑腿----帮我送/买
	
 		$pttype = intval(IReq::get('pttype')); 
		$data['pttype'] = $pttype;  // 1为帮我送  2为帮我买
		
		$data['ptsetinfo'] = $this->mysql->select_one(" select * from ".Mysite::$app->config['tablepre']."paotuiset   "); 
		 
		  $postdate =  $data['ptsetinfo']['postdate'];
		  $befortime = $data['ptsetinfo']['pt_orderday'];
		  
 $nowhout = strtotime(date('Y-m-d',time()));//当天最小linux 时间
		$timelist = !empty($postdate)?unserialize($postdate):array();
		$data['pstimelist'] = array();
		$checknow = time();
		
		 
		$whilestatic = $befortime;
		$nowwhiltcheck = 0;
		while($whilestatic >= $nowwhiltcheck){
		    $startwhil = $nowwhiltcheck*86400;
			foreach($timelist as $key=>$value){
				$stime = $startwhil+$nowhout+$value['s'];
				$etime = $startwhil+$nowhout+$value['e'];
				if($etime  > $checknow){
					$tempt = array();
					$tempt['value'] = $value['s']+$startwhil;
					$tempt['s'] = date('H:i',$nowhout+$value['s']);
					$tempt['e'] = date('H:i',$nowhout+$value['e']);
					$tempt['d'] = date('Y-m-d',$stime);
					$tempt['i'] =  $value['i'];
					$tempt['cost'] =  isset($value['cost'])?$value['cost']:0;
					
					$tempt['name'] = $stime > $checknow?$tempt['s'].'-'.$tempt['e']:'立即配送';
					$data['pstimelist'][] = $tempt;
				}
			}
		 
			$nowwhiltcheck = $nowwhiltcheck+1;
		}
		
		
		Mysite::$app->setdata($data);
	}
	
	function specialpage(){ //专题页
		$id = intval(IReq::get('id'));
		$data['id'] = $id;
		$ztyinfo = $this->mysql->select_one(" select * from ".Mysite::$app->config['tablepre']."specialpage where id = ".$id."  ");
		$data['ztyinfo'] = $ztyinfo;
		$data['addressname'] = ICookie::get('addressname');
		 
		/* 
								<{if $items['cx_type'] == 6}>【外卖订餐】<{/if}>
								<{if $items['cx_type'] == 7}>【在线超市】<{/if}>
								<{if $items['cx_type'] == 8}>【预定点菜】<{/if}>
								<{if $items['cx_type'] == 9}>【跑腿服务】<{/if}>
								<{if $items['cx_type'] == 9}>【商家入驻】<{/if}>
		*/
		if($ztyinfo['is_custom'] == 1 && $ztyinfo['showtype'] == 0 && $ztyinfo['cx_type'] > 5 ){
			if($ztyinfo['cx_type'] == 6){
				$link = IUrl::creatUrlEx($this->mid,'wxsite/waimai'); 
			}
			if($ztyinfo['cx_type'] == 7){
				$link = IUrl::creatUrlEx($this->mid,'wxsite/marketlist'); 
			}
			if($ztyinfo['cx_type'] == 8){
				$link = IUrl::creatUrlEx($this->mid,'wxsite/dingtai'); 
			}
			if($ztyinfo['cx_type'] == 9){
				$link = IUrl::creatUrlEx($this->mid,'wxsite/paotui'); 
			}
			if($ztyinfo['cx_type'] == 10){
				$link = IUrl::creatUrlEx($this->mid,'wxsite/shopSettled'); 
			}
			$this->message('',$link);
		}
		$speciallist = $this->getztyshowlist($ztyinfo['is_custom'],$ztyinfo['showtype'],$ztyinfo['cx_type'],$ztyinfo['listids']);
		
		#print_r($speciallist);		
		 
		 $data['speciallist'] = $speciallist;
		 
		 
		Mysite::$app->setdata($data);
	}
	
	
 function specialpagelistdata(){
		$id = intval(IReq::get('id'));
		 
		$ztyinfo = $this->mysql->select_one(" select * from ".Mysite::$app->config['tablepre']."specialpage where id = ".$id."  ");
		$data['ztyinfo'] = $ztyinfo;
		$data['addressname'] = ICookie::get('addressname');
		
		$speciallist = $this->getztyshowlist($ztyinfo['is_custom'],$ztyinfo['showtype'],$ztyinfo['cx_type'],$ztyinfo['listids']);
		
		#print_r($speciallist);		
		 
		 $data['speciallist'] = $speciallist;
		 
		
	$datas = json_encode($data['speciallist']);
	if($ztyinfo['showtype'] == 0 ){
		echo 'showmorespeciallist('.$datas.')';
	}
	if($ztyinfo['showtype'] == 1 ){
		echo 'showgoodsspeciallist('.$datas.')';
	}
		exit; 
	    $this->success($data);
		
/* 	 print_r($backdata);
		exit;  */
		$this->success($backdata);
	}
	
	
	
	
	
	
	
	/* 
	专题页管理
	xiaozu_specialpage  专题页活动表
		id
		name	名称
		indeximg	首页显示图片	
		specialimg	专题页头部显示图片	
		color	专题页背景主色调	
***		is_custom 	是否是自定义	默认为1固定的  0为自定义的
***		showtype	针对的是商品还是店铺  默认0为店铺 1为商品
***		cx_type		如果是商品1为折扣  如果是店铺 1为推荐店铺  2为免减商家 3为打折商家 4免配送费  
***		listids		如果是自定义的话 所对应的店铺id集或者商品id集
		ruleintro	规则说明
		is_show		是否展示 默认1展示 0不展示
		orderid		排序
		addtime		添加时间 
	*/
function getztyshowlist($is_custom,$showtype,$cx_type,$listids){
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
            	  
							$lng = 0;
							$lat = 0;
					
            	            $lng = ICookie::get('lng');
            	            $lat = ICookie::get('lat');
						    $lng = empty($lng)?0:$lng;
							$lat =empty($lat)?0:$lat;
  //   $where = empty($where)?'   and  SQRT((`lat` -'.$lat.') * (`lat` -'.$lat.' ) + (`lng` -'.$lng.' ) * (`lng` -'.$lng.' )) < (`pradiusa`*0.01094) ': $where.' and SQRT((`lat` -'.$lat.') * (`lat` -'.$lat.' ) + (`lng` -'.$lng.' ) * (`lng` -'.$lng.' )) < (`pradiusa`*0.01094) ';
            	 
            	         
            	         $lng = trim($lng);
            	         $lat = trim($lat);
            	         $lng = empty($lng)?0:$lng;
						 $lat =empty($lat)?0:$lat;
                 
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
								   
	 $shopcounts = $this->mysql->select_one( "select count(id) as shuliang  from ".Mysite::$app->config['tablepre']."order	 where   status = 3 and  shopid = ".$values['id']."" );
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
					$detaa = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goods where  id in (".$listids.")  and shopid='".$vatt['id']."'  and shoptype = ".$vatt['shoptype']."  and    FIND_IN_SET( ".$weekji." , `weeks` )  ".$goodlistwhere."   order by good_order asc  ");
					//limit ".$pageinfo->startnum().", ".$pageinfo->getsize()." 
			
			  }else{
					$detaa = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goods where shopid='".$vatt['id']."'  and shoptype = ".$vatt['shoptype']."  and    FIND_IN_SET( ".$weekji." , `weeks` )  ".$goodlistwhere."   order by good_order asc  ");
			   }//limit ".$pageinfo->startnum().", ".$pageinfo->getsize()."
			   
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
	
	 

function getdycxshops($type){  // 获取对应促销类型的商家

			$pageinfo = new page();
					 $pageinfo->setpage(intval(IReq::get('page')),10);  
	  //  limit ".$pageinfo->startnum().", ".$pageinfo->getsize()."
				$cxlist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."rule where  status = 1 and controltype = ".$type." and starttime  < ".time()." and endtime > ".time()." ");
				$shopids = array();
				foreach($cxlist as $key=>$value){
					$shopids[] = $value['shopid'];
				}
				$shopids = implode(',',array_unique($shopids));
				if(!empty($shopids)){
					$list = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shop where is_pass = 1 and  id in (".$shopids.")  order by sort asc limit ".$pageinfo->startnum().", ".$pageinfo->getsize()." ");
				}else{
					$list = array();
				} 
				return $list;
}
		
/* 商家入住流程 */
function shopSettled(){
	$urllink = IUrl::creatUrlEx($this->mid,'wxsite/login');
	if($this->member['uid'] == 0)  $this->message('',$urllink); 
	$type =    intval(IReq::get('type'));
	$catparent = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shoptype  where  type='checkbox' order by cattype asc limit 0,100");
			$catlist = array();
			foreach($catparent as $key=>$value){
				$tempcat   = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."shoptype  where parent_id = '".$value['id']."'  limit 0,100");
				foreach($tempcat as $k=>$v){
					 $catlist[] = $v;
				}
			}
			$data['catarr'] = array('0'=>'外卖','1'=>'超市');
			$data['catlist'] = $catlist;
			
			$uid =    $this->member['uid'];
			
			if($uid > 0){
				$memberinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member  where uid = '".$uid."' ");
				$data['shopinfo'] =	$this->mysql->select_one("select id,is_pass from ".Mysite::$app->config['tablepre']."shop  where id = '".$memberinfo['shopid']."' ");
			}else{
				$data['shopinfo'] = array();
			}
		 
		if($type != 1  && $memberinfo['shopid'] > 0  )	{ 
			//if(!empty($data['shopinfo'])){
				$link = IUrl::creatUrlEx($this->mid,'wxsite/shangjiaresult/shopid/'.$memberinfo['shopid']);
				$this->message('',$link);
			//}
		}
		#	print_r($data['shopinfo']);
			Mysite::$app->setdata($data);
}
function sjapplyrz(){  
	
	$shopphone	 =    IFilter::act(IReq::get('shopphone'));
	$shopname    =    IFilter::act(IReq::get('shopname'));
	$shopaddress =    IFilter::act(IReq::get('shopaddress'));
	$shoplicense =    IFilter::act(IReq::get('shoplicense'));
	$shoptype =    IReq::get('shoptype');
	if(empty($shopphone)) $this->message("请填写联系电话");
	if(!empty($shopphone)&&!(IValidate::phone($shopphone)))$this->message('errphone');
	$checkphone = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member  where phone = ".$shopphone." ");
	if(!empty($checkphone)) $this->message("手机号已存在"); 
	if(empty($shopname)) $this->message("请填写店铺名称");
	$checkshopname = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop  where shopname = '".$shopname."' ");
	if(!empty($checkshopname)) $this->message("店铺名字已存在"); 
	if(empty($shopaddress)) $this->message("请填写店铺地址");
	if(empty($shoplicense)) $this->message("请上传营业执照");
	
	$data['shopphone'] = $shopphone;
	$data['shopname'] = $shopname;
	$data['shopaddress'] = $shopaddress;
	$data['shoptype'] = $shoptype;
	$data['shoplicense'] = $shoplicense;
	$this->success($data);
	 
	
}	
	function shangjiaapply(){  
	
	$shopphone	 =    IFilter::act(IReq::get('shopphone'));
	$shopname    =    IFilter::act(IReq::get('shopname'));
	$shopaddress =    IFilter::act(IReq::get('shopaddress'));
	$shoplicense =    IFilter::act(IReq::get('shoplicense'));
	$shoptype =    IReq::get('shoptype');
	$link = IUrl::creatUrlEx($this->mid,'wxsite/shangjia');
	if(empty($shopphone)) $this->message("请填写联系电话",$link);
	if(!empty($shopphone)&&!(IValidate::phone($shopphone)))$this->message('errphone',$link);
	$checkphone = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member  where phone = ".$shopphone." ");
	if(!empty($checkphone)) $this->message("手机号已存在",$link); 
	if(empty($shopname)) $this->message("请填写店铺名称",$link);
	$checkshopname = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop  where shopname = '".$shopname."' ");
	if(!empty($checkshopname)) $this->message("店铺名字已存在",$link); 
	if(empty($shopaddress)) $this->message("请填写店铺地址",$link);
	
	$data['shopphone'] = $shopphone;
	$data['shopname'] = $shopname;
	$data['shopaddress'] = $shopaddress;
	$data['shoptype'] = $shoptype;
	$data['shoplicense'] = $shoplicense;
 
	 Mysite::$app->setdata($data);
	
}	
function shangjiaresult(){
	$shopid =    intval( IReq::get('shopid') );
	$data['shopinfo'] =	$this->mysql->select_one("select id,is_pass from ".Mysite::$app->config['tablepre']."shop  where id = '".$shopid."' ");
	 
  	Mysite::$app->setdata($data);
	
}



/* 新增网站通知 */
	
function ajaxnoticelist(){
		 
	$pageinfo = new page();
	$pageinfo->setpage(intval(IReq::get('page')),10);  
 	$mid = $this->mid;
	$list = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."information where type=1  and  mid = '".$mid."' and unix_timestamp(now())>notice_time  order by orderid asc limit ".$pageinfo->startnum().", ".$pageinfo->getsize()."");   
	$backdata = array();
	foreach($list as $key=>$value){
		$value['addtime'] = date("Y-m-d",$value['addtime']); 
		$backdata[] = $value;
	}  
	$data['noticelist'] = $backdata;
	Mysite::$app->setdata($data);
}	  
function notice(){
	
	$id =    intval( IReq::get('id') );
	$data['noticeinfo'] =	$this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."information   where type= 1 and  id = '".$id."' "); 
  	Mysite::$app->setdata($data);
	
}
 	
/* 新增生活助手 */
function ajaxlifeasslist(){
		 
	  $pageinfo = new page();
	  $pageinfo->setpage(intval(IReq::get('page')),10);  
 
	 $list = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."information where type=2  order by orderid asc limit ".$pageinfo->startnum().", ".$pageinfo->getsize()."");   
	 $backdata = array();
	foreach($list as $key=>$value){
		$value['addtime'] = date("Y-m-d",$value['addtime']); 
		$backdata[] = $value;
	 }  
	 $data['noticelist'] = $backdata;
	 Mysite::$app->setdata($data);
	}	  
function lifeass(){
	
	$id =    intval( IReq::get('id') );
	$data['lifeassinfo'] =	$this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."information  where type = 2 and  id = '".$id."' "); 
  	Mysite::$app->setdata($data);
	
}

//保存店铺
	function saveshop()
	{
		$laiyuan = intval(IReq::get('laiyuan')); // 申请来源。1为微信端，主要用于判断微信端用户是否开过店
		$subtype = intval(IReq::get('subtype'));
		$id = intval(IReq::get('uid'));
		if(!in_array($subtype,array(1,2))) $this->message('system_err');
		if($subtype == 1){
			  $username = IReq::get('username');
			  if(empty($username)) $this->message('member_emptyname');
				$testinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where username='".$username."'  ");
			  if(empty($testinfo)) $this->message('member_noexit');
			  $shopinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."usrlimit where  `group`='".$testinfo['group']."' and  name='editshop' ");
			  if(empty($shopinfo)) $this->message('member_noownshop');
			  $shopinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where  uid='".$testinfo['uid']."' ");
			  if(!empty($shopinfo)) $this->message('member_isbangshop');
			  $data['shopname'] = IReq::get('shopname');
			  if(empty($data['shopname']))  $this->message('shop_emptyname');
			  $shopinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where  shopname='".$data['shopname']."'  ");
			  $this->mysql->update(Mysite::$app->config['tablepre'].'member',array('admin_id'=>intval(IReq::get('admin_id'))),"uid='".$testinfo['uid']."'");
			  if(!empty($shopinfo)) $this->message('shop_repeatname');
			  $data['uid'] = $testinfo['uid'];
			 
			   $data['admin_id'] = intval(IReq::get('admin_id'));
			  $nowday = 24*60*60*365;
	       $data['endtime'] = time()+$nowday;
 			   
			   
			  
		$shoptype =  IReq::get('shoptype') ; 
	  $temparray = explode('_',$shoptype);
	   
	  $sdata['shoptype']  = $temparray[0];   // 店铺大类型 0为外卖 1为超市
	  $attrid =  $temparray[1];
	   
  
	   $checkshoptype =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shoptype where id=".$attrid."  ");
	   if(empty($checkshoptype))  $this->message("获取店铺分类失败");
	   
	   
	    $this->mysql->insert(Mysite::$app->config['tablepre'].'shop',$data);
	  
	   $shopid = $this->mysql->insertid();
	    
	   $attrdata['shopid'] = $shopid;
	   $attrdata['cattype'] = $checkshoptype['cattype'];
	   $attrdata['firstattr'] = $checkshoptype['parent_id'];
	   $attrdata['attrid'] = $checkshoptype['id'];
	   $attrdata['value'] = $checkshoptype['name']; 
	   
	   $this->mysql->insert(Mysite::$app->config['tablepre'].'shopattr',$attrdata);
	  
			  
			  
			  
			  $this->success('success');
		}elseif($subtype ==  2){
			/*检测*/
			$data['username'] = IReq::get('username');
		  $data['phone'] = IReq::get('maphone');
      $data['email'] = IReq::get('email');
      $data['password'] = IReq::get('password');
      $sdata['shopname'] = IReq::get('shopname');
      $sdata['address'] = IReq::get('shopaddress');
       if(empty($sdata['shopname']))  $this->message('shop_emptyname');
		   $shopinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where  shopname='".$sdata['shopname']."'  ");
			 if(!empty($shopinfo)) $this->message('shop_repeatname');
			 $password2 = IReq::get('password2');
		   if($password2 != $data['password']) $this->message('member_twopwdnoequale');
			 $uid = 0;
			 if($this->memberCls->regester($data['email'],$data['username'],$data['password'],$data['phone'],3)){
			 	$uid = $this->memberCls->getuid(); 
			 	$this->mysql->update(Mysite::$app->config['tablepre'].'member',array('admin_id'=>intval(IReq::get('admin_id'))),"uid='".$uid."'");
			 	
			 }else{
			 	 $this->message($this->memberCls->ero());
			 }
      $sdata['uid'] = $uid;
      $sdata['maphone'] =  $data['phone'];
      $sdata['addtime'] = time();
      $sdata['email'] =  $data['email'];    
      $sdata['admin_id'] = intval(IReq::get('admin_id'));
      $nowday = 24*60*60*365;
	     $sdata['endtime'] = time()+$nowday;
  
  
		$shoptype =  IReq::get('shoptype') ; 
	  $temparray = explode('_',$shoptype);
	   
	  $sdata['shoptype']  = $temparray[0];   // 店铺大类型 0为外卖 1为超市
	  $attrid =  $temparray[1];
	   
  
	   $checkshoptype =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shoptype where id=".$attrid."  ");
	   if(empty($checkshoptype))  $this->message("获取店铺分类失败");
	   
	   
	    $this->mysql->insert(Mysite::$app->config['tablepre'].'shop',$sdata);
	  
	   $shopid = $this->mysql->insertid();
	    
	   $attrdata['shopid'] = $shopid;
	   $attrdata['cattype'] = $checkshoptype['cattype'];
	   $attrdata['firstattr'] = $checkshoptype['parent_id'];
	   $attrdata['attrid'] = $checkshoptype['id'];
	   $attrdata['value'] = $checkshoptype['name']; 
	   
	   $this->mysql->insert(Mysite::$app->config['tablepre'].'shopattr',$attrdata);
	 
	 
	   if($laiyuan == 1){
		   $this->mysql->update(Mysite::$app->config['tablepre'].'member',array('shopid'=>$shopid),"uid='".$this->member['uid']."'");
	   }
	   $this->success($shopid);
	 //$this->success('success');
	   
		}else{
		 $this->message('system_err');
		}
	}
		
	/* 举报商家页面 */
	   function shopreport(){
        $this->checkwxuser();
        $shopid = intval(IReq::get(shopid));
		$shopinfo =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where id=".$shopid."  ");
		if(empty($shopinfo)) $this->message("获取店铺信息失败！");
        $shopname = $shopinfo['shopname'];
		$typelist = unserialize( Mysite::$app->config['refundreasonlist'] );
		$data['typelist'] = $typelist;
   
        $data['shopname'] = $shopname;
        Mysite::$app->setdata($data);
    } 
    function saveshopreport(){
        $typeidContent = IFilter::act(IReq::get('typeidContent'));
        $shopname = IFilter::act(IReq::get('shopname'));
        $content = IFilter::act(IReq::get('content'));
        $phone= IReq::get('phone');
        if($typeidContent == null || $typeidContent == '')	$this->message('请选择一种投诉类型');
        if(empty($content))	$this->message('详细情况不能为空');
        if(empty($phone))	$this->message('手机号码不能为空');
        $myreg = '/^1[34578]{1}\d{9}$/';
        if(!preg_match($myreg,$phone))$this->message('手机号码格式错误');
        $arr['typeidContent'] = $typeidContent;
        $arr['shopname'] = $shopname;
        $arr['addtime'] = time();
        $arr['content'] = $content;
        $arr['phone'] = $phone;
        $this->mysql->insert(Mysite::$app->config['tablepre'].'shopreport',$arr);
        $this->success('success');
    }


/* 8.3新增  2016-05-31  zem */
	function sharehb(){   //下单分享领取优惠券页面
 		$wxclass = new wx_s();
		$signPackage = $wxclass->getSignPackage($this->mid);
 		$data['signPackage'] = $signPackage;
		
		$shareinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."juanshowinfo where type=2 order by orderid asc  ");
		 
		if( empty($shareinfo) ){
			$shareinfo['title'] = Mysite::$app->config['sitename'];
			$shareinfo['img'] = Mysite::$app->config['sitelogo'];
			$shareinfo['describe'] = Mysite::$app->config['sitename'];
		}
		$data['shareinfo'] = $shareinfo;
	
		$orderid = intval(IReq::get('did'));
		$orderinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where id=".$orderid."  ");
		
		$where = "  where type=2 and addtime < ".time()." and endtime > ".time()." and is_open = 1 and juannum > 0 ";
 		$checkinfosendjuan = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."juanrule ".$where." order by orderid asc ");
		
		
		$historyphone =   $_COOKIE['historyphone'];  //  ICookie::get('historyphone');
 		if( !empty($historyphone) ){
			$juanlist =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."juan where orderid=".$orderid."  and bangphone = '".$historyphone."' ");
		}else{
			$juanlist = array();
		}
 		$data['checkinfosendjuan'] = $checkinfosendjuan;
 		$data['historyphone'] = $historyphone;
		$data['juanlist'] = $juanlist;
		$data['orderinfo'] = $orderinfo;
		Mysite::$app->setdata($data);
		
	}
	function receivejuan(){    //下单成功分享优惠券 根据手机号  领取优惠券
		
		$orderid = intval(IReq::get('orderid'));
		$phone = IFilter::act(IReq::get('phone'));
		$orderinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where id=".$orderid."  ");
		if(empty($orderinfo)) $this->message('获取失败，请刷新页面尝试~');
		if(empty($phone)) $this->message('请填写领取使用的手机号~');
		if(!(IValidate::suremobi($phone)))$this->message('请填写正确的手机号~');
		
		$checkisrec =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."juan where orderid=".$orderid."  and bangphone = '".$phone."' ");
		if( !empty($checkisrec) )  {
			$this->success('success');
		}
		
	 	$where = "  where type=2 and addtime < ".time()." and endtime > ".time()." and is_open = 1 and juannum > 0 ";
 		$checkinfosendjuan = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."juanrule ".$where." order by orderid asc ");
 		$sendjuanlist = array();
		if(!empty($checkinfosendjuan)){	 	
				for($i=1;$i<=$checkinfosendjuan['juannum'];$i++ ){  
					$juanjiancost = rand( $checkinfosendjuan['jiancostmin'] ,$checkinfosendjuan['jiancostmax'] );
					$juanjiacost = rand( $checkinfosendjuan['jiacostmin'] ,$checkinfosendjuan['jiacostmax'] ); 
  					$temparray = array();
					$temparray['limitcost']  = $juanjiancost;
					$temparray['cost']  = $juanjiacost; 
					$sendjuanlist[] = $temparray; 
 				} 
		}
		$memberinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where phone = ".$phone."   ");
		
		if( !empty($sendjuanlist) ){
						foreach($sendjuanlist as $key=>$val){ 
							
							$data['creattime'] = time();
							$data['cost'] = $val['cost'];
							$data['limitcost'] = $val['limitcost'];
							$data['endtime'] = $checkinfosendjuan['endtime'];
							if( !empty($memberinfo) ){
								$data['uid'] = $memberinfo['uid'];
								$data['username'] = $memberinfo['username'];
								$data['status'] = 1;
							}else{
								$data['status'] = 0;
							}							
							$data['bangphone'] = $phone;
							$data['orderid'] = $orderinfo['id'];
							$data['name'] = "满".$val['limitcost']."元减".$val['cost']."元";
							$data['type'] = 2;
							$data['paytype'] = $checkinfosendjuan['paytype'];
 							$this->mysql->insert(Mysite::$app->config['tablepre'].'juan',$data);  
						}
		}
		 
		$this->success('success');
	}
	function memsharej(){   //会员忠心推广分享优惠券页面
		$link = IUrl::creatUrlEx($this->mid,'wxsite/index'); 
		if($this->member['uid'] == 0)  $this->message('未登陆',$link); 
 		$jiamiuidkey = base64_encode($this->member['uid']);  //  base64_decode
		$data['jiamiuidkey'] = $jiamiuidkey;
  		$wxclass = new wx_s();
		$signPackage = $wxclass->getSignPackage($this->mid);
 		$data['signPackage'] = $signPackage;
		
		$shareinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."juanshowinfo where type=3 order by orderid asc  ");
		 
		if( empty($shareinfo) ){
			$shareinfo['title'] = Mysite::$app->config['sitename'];
			$shareinfo['img'] = Mysite::$app->config['sitelogo'];
			$shareinfo['describe'] = Mysite::$app->config['sitename'];
		}
		$data['shareinfo'] = $shareinfo;

		$memberinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where uid=".$this->member['uid']."  ");
		
		$where = "  where type=3 and addtime < ".time()." and endtime > ".time()." and is_open = 1 and juannum = 1 ";
 		$checkinfosendjuan = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."juanrule ".$where." order by orderid asc ");
		
		
		$historyphone =   $_COOKIE['historyphone'];  //  ICookie::get('historyphone');
 		if( !empty($historyphone) ){
			$juanlist =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."juan where shareuid=".$this->member['uid']."  and bangphone = '".$historyphone."' ");
		}else{
			$juanlist = array();
		}
 		$data['checkinfosendjuan'] = $checkinfosendjuan;
 		$data['historyphone'] = $historyphone;
		$data['juanlist'] = $juanlist;
		$data['memberinfo'] = $memberinfo;
		Mysite::$app->setdata($data);
		
	}
	
 function memsharehb(){   //会员忠心推广分享领取优惠券页面
		 $uidkey = trim(IReq::get('key'));
 		$uid = base64_decode($uidkey);  //  
		if( is_numeric($uid) ){
			$data['uid'] = $uid;
		}else{
			$data['uid'] = 0;
		} 
  		$sharememberinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where uid='".$data['uid']."'  ");
   		$shareinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."juanshowinfo where type=3 order by orderid asc  ");
		 
		if( empty($shareinfo) ){
			$shareinfo['title'] = Mysite::$app->config['sitename'];
			$shareinfo['img'] = Mysite::$app->config['sitelogo'];
			$shareinfo['describe'] = Mysite::$app->config['sitename'];
		}
		$data['shareinfo'] = $shareinfo;
	
 		
		$where = "  where type=3 and addtime < ".time()." and endtime > ".time()." and is_open = 1 and juannum = 1 ";
 		$checkinfosendjuan = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."juanrule ".$where." order by orderid asc ");
		
		
		$historyphone =   $_COOKIE['historyphone'];  //  ICookie::get('historyphone');
 		if( !empty($historyphone) ){
			$juanlist =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."juan where shareuid='".$sharememberinfo['uid']."'  and bangphone = '".$historyphone."' ");
		}else{
			$juanlist = array();
		}
 		$data['checkinfosendjuan'] = $checkinfosendjuan;
 		$data['historyphone'] = $historyphone;
		$data['juanlist'] = $juanlist;
		$data['sharememberinfo'] = $sharememberinfo;
		Mysite::$app->setdata($data);
		
	}
	
 function memsharelqjuan(){    //会员推广分享优惠券 根据手机号  领取优惠券
		
		$uid = intval(IReq::get('uid'));
		$phone = IFilter::act(IReq::get('phone'));
		$sharememberinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where uid=".$uid."  ");
		if(empty($sharememberinfo)) $this->message('获取失败，请刷新页面尝试~');
		if(empty($phone)) $this->message('请填写领取使用的手机号~');
		if(!(IValidate::suremobi($phone)))$this->message('请填写正确的手机号~');
		
		$checkisrec =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."juan where shareuid=".$uid."  and bangphone = '".$phone."' ");
		if( !empty($checkisrec) )  {
			$this->success('success');
		}
		
	 	$where = "  where type=3 and addtime < ".time()." and endtime > ".time()." and is_open = 1 and juannum = 1 ";
 		$checkinfosendjuan = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."juanrule ".$where." order by orderid asc ");
		
		
 		$sendjuanlist = array();
		if(!empty($checkinfosendjuan)){	 	
 					$juanjiancost = rand( $checkinfosendjuan['jiancostmin'] ,$checkinfosendjuan['jiancostmax'] );
					$juanjiacost = rand( $checkinfosendjuan['jiacostmin'] ,$checkinfosendjuan['jiacostmax'] ); 
  					$temparray = array();
					$temparray['limitcost']  = $juanjiancost;
					$temparray['cost']  = $juanjiacost; 
					$sendjuanlist  = $temparray;  
		} 
		$memberinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where phone = ".$phone."   ");
		
		if( !empty($sendjuanlist) ){
 							
							$data['creattime'] = time();
							$data['cost'] = $sendjuanlist['cost'];
							$data['limitcost'] = $sendjuanlist['limitcost'];
							$data['endtime'] = $checkinfosendjuan['endtime'];
							if( !empty($memberinfo) ){
								$data['uid'] = $memberinfo['uid'];
								$data['username'] = $memberinfo['username'];
								$data['status'] = 1;
							}else{
								$data['status'] = 0;
								$data['username'] = '';
							}							
							$data['bangphone'] = $phone;
							$data['orderid'] = $orderinfo['id'];
							$data['name'] = "满".$sendjuanlist['limitcost']."元减".$sendjuanlist['cost']."元";
							$data['type'] = 3;
							$data['paytype'] = $checkinfosendjuan['paytype'];
							$data['shareuid'] = $sharememberinfo['uid'];
								 
 							$this->mysql->insert(Mysite::$app->config['tablepre'].'juan',$data);  
						 
		}
		 
		$this->success('success');
	}


    //8.3新增  绑定手机号发送验证码  lzh  2016-6-7
    function bindingphone(){
        $this->checkwxuser();
        $userid=$this->member['uid'];
        $phone = IFilter::act(IReq::get('phone'));
        if(empty($phone)){
            echo 'noshow(\'请填写手机号\')';
            exit;
        }
        if(!preg_match("/^1[34578]{1}\d{9}$/",$phone)){
            echo  'noshow(\'手机格式错误\')';
            exit;
        }
        $userinfoarray = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."member where phone='".$phone."' ");
        if( count($userinfoarray) >= 1 )
        {
            echo 'noshow(\'此手机号已被绑定！\')';
            exit;
        }
        $makecode =  mt_rand(10000,99999);
        $contents =  '绑定手机号，验证码为：'.$makecode; 
		 $phonecode = new phonecode($this->mysql,0,$phone);
		 $phonecode->sendother($contents); 
        ICookie::set('bindingphonecode',$makecode,90);
        ICookie::set('bindingphone',$phone,90);
        $longtime = time()+90;
        ICookie::set('bindingtime',$longtime,90);
        echo 'showsend(\''.$phone.'\',90,\''.$userid.'\')';
        exit;
    }
	
	

    //8.3新增   绑定手机号  lzh  2016-6-7
    function surebinding(){
        $this->checkwxuser();
        $pwdyzm = intval( IFilter::act(IReq::get('pwdyzm')) );
        $phoneyan =  IFilter::act(IReq::get('phone')) ;
        $datauid = intval( IFilter::act(IReq::get('datauid')) );
        if(empty($phoneyan)) $this->message('请填写手机号');
        if(!preg_match("/^1[34578]{1}\d{9}$/",$phoneyan)){
            $this->message('手机格式错误');
        }
        $userinfoarray = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."member where phone='".$phoneyan."' ");
        if( count($userinfoarray) >= 1 )
        {
            $this->message('此手机号已被绑定！');
        }
        if(Mysite::$app->config['regestercode'] == 1){
            if(empty($pwdyzm)) $this->message('请输入您收到的验证码');
            if(!empty($phoneyan)){
                $checkcode = ICookie::get('bindingphonecode');
                if($pwdyzm != $checkcode) $this->message('验证码错误');
            }
        }

        $data['phone']=$phoneyan;
        $this->mysql->update(Mysite::$app->config['tablepre'].'member',$data,"uid='".$datauid."'");
		
		/* 更新绑定手机号有关优惠券信息 */
		$memberCls = new memberclass($this->mysql);  
		$memberCls->updatememjuaninfo($phoneyan);
		
        $this->success('success');
    }

    //8.3 个人中心点击头像判断登录
    function myaccount(){
        $this->checkwxweb();
   	    $this->checkwxuser();
    }
    //8.3 个人中心点击我的收藏判断登录
    function collect(){
        $this->checkwxweb();
        $this->checkwxuser();
    }
    //8.3 个人中心点击绑定手机号判断登录
    function binding(){
        $this->checkwxweb();
        $this->checkwxuser();
    }

    //8.3 闪惠支付页面支付数据处理
    function gotopayhui(){
        $orderid = intval(IReq::get('orderid'));
        $payerrlink = IUrl::creatUrlEx($this->mid,'wxsite/subpayhui/orderid/'.$orderid);
        $errdata = array('paysure'=>false,'reason'=>'','url'=>'');

        if(empty($orderid)){
            $backurl = IUrl::creatUrlEx($this->mid,'wxsite/index');
            $errdata['url'] = $backurl;
            $errdata['reason'] = '订单获取失败';
            $errdata['paysure'] = false;
            $this->showpayhtml($errdata);

        }
        $userid = empty($this->member['uid'])?0:$this->member['uid'];
        if($userid == 0){
                $errdata['url'] = $payerrlink;
                $errdata['reason'] = '订单操作无权限';
                $errdata['paysure'] = false;
                $this->showpayhtml($errdata);
        }
        $orderinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shophuiorder where id=".$orderid."  ");  //获取主单
        //	print_r($orderinfo);
        if(empty($orderinfo)){
            $errdata['url'] = $payerrlink;
            $errdata['reason'] = '订单数据获取失败';
            $errdata['paysure'] = false;
            $this->showpayhtml($errdata);
        }
        if($userid > 0){
            if($orderinfo['uid'] !=  $userid){
                $errdata['url'] = $payerrlink;
                $errdata['reason'] = '订单不属于您';
                $errdata['paysure'] = false;
                $this->showpayhtml($errdata);
            }
        }
        if($orderinfo['paytype'] == 0){

            $errdata['url'] = $payerrlink;
            $errdata['reason'] = '此订单的支付类型不可操作';
            $errdata['paysure'] = false;
            $this->showpayhtml($errdata);

        }
        if($orderinfo['status']  > 1){

            $errdata['url'] = $payerrlink;
            $errdata['reason'] = '此订单状态不可操作';
            $errdata['paysure'] = false;
            $this->showpayhtml($errdata);

        }
        //
        $paydotype = IFilter::act(IReq::get('paydotype'));


        $paylist = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."paylist where  loginname = '".$paydotype."' and (type = 0 or type=2) order by id asc limit 0,50");

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
        $dopaydata = array('type'=>'yhorder','upid'=>$orderid,'cost'=>$orderinfo['sjcost'],'source'=>2,'paydotype'=>$paydotype);//支付数据
        include_once($paydir.'/pay.php');
        //调用方式  直接调用支付方式
        exit;
    }


	//新增微信端积分兑换商品详情页面   2016-6-27  lzh
	function giftinfo(){
		$id = intval(IReq::get('id'));
		$data['list'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."gift where id = ".$id);
		Mysite::$app->setdata($data);
	}
	
// 8.4新增 关注微信领取优惠券功能  2016-07-15
function gzwx(){    
 		$wxclass = new wx_s();
		$signPackage = $wxclass->getSignPackage($this->mid);
 		$data['signPackage'] = $signPackage;
		
		$uid = $this->member['uid'];
		 
		 
		$shareinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."juanshowinfo where type=4 order by orderid asc  ");
		 
		if( empty($shareinfo) ){
			$shareinfo['title'] = Mysite::$app->config['sitename'];
			$shareinfo['img'] = Mysite::$app->config['sitelogo'];
			$shareinfo['describe'] = Mysite::$app->config['sitename'];
		}
		$data['shareinfo'] = $shareinfo;
	
		#$orderid = intval(IReq::get('did'));
		$orderinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where uid=".$uid."  ");
		 
		$where = "  where type=2 and addtime < ".time()." and endtime > ".time()." and is_open = 1 and juannum > 0 ";
 		$checkinfosendjuan = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."juanrule ".$where." order by orderid asc ");
		
		
		$historyphone =   $_COOKIE['historyphone'];  //  ICookie::get('historyphone');
 		if( !empty($historyphone) ){
			$juanlist =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."juan where type = 5 and   bangphone = '".$historyphone."' ");
		}else{
			$juanlist = array();
		}
 		$data['checkinfosendjuan'] = $checkinfosendjuan;
 		$data['historyphone'] = $historyphone;
		$data['juanlist'] = $juanlist;
		$data['orderinfo'] = $orderinfo;
		Mysite::$app->setdata($data);
		
	}
	function receivgzwxjuan(){    
		
		$orderid = intval(IReq::get('orderid'));
		$phone = IFilter::act(IReq::get('phone'));
		
		#$orderinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where id=".$this->member['uid']."  ");
		#if(empty($orderinfo)) $this->message('获取失败，请刷新页面尝试~');
		
		if(empty($phone)) $this->message('请填写领取使用的手机号~');
		if(!(IValidate::suremobi($phone)))$this->message('请填写正确的手机号~');
		
		$checkisrec =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."juan where  type = 5 and  bangphone = '".$phone."' ");
		if( !empty($checkisrec) )  {
			$this->success('success');
		}
		
	 	$where = "  where type=4 and addtime < ".time()." and endtime > ".time()." and is_open = 1 and juannum > 0 ";
 		$checkinfosendjuan = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."juanrule ".$where." order by orderid asc ");
 		$sendjuanlist = array();
		if(!empty($checkinfosendjuan)){	 	
				for($i=1;$i<=$checkinfosendjuan['juannum'];$i++ ){  
					$juanjiancost = rand( $checkinfosendjuan['jiancostmin'] ,$checkinfosendjuan['jiancostmax'] );
					$juanjiacost = rand( $checkinfosendjuan['jiacostmin'] ,$checkinfosendjuan['jiacostmax'] ); 
  					$temparray = array();
					$temparray['limitcost']  = $juanjiancost;
					$temparray['cost']  = $juanjiacost; 
					$sendjuanlist[] = $temparray; 
 				} 
		}
		$memberinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where phone = ".$phone."   ");
		
		if( !empty($sendjuanlist) ){
						foreach($sendjuanlist as $key=>$val){ 
							
							$data['creattime'] = time();
							$data['cost'] = $val['cost'];
							$data['limitcost'] = $val['limitcost'];
							$data['endtime'] = $checkinfosendjuan['endtime'];
							if( !empty($memberinfo) ){
								$data['uid'] = $memberinfo['uid'];
								$data['username'] = $memberinfo['username'];
								$data['status'] = 1;
							}else{
								$data['status'] = 0;
							}							
							$data['bangphone'] = $phone;
 							$data['name'] = "满".$val['limitcost']."元减".$val['cost']."元";
							$data['type'] = 5;
							$data['paytype'] = $checkinfosendjuan['paytype'];
 							$this->mysql->insert(Mysite::$app->config['tablepre'].'juan',$data);  
						}
		}
		 
		$this->success('success');
	}
	
  public function mealTable(){
  	$storeid = intval(IReq::get('sid'));
  	$tableid = intval(IReq::get('id'));
  	ICookie::set('tableid',$tableid,2592000);
  	$where = " and id ='".$storeid."' or sid ='".$storeid."'";
  	$tempdd[] =   $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where is_pass = 1  and is_open = 1  ".$where."");

  	$tempdd = $tempdd[0];
  	$table = 'eat_table';
  	$data = array();
  	if( $tableid !=''){
 		$searchdata = array();
 		$searchdata['id'] = $tableid;
 		/* $url = Mysite::$app->config['banklay_url'].'/getShopTableInfo/table/eat_table';
 		$tableinfo = https_request($url,$searchdata);
 		if($tableinfo){
 			$tableinfo = json_decode($tableinfo,true);
 			$data['name'] =$tableinfo['name'];
 			$data['sn'] =$tableinfo['sn'];
	    } */
 		$rs = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."table where id = ".$tableid."   ");
 		$sn = "";
 		if(!empty($rs)){
 			$set = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."table_qrcode_set where tableid = '{$rs["id"]}'   ");
 			if($set){
 				foreach ($set as $list){
 					if($sn == ""){
 						$sn = $list['sn'];
 					}
 					else
 					{
 						$sn = $sn.",".$list['sn'];
 					}
 				}
 			}
 			$data['name'] = $rs['name'];
 		}
 		$data['sn'] = $sn;
	}
	
	$data['shopid'] = $storeid;	 
  	$data['shopinfo'] = $tempdd;
  	$data['wifi']     = unserialize($tempdd['wifi_info']);	
  	$data['storeid']  = $tempdd['id'];
  	$data['tableid']  = $tableid;
  	
	//小二广告,活动
	$ad1Sql = "select * from ".Mysite::$app->config['tablepre']."adv  where advtype='ad1'  and shopid='".$tempdd['id']."' order by id desc limit 3";
	$ad1    = $this->mysql->select_one($ad1Sql);
	$ad2Sql = "select * from ".Mysite::$app->config['tablepre']."adv  where advtype='ad2'  and shopid='".$tempdd['id']."' order by id desc limit 3";
	$ad2    = $this->mysql->select_one($ad2Sql);
	$ad3Sql = "select * from ".Mysite::$app->config['tablepre']."adv  where advtype='ad3'  and shopid='".$tempdd['id']."' order by id desc limit 3";
	$ad3    = $this->mysql->select_one($ad3Sql);
	$ad4Sql = "select * from ".Mysite::$app->config['tablepre']."adv  where advtype='ad4'  and shopid='".$tempdd['id']."' order by id desc limit 3";
	$ad4    = $this->mysql->select_one($ad4Sql);
	$ad5Sql = "select * from ".Mysite::$app->config['tablepre']."adv  where advtype='ad5'  and shopid='".$tempdd['id']."' order by id desc limit 3";
	$ad5    = $this->mysql->select_one($ad5Sql);
	$ad6Sql = "select * from ".Mysite::$app->config['tablepre']."adv  where advtype='ad6'  and shopid='".$tempdd['id']."' order by id desc limit 3";
	$ad6    = $this->mysql->select_one($ad6Sql);

	$data['ad1']   = $ad1;
	$data['ad2']   = $ad2;
	$data['ad3']   = $ad3;
	$data['ad4']   = $ad4;
	$data['ad5']   = $ad5;
	$data['ad6']   = $ad6;

	 
	//获取 是否会员卡
	if (!empty($this->member['card_openid'])) {
		$params = array();
		$params ['mid'] = $this->mid;
		$params ['openid'] = $this->member['card_openid'];
			
		if (api_post_isMemberOfMerchant( $params, $return_code, $return_msg, $return_data )) {
			if ($return_code==1 && $return_data) {
				$couponList = array();
				$data['is_member']   = $return_data['is_member'];
				$data['activie_member_card']   = $return_data['activie_member_card'];
			}
		}
	}
	//获取会员卡、卡券信息
	$params = array();
	$params ['mid'] = $this->mid;
	$params ['coupon_type'] = "CASH,DISCOUNT";
	
	if (api_post_getCouponListOfMerchant ( $params, $return_code, $return_msg, $return_data )) {
		if ($return_code==1 && $return_data) {
			$couponList = array();
			$needCount = 0;
			foreach($return_data as $cardInfo) {
				$arrTmp = array();
				$arrTmp['id'] =  $cardInfo['id'];
				$arrTmp['title'] =  $cardInfo['name'];
				$arrTmp['expire'] =  "有效期:".$cardInfo['deadline'];
				if ($cardInfo['type'] == 'CASH') {
					$arrTmp['discount'] =  "￥".round($cardInfo['amount']/100);
				}
				else if ($cardInfo['type'] == 'DISCOUNT') {
					if ( $cardInfo['discount'] >= 100) {
						$cardInfo['discount'] = 0;
					}
					$arrTmp['discount'] =  ($cardInfo['discount']*10).' 折';
				}
				$couponList[] = $arrTmp;
				$needCount++;
				if ($needCount > 2) {
					break;
				}
			}
			$data['couponlist']   = $couponList;
	
		}
	}
	
	$wxclass = new wx_s();
	$signPackage = $wxclass->getSignPackage($this->mid);
	$data['signPackage'] = $signPackage;
	
  	Mysite::$app->setdata($data);
  }
 
	public function callService($sn,$tableid,$sid,$title,$call_type,$tablename,$back) {
		$sn 	 	= $sn ? $sn : IReq::get ( 'sn' );
		$tableid 	= $tableid ? $tableid : IReq::get ( 'tableid' );
		$sid 		= $sid ? $sid : IReq::get ( 'sid' );
		$tablename 	= $tablename ? $tablename :IReq::get ( 'tablename' );
		$title 		= $title ? $title : IReq::get ( 'title' );
		$call_type 	= $call_type ? $call_type : IReq::get ( 'call_type' );
		$mid = $this->mid;
		$orderlist = array();
		//找出呼叫人uid
		$openid = ICookie::get('wxopenid'.$this->mid.'_'.$this->appid);
		$wxuserinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."wxuser where mid = '".$this->mid."' and openid ='".$openid."'  ");
		$uid = empty($wxuserinfo['uid'])?0:$wxuserinfo['uid'];
		$nickname = empty($wxuserinfo['nickname'])?'未知':$wxuserinfo['nickname'];
		//接单服务需要把订单号带上
		if($call_type == '3'){
			$order_info = array();
			
			$sql = "select c.id as orderid,c.tableid,c.dno,c.addtime,c.allcost total
			from ".Mysite::$app->config['tablepre']."table_qrcode_set a, ".Mysite::$app->config['tablepre']."table b, ".Mysite::$app->config['tablepre']."order c
			where a.tableid = b.id and b.mid = c.mid
			and b.shopid = c.shopid and c.paystatus=0
			and c.is_preeat=1 and b.id = c.tableid
			and c.addtime >= UNIX_TIMESTAMP(date_sub(now(),interval 1 day))
			and a.tableid = '{$tableid}' and c.buyeruid ='{$uid}' group by c.dno";

			$orderlist = $this->mysql->getarr ( $sql );
			
			/* if($orderlist[0]['orderid'] =='' || empty($orderlist[0]['orderid']) || $orderlist[0]['orderid'] ==null){
				$appresult = new AppResultclass();
		        $appresult->code = 1;
				$appresult->message ="请下单再结单";
				echo $appresult->returnJSON($appresult);
			} *///排除线下下单情况
			foreach ($orderlist as $k=>$v){
				$orderlist[$k]['orderid'] = $v['orderid'];
				$orderlist[$k]['tableid'] = $v['tableid'];
				$orderlist[$k]['dno'] = $v['dno'];
				$orderlist[$k]['allcost'] = $v['total'];
				$orderlist[$k]['addtime'] = date('Y-m-d H:i:s',$v['addtime']);
			}
		}
		$pushdata = array ();
		$pushdata ['call_type'] = $call_type;
		$pushdata ['order_info'] = json_encode($orderlist);
		$pushdata ['sn'] = $sn;
		$pushdata ['mid'] = $mid;
		$pushdata ['sid'] = $sid;
		$pushdata ['tableid'] = $tableid;
		$pushdata ['tablename'] = $tablename;
		$pushdata ['nickname'] = $nickname;
		$pushdata ['title'] = $title;
		$url = Mysite::$app->config ['banklay_url'] . '/pushOrderNews';
		$tableinfo = https_request( $url, $pushdata );
		//返回格式区别
		if($back){
			return $tableinfo;
		}else{
			echo $tableinfo;
			die ();
		}
	}

	
      //排号详情
      function phdetails(){
              $shopid = intval(IReq::get('shopid'));
              $mid = $this->mid;
              $username = $this->member['username'];
              $storeid = $shopid;
              $lng = ICookie::get('lng');
              $lat = ICookie::get('lat');
              $addressname = ICookie::get('addressname');

              $lat = empty($lat)?0:$lat;
              $lng = empty($lng)?0:$lng;
              $wxclass = new wx_s();

              $signPackage = $wxclass->getSignPackage($this->mid);

              $data['signPackage'] = $signPackage;
              if(empty($addressname)){
                      $addressname = '' ;
              }
              $where = " and id ='".$storeid."' ";
              $tempdd[] =   $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where is_pass = 1  and is_open = 1   ".$where."    ");
              $tempdd = $tempdd[0];
      //var_dump($tempdd);
              $data['shopinfo'] = $tempdd;
              $data['addressname'] = $addressname;
              //$data['mid'] = $mid;
              $data['lat'] = $lat;
              $data['lng'] = $lng;
              $data['storeid'] = $storeid;
              $mi = $this->GetDistance($lat,$lng, $data['lat'],$data['lng'], 1);
              $tempmi = $mi;
              $mi = $mi > 1000? round($mi/1000,2).'km':$mi.'m';
              $data['juli'] = 		$mi;

            //排号信息
            $sid = intval(IReq::get('sid'));
            $mid = $this->mid;
                
            $krdl_arr = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."table_krdl  where mid='".$mid."' and shopid='".$shopid."' and wxname='".$username."' and status=1 and to_days(addtime) = to_days(now()) ");
            $count = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."table_krdl where mid='".$mid."' and shopid='".$shopid."' and phsz_id='".$krdl_arr['phsz_id']."' and status=1 and to_days(addtime) = to_days(now())");
 
              //$count = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."table_krdl where mid='".$mid."' and sid='".$sid."' and status=1 and to_days(addtime) = to_days(now()) ");
            
            $data['count'] = $count? $count-1 : 0;
            
            $sql = "select * from ".Mysite::$app->config['tablepre']."table_krdl where mid='".$mid."' and shopid='".$shopid."' and wxname='".$username."' and to_days(addtime) = to_days(now())";
            $data['ndhm'] = $this->mysql->select_one($sql);
            $sql1 ="select * from ".Mysite::$app->config['tablepre']."table_krdl where mid='".$mid."' and shopid='".$shopid."' and status=1 and phsz_id='".$krdl_arr['phsz_id']."' and to_days(addtime) = to_days(now()) order by id asc";
            $data['yjh'] = $this->mysql->select_one($sql1);
              
            Mysite::$app->setdata($data);
       }
       
      //排号通知自动提醒
      function lineup_notice(){
        $uid = $this->member['uid'];
//        $krdllist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."table_krdl  where status=1 and to_days(addtime) = to_days(now()) order by id desc");
        
        

//             foreach($krdllist as $key=>$val){
                
            $krdl_arr = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."table_krdl  where  uid='".$uid."' and status=1 and to_days(addtime) = to_days(now()) ");

            $count =$this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."table_krdl where phsz_id='".$krdl_arr['phsz_id']."' and status=1 and to_days(addtime) = to_days(now())");
         
            $cxinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."table_phsz where id = '".$krdl_arr['phsz_id']."'  order by id desc limit 0, 100");
            
              if($count){
              $count = $count - 1;
              }else{
                $count= 0;  
              }

//             echo $count;
//             echo $cxinfo['tqtzrs'];
                 if($count=$cxinfo['tqtzrs']){
                    $krdl_info = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."wxuser where uid='".$krdl_arr['uid']."' ");
                    sendCustomMsg($this->mid, $this->SendWxMsg($krdl_arr['uid'],$krdl_arr['shopid']),$krdl_info['openid']);//发送微信信息
                 }
           
                   
//               }
          
      }
      
        //排号微信内容
        function SendWxMsg($uid=NULL,$shopid=NULL){
            if(empty($uid)) $this->message('微信信息发送失败！');
            //$shopid = 214;
            $mid = $this->mid;
             
            $cxshop =   $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where id ='".$shopid."' ");
              $krdl_arr = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."table_krdl  where shopid='".$shopid."' and uid='".$uid."' and status=1 and to_days(addtime) = to_days(now()) order by id desc");
              $count =$this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."table_krdl where shopid='".$shopid."' and phsz_id='".$krdl_arr['phsz_id']."' and status=1 and to_days(addtime) = to_days(now())");
              if($count){
              $count = $count - 1;
              }else{
                $count = 0;  
              }
            
            $str = "排号提醒\n";
            $str .= "门店：".$cxshop['shopname']."\n";
            $str .= "排号时间：".date('H:i:s',$krdl_arr['start_queue_time']) ."\n";
            $str .= "排队号码：".$krdl_arr['qhms']."\n";
            $str .= "前面等待：".$count."\n";
            $str .= "请留意微信平台排号变化通知若过号，在现场叫号基础上顺延三桌安排\n";
            $link = IUrl::creatUrlEx($this->mid,'wxsite/phdetails/shopid/'.$shopid);
            $str .= "<a href='".$link."'>查看详情</a>";
            
            return $str;
        }
        
        //预订微信内容
        function SendWxMsg_yd($uid=NULL,$shopid=NULL,$orderid=NULL){
            if(empty($uid)) $this->message('微信信息发送失败！');
            
            $info = $this->mysql->select_one("select b.shopname,a.booktime,a.table_area,a.table_type from ".Mysite::$app->config['tablepre']."order a, ".Mysite::$app->config['tablepre']."shop b where a.shopid = b.id and a. id = '".$orderid."'");
            
            $str = "在线预订成功提醒\n";
            $str .= "门店：".$info['shopname']."\n";
            $str .= "预订时间：".date('Y-m-d H:i',$info['booktime']) ."\n";
            $str .= "区域：".$info['table_area']."\n";
            $str .= "桌型：".$info['table_type']."人桌\n";
            $str .= "请按照预订时间到店消费，超过规定时间将自动取消订单\n";
            //$link = IUrl::creatUrlEx($this->mid,'wxsite/phdetails/shopid/'.$shopid);
            //$str .= "<a href='".$link."'>查看详情</a>";
            
            return $str;
        }


    //打赏相关 ******************************************************************************************************************
    //打赏首页
	public  function  weixin_reward_index(){
		$this->checkwxweb();

		$sn         = IReq::get('sn');
		//$payment_no = $this->mid.time() . rand(100, 999) . rand(10000, 99999);
		$payment_no = IReq::get('payment_no'); 
		if(empty($sn)){
			 $this->message('参数错误~');
		}
 		//操作员信息
		$empSql  = "select s.*,sqs.sn from ".Mysite::$app->config['tablepre']."staff as s left join ".Mysite::$app->config['tablepre']."staff_qrcode_set as sqs on sqs.staffid =s.id where sqs.sn='".$sn."' ";
		$empInfo = $this->mysql->select_one($empSql);
 
		//打赏设置信息		
		$rewardSql  = "select * from ".Mysite::$app->config['tablepre']."weixin_reward_set where mid='".$this->mid."' and shopid= '".$empInfo['shopid']."' ";
		$rewardInfo = $this->mysql->select_one($rewardSql);

		if(empty($rewardInfo['memo'])){
			$rewardInfo['memo']="你的赞赏是对我服务最大的肯定！";
		}

	/*	session_start();
		$_SESSION['payment_no'] = $payment_no;  */
 
	    $data['empInfo']    = $empInfo;
 		$data['rewardInfo'] = $rewardInfo;
 		$data['payment_no'] = $payment_no;
 		$data['sn']         = $sn;
		Mysite::$app->setdata($data);

	}

	//打赏,点评页面by yanyh
	public  function  weixin_reward(){ 
	    $sn    		 = IReq::get('sn'); 
	    $type  		 = IReq::get('Ctype');
	    $payment_no  = IReq::get('payment_no');
	    //操作员信息
		$empSql  = "select s.*,sqs.sn from ".Mysite::$app->config['tablepre']."staff as s left join ".Mysite::$app->config['tablepre']."staff_qrcode_set as sqs on sqs.staffid =s.id where sqs.sn='".$sn."' ";
		$empInfo = $this->mysql->select_one($empSql);
		if($empInfo){
			if(empty($empInfo['picurl']) || $empInfo['picurl']==''|| $empInfo['picurl']==null){
				$empInfo['picurl'] = '/images/reward/a1.jpg';
			} 
			if(empty($empInfo['applypost']) || $empInfo['applypost'] ==null || $empInfo['applypost'] ==''){
				$empInfo['applypost']="服务员";
			}
	    }

	 	$mid = $empInfo['mid'];

	 	session_start();
	    $p_no = $_SESSION['pno'.$payment_no];
	
	    //写入打赏表
	    $rewardData['AID']      = '';//!empty($result['aid'])?$result['aid']:'';
	    $rewardData['MID']      = !empty($mid)?$mid:'';
	    $rewardData['SN']       = !empty($sn)?$sn:'';
	    $rewardData['Operator'] = !empty($empInfo['name'])?$empInfo['name']:'';
	    $rewardData['SID'] 		=  !empty($empInfo['sid'])?$empInfo['sid']:'';
	    $rewardData['shopid'] 		=  !empty($empInfo['shopid'])?$empInfo['shopid']:'';
	    $rewardData['payment_no'] = $payment_no;;
	    $rewardData['payment_time'] = $payment_no;;
	    $rewardData['time']     = date('Y-m-d H:i:s',time());
	    $rewardData['nickname']     = $this->member['username'];
	    $rewardData['headimgurl']   = $this->member['logo'];
	    $rewardData['sex']   		= $this->member['sex'];

   		//是否第一次进入
	    if(!$p_no){
	    	$return_str = $this->mysql->insert(Mysite::$app->config['tablepre']."weixin_reward",$rewardData);
			$_SESSION['pno'.$payment_no] = $payment_no;  
		}else{
			$return_str = $this->mysql->update(Mysite::$app->config['tablepre']."weixin_reward",$rewardData,"payment_no= '".$payment_no."'");
		}
  
    	$recordSql = "select * from ".Mysite::$app->config['tablepre']."weixin_reward where payment_no='".$payment_no."' order by id desc ";
    	$recordInfo = $this->mysql->select_one($recordSql);
 
	  
		//打赏设置信息
		$rewSetSql    = "select * from ".Mysite::$app->config['tablepre']."weixin_reward_set where mid='".$empInfo['mid']."' and shopid='".$empInfo['shopid']."' order by id desc ";
	    $re_array_set = $this->mysql->select_one($rewSetSql);
	
		if(empty($re_array_set['price1']) || $re_array_set['price1'] ==null || $re_array_set['price1'] ==''){
			$re_array_set['price1']="10";
		}
		if(empty($re_array_set['price2']) || $re_array_set['price2'] ==null || $re_array_set['price2'] ==''){
			$re_array_set['price2']="5";
		}
		if(empty($re_array_set['price3']) || $re_array_set['price3'] ==null || $re_array_set['price3'] ==''){
			$re_array_set['price3']="2";
		}
		if(empty($re_array_set['price4']) || $re_array_set['price4'] ==null || $re_array_set['price4'] ==''){
			$re_array_set['price4']="1";
		}
		if(empty($re_array_set['price5']) || $re_array_set['price5'] ==null || $re_array_set['price5'] ==''){
			$re_array_set['price5']="0.1";
		}
		if(empty($re_array_set['comment1']) || $re_array_set['comment1'] ==null || $re_array_set['comment1'] ==''){
			$re_array_set['comment1']="很好";
		}
		if(empty($re_array_set['comment2']) || $re_array_set['comment2'] ==null || $re_array_set['comment2'] ==''){
			$re_array_set['comment2']="好";
		}
		if(empty($re_array_set['comment3']) || $re_array_set['comment3'] ==null || $re_array_set['comment3'] ==''){
			$re_array_set['comment3']="一般";
		}
		if(empty($re_array_set['comment4']) || $re_array_set['comment4'] ==null || $re_array_set['comment4'] ==''){
			$re_array_set['comment4']="差";
		}
		if(empty($re_array_set['comment5']) || $re_array_set['comment5'] ==null || $re_array_set['comment5'] ==''){
			$re_array_set['comment5']="很差";
		}
		if(empty($re_array_set['memo']) || $re_array_set['memo'] ==null || $re_array_set['memo'] ==''){
			$re_array_set['memo']="你的赞赏是对我服务最大的肯定！";
		}

		$data['re_array_set'] = $re_array_set;
		$data['empInfo'] = $empInfo;
		$data['recordInfo'] = $recordInfo;
		$data['payment_no'] = $payment_no;
		$data['sn'] = $sn;

	    //$this->assign('info',$re_array);
	    if($type == 'dz'){
	    	Mysite::$app->setdata($data);
			Mysite::$app->setAction('weixin_dianzan');
	  	}else{ 
			Mysite::$app->setdata($data);
			Mysite::$app->setAction('weixin_reward');
	 
	  	}
 

	}

	//提交点评
	public  function  weixin_reward_commit(){
	    $type  = IReq::get('Ctype');

	    if($type =="dz"){
	        $data['start']   = $score    = IReq::get('score');   
	        $data['comment'] = $dianping = IReq::get('dianping');  
	        $payment_no 	 = IReq::get('payment_no');  

 			$rs = $this->mysql->update(Mysite::$app->config['tablepre']."weixin_reward",$data,"payment_no= '".$payment_no."'");

 			$siteurl = Mysite::$app->config['siteurl']."/index.php/".$this->mid; 
	        if(false !== $rs){
	        	$this->success($siteurl);
	        }else{
	            $this->message('保存失败');
	        }
	      
	    }
    
	}

	//赏金排行，小二排行
	public function employeeList(){
	    $sn  = IReq::get('sn');
	    $mid = $this->mid;

	    $recordSql = "select id,AID,MID,Operator,SID, round(AVG(start),2) start,comment,SUM(fee) tfee from ".Mysite::$app->config['tablepre']."weixin_reward where MID ='".$mid."' and successed='Y' and SN='{$sn}' group by Operator order by tfee DESC ";
    	$recordList = $this->mysql->getarr($recordSql);

	    // if($recordList){
	    //     foreach ($recordList as $k=>$v){
	    //         $recordList[$k]['tfee'] = ($v['tfee']/100);
	    //     }
	    // }
 
        $data['sn']   = $sn;
        $data['recordList'] = $recordList;
	    Mysite::$app->setdata($data);
	}
	//打赏排行
	public function rankingList(){
	    $sn  = IReq::get('sn');
	    $mid = $this->mid;
	   	 
	    $recordSql = "select MAX(nickname) nickname,MAX(headimgurl) headimgurl,SUM(fee) tfee from ".Mysite::$app->config['tablepre']."weixin_reward where MID ='".$mid."' and successed='Y' and SN='{$sn}' group by nickname order by tfee DESC ";

    	$recordList = $this->mysql->getarr($recordSql);

		$re_array = array();
	    if($recordList){
	        $re_array =$recordList;
	        foreach ($re_array as $k=>$v){
	            $re_array[$k]['fee'] =$v['tfee'];
				if(!empty($v['nickname']) && $v['nickname'] != null){
					$re_array[$k]['nickname'] = $v['nickname'];
				}
				else{
					$re_array[$k]['nickname'] = "匿名";
				}
				if(!empty($v['headimgurl']) && $v['headimgurl'] != null){
					$re_array[$k]['headimgurl'] = $v['headimgurl'];
				}
				else{
					$re_array[$k]['headimgurl'] = '/newstyle/images/reward/avatar.png';
				}

	        }
	    }

	    $data['sn']   = $sn;
        $data['re_array'] = $re_array;
	    Mysite::$app->setdata($data);
	 
	}
	//打赏记录
	public function playRecord(){
	    $sn  = IReq::get('sn');
	    $mid = $this->mid;

	    $recordSql = "select * from ".Mysite::$app->config['tablepre']."weixin_reward where MID ='".$mid."' and  successed='Y' and fee >0 and SN='{$sn}'";
    	$recordList = $this->mysql->getarr($recordSql);

	    $re_array = array();
	    if($recordList){
	        $re_array =$recordList;
	        foreach ($re_array as $k=>$v){
	            $re_array[$k]['time'] = date('m-d H:i',strtotime($v['time']));
				if(!empty($v['nickname']) && $v['nickname'] != null){
					$re_array[$k]['nickname'] = $v['nickname'];
				}
				else{
					$re_array[$k]['nickname'] = "匿名";
				}
				if(!empty($v['headimgurl']) && $v['headimgurl'] != null){
					$re_array[$k]['headimgurl'] = $v['headimgurl'];
				}
				else{
					$re_array[$k]['headimgurl'] = '/newstyle/images/reward/avatar.png';
				}
	        }
	    }
	    $data['sn']   = $sn;
        $data['re_array'] = $re_array;
 
	    Mysite::$app->setdata($data);
	}

	//立即打赏
	public function rewardpayment(){

		$this->checkwxuser();
		if( $this->checkbackinfo() ){
			if($this->member['uid'] == 0||empty($this->mid))  $this->message('未登陆'); 
	    } 
	 
	    $data['id'] = $id  = IReq::get('id');
	    $data['fee'] = $price  = IReq::get('price');
	    $data['comment'] = $dianping  = IReq::get('dianping');
	    $data['start'] = $score  = IReq::get('score');

		$rs = $this->mysql->update(Mysite::$app->config['tablepre']."weixin_reward",$data,"id= '".$id."'");

	    // $recordSql = "select * from ".Mysite::$app->config['tablepre']."weixin_reward where MID ='".$mid."' and id='".$id."'";
    	// $recordInfo = $this->mysql->select_one($recordSql);

    	//add by rain will *********************************************************
	   	$paymethod 				 = 'W';
	   	$params    				 = array();
	   	$params['out_trade_no'] = '3_'.$id;
	   	$params['body'] 		 = "打赏下单";
	   	$params['mid'] 		 = $this->mid;
	   	$params['order_type'] 	 = 1;
	   	$params['attach'] =  "3;";
// 	   	$params['pay_type'] = $paymethod;
// 	   	$params['trade_type'] = 'JSAPI';
	   	$params['order_amount'] =  $price*100;
       	$params['pay_amount']   =  $price*100;

		$order_sn ='';
		if (api_post_order_add ( $params, $return_code, $return_msg, $return_data )) {
			if ($return_code==1 && $return_data) {
				$updateData = array();
				$updateData['payment_no'] = $return_data['order_id'];
				$order_sn =  $return_data['order_sn'];
				$this->mysql->update(Mysite::$app->config['tablepre'].'weixin_reward',$updateData,"id='".$id."'");	
		
				//支付链接
				$wxclass = new wx_s();
				$retUrl = IUrl::creatUrlEx($this->mid,'wxsite/rewardpayover/orderid/'.$id);
				$url    = getWxJSApiPayUrl ($return_data['order_id'],$retUrl);
				 
				$this->success($url);  
 				return; 
			}
		}
                
	   	$opMsg=empty($return_msg)?'统一支付下单失败':$return_msg;	   
	   	$this->message($opMsg);
		exit; 
 
	}

	//打赏完成
	function rewardpayover(){
		$orderid = intval(IReq::get('orderid'));
		$userid = empty($this->member['uid'])?0:$this->member['uid'];
		if(empty($orderid)) $this->message('打赏订单获取失败');
	 
		if($orderid < 1){
			$this->message('订单获取失败');
		}

		$order = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."weixin_reward where id = ".$orderid."");

		if($order['successed'] == 'N'){
			$this->syncRewardPayStatus($order);

	 		$data['successed'] = 'Y';
			$ret = $this->mysql->update(Mysite::$app->config['tablepre'].'weixin_reward',$data,"id='".$orderid."'");	
		}

		if(false !== $ret){
			$link = IUrl::creatUrlEx($this->mid,'wxsite/employeeList/sn/'.$order['SN']); 		
			$this->message('打赏成功',$link);
		}
	
	 	
	}
	//重新检查
	private function syncRewardPayStatus($order) {
		if($order['successed']=='N'){
			$return_code='';
			$return_msg='';
			$return_data= null;
			$params =array();
			$params ['order_id'] = $order['payment_no'];
			if (queryOrder ( $params, $return_code, $return_msg, $return_data )) {
				if ($return_code==1 && $return_data&&$return_data['pay_status'] >0) {
					$transaction_id = $return_data['transaction_id'];
					if (empty($transaction_id)) {
						$transaction_id='';
					}
					$payTypeAndName = getPayTypeAndName($return_data['pay_type']);
						
					$diTmp = array();
					$diTmp['successed'] = 'Y'; 
					$this->mysql->update(Mysite::$app->config['tablepre'].'weixin_reward',$diTmp,"id='".$order['id']."' and `payment_no`=".$return_data['order_id']."");
						
				}
			}
		}
		
	}

	

}

class AppResultclass {

	public $code;

	public $message;

	public $data;

	public function __construct() {
		$this->code = 1;
		$this->message = '有错误，请重试！';
		$this->data = array();
	}

	/**
	 * 输出JSON头和数据
	 */
	public function returnJSON(){
		header('Content-type: text/json');
		$data =  json_encode($this, JSON_UNESCAPED_UNICODE);
		exit($data);
	}
}
