<?php
class method   extends adminbaseclass
{
	 function savearea(){
	   
	  $mid = $this->mid;
	  $id = intval(IReq::get('uid'));
	    $data['mid'] = $mid;
		$data['name'] = IReq::get('name');
		$data['orderid']  = intval(IReq::get('orderid'));
		$data['pin'] = IReq::get('pin');
		$data['parent_id'] = intval(IReq::get('parent_id'));
		$data['imgurl'] = IReq::get('imgurl');
		$data['is_com'] = intval(IReq::get('is_com'));
		if(empty($id))
		{
			$data['lng'] = 0;
		  $data['lat'] = 0;
			$link = IUrl::creatUrl('area/adminarealist');
			if(empty($data['name']))  $this->message('area_emptyname',$link);
			if(empty($data['pin']))	$this->message('area_emptyfirdstword',$link);
			//if($data['parent_id'] == 0 && empty($data['imgurl']))$this->message('area_emptyimg',$link);
      if($data['parent_id'] > 0){
        $checkarea = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."area where id =".$data['parent_id']."");
        $data['admin_id'] = $checkarea['admin_id'];
      }
			if(!$this->mysql->insert(Mysite::$app->config['tablepre'].'area',$data)){
			   $this->message('system_err');
			} 
		}else{
			$link = IUrl::creatUrl('area/adminarealist/id/'.$id);
			if(empty($data['name']))  $this->message('area_emptyname',$link);
			if(empty($data['pin']))	$this->message('area_emptyfirdstword',$link);
			//if($data['parent_id'] == 0 && empty($data['imgurl']))$this->message('area_emptyimg',$link);
			if($data['parent_id'] > 0){
        $checkarea = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."area where id =".$data['parent_id']."");
        $data['admin_id'] = $checkarea['admin_id'];
      }
			$this->mysql->update(Mysite::$app->config['tablepre'].'area',$data,"id='".$id."'");
		}
		$link = IUrl::creatUrl('area/adminarealist');
		$this->success('success',$link);
	 }
	 function adminareacost(){
	 	 $areaid = intval(IReq::get('areaid'));
		 $type = trim(IReq::get('type'));
		 $cost  = IFilter::act(IReq::get('cost'));
		 $cost = intval($cost*10)/10;
		 if($areaid < 1) $this->message('area_iderr');
		 if(!in_array($type,array('add','del','updat'))) $this->message('nodefined_func');
		 $areainfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."area where id =".$areaid."");
		 if(empty($areainfo)) $this->message('area_empty');
		 $insertdata['cost'] = $cost;
	   $this->mysql->update(Mysite::$app->config['tablepre'].'areatoadd',$insertdata," shopid = 0 and areaid ='".$areaid."'");
	 	 $this->success('success');
	 }
	 function adminpsset(){
	 	   $data['pestypearr'] = array(
	 	   '1'=>'网站统一配送费',
	 	   '2'=>'根据不同区域统一配送费',
	 	   '3'=>'不计算配送费',
	 	   '4'=>'百度地图根据距离测算配送费',
	 	   '5'=>'根据菜品数计算配送费'
	 	   );
	     Mysite::$app->setdata($data);
	 }
	 function savepsset(){
	 	 $locationtype =  intval(IFilter::act(IReq::get('locationtype')));
	 	 $licationmudule =  intval(IFilter::act(IReq::get('licationmudule')));
	 	 if($locationtype == 1){
	 	    $test =  Mysite::$app->config['baidumapkey'];
	 	    if(empty($test)) $this->message('setmapsecrkey');
	 	 }
	     $locationradius =  intval(IFilter::act(IReq::get('locationradius'))); 
	 	 $savearray['locationtype']  =  $locationtype;
	 	 $savearray['licationmudule']  =  $licationmudule;
	 	 $savearray['locationradius']  = $locationradius; 
		 if($savearray['locationradius'] > 30){
			 $this->message('配送半径最大30公里');
		 }
	 	 $temparray = array();
	 	 for($i=0;$i<$locationradius;$i++){
	 	   $temparray[$i] = intval(IFilter::act(IReq::get('radiusvalue'.$i))); 
	 	 }
	 	 $savearray['radiusvalue'] = serialize($temparray);
	 	 $savearray['psvalue'] = intval(IFilter::act(IReq::get('psvalue'))); 
		 
		  $savearray['psycostset']  = intval(IFilter::act(IReq::get('psycostset'))); 
		   $savearray['psycost']  = intval(IFilter::act(IReq::get('psycost'))); 
		    $savearray['psybili']  = trim(IFilter::act(IReq::get('psybili'))); 
			
		 
		 
		 $config = new config('hopeconfig.php',hopedir,$this->mid,$type=1,Mysite::$app->config['conf_table']);
	   $config->write($savearray);
	   $this->success('success');

	 }
	 function adminarealist(){
	 	$mid = $this->mid;
	 	 $areainfo = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."area where mid ='".$this->mid."'  order by orderid asc");
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

	  
	  $adminlist =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."admin where groupid='4' ");  
	 	 $temparr = array();
	 	 foreach($adminlist as $key=>$value){
	 	    $temparr[$value['uid']] = $value['username'];
	 	 }
	 	 $data['mid'] = $mid;
	 	 $data['adminlist'] = $temparr;
	   $data['adminall'] = $adminlist;
	 	 Mysite::$app->setdata($data);
	 }
	 function setareaadmin(){
	 		$areaid =  intval(IReq::get('areaid'));
	 	  $admin_id =  intval(IReq::get('adminid')); 
	 	  if(empty($areaid)) $this->message('area_empty');
	 	  $checkarea =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."area where id = '".$areaid."' ");  
	 	  if(empty($checkarea)) $this->message('area_empty');
	 	  if($checkarea['parent_id'] > 0) $this->message('area_isnotparent');
	 	  $this->getgodiguiupdata($areaid,$admin_id); 
	 	  $this->success('success'); 
   }
   function getgodiguiupdata($areaid,$admin_id){
   	   $this->mysql->update(Mysite::$app->config['tablepre'].'area',array('admin_id'=>$admin_id),"id='".$areaid."'");	 
   	   $arraylist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."area where parent_id = '".$areaid."' ");  
	 	   if(count($arraylist) > 0){
	 	      foreach($arraylist as $key=>$value){ 
	 	              $this->getgodiguiupdata($value['id'],$admin_id); 
	 	      } 
	 	   }
	 }
	 function setps(){
		$shopid =  intval(IReq::get('shopid'));
		$mid = $this->mid;
	 	  if(empty($shopid))
	 	  {
	 	  	 echo '店铺不存在';
	 	  	 exit;
	 	   }
	 	  $shopinfo= $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where id=".$shopid."  ");
	 	  if(empty($shopinfo))
	 	  {
	 	     echo '店铺不存在';
	 	  	 exit;
	 	  }
	 	  $tempinfo = array();
	 	  if($shopinfo['shoptype'] == 0){ 
	 	     $tempinfo = 	$this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast where shopid=".$shopid."  ");
	 	  }else{
	 	  	$tempinfo = 	$this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket where shopid=".$shopid."  ");
	 	  }
	   $data['shopinfo'] = $shopinfo;
	   $data['shopdetinfo'] = $tempinfo;
	   
	   
	   //地区信息 
	    $areainfo = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."area where mid='".$mid."'  order by orderid asc");
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
	 	 
	 	 
	 	 //配送费  
	 	 $data['shopvalues'] = array();
	 	 if(empty($tempinfo['pradiusvalue'])){
	 	 	 $data['shopvalues'] = unserialize(Mysite::$app->config['radiusvalue']);
	 	 }else{
	 	 	$data['shopvalues'] =  unserialize($tempinfo['pradiusvalue']);
	 	 }
	 	 $data['dolenth'] = count($data['shopvalues']);
	 	 
	 	 
	 	 
	 	 //店铺可配送区域
	 	 $choicelist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."areashop where shopid = ".$shopid."   order by areaid asc limit 0,1000");
	 	 $temp = array();
	 	 foreach($choicelist as $key=>$value){
	 	   $temp[]= $value['areaid'];
	 	 }
	 	 $data['choiceid']  = $temp; 
	 	 
	 	 $adminlist =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."admin where groupid='4' and mid='".$mid."' ");
	 	 $data['adminlist'] = $adminlist;
	   Mysite::$app->setdata($data);
	}
	function savesetps(){
		$shopid =  intval(IReq::get('shopid'));
		if(empty($shopid)){ 
			 echo "<script>parent.uploaderror('请先完善店铺的基本信息');</script>";
		 	 exit; 
		}
		$shopinfo= $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where id=".$shopid."  ");
		if(empty($shopinfo)){
			echo "<script>parent.uploaderror('请先完善店铺的基本信息');</script>";
		 	 exit; 
		}
		 $tempinfo = array();
	 	  if(empty($shopinfo['shoptype'])){ 
	 	     $tempinfo = 	$this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopfast where shopid=".$shopid."  ");
	 	  }else{
	 	  	$tempinfo = 	$this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shopmarket where shopid=".$shopid."  ");
	 	  }
	  if(empty($tempinfo)){
	  	echo "<script>parent.uploaderror('店铺数据不存在');</script>";
		 	 exit; 
	  }
	  $data['sendtype'] = intval(IReq::get('sendtype'));
	  $data['pscost'] =  intval(IReq::get('pscost'));
	  $data['pradius'] = intval(IReq::get('pradius'));
	  if($data['pradius'] > 30){
		  echo "<script>parent.uploaderror('配送半径最大30公里');</script>";
		 	 exit; 
	  }
	  $tempdo = array();
	  for($i=0;$i< $data['pradius'];$i++){
	    $tempdo[$i] = intval(IReq::get('radiusvalue'.$i));
	  }
	  $data['pradiusvalue'] = serialize($tempdo);
	   
	 if(empty($shopinfo['shoptype'])){ 
	 	   $this->mysql->update(Mysite::$app->config['tablepre'].'shopfast',$data,"shopid='".$shopid."'");
	 }else{
	 	  $this->mysql->update(Mysite::$app->config['tablepre'].'shopmarket',$data,"shopid='".$shopid."'");
	 }
	 $shopdata['pradiusa'] = intval(IReq::get('pradius'));
	 $this->mysql->update(Mysite::$app->config['tablepre'].'shop',$shopdata,"id='".$shopid."'");
	 //更新店铺配送设置
	 /*更新店铺所在管理员*/
	 $admin_id = intval(IReq::get('admin_id'));
   $this->mysql->update(Mysite::$app->config['tablepre'].'shop',array('admin_id'=>$admin_id),"id='".$shopid."'");
   
    $this->mysql->update(Mysite::$app->config['tablepre'].'member',array('admin_id'=>$admin_id),"uid='".$shopinfo['uid']."'");
   
   
	 $areaids = IReq::get('areaids');
	 $tempwhere = empty($admin_id)?'':' and admin_id =\''.$admin_id.'\'';
	 if(is_array($areaids)){
	 	   
	 	   //首先清理数据存在的区域数据
	 	   $this->mysql->delete(Mysite::$app->config['tablepre']."areashop"," shopid='".$shopid."'  "); 
	 	   $checkareais = $areaids;
	 	   while(count($checkareais) > 0){
	 	   	   $checkarealist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."area where id in(".join(',',$checkareais).") ".$tempwhere."  order by id desc limit 0,1000");
	 	   	   $checkareais = array();
	 	   	   foreach($checkarealist as $key=>$value){
	 	   	   	  $areadata['shopid'] = $shopid;
	 	   	   	  $areadata['areaid'] = $value['id']; 
	 	   	      $this->mysql->insert(Mysite::$app->config['tablepre']."areashop",$areadata);
	 	   	      if($value['parent_id'] > 0){
	 	   	        $checkareais[] = $value['parent_id'];
	 	   	      }
	 	   	   }
	 	       $checkareais = array_unique($checkareais);
	 	   }
	 	   
	 	
	 	 
  	}else{
  		if(Mysite::$app->config['locationtype'] !=1){
  		  echo "<script>parent.uploaderror('配送区域未选择');</script>";
		 	 exit; 
  		}
  			
  	}
  	 
	   echo "<script>parent.uploadsucess('');</script>";
		 exit;
	  
	}
	 //递归转换保存到数据中
	 /*
	 arraylist 数据数据
	 $nowid  当前接点的parent_ID,
	 $nowkey  当前循环次数
	 */
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
  function baidumap(){
    $id = intval(IReq::get('id'));
		 $areainfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."area where  id = '$id' order by id asc");
		 if(empty($areainfo)){
		   echo '获取信息失败';
		   exit;
		 }
		 $is_name = 1;
		 $data['arealng'] = 0;
		 $data['arealat'] = 0;
		 $data['nowcityname'] = '';
		 if(empty($areainfo['parent_id'])){
		    $data['nowcityname'] = $areainfo['name'];
		    $checklng = intval($areainfo['lng']);
		    if(!empty($checklng)){
		      $is_name = 2;
		      $data['arealng'] = $areainfo['lng'];
		      $data['arealat'] = $areainfo['lat'];
		    }

		 }else{
		 	   $data['nowcityname'] = $areainfo['name'];
		 	    $pinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."area where  id = '".$areainfo['parent_id']."' order by id asc");
		 	    if(empty($pinfo)){
		         echo '获取上级地址失败';
		         exit;
		      }
		     $checklng = intval($areainfo['lng']);
		     if(!empty($checklng)){
		        $is_name = 2;
		        $data['arealng'] = $areainfo['lng'];
		        $data['arealat'] = $areainfo['lat'];
		     }else{
		     	   $checklng2 = intval($pinfo['lng']);
		     	   if(!empty($checklng2)){
		     	     $is_name = 2;
		           $data['arealng'] = $pinfo['lng'];
		           $data['arealat'] = $pinfo['lat'];
		         }else{

		           echo '未设置上级地址百度坐标';
		           exit;
		         }
		     }



		 }
		 $data['arealng'] = $data['arealng'] == 0?Mysite::$app->config['baidulng']:$data['arealng'];
		 $data['arealat'] = $data['arealat'] == 0?Mysite::$app->config['baidulat']:$data['arealat'];
      
		 $data['is_name'] = $is_name;
		 $data['myareaid'] = $id;
		 $data['baidumapkey'] = Mysite::$app->config['baidumapkey'];
		 Mysite::$app->setdata($data);
  }
  function savemaplocation(){
  	 $id = intval(IReq::get('id'));
		if($id < 1){
		  $this->json('area_empty');
		}
		 $data['lng'] = IReq::get('lng');
		$data['lat'] = IReq::get('lat');
		if(empty($data['lng'])) $this->message('emptylng');
		if(empty($data['lat'])) $this->message('emptylat');
		$this->mysql->update(Mysite::$app->config['tablepre'].'area',$data,"id='".$id."'");
		
	  $areainfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."area where  id = '".$id."' order by id asc");
	  if(!empty($areainfo)){
	    $areasearch = new areasearch($this->mysql);    
      $areasearch->setdata($areainfo['name'],'1',$id,$areainfo['lat'],$areainfo['lng']);
      $areasearch->del();
      if($areasearch->save()){
    	
      }else{
       $checkinfo = $areasearch->error(); 
      } 
    }
	  $this->success('success');
  }
  function searchkey(){
  	$data['searchvalue'] = '';
  	$data['typearr'] = array('0'=>'未定义','1'=>'区域地址','2'=>'店铺','3'=>'用户搜索');
  	$where = "";
  	$searchvalue =  trim(IReq::get('searchvalue'));
  	$typeid = intval(IReq::get('typeid'));
  	$newlink = "";
  	$data['typeid'] = "";
  	if(!empty($searchvalue)){
  	  $data['searchvalue'] = $searchvalue;
  	  $where = "  where   datacontent like '%".$searchvalue."%' ";
  	  $newlink = "/searchvalue/".$searchvalue;
  	}
  	if(in_array($typeid,array(1,2,3))){
  	   $data['typeid'] = $typeid;
  	  $where = empty($where)?"  where  datatype='".$typeid."' ":$where."  and datatype='".$typeid."'";
  	  $newlink .= "/typeid/".$typeid;
  	}
   
  	
   
  	$link = IUrl::creatUrl('/adminpage/area/module/searchkey'.$newlink);
	    
	    	$this->pageCls->setpage(IReq::get('page'),15);
	    	
	    	 //order: id  dno 订单编号 shopuid 店铺UID shopid 店铺ID shopname 店铺名称 shopphone 店铺电话 shopaddress 店铺地址 buyeruid 购买用户ID，0未注册用户 buyername
	    	 //
	    	$data['list'] = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."positionkey    ".$where." order by datatype,parent_id   limit ".$this->pageCls->startnum().", ".$this->pageCls->getsize()."");
	    	$shuliang  = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."positionkey    ".$where."   ");
	    	$this->pageCls->setnum($shuliang);
	    	
	    	$data['pagecontent'] = $this->pageCls->getpagebar($link);
   
  	Mysite::$app->setdata($data);
  }
  function delsearch(){
    $datatype =  intval(IReq::get('dataintype'));
  	$parent_id = intval(IReq::get('parent_id'));
  	$areasearch = new areasearch($this->mysql);   
  	$areasearch->setdata('',$datatype,$parent_id);
  	$areasearch->del();
  	$this->success('success'); 
  }
  function clearsearch(){
  	 $this->mysql->delete(Mysite::$app->config['tablepre'].'positionkey','datatype > 0');
  	 $this->success('success');
  }
  
	function setshow(){
	  $id = intval(IReq::get('id'));
	  $type = intval(IReq::get('type'));
	  if(empty($id))$this->message('area_empty');
	  $data['show'] = $type != 1?0:1;
	  $this->mysql->update(Mysite::$app->config['tablepre'].'area',$data,"id='".$id."'");
	  $this->success('success');
	}
	function delarea()
	{
		 $uid = intval(IReq::get('id'));
		 if(empty($uid))  $this->message('area_empty');
		 $areainfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."area where  parent_id = '".$uid."' order by id asc");
		 if(!empty($areainfo)) $this->message('area_isextchild');

	   $this->mysql->delete(Mysite::$app->config['tablepre'].'area',"id = '$uid'"); 
	   $this->mysql->delete(Mysite::$app->config['tablepre'].'areashop',"areaid = '$uid'"); 

	   $this->success('success');;
	}
	function addarealist(){
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
}



?>