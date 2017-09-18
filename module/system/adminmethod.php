<?php
class method   extends adminbaseclass
{ 
	 function saveotherset(){ 
		 $tmsg = limitalert();
		if(!empty($tmsg)) $this->message($tmsg);
		
	 	$siteinfo['addresslimit'] = intval(IReq::get('addresslimit'));
	  $siteinfo['shoptypelimit'] = intval(IReq::get('shoptypelimit'));
	  $siteinfo['shopgoodslimit'] = intval(IReq::get('shopgoodslimit')); 
		$siteinfo['allowedcode'] = intval(IReq::get('allowedcode')); 
		$siteinfo['allowedsendshop'] = intval(IReq::get('allowedsendshop'));
		$siteinfo['allowedsendbuyer'] = intval(IReq::get('allowedsendbuyer'));
		
	
		$siteinfo['ordercheckphone'] = intval(IReq::get('ordercheckphone')); //下单前手机验证
		$siteinfo['man_ispass'] = intval(IReq::get('man_ispass'));//为1管理员后台审核
		$siteinfo['open_acout'] = intval(IReq::get('open_acout'));//是否开启在线支付
		$siteinfo['is_daopay'] = intval(IReq::get('is_daopay'));//是否开启 到付   
		$siteinfo['areacode'] = intval(IReq::get('areacode'));//是否开启  收货地址短信验证码
		$siteinfo['addAreaType'] = intval(IReq::get('addAreaType'));//添加地址选择方式   默认0地图选择  1手动输入
		$siteinfo['wxLoginType'] = intval(IReq::get('wxLoginType'));//微信端登录方式  默认0自动登录 1账号登录
        $siteinfo['open_wxcx'] = intval(IReq::get('open_wxcx'));//微信端首页商家列表显示促销信息
		$siteinfo['noticeshopemail'] = intval(IReq::get('noticeshopemail')); 
		$siteinfo['auto_send'] = intval(IReq::get('auto_send'));
		$siteinfo['regestercode'] = intval(IReq::get('regestercode'));
		$siteinfo['allowedguestbuy'] = intval(IReq::get('allowedguestbuy'));
		$siteinfo['allowed_is_make'] = intval(IReq::get('allowed_is_make'));
		$siteinfo['plateshopid'] = intval(IReq::get('plateshopid'));
		if(empty($siteinfo['plateshopid'])){
			$this->message('采购店铺id不能为空');
		}
		$checkshopinfo = $this->mysql->select_one("select   * from ".Mysite::$app->config['tablepre']."shop where id='".$siteinfo['plateshopid']."' ");
		if(empty($checkshopinfo)){
			$this->message('采购店铺不存在');
		}elseif($checkshopinfo['shoptype'] == 0){
			$this->message('采购店铺必须为超市');
		}
		
		
	  $siteinfo['weixinpay'] = intval(IReq::get('weixinpay'));
	  $checkios = intval(IReq::get('ios_waiting'));
	  if($checkios == 1){
		  $siteinfo['ios_waiting'] = true;
	  }else{
		   $siteinfo['ios_waiting'] = false;
	  }
	  
	  
    //自动完成时间auto_overtime
	  $config = new config('hopeconfig.php',hopedir,$this->mid,$type=1,Mysite::$app->config['conf_table']);  
	  $config->write($siteinfo);
		 
		$this->success('success');
	 }
	 function saveset(){
	 	 $tmsg = limitalert();
		if(!empty($tmsg)) $this->message($tmsg);
 	 	$sitename = IReq::get('sitename');
//	    $description = IReq::get('description');
//	    $keywords = IReq::get('keywords');
//	    $beian = IReq::get('beian');
//	    $yjin = IReq::get('yjin');
//	    $yjin = round($yjin, 2);
	    $cityname = trim(IReq::get('cityname'));
//	    $shoplogo = trim(IReq::get('shoplogo'));
//	    $userlogo = trim(IReq::get('userlogo'));
	    $notice = trim(IReq::get('notice'));
//	    $sitelogo = trim(IReq::get('sitelogo'));
//	    $area_grade = intval(IReq::get('area_grade'));
//	    $metadata = trim(IReq::get('metadata'));
//	    $footerdata = trim(IReq::get('footerdata'));
//	    $guidetype = intval(IReq::get('guidetype'));
	    $html5logo =  trim(IReq::get('html5logo'));
//	     $baidumapkey =  trim(IReq::get('baidumapkey'));
//	    $baidumapsecret =  trim(IReq::get('baidumapsecret'));
//	    $siteinfo['baidulng'] =  trim(IReq::get('baidulng'));
//	    $siteinfo['baidulat'] =  trim(IReq::get('baidulat'));
	    //提成设置
	    if(empty($sitename)) $this->message('system_emptysitename');
//	    if(empty($description)) $this->message('system_emptydes');
//	    if(empty($keywords)) $this->message('system_emptyseo');
       if(empty($cityname)) $this->message('system_emptycity');
	    $siteinfo['litel'] = IReq::get('litel');//客服电话
	    $siteinfo['sitename'] = $sitename;//网站名
// 	  	$siteinfo['description'] = $description;
//	  	$siteinfo['keywords'] = $keywords;
//		  $siteinfo['beian'] = $beian;
//		  $siteinfo['yjin'] = $yjin;
		  $siteinfo['cityname'] = $cityname;//网站默认城市
//	  	$siteinfo['shoplogo'] = $shoplogo;
//		  $siteinfo['userlogo'] = $userlogo;
	   	$siteinfo['notice'] = $notice;//网站公告
//	  	$siteinfo['sitelogo'] = $sitelogo;
//      $siteinfo['area_grade'] = $area_grade;
//	  	$siteinfo['metadata'] = $metadata;
//	  	$siteinfo['footerdata'] = $footerdata;
//	  	$siteinfo['guidetype'] = $guidetype;
	  	$siteinfo['html5logo'] = $html5logo;//手机站LOGO
//
////	  	$siteinfo['baidumapkey'] = $baidumapkey;
////	  	$siteinfo['baidumapsecret'] = $baidumapsecret;
	    $config = new config('hopeconfig.php',hopedir,$this->mid,1,Mysite::$app->config['conf_table']);
	    $config->write($siteinfo);
	    $configs = new config('hopeconfig.php',hopedir,$this->mid,$type=1,Mysite::$app->config['conf_table']);
	    $tests = $config->getInfo();
	    /*生成伪JS文件*/
	    /*
	    $savejsinfo = ' var siteurl =  \''.$tests['siteurl'].'\';	  var sitename = \''.$tests['sitename'].'\';   var sitelogo = \''.$tests['sitelogo'].'\'; var beian = \''.$tests['beian'].'\';';
	   
	     $fp = fopen(hopedir.'/html5/config.js', 'w');
	      fwrite($fp, $savejsinfo);
        fclose($fp); */
		 $this->success('success');
	 } 
	 function savesitebk(){
	 	
	 	 $siteinfo['sitebk'] = IReq::get('userlogo'); 
		 $siteinfo['is_water'] = IReq::get('is_water'); 
		 $siteinfo['water_pos'] = IReq::get('water_pos');
		 $siteinfo['logo_water'] = IReq::get('logo_water');
	   	 $siteinfo['text_water'] = IReq::get('text_water');
	  	 $siteinfo['size_water'] = IReq::get('size_water');
	  	 $siteinfo['color_water'] = IReq::get('color_water');
	     $config = new config('hopeconfig.php',hopedir,$this->mid,$type=1,Mysite::$app->config['conf_table']);
	     $config->write($siteinfo);
	 	 $this->success('success'); 
   }
   function savetoplink(){
   	$arrtypename = IReq::get('typename');
			$arrtypeurl = IReq::get('typeurl');
			$arrtypeorder = IReq::get('typeorder'); 
		  if(empty($arrtypename)) $this->message('empty_link'); 
		  if(is_array($arrtypename))
		  {
		  	$orderinfo = array(); 
		  	foreach($arrtypename as $key=>$value)
		  	{
		  		if(isset($arrtypeorder[$key]))
		  		{
		  		  $dokey = !empty($arrtypeorder[$key])? $arrtypeorder[$key]:0; 
		  		  array_push($orderinfo,$dokey);
		  	  }else{
		  	  	 array_push($orderinfo,0);
		  	  }
		  	} 
		  	$orderinfo = array_unique($orderinfo); 
		  	sort($orderinfo); 
		  	$newinfo =  array();
		  	foreach($orderinfo as $key=>$value)
		  	{
		  		foreach($arrtypename as $k=>$v)
		  		{
		  	    if(isset($arrtypeorder[$k]))
		  	   	{
		  	   	  $checkcode = !empty($arrtypeorder[$k])? $arrtypeorder[$k]:0; 
		  	   	 
		  	     }else{
		  	     	$checkcode = 0;
		  	     }
		  		 
		  			if($checkcode == $value)
		  			{
		  				$data['typename'] = $v;
		  				$data['typeurl'] = isset($arrtypeurl[$k])? $arrtypeurl[$k]:'';
		  				$data['typeorder'] = $checkcode;
		  				$newinfo[] = $data;
		  			}
		  		}
		  	}
		  	 
		  }else{
		  	$newinfo['typename'] = $arrtypename;
		  	$newinfo['typeurl'] = $arrtypeurl;
		  	$newinfo['typeorder'] = $arrtypeorder;
		  }
		$siteinfo['footlink'] =   serialize($newinfo);
		$config = new config('hopeconfig.php',hopedir,$this->mid,$type=1,Mysite::$app->config['conf_table']);  
	    $config->write($siteinfo);
	    $this->success('success'); 
   }
   function savefootinfo(){
   	
    $arrtypename = IReq::get('typename');
			$arrtypeurl = IReq::get('typeurl'); 
			$arrtypeorder = IReq::get('typeorder'); 
		  if(empty($arrtypename)) $this->message('empty_link'); 
		  if(is_array($arrtypename))
		  {
		  	$orderinfo = array(); 
		  	foreach($arrtypename as $key=>$value)
		  	{
		  		if(isset($arrtypeorder[$key]))
		  		{
		  		  $dokey = !empty($arrtypeorder[$key])? $arrtypeorder[$key]:0; 
		  		  array_push($orderinfo,$dokey);
		  	  }else{
		  	  	 array_push($orderinfo,0);
		  	  }
		  	} 
		  	$orderinfo = array_unique($orderinfo); 
		  	sort($orderinfo); 
		  	$newinfo =  array();
		  	foreach($orderinfo as $key=>$value)
		  	{
		  		foreach($arrtypename as $k=>$v)
		  		{
		  	    if(isset($arrtypeorder[$k]))
		  	   	{
		  	   	  $checkcode = !empty($arrtypeorder[$k])? $arrtypeorder[$k]:0; 
		  	   	 
		  	     }else{
		  	     	$checkcode = 0;
		  	     }
		  		 
		  			if($checkcode == $value)
		  			{
		  				$data['typename'] = $v;
		  				$data['typeurl'] = isset($arrtypeurl[$k])? $arrtypeurl[$k]:'';
		  				$data['typeorder'] = $checkcode;
		  				$newinfo[] = $data;
		  			}
		  		}
		  	}
		  	 
		  }else{
		  	$newinfo['typename'] = $arrtypename;
		  	$newinfo['typeurl'] = $arrtypeurl;
		  	$newinfo['typeorder'] = $arrtypeorder;
		  }
		$siteinfo['toplink'] =   serialize($newinfo);
		$config = new config('hopeconfig.php',hopedir,$this->mid,$type=1,Mysite::$app->config['conf_table']);  
	  $config->write($siteinfo);
	    $this->success('success'); 
   }
   
	 function savemodule(){
	 	 
	 	   //module   id	name	cnname	install
	 	   $arr['name'] = IFilter::act(IReq::get('name')); 
	 	   $arr['cnname'] = IFilter::act(IReq::get('cnname')); 
	 	   $arr['install'] = 1;
	 	   $is_main = intval(IFilter::act(IReq::get('is_main'))); 
	 	  if(empty($arr['name'])) $this->message('empty_filename');
	 	  if(empty($arr['cnname'])) $this->message('empty_CNname');
	 	  if(empty($is_main)) $this->message('module_nochoice');
	 	  $parent_id = intval(IFilter::act(IReq::get('parent_id'))); 
	 	  if($is_main == 1){
	 	    $arr['parent_id'] = 0;
	 	  }else{
	 	    $arr['parent_id'] = $parent_id;
	 	    if(empty($parent_id)) $this->message('module_noparent');
	 	  }
	 	  
	 	  $this->mysql->insert(Mysite::$app->config['tablepre'].'module',$arr);  
	 	  $moduleid = $this->mysql->insertid();
	 	  //写入菜单 
	 	  $menudata['name'] = 'limitset';
	 	  $menudata['cnname'] = '权限设置';
	 	  $menudata['moduleid'] = $moduleid;
	 	  $menudata['group'] = 1;
	 	  $menudata['id'] = 0;
	 	  $this->mysql->insert(Mysite::$app->config['tablepre'].'menu',$menudata);  
	 	  //写入权限
	 	  $limitdata['name'] =  'limitset';
	 	  $limitdata['cnname'] = '权限列表';
	 	  $limitdata['moduleid'] = $moduleid;
	 	  $limitdata['group'] = 1;
	 	  $this->mysql->insert(Mysite::$app->config['tablepre'].'usrlimit',$limitdata);  
	 	  $limitdata['name'] = 'savelimit';
	 	  $limitdata['cnname'] = '保存权限设置'; 
	 	  $this->mysql->insert(Mysite::$app->config['tablepre'].'usrlimit',$limitdata);  
	 	  $this->success('success');
	 }
	 function tempset(){
	 	
	 	   $logindir = hopedir.'/templates'; 
       $dirArray[]=NULL;   
       if (false != ($handle = opendir ( $logindir ))) {   
         $i=0;   
         while ( false !== ($file = readdir ( $handle )) ) {   
             //去掉"“.”、“..”以及带“.xxx”后缀的文件   
             if ($file != "." && $file != ".."&&!strpos($file,".")) { 
             	  if(file_exists($logindir.'/'.$file.'/stro.txt'))
                 { 
                 		$license = file_get_contents($logindir.'/'.$file.'/stro.txt');   
	                  $dirArray[$i]['instro'] =  nl2br(str_replace(' ', '&nbsp;', htmlspecialchars($license,ENT_COMPAT,'UTF-8')));  
                 	  $dirArray[$i]['img'] = file_exists($logindir.'/'.$file.'/instro.jpg')? Mysite::$app->config['siteurl'].'/templates/'.$file.'/instro.jpg':'';
                 	  $dirArray[$i]['filename'] =$file;  
                 	  
                    $i++;   
                 }
             }  

         }   
         //关闭句柄  
         //if(!file_exists(hopedir.'/templates/'.$templtepach))//判断文件是否存在  判断配置文件是否存在
         closedir ( $handle );  

       } 
       $data['apilist'] = $dirArray; 
        Mysite::$app->setdata($data);
	 	
   }
   function savetempset(){
   	
   	$tempname = IFilter::act(IReq::get('modulename')); 
   	if(empty($tempname)){
   		 $this->message('module_emptyname');
   	}
    $logindir = hopedir.'/templates'; 
    if(!file_exists($logindir.'/'.$tempname.'/stro.txt')) $this->message('template_noexit');
    $siteinfo['sitetemp'] =  $tempname;
		$config = new config('hopeconfig.php',hopedir,$this->mid,$type=1,Mysite::$app->config['conf_table']);  
	  $config->write($siteinfo);
    IFile::clearDir('templates_c'); 
	  $this->success('success'); 
   }
function savemobiletempset(){
   	
   	$tempname = IFilter::act(IReq::get('mobilemodule')); 
   	if(empty($tempname)){
   		 $this->message('module_emptyname');
   	}
      $siteinfo['mobilemodule'] =  $tempname;
		$config = new config('hopeconfig.php',hopedir,$this->mid,$type=1,Mysite::$app->config['conf_table']);  
	  $config->write($siteinfo);
    IFile::clearDir('templates_c'); 
	  $this->success('success'); 
   }

   function delmodule(){
   	 $tmsg = limitalert();
		if(!empty($tmsg)) $this->message($tmsg);
      	$id = intval(IFilter::act(IReq::get('id'))); 
   	     $checinfo = $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre']."module  where id= '".$id."'  ");
   	     if(empty($checinfo)) $this->message('module_noexit');
   	     if($checinfo['parent_id'] == 0){
   	       	 $sublist = $this->mysql->getarr("select *  from ".Mysite::$app->config['tablepre']."module  where parent_id= '".$id."'  ");
   	       	 foreach($sublist as $key=>$value){
   	       	 	  $this->mysql->delete(Mysite::$app->config['tablepre']."module"," id='".$value['id']."'  "); 
   	       	 	   $this->mysql->delete(Mysite::$app->config['tablepre']."menu"," moduleid='".$value['id']."'  "); 
   	       	 	   $this->mysql->delete(Mysite::$app->config['tablepre']."usrlimit"," moduleid='".$value['id']."'  "); 
   	       	 }
   	       	 
   	     }
   	      $this->mysql->delete(Mysite::$app->config['tablepre']."module"," id='".$id."'  "); 
   	      $this->mysql->delete(Mysite::$app->config['tablepre']."menu"," moduleid='".$id."'  "); 
   	      $this->mysql->delete(Mysite::$app->config['tablepre']."usrlimit"," moduleid='".$id."'  "); 
   	     $this->success('success');
    
   }
   function limitset(){
   	
   	 $id = intval(IReq::get('id'));	  
	 	   $data['groupid'] = intval(IReq::get('usergrade'));	
	 	     $data['groupinfo'] = array();
	 	     if($data['groupid'] > 0){
	 	     	
	 	     	 $data['groupinfo'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."group where id = ".$data['groupid']." "); 
	 	    }
   	 
	 	   $modelist =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."module  where install='1' limit 0,30"); 
	 	   //name 操作名称	cnname	moduleid	group	id
	 	   $templist = array();
	 	   foreach($modelist as $key=>$value){
	 	        $menufile = hopedir."/module/".$value['name']."/menudata.php";  
	 	        if(file_exists($menufile))//判断文件是否存在
	 	        {
	 	        	    $value['det'] = include($menufile);    
	 	        	    $temp_c = $this->mysql->getarr("select name from ".Mysite::$app->config['tablepre']."menu where moduleid='".$value['id']."'  and `group`=".$data['groupid']." ");
	 	        	    $value['menuarray'] = array();
	 	        	    if(is_array($temp_c)){
	 	        	      foreach($temp_c as $k=>$val){
	 	        	      	$value['menuarray'][] = $val['name'];
	 	        	      }
	 	        	    }
	 	        }else{
	 	            $value['det'] = array();
	 	        }
	 	        $templist[] = $value;
	 	   }  
	 	   
	 	   $data['modelist'] = $templist;
	 	  
	 	   Mysite::$app->setdata($data); 
	 }
	 //保存权限
	 function savelimit(){
	 	 
	 	  $groupid = intval(IReq::get('groupid'));	
	 	  if(empty($groupid)) $this->message('member_group_noexit'); 
	 	  $groupinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."group where id = ".$groupid." "); 
	 	  if(empty($groupinfo)) $this->message('member_group_noexit');  
	 	  if($groupinfo['type'] != 'admin') $this->message('不是管理员不需要设置导航条');
	 	  $this->mysql->delete(Mysite::$app->config['tablepre'].'menu',"`group`=".$groupid."");   
	 	  $modelist =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."module  where install='1' limit 0,30");
	 	  //获取所有的模块
	 	  foreach($modelist as $key=>$value){
	 	  	    $menufile = hopedir."/module/".$value['name']."/menudata.php";  
	 	        if(file_exists($menufile))//判断文件是否存在
	 	        {
	 	        	  $getinfo = IFilter::act(IReq::get($value['name']));
	 	        	  if(is_array($getinfo)){
	 	        	     $munulist =  include($menufile); 
	 	        	     foreach($getinfo as $k=>$val){
	 	        	     	   if(isset($munulist[$val])){
	 	        	     	   	$xieru['name'] = $val;
	 	        	     	   	$xieru['cnname'] = $munulist[$val];
	 	        	          $xieru['moduleid'] = $value['id'];
	 	        	           $xieru['group'] = $groupid;
	 	        	           $xieru['id'] = $k;
	 	        	          $this->mysql->insert(Mysite::$app->config['tablepre'].'menu',$xieru);  
	 	        	         }
	 	        	         
	 	        	     }
	 	        	 }
	 	        	
	 	        }  
	 	  } 
	 	  $this->success('success');  
	 	
	 }
         
  /* 2016.03.27 新增网站通知模块 */
	
	/* 网站通知 */
  function addnotice() {
        $id = intval(IReq::get('id'));
        $data['info'] = $this->mysql->select_one("select * from " . Mysite::$app->config['tablepre'] . "information where id=" . $id . "  and type=1 ");

        Mysite::$app->setdata($data);
    }

    function savenotice() {
        $id = IReq::get('uid');
        $data['mid'] = $this->mid;
        $data['notice_time'] = strtotime(IReq::get('notice_time') . ' 00:00:00');
        $data['addtime'] = time();
        $data['title'] = IReq::get('title');
        $data['content'] = IReq::get('content');
        $data['orderid'] = IReq::get('orderid');
        $data['img'] = IReq::get('img');
        $data['type'] = 1; // type 1为网站通知 2为生活服务
        if (empty($id)) {
            $link = IUrl::creatUrl('adminpage/system/module/addnotice');
            if (empty($data['title']))
                $this->message('通知标题不能为空！', $link);
            if (empty($data['content']))
                $this->message('通知内容不能为空！', $link);
            $this->mysql->insert(Mysite::$app->config['tablepre'] . 'information', $data);
        }else {
            $link = IUrl::creatUrl('adminpage/system/module/addnotice/id/' . $id);
            if (empty($data['title']))
                $this->message('通知标题不能为空！', $link);
            if (empty($data['content']))
                $this->message('通知内容不能为空！', $link);
            $this->mysql->update(Mysite::$app->config['tablepre'] . 'information', $data, "id='" . $id . "'");
        }
        $link = IUrl::creatUrlEx($this->mid, 'adminpage/system/module/information');

        $this->success('success', $link);
    }

    function delnotice() {
        $id = IFilter::act(IReq::get('id'));
        if (empty($id))
            $this->message('获取数据失败');
        $ids = is_array($id) ? join(',', $id) : $id;
        if (empty($ids))
            $this->message('未选中数据');
        $this->mysql->delete(Mysite::$app->config['tablepre'] . 'information', " id in($ids)");
        $this->success('success', '');
    }

    public function noticeupload() {

        $_FILES['imgFile'] = $_FILES['head'];
        $json = new Services_JSON();
        $uploadpath = 'upload/notice/';
        $filepath = '/upload/notice/';
        $upload = new upload($uploadpath, array('gif', 'jpg', 'jpge', 'png')); //upload
        $file = $upload->getfile();
        if ($upload->errno != 15 && $upload->errno != 0) {
            $this->message($upload->errmsg());
        } else {
            $this->success($filepath . $file[0]['saveName']);
        }
    }

    function cleartpl() {
        IFile::clearDir('templates_c');
        $link = IUrl::creatUrlEx($this->mid, '/adminpage/adminindex/module/index');
        $this->refunction('清除缓存文件成功', $link);
    }

}



?>