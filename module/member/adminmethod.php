<?php
/*
*   method 方法  包含所有会员相关操作
    管理员/会员  添加/删除/编辑/用户登陆
    用户日志其他相关连的通过  memberclass关联
*/
class method   extends adminbaseclass
{ 
	 function index(){  
	 	       $link = IUrl::creatUrl('member/memberlist');
		       $this->refunction('',$link);
	 }
	 
	 function memberlist(){
	   	
	 	 $data['username'] =  trim(IReq::get('username'));
	     $data['email'] =  trim(IReq::get('email'));
	 	 $data['groupid'] =  5;
	 	 $data['phone'] =  trim(IReq::get('phone'));
	 	 //构造查询条件
	 	 $where = '';
	 	 $where =  $this->sqllink($where,'username',$data['username'],'=');
	 	 $where =  $this->sqllink($where,'email',$data['email'],'=');
	 	 $where =  $this->sqllink($where,'group',$data['groupid'],'=');
	 	 $where =  $this->sqllink($where,'phone',$data['phone'],'=');
	 	 $data['where'] = $where;
	 	 Mysite::$app->setdata($data);
	 }
	 function memberlistshop(){
	   
	     $mid=  trim(IReq::get('mid'));
             
             $typetime =  trim(IReq::get('typetime'));
             $data['typetime'] = $typetime;
             $starttime = IReq::get('starttime');
	     $endtime = IReq::get('endtime');
          

             $data['starttime'] = $starttime;
	     $data['endtime'] = $endtime;
             
             
	     $where .= " mid = '".$mid."'";
	     $data['username'] =  trim(IReq::get('username'));
	     $data['groupid'] = 3;
	 	
	 	 //构造查询条件
		 if(!empty($data['username'])){
			 $where .= " and username like '%".$data['username']."%'";
		 }
                 
                if($starttime){
		   $where .= " and creattime > ".strtotime($starttime.' 00:00:00');
		}

		if($endtime)
		{
	           $where .= " and creattime < ".strtotime($endtime.' 23:59:59');
		}
                 
                 
                 
                if($typetime==1){
                    
	            $data['orderby'] = "creattime";
                    
		 }elseif($typetime==2) {
                     
                    $data['orderby'] = "logintime";
                    
                 }else{
                     
                    $data['orderby'] = "logintime";
                    
                 }

//	 	 $where = '';
//	 	 if(!empty($data['username'])){
//	 	 $where =  $this->sqllink($where,'username',$data['username'],'=');
//	 	 }
//	 	 if(!empty($data['email'])){
//	 	 $where =  $this->sqllink($where,'email',$data['email'],'=');
//	 	 }
//	 	 if(!empty($data['group'])){
//	 	 $where =  $this->sqllink($where,'group',$data['groupid'],'=');
//	 	 }
//	 	 if (!empty($data['phone'])){
//	 	 $where =  $this->sqllink($where,'phone',$data['phone'],'=');
//	 	 }
	 	 $data['where'] = $where;

	 	Mysite::$app->setdata($data);
	 }

	 function adminlist(){
	     $mid = trim(IReq::get('mid'));

	     $data['where']=" mid = '".$mid."' ";


	 	 $grouplist =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."group where id =1 ");
	 	 $temp = array();
	 	 foreach($grouplist as $key=>$value){
	 	 	  $temp[$value['id']] = $value['name'];
	 	 }
	 	 $data['grouplist'] = $temp;
		 Mysite::$app->setdata($data);
	 }


	 function getmid(){
	  $data['where'] = "mid = '$this->mid'";
		Mysite::$app->setdata($data);
	 }

	 //后台添加管理员
	 function saveadmin(){
	        $mid = trim(IReq::get('mid'));
	 	    $uid = IReq::get('uid');


		    $username = IReq::get('username');
		    $password = IReq::get('password');
		    $groupid = IReq::get('groupid');
		   if(empty($uid))
		   {

		   	  if(empty($username)) $this->message('member_emptyname');
		   	  if(empty($username)) $this->message('member_emptypwd');
		   	  $testinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."admin where username='".$username."' ");
		   	  if(!empty($testinfo)) $this->message('member_repeatname');
		   	 	$arr['username'] = $username;
	     	  $arr['password'] = md5($password);
	     	  $arr['time'] = time();
	     	  $arr['groupid'] = $groupid;
	     	  $arr['mid'] = $mid;
	     	  $this->mysql->insert(Mysite::$app->config['tablepre'].'admin',$arr);
		   }else{
		   	  $testinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."admin where username='".$username."' ");
		   	  if(empty($testinfo)) $this->message('member_editfail');
		   	  if(!empty($password)){
		   	     $arr['password'] = md5($password);
		   	  }
		   	  $arr['groupid'] = $groupid;
		   	  $this->mysql->update(Mysite::$app->config['tablepre'].'admin',$arr,"uid='".$uid."'");
		   }
		   $this->success('success');
		  // $this->json(array('error'=>false));
	 }
	 //后台删除管理管理员
	 function deladmin(){

		 $tmsg = limitalert();
		if(!empty($tmsg)) $this->message($tmsg);

	    $uid = intval(IReq::get('id'));
		  if(empty($uid))  $this->message('member_emptyuid');
	    $this->mysql->delete(Mysite::$app->config['tablepre'].'admin',"uid = '$uid'");
	    $this->success('success');
	 }
	 //后台保存会员
	 function savemember()
   {
	  	$uid = intval(IReq::get('uid'));
		  $data['username'] = IReq::get('username');
	 	  $data['password'] = IReq::get('password');
	  	$data['phone'] = IReq::get('phone');
	  	$data['address'] = IReq::get('address');
	   	$data['email'] = IReq::get('email');
		  $data['group'] = IReq::get('group');
		 $data['score']=  IReq::get('score');
		  if($uid > 0){
			  $checkmem = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where uid='".$uid."' ");
			  if( !empty($checkmem) ){
				  $cost = $checkmem['cost'];
			  }else{
				  $this->message("获取用户信息失败");
			  }
		  }else{
			  $cost = IReq::get('cost');
		  }

		$is_zengjian = IReq::get('is_zengjian'); //增加/减少积分  0未选择 1增加 2减少

		if( $is_zengjian == 0  ){
			$data['cost'] = $cost;
		}

		if( $is_zengjian == 1  ){
			$yuecost = IReq::get('yuecost');
			$data['cost'] = $cost+$yuecost;
		}
		if( $is_zengjian == 2 ){
			$yuecost = IReq::get('yuecost');
			$data['cost'] = $cost-$yuecost;
		}



		  if(!IValidate::email($data['email'])) $this->message('erremail');
		  if(!IValidate::phone($data['phone'])) $this->message('errphone');
		  if(empty($data['username'])) $this->message('member_emptyname');
		  if(empty($uid))
	    {
	    	  if($this->memberCls->regester($data['email'],$data['username'],$data['password'],$data['phone'],$data['group'],'','',$data['cost'],$data['score']))
	    	  {


	    	  	$this->success('success');
	    	  }else{
	    	  	$this->message($this->memberCls->ero());
	    	  }
	    }else{

	      if($this->memberCls->modify($data,$uid))
	      {			$is_phonenotice = IReq::get('is_phonenotice');  //是否短信通知 1为通知  0不通知
					$notice_content = trim(IReq::get('notice_content'));


					 $bdliyou = $is_phonenotice==0?"管理员直接操作变动":$notice_content;
					 if($is_zengjian > 0){
						 $this->memberCls->addlog($uid,2,$is_zengjian,$yuecost,'管理员操作金额',$bdliyou,$data['cost']);
						$this->memberCls->addmemcostlog($this->mid, $uid,$checkmem['username'],$checkmem['cost'],$is_zengjian,$yuecost,$data['cost'],$bdliyou,ICookie::get('adminuid'),ICookie::get('adminname') );
					 }
						if( $is_phonenotice == 1 ){

								 $this->fasongmsg($notice_content,$data['phone'])  ;
 								 $this->success('success');


						}



	      }else{
	    	 	$this->message($this->memberCls->ero());
	      }

	    }
	    $this->success('操作成功');
   }
	function fasongmsg($notice_content,$phone){
		$contents = $notice_content;
		$phonecode = new phonecode($this->mysql,0,$phone);
		if(strlen($contents) > 498){
			$content1 = substr($contents,0,498);
			$phonecode->sendother($content1);
			$content2 = substr($contents,498,strlen($contents));
			$phonecode->sendother($content2);
		}else{
			$phonecode->sendother($contents);
		}
	}
   //后台删除会员
   function delmember()
 	{
		 $uid = intval(IReq::get('id'));
		 if(empty($uid))  $this->message('member_emptyuid');

	   /*删除相关店铺**/
     /*用户直接相关部分*/
    $this->mysql->delete(Mysite::$app->config['tablepre'].'member',"uid = '$uid'");
    $this->mysql->delete(Mysite::$app->config['tablepre'].'oauth',"uid = '$uid'");
    $this->mysql->delete(Mysite::$app->config['tablepre'].'giftlog',"uid = '$uid'");
    $this->mysql->delete(Mysite::$app->config['tablepre'].'address',"userid = '$uid'");
    $this->mysql->delete(Mysite::$app->config['tablepre'].'comment',"uid = '$uid'");
    $this->mysql->delete(Mysite::$app->config['tablepre'].'collect',"uid = '$uid'");
    $this->mysql->delete(Mysite::$app->config['tablepre'].'card',"uid = '$uid'");
    $this->mysql->delete(Mysite::$app->config['tablepre'].'ask',"uid = '$uid'");
    $this->mysql->delete(Mysite::$app->config['tablepre'].'juan',"uid = '$uid'");
    $this->mysql->delete(Mysite::$app->config['tablepre'].'memberlog',"userid = '$uid'");
    $testinfo = $this->mysql->select_one("select id from ".Mysite::$app->config['tablepre']."shop where uid='".$uid."' ");
	  if(!empty($testinfo))
	  {
	     $this->mysql->delete(Mysite::$app->config['tablepre'].'shop',"id = '".$testinfo['id']."'");
	     $this->mysql->delete(Mysite::$app->config['tablepre'].'shopfast',"shopid = '".$testinfo['id']."'");
	     $this->mysql->delete(Mysite::$app->config['tablepre'].'shopattr',"shopid = '".$testinfo['id']."'");
	     $this->mysql->delete(Mysite::$app->config['tablepre'].'shopsearch',"shopid = '".$testinfo['id']."'");
	     $this->mysql->delete(Mysite::$app->config['tablepre'].'areatoadd',"shopid = '".$testinfo['id']."'");
	     $this->mysql->delete(Mysite::$app->config['tablepre'].'areashop',"shopid = '".$testinfo['id']."'");

	     $this->mysql->delete(Mysite::$app->config['tablepre'].'goods',"shopid = '".$testinfo['id']."'");
	     $this->mysql->delete(Mysite::$app->config['tablepre'].'goodstype',"shopid = '".$testinfo['id']."'");
	     $this->mysql->delete(Mysite::$app->config['tablepre'].'order',"shopid = '".$testinfo['id']."'");
	     $this->mysql->delete(Mysite::$app->config['tablepre'].'orderdet',"shopid = '".$testinfo['id']."'");
	     $this->mysql->delete(Mysite::$app->config['tablepre'].'searkey'," type=1 and goid = '".$testinfo['id']."'");
	     $this->mysql->delete(Mysite::$app->config['tablepre'].'rule',"shopid = '".$testinfo['id']."'");
   }
	   $this->success('success');
	 }
	 //后台保存会员组
	 function savegroup(){

	    	$id = intval(IReq::get('id'));
	 	    $data['name'] = IReq::get('name');
	 	    $type = IReq::get('type');
	 	    if(empty($data['name']))  $this->message('member_group_noexit');
	 	    $data['type'] = $type == 1?'admin':'font';
	 	    if(empty($id)){
	 	    	 $testinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."group where name='".$data['name']."' ");
		   	   if(!empty($testinfo)) $this->message('member_group_repeat');
		       $this->mysql->insert(Mysite::$app->config['tablepre'].'group',$data);
	 	    }else{
	 	    	 $this->mysql->update(Mysite::$app->config['tablepre'].'group',$data,"id='".$id."'");
	 	    }
	 	  $this->success('success');
	 }
	 //后台删除会员组
	 function delgroup(){

	     $uid = intval(IReq::get('id'));
		  if(empty($uid))  $this->message('member_emptyuid');
		  if(in_array($uid,array(1,2,3,4,5))) $this->message('member_group_system');
	    $this->mysql->delete(Mysite::$app->config['tablepre'].'group',"id = '$uid'");
	    $this->success('success');
	 }
	 //后台管理员登出
	 function adminloginout(){
	 	 ICookie::clear('adminname');
	    ICookie::clear('adminpwd');
	    ICookie::clear('adminuid');
	    ICookie::clear('adminshopid');
      $link = IUrl::creatUrl('member/adminlogin');
      $this->refunction('',$link);
	 }
	function adminmodify(){

		if( $this->admin['uid'] == 1 ){
			$this->message("您暂无权限修改密码！");
		}
	    $oldpwd = trim(IReq::get('oldpwd'));
		$pwd  = trim(IReq::get('pwd'));
		$pwds  = trim(IReq::get('pwds'));
		if(empty($oldpwd))
		{
			 $this->message('emptyoldpwd');
		}
		if(empty($pwd))
		{
			 $this->message('emptynewpwd');
		}
		if(empty($pwds))
		{
			$this->message('emptycheckpwd');
		}
		if($pwd !==$pwds){
			$this->message('twopwdno');
		}
		if($this->admin['password'] != md5($oldpwd))
		{
			 $this->message('oldpwderr');
		}
		 $arr['password'] = md5($pwd);
		 $this->mysql->update(Mysite::$app->config['tablepre'].'admin',$arr,"uid='".$this->admin['uid']."'");	 
		 $this->success('success');
	} 
	         
  function membergrade(){
    	
    	$configs = new config('membergrade.php',hopedir);   
	    $data['membergrade'] = $configs->getInfo();
	    
      Mysite::$app->setdata($data); 
  }
  function savemembergrade(){
  	$gradename =   IFilter::act(IReq::get('gradename')); 
  	$from =   IFilter::act(IReq::get('from')); 
  	$to =   IFilter::act(IReq::get('to')); 
  	if(!is_array($gradename)) $this->message('member_grade_formaterr');
  	if(count($gradename) != count($from)){
  	    $this->message('member_grade_counterr');
  	}
  	if(count($gradename) != count($to)){
  		 $this->message('member_grade_jifenerr');
  	}
  	$newarray = array();
  	foreach($gradename as $key=>$value){
  	   $temp['gradename'] = $value;
  	   $temp['from'] = $from[$key];
  	   $temp['to'] = $to[$key];
  	   $newarray[] = $temp;
  	}
  	 
  	
  	$configData = var_export($newarray,true);
		$configStr = "<?php return {$configData}?>"; 
		$fileObj   = new IFile(hopedir.'/config/membergrade.php','w+'); 
		$fileObj->write($configStr);
   
	  $this->success('success');
  }
   function gradeinstro(){
   	 
  	 $configs = new config('membergrade.php',hopedir);   
	   $data['membergrade'] = $configs->getInfo();
	   
	   //总长度900px;
	   
	   $data['perlong'] = intval(900/(count($data['membergrade'])));
	    
	   
      Mysite::$app->setdata($data); 
     
	 }
	 
	 function setmemsafepwd(){
		 
		  $uid = intval($this->admin['uid']) ;
		   $data['id'] = $uid;	  
	  
		  $testinfo = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."safepwd where adminuid='".$uid."' ");  
		  print_r($testinfo);
		   Mysite::$app->setdata($data); 
	 }
  // 保存用户验证密码
  function savememsafepwd(){
	  $uid = IFilter::act(IReq::get('uid')) ; 
     
	  $checksafeinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."safepwd where adminuid='".$uid."' ");  
	  if( !empty($checksafeinfo) ){
		  $this->message('管理员已设置过验证密码');
	  }

	  $pwd = IFilter::act(IReq::get('password')); 
	  if(empty($pwd))
		{
			 $this->message('验证密码为空！');
		}
	  $data['adminuid'] = $uid;
	  $data['password'] = md5($pwd);
	  $data['addtime']  = time();
	  $data['type']     = 0;
	  $this->mysql->insert(Mysite::$app->config['tablepre'].'safepwd',$data);  
	  $this->success('success');
  }
  function xiugaimemsafepwd(){
	 # print_r($this->admin['uid']);
	 # exit;
	  	$oldpwd  = trim(IReq::get('oldpwd'));
	  	$md5oldpwd  = md5(trim(IReq::get('oldpwd')));
	  	$newpwd  = trim(IReq::get('newpwd'));
		if(empty($oldpwd))
		{
			 $this->message('旧密码不能为空！');
		}
		$checksafeinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."safepwd where adminuid='".$this->admin['uid']."'   ");  
		if( empty($checksafeinfo ) ){
			 $this->message('用户不存在！');
		}
	
		if($checksafeinfo['password'] != md5($oldpwd))
		{
			 $this->message('旧密码不正确！');
		}
		if(empty($newpwd))
		{
			 $this->message('新密码不能为空！');
		}
		
		 $arr['password'] = md5($newpwd);
		 $this->mysql->update(Mysite::$app->config['tablepre'].'safepwd',$arr,"adminuid='".$this->admin['uid']."'");	 
		 $this->success('success');
  }
  function memcostloglist(){
      
	    	$querytype = IReq::get('querytype');
	    	$searchvalue = IReq::get('searchvalue');
	    	$starttime = IReq::get('starttime');
	    	$endtime = IReq::get('endtime');
	    	$nowday = date('Y-m-d',time());
	    	$starttime = empty($starttime)? $nowday:$starttime;
	    	$endtime = empty($endtime)? $nowday:$endtime;
	      $where = '  where addtime > '.strtotime($starttime.' 00:00:00').' and addtime < '.strtotime($endtime.' 23:59:59');
	    	$data['starttime'] = $starttime;
	    	$data['endtime'] = $endtime;
	    	$newlink = '/starttime/'.$starttime.'/endtime/'.$endtime;
	    	 $data['searchvalue'] ='';
	    	 $data['querytype'] ='';
	    	if(!empty($querytype))
	    	{
	    		if(!empty($searchvalue)){
	    			 $data['searchvalue'] = $searchvalue;
	       	   $where .= ' and '.$querytype.' LIKE \'%'.$searchvalue.'%\' ';
	       	   $newlink .= '/searchvalue/'.$searchvalue.'/querytype/'.$querytype;
	       	   $data['querytype'] = $querytype;
	    		}//IUrl::creatUrl('admin/asklist/commentlist
	    	}
	      
	    	$link = IUrl::creatUrl('/adminpage/member/module/memcostloglist'.$newlink);
	    	$pageshow = new page();
	    	$pageshow->setpage(IReq::get('page'),10);
	    	 //order: id  dno 订单编号 shopuid 店铺UID shopid 店铺ID shopname 店铺名称 shopphone 店铺电话 shopaddress 店铺地址 buyeruid 购买用户ID，0未注册用户 buyername
	    	 //
	    	 
	    	$memcostloglist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."memcostlog ".$where."  order by addtime desc limit ".$pageshow->startnum().", ".$pageshow->getsize()."");
	    	$shuliang  = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."memcostlog  ".$where." ");
	    	$pageshow->setnum($shuliang);
	    	$data['pagecontent'] = $pageshow->getpagebar($link);
			$data['memcostloglist'] = $memcostloglist;
			 
	     Mysite::$app->setdata($data);
	}
  
}



?>