<?php
/*
*   method 方法  包含所有会员相关操作
    管理员/会员  添加/删除/编辑/用户登陆
    用户日志其他相关连的通过  memberclass关联
*/
class method   extends baseclass
{ 
	 function index(){ 
	 	    
	 	    	if(empty($this->member['uid'])){
	 	    		 $link = IUrl::creatUrlEx($this->mid,'member/login');
           $this->refunction('',$link); 
	 	    	}else{
	 	    	 $link = IUrl::creatUrlEx($this->mid,'member/base');
           $this->refunction('',$link); 
          }
	 	    
	 }
	function base(){
	  $this->checkmemberlogin();
	   $temparea =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."area   ");
	   $areatoname = array();   
	   foreach($temparea as $key=>$value){
	   	$areatoname[$value['id']] = $value['name'];
	   }
	   $areatoname[0] = '';
	   $data['areatoname'] = $areatoname; 
	     $nowday = date('Y-m-d',time()); 
	 //   $where = '  and posttime > '.strtotime($nowday.' 00:00:00').' and posttime < '.strtotime($nowday.' 23:59:59');  
	    $where = ' and status < 3';
		  $data['temp'] =  $this->mysql->select_one("select count(id) as shuliang from ".Mysite::$app->config['tablepre']."order where buyeruid='".$this->member['uid']."'  ".$where."  and shoptype=1 order by id desc  limit 0,6"); //商城订单
	 
		  $data['temp2'] =  $this->mysql->select_one("select count(id) as shuliang from ".Mysite::$app->config['tablepre']."order where buyeruid='".$this->member['uid']."'  ".$where."  and shoptype=0 order by id desc  limit 0,6"); 
		 
		 $data['temp3'] =  $this->mysql->select_one("select count(id) as shuliang from ".Mysite::$app->config['tablepre']."order where buyeruid='".$this->member['uid']."'  ".$where."   and shoptype=0 order by id desc  limit 0,6"); 
		 $data['temp4'] =  $this->mysql->select_one("select count(id) as shuliang from ".Mysite::$app->config['tablepre']."order where buyeruid='".$this->member['uid']."' and status = 3 and is_ping =0 order by id desc  limit 0,6");  
	   
		 Mysite::$app->setdata($data);
	}
	  
	  
	  
	  
   
	 //后台管理员登陆
	 function adminlogin(){
	 	// ICookie::clear('adminname_ck'); 
	  //   ICookie::clear('adminpwd_ck'); 

	 	$mid =  IFilter::act(IReq::get(IUrl::MidName));
	 	$signup_name =  IFilter::act(IReq::get('signup_name'));   
  	    $signup_password =  IFilter::act(IReq::get('signup_password'));   

  	   if(!empty($signup_name)){
  	       	$userinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."admin where mid ='".$mid."' and  username ='".$signup_name."'"); 
  	     	  if(empty($signup_password))
  	        {
  	            $this->message('signup_password_tip'); 	 
  	        }
  	        if($userinfo['password'] != md5($signup_password))
  	        {
  	            $this->message('signup_password_tip'); 	  
  	        }
  	        $data['loginip'] = IClient::getIp();
  	        $data['logintime'] = time();
  	       $this->mysql->update(Mysite::$app->config['tablepre'].'admin',$data,"uid='".$userinfo['uid']."'");	
           ICookie::set('adminname',$userinfo['username'],86400);
           ICookie::set('adminpwd',$signup_password,86400);
           ICookie::set('adminuid',$userinfo['uid'],86400);  
           ICookie::set("sso_mid", $mid, 86400);//解决统一登录出现的问题       
//记住帐号和密码
           // if(intval(IReq::get('jzw'))==1)
           // {
	           ICookie::set('adminname_ck',$userinfo['username'],86400);
	           ICookie::set('adminpwd_ck',$signup_password,86400);
           // }

  	   	   $this->success('success'); 
  	   } 
        
        ////记住帐号和密码
  	    $data['adminname'] = ICookie::get('adminname_ck');
  	    $data['adminpwd'] = ICookie::get('adminpwd_ck');
  		Mysite::$app->setdata($data);
	 }
	 //后台管理员登出
	 function adminloginout(){
	 	$mid = $this->mid;
	 	 ICookie::clear('adminname'); 
	    ICookie::clear('adminpwd');  
	    ICookie::clear('adminuid');  
	    ICookie::clear('adminshopid'); 
      $link = IUrl::creatUrl($mid.'/member/adminlogin');
      $this->refunction('',$link); 
	 }
	 function login(){ 

	    $uname = IFilter::act(IReq::get('uname')); 
	    $pwd = IFilter::act(IReq::get('pwd')); 
	    $link = IUrl::creatUrlEx($this->mid,'member/login'); 
	    $logincode = intval(IFilter::act(IReq::get('logincode'))); 
	    if(!empty($logincode)){
	    	ICookie::set('logincode',$logincode,86400);
	    }
	    if(!empty($uname)){
		    if(empty($uname)) $this->message('member_emptyname',$link);
		    if(empty($pwd)) $this->message('member_emptypwd',$link);  
		    $logintype = IFilter::act(IReq::get('logintype')); 
		    if($logintype != 'html5'){
		      if(Mysite::$app->config['allowedcode'] == 1)
		      { 
		 	       $Captcha = IFilter::act(IReq::get('Captcha'));
		 	       if($Captcha != ICookie::get('Captcha')) 	$this->message('member_codeerr',$link); 
		      }  
		    }
	      if(!$this->memberCls->login($this->mid,$uname,$pwd)){
	    	    $this->message($this->memberCls->ero(),$link); 
	      }  
        $link = IUrl::creatUrlEx($this->mid, 'member/base');
        $this->success('',$link);
      }
	 }
	 function loginout(){
	    $mid = $this->mid;
	    $this->memberCls->loginout();
            ICookie::set('adminshopid',null);
	    ICookie::clear('adminshopid'); 
            
            $link = IUrl::creatUrl($mid.'/member/shoplogin');
            $this->refunction('',$link);  
	 }
	 function payoncard(){
	 	  $this->checkmemberlogin();
	 }
	 function shoplogin(){
	 	$mid = $this->mid;
	 	$data['mid'] = $mid;
	 	Mysite::$app->setdata($data);
	 }
	 function paylog(){
	 	  $this->checkmemberlogin();
  	 	$data['sitetitle'] = '资金变换记录'; 
  		$data['nowdate'] = date('Y-m-d',(time() - 30*24*60*60));
  		
  		$status = intval(IFilter::act(IReq::get('status')));
  		$starttime =  IFilter::act(IReq::get('starttime'));
  		$endtime =  IFilter::act(IReq::get('endtime'));
  		$starttime = empty($starttime)?  time() - 30*24*60*60:strtotime($starttime.' 00:00:01');
  		$endtime = empty($endtime)? time():strtotime($endtime.' 23:59:59');
  		
  		$statusarr = array(1=>'',2=>' and addtype != 1 ',3=>' and addtype = 1'); 
  		$where = in_array($status,array(1,2,3)) ? $statusarr[$status]:'';
  		$where .= ' and addtime > '.$starttime.' and '.$endtime;
  		$data['where'] = ' userid = '.$this->member['uid'].' and type=2 '.$where;
  		Mysite::$app->setdata($data);
   }
   
   
   
   function jifenlog(){
   	 $this->checkmemberlogin();
  	 $data['sitetitle'] = '积分记录表'; 
  	  $what = trim(IFilter::act(IReq::get('what'))); 
  	  $query = array('out'=>' and addtype != 1','in'=>' and addtype = 1');
  	  $where = in_array($what,array('out','in'))? $query[$what]:'';
  	  $data['what'] = in_array($what,array('out','in'))? $what:'';
  	  $link = in_array($what,array('out','in'))? '/member/jifenlog/what/'.$what.'/page/@page@':'/membercenter/jifenlog/page/@page@';
      $data['where'] = ' userid =\''.$this->member['uid'].'\' and type=1 '.$where;
       Mysite::$app->setdata($data); 
	 }
	 
	  function xiugaimempwd(){ //会员密码
		//'oldpaypwd':$('#oldpaypwd').val(),'newpaypwd':$('#newpaypwd').val(),'verifynewpaypwd':$('#').val()
		$this->checkmemberlogin();
		$oldpaypwd = trim(IFilter::act(IReq::get('oldpaypwd')));
		$newpaypwd = trim(IFilter::act(IReq::get('newpaypwd')));
		$verifynewpaypwd = trim(IFilter::act(IReq::get('verifynewpaypwd')));
		if(empty($oldpaypwd)) $this->message('原密码为空');
		if($this->member['password'] != ''){
	    if(md5($oldpaypwd) != $this->member['password']) $this->message('原密码不正确');
		}
		if(empty($newpaypwd)) $this->message('emptynewpwd');
		if($newpaypwd !=  $verifynewpaypwd) $this->message('member_twopwdnoequale');
		$data['password'] = md5($newpaypwd);
		$this->mysql->update(Mysite::$app->config['tablepre'].'member',$data,"uid ='".$this->member['uid']."' ");	
	#	 ICookie::clear('uid'); 
		 $this->success('success'); 
	 }
	 
	  function safepwd(){  //支付密码
		//'oldpaypwd':$('#oldpaypwd').val(),'newpaypwd':$('#newpaypwd').val(),'verifynewpaypwd':$('#').val()
		$this->checkmemberlogin();
		$oldpaypwd = trim(IFilter::act(IReq::get('oldpaypwd')));
		$newpaypwd = trim(IFilter::act(IReq::get('newpaypwd')));
		$verifynewpaypwd = trim(IFilter::act(IReq::get('verifynewpaypwd')));
		if($this->member['safepwd'] != ''){
	    if(md5($oldpaypwd) != $this->member['safepwd']) $this->message('member_safepwderr');
		}
		if(empty($newpaypwd)) $this->message('emptynewpwd');
		if($newpaypwd !=  $verifynewpaypwd) $this->message('member_twopwdnoequale');
		$data['safepwd'] = md5($newpaypwd);
		$this->mysql->update(Mysite::$app->config['tablepre'].'member',$data,"uid ='".$this->member['uid']."' ");	
		 $this->success('success'); 
	 }
	 
	 function edituser(){
	 	$this->checkmemberlogin();
	  $data['showaction'] = IFilter::act(IReq::get('showaction'));  
		$data['sitetitle'] = '修改密码';
		 Mysite::$app->setdata($data);
	 }
	 function saveuser(){ 
	 	 $this->checkmemberlogin();
	 	  $controlname = IFilter::act(IReq::get('controlname')); 
 	     switch($controlname)
 	     {
 	     	  case 'username':
 	     	    $arra['username'] = trim(IFilter::act(IReq::get('obj')));  
 	     	    if(!(IValidate::len($arra['username'],3,20)))$this->message('member_usernamelent3to30');  
 	     	    $checkinfo = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."member where username='".$arra['username']."'");  
 	     	    if($checkinfo > 0) $this->message('member_repeatname');
 	     	    $this->mysql->update(Mysite::$app->config['tablepre'].'member',$arra,'uid = '.$this->member['uid'].' ');  
 	     	     $this->success('success'); 
 	     	  break;
 	     	  case 'email':
 	     	    $arra['email'] = trim(IFilter::act(IReq::get('obj')));  
 	     	     if(!empty($this->member['email'])) $this->message('member_cantemail');
 	     	    if(!(IValidate::email($arra['email'])))$this->message('erremail');  
 	     	    $checkinfo = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."member where email='".$arra['email']."'");  
 	     	    if($checkinfo > 0) $this->message('exitemail');
 	     	    $this->mysql->update(Mysite::$app->config['tablepre'].'member',$arra,'uid = '.$this->member['uid'].' ');  
 	     	     $this->success('success'); 
 	     	  break;
 	     	  case 'mobile':
 	     	    $arra['phone'] = trim(IFilter::act(IReq::get('obj')));  
 	     	    if(!empty($this->member['phone'])) $this->message('member_cantphone');
 	     	    if(!(IValidate::suremobi($arra['phone'])))$this->message('errphone');  
 	     	     $checkinfo = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."member where phone='".$arra['phone']."'");   
 	     	    if($checkinfo > 0) $this->message('exitphone');
 	     	    $checkinfo = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."member where phone='".$arra['phone']."'");  
 	     	    $this->mysql->update(Mysite::$app->config['tablepre'].'member',$arra,'uid = '.$this->member['uid'].' ');  
 	     	     $this->success('success'); 
 	     	  break;
 	     	  case 'pwd':
 	     	    $pwd =  IFilter::act(IReq::get('pwd')); 
           	$newpwd = IFilter::act(IReq::get('newpwd'));  
	          $newpwd2 = IFilter::act(IReq::get('newpwd2'));  
	          if(empty($pwd))$this->message('oldpwderr'); 
	          $checkpass = md5($pwd); 
	          if($checkpass != $this->member['password']) $this->message('oldpwderr');  
	          if(empty($newpwd))$this->message('emptynewpwd'); 
	          if($newpwd != $newpwd2)$this->message('member_twopwdnoequale');  
	          $arr['password'] = md5($newpwd);  
            $this->mysql->update(Mysite::$app->config['tablepre'].'member',$arr,"uid='".$this->member['uid']."'"); 
            $this->memberCls->loginout();
	        	$this->success('success'); 
 	     	  default:
 	     	  $this->message('nodefined_func');
 	     	  break;
 	     	  
 	     } 
	 }
	  function linktest(){
	 	  $logintype = IFilter::act(IReq::get('logintype')); 
		  if(in_array($logintype,array('qq','sina'))){
			 $link =  Mysite::$app->config['siteurl']."/plug/login/".$logintype."/login.php";
			  $this->message('',$link);  
		  }else{
			  	$link = IUrl::creatUrl('member/login');
						 
			 $this->message('未定义的接口',$link);  
		  }
	 	  
	 }
	 function otherlogin(){
	  
	 	  $logintype = IFilter::act(IReq::get('logintype')); 
		 
	 	  if(empty($logintype)){
	 		    $this->message('other_emptyapi'); 
	 	  } 
	 	  $logindir = hopedir.'/plug/login/'.$logintype;
		#  print_r($logindir);
		  
	 	  if(!file_exists($logindir.'/back.php'))
      { 
      	$this->error('other_emptyapi');
       } 
       $apiinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."otherlogin where loginname='".$logintype."'  ");
	 	   if(empty($apiinfo)){
	 	   	     $this->message('other_notinstallapi');
	 	   }

	 	   include_once($logindir.'/back.php');  
		   
	 }
	 function bandaout(){
	 	  $logintype = ICookie::get('adlogintype');
		  
  	 	   $token = ICookie::get('adtoken');
	 	   $openid = ICookie::get('adopenid'); 
	 	   if(empty($logintype)){
	 	   	   $this->message('other_emptyapi');
	 	   }
	 	   if(!empty($this->member['uid'])){ 
	 	   	  $this->message('member_islogin'); 
	 	   } 
	 	   $apiinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."otherlogin where loginname='".$logintype."'  ");
	 	   if(empty($apiinfo)){
	 	   	     $this->message('other_notinstallapi');
	 	   }
	 	   $data['apiinfo'] = $apiinfo;
	 	   $data['uid'] = $this->member['uid'];
	 	   $data['allowedcode'] =  Mysite::$app->config['allowedcode']; 
	 	   $data['apiuname'] = ICookie::get('apiuname');
	 	   $data['apiemail'] = ICookie::get('apiemail');
	 	   $data['apilogo'] = ICookie::get('apilogo');
	 	   
	 	   Mysite::$app->setdata($data); 
	 }
	 function saveregester()
	 {
	 	 if(!empty($this->member['uid']))
	 	 {
	 		 $this->message('member_islogin');
	 	 }
	   $mid = IFilter::act(IReq::get('mid'));
	   $tname = IFilter::act(IReq::get('tname'));  
       $password = IFilter::act(IReq::get('pwd')); 
       $email = IFilter::act(IReq::get('email'));  
       $phone = IFilter::act(IReq::get('phone')); 
	   $password2 = IFilter::act(IReq::get('pwd2'));  
	   $phoneyan =  IFilter::act(IReq::get('phoneyan')); 
	   $checklogin =  IFilter::act(IReq::get('checklogin'));
	   if($password2 != $password){
		     $this->message('member_twopwdnoequale');
		 }
	
		    if(Mysite::$app->config['regestercode'] == 1)
		    {
		    	   if(empty($phoneyan)) $this->message('member_codeerr');
		    	   if(!empty($phone)){
		    	   	    $checkcode =    ICookie::get('regphonecode');
		    	   	    //modify by rain
		    	   	   // if($phoneyan != $checkcode) $this->message('member_codeerr');
		    	   }elseif(!empty($email)){
		    	   	   $checkcode =    ICookie::get('regemailcode');
		    	   	   if($phoneyan != $checkcode) $this->message('member_codeerr1');
		    	   }
		    }else{
				if(Mysite::$app->config['allowedcode'] == 1)
				 {
					   $Captcha = IFilter::act(IReq::get('Captcha'));
					  if($Captcha != ICookie::get('Captcha')) 	$this->message('member_codeerr'); 
				 }
		   }
	#  }
		  
		 //code check 外面
		 
		 if($this->memberCls->regester($mid,$email,$tname,$password,$phone,5)){ 
		 	   $this->memberCls->login($tname,$password);
		 	   $this->success('success');
		 }else{
		 	 $this->message($this->memberCls->ero());
		 } 
	 }
	 
	 function checkemail()
	 { 
	     $uname = IFilter::act(IReq::get('uname'));   
	    if($this->memberCls->checkemail($uname)){ 
	    	$this->success('error');
	     
	    }else{  
	      $this->message('true');
	    } 
	 }
	 function checkname()
	 {
	    $uname = IFilter::act(IReq::get('uname'));    
	    if($this->memberCls->checkname($uname)){
	    	$this->success('error');
	    }else{
	    	 $this->message($this->memberCls->ero());
	    } 
	 }
	function savefind(){ 
	 	  $uname =IFilter::act(IReq::get('uname'));  
	 	 if($this->memberCls->findpwd($uname)){
	 	 	  $this->success('success');
	 	 }else{ 
	 	 	$this->message($this->memberCls->ero());
	 	 } 
	}
	function payonline(){ 
		//在线支付
		$this->checkmemberlogin();
	 	$paytype =  IReq::get('paydotype') ;
		 
	//	$paytype='alipay';
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
	 
	//发送邮箱验证码
	function regesteremail(){
		  $regestercode = Mysite::$app->config['regestercode'];
		   $checkcode =    ICookie::get('regemailcode');
		   $checkphone =   ICookie::get('regemail');
		   $checktime =   ICookie::get('regtime'); 
      if(empty($regestercode)){
    	 	echo 'noshow(\'不需要验证CODE\')';
    	 	exit;
    	} 
      if(!empty($checkcode)){
      	  $backtime = $checktime-time();
		  	 if($backtime > 0){ 
		  	   echo 'showsendemail(\''.$checkphone.'\','.$backtime.')';
		  	   exit;
		  	 }
		  } 
    	if(!empty($this->member['uid'])){
    	  echo 'noshow(\'已登陆\')';
    	  exit;
    	} 
      $email = IFilter::act(IReq::get('email')); 
      if(!(IValidate::email($email))){
      		echo '';
      		exit;
      }
      $userinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where email='".$email."' ");
      if(!empty($userinfo))
      {
        	 echo 'noshow(\'邮箱已注册\')';
        	 exit;
      } 
       
      
       $makecode =  mt_rand(10000,99999);
       $title =Mysite::$app->config['sitename'].'注册验证码';
       $smtp = new ISmtp(Mysite::$app->config['smpt'],25,Mysite::$app->config['emailname'],Mysite::$app->config['emailpwd'],false); 
       $content = '欢迎注册'.Mysite::$app->config['sitename'].'会员,注册验证码为:'.$makecode;
       $info = $smtp->send($email, Mysite::$app->config['emailname'],$title,$content , "" , "HTML" , "" , "");  
      ICookie::set('regemailcode',$makecode,180);
      ICookie::set('regemail',$email,180);
      $longtime = time()+180;
      ICookie::set('regtime',$longtime,180);
      echo 'showsendemail(\''.$email.'\',180)';
      exit; 
	}
	//发送手机验证码
	function regesterphone(){
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
      		echo '';
      		exit;
      }
      $userinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where phone='".$phone."' ");
      if(!empty($userinfo))
      {
        	 echo 'noshow(\'手机号码已注册\')';
        	 exit;
      } 
      $makecode =  mt_rand(10000,99999);
      
   /* 
	     $contents =  '欢迎注册'.Mysite::$app->config['sitename'].'会员,验证码为：'.$makecode; 
	     $phonecode =new phonecode($this->mysql,0,$phone);
		 $phonecode->sendother($contents); 							
      
      ICookie::set('regphonecode',$makecode,90);
      ICookie::set('regphone',$phone,90);
      $longtime = time()+90;
      ICookie::set('regtime',$longtime,90);
      echo 'showsend(\''.$phone.'\',90)';
      exit; 
	}
	 */
	 define('hopedir', dirname(__FILE__).DIRECTORY_SEPARATOR);  
include hopedir."plug/"."alidayu/TopSdk.php";
$c = new TopClient;
$c->appkey = "23345507";
$c->secretKey = "f585620e86a17c897fa6c4e8f3f5ddd6";
$req = new AlibabaAliqinFcSmsNumSendRequest;
$req->setExtend("123456");
$req->setSmsType("normal");
$req->setSmsFreeSignName("登录验证");
$req->setSmsParam("{\"code\": \"".$makecode."\", \"product\": \"【外卖订餐系统】\"}");
 $req->setRecNum($phone);
$req->setSmsTemplateCode("SMS_7805160"); 
$resp = $c->execute($req); 
#print_r($resp);
 if(   ($resp->code) >  0  ){ 
 	 logwrite('大鱼短信登录发送验证码发送错误：'.$resp->sub_msg);   
    echo 'noshow(\''.$resp->sub_msg.'\')';
    exit;
}else{
	logwrite('大鱼短信登录发送验证码发送成功！');    
}  
      
      ICookie::set('regphonecode',$makecode,90);
      ICookie::set('regphone',$phone,90);
      $longtime = time()+90;
      ICookie::set('regtime',$longtime,90);
      echo 'showsend(\''.$phone.'\',90)';
      exit; 
	}
	 
	function shoploginin(){
		$mid = $this->mid;
		  $uname = IFilter::act(IReq::get('uname')); 
	    $pwd = IFilter::act(IReq::get('pwd'));  
	   if(empty($uname)) $this->message('member_emptyname');
		 if(empty($pwd)) $this->message('member_emptypwd');  
		 if(Mysite::$app->config['allowedcode'] == 1)
	   {
		 	     $Captcha = IFilter::act(IReq::get('Captcha'));
		 	     if($Captcha != ICookie::get('Captcha')) 	$this->message('member_codeerr'); 
	   }  
	   
	   if(!$this->memberCls->login($mid,$uname,$pwd)){ 
	       $this->message($this->memberCls->ero()); 
	   }   
	   $checkuid = $this->memberCls->getuid();
	   $userinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop where    is_pass=1 and uid=".$checkuid." ");
     if(empty($userinfo)) $this->message('shop_noexit'); 
	    ICookie::set('adminshopid',$userinfo['id'],86400); 
	    $this->success('success');
	}
	 
	   function resetpwd()
	 {
	 	 if(!empty($this->member['uid']))
	 	 {
	 		 $link = IUrl::creatUrl('member/base');
       $this->message('',$link); 
	 	 }
	 	 $username = trim(IFilter::act(IReq::get('username'))); 
     $sign = trim(IFilter::act(IReq::get('sign')));
     $uid = intval(IFilter::act(IReq::get('uid')));
     $link = IUrl::creatUrl('member/findpwd');
     $newpwd = trim(IFilter::act(IReq::get('newpwd'))); 
     $newpwd2 = trim(IFilter::act(IReq::get('newpwd2')));  
     $data['error'] = '';
     if(!empty($newpwd)){
     if($this->memberCls->resetpwd($username,$sign,$uid,$newpwd,$newpwd2)){
     	if($this->memberCls->ero() == 'ok'){
     		 $link = IUrl::creatUrl('member/login');
            $this->message('success',$link); 
     	}else{
      	$data['error'] = $this->memberCls->ero();
     	} 
     	
     }else{
     //$data['error'] = $this->memberCls->ero();
     	$link = IUrl::creatUrl('member/findpwd');
      $this->message($this->memberCls->ero(),$link); 
     }
     }
     $data['sitetitle'] = '重置密码';
     $data['actionlink'] = '/index.php/member/resetpwd/uid/'.$uid.'/username/'.$username.'/sign/'.$sign;
    Mysite::$app->setdata($data); 
	 }
	   
  function drawbacklog(){
  	 if(empty($this->member['uid']))
	 	 {
	 		 $link = IUrl::creatUrl('member/login');
       $this->message('',$link); 
	 	 }
   
  }
 function savedrawbacklog(){
		if(empty($this->member['uid'])){
			$this->message('member_nologin');
		}
	 	 
		$dno = trim(IFilter::act(IReq::get('dno'))); 
		$data['reason'] = trim(IFilter::act(IReq::get('reason'))); 
		$data['content'] = trim(IFilter::act(IReq::get('content'))); 
		$data['phone'] = trim(IFilter::act(IReq::get('phone'))); 
		$data['contactname'] = trim(IFilter::act(IReq::get('contactname')));  
		$typeid = intval(IFilter::act(IReq::get('typeid')));
		if(empty($dno)) $this->message('order_noexit');
		if($typeid == 1){
			$orderinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where dno='".$dno."' "); 
			if(empty($orderinfo)) $this->message('order_noexit');
			if($orderinfo['buyeruid'] != $this->member['uid']) $this->message('order_baknoown');
			if($orderinfo['paystatus'] != 1) $this->message('order_nopay');
			if($orderinfo['status'] < 1 && $orderinfo['status'] < 3)$this->message('order_cantdoorder');
			if($orderinfo['paytype'] == 'outpay'||empty($orderinfo['paytype'])) $this->message('order_iswaitcantbak');
			if(!empty($orderinfo['is_reback'])) $this->message('order_bakrepeat');
			$checklog = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."drawbacklog where orderid='".$orderinfo['id']."' "); 
		}else{
			if(empty($data['reason'])) $this->message('order_bakreason');
			if(empty($data['content'])) $this->message('order_bakcontent'); 
			if(!(IValidate::suremobi($data['phone']))) $this->message('errphone');
			if(empty($data['contactname'])) $this->message('emptycontact');
			$orderinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where dno='".$dno."' "); 
			if(empty($orderinfo)) $this->message('order_noexit');
			if($orderinfo['buyeruid'] != $this->member['uid']) $this->message('order_baknoown');
			if($orderinfo['paystatus'] != 1) $this->message('order_nopay');
			if($orderinfo['status'] < 1 && $orderinfo['status'] < 3)$this->message('order_cantdoorder');
			if($orderinfo['paytype'] == 'outpay'||empty($orderinfo['paytype'])) $this->message('order_iswaitcantbak');
			if(!empty($orderinfo['is_reback'])) $this->message('order_bakrepeat');
			$checklog = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."drawbacklog where orderid='".$orderinfo['id']."' "); 
			if(!empty($checklog)) $this->message('order_bakrepeat');
		}
		$data['orderid'] = $orderinfo['id'];
		$data['uid'] = $this->member['uid'];
		$data['username'] = $this->member['username'];
		$data['status'] = 0;
		$data['addtime'] = time();
		$data['cost'] = $orderinfo['allcost'];
		$data['admin_id'] = $orderinfo['admin_id'];
		$data['type'] = $typeid;
		$this->mysql->insert(Mysite::$app->config['tablepre'].'drawbacklog',$data);   
		$udata['is_reback'] = 1;
		$this->mysql->update(Mysite::$app->config['tablepre'].'order',$udata,"id='".$orderinfo['id']."'"); 
		$this->success('success'); 
	}
   
    
   /* 新增代理商功能 */
      
	  
   //代理商后台管理员登陆
	 function saveagentlogin(){
	 	 $signup_name =  IFilter::act(IReq::get('signup_name'));  
    	   $signup_password =  IFilter::act(IReq::get('signup_password'));   
  	   if(!empty($signup_name)){
  	       	$userinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."admin where username ='".$signup_name."'"); 			 
			if( $userinfo['groupid'] != 4 ){
				$this->message("不是代理商用户！");
			}
  	     	  if(empty($signup_password))
  	        {
  	            $this->message('密码为空！'); 	 
  	        }
  	        if($userinfo['password'] != md5($signup_password))
  	       {
  	            $this->message('密码不正确！'); 	  
  	        }
  	        $data['loginip'] = IClient::getIp();
  	        $data['logintime'] = time();
  	       $this->mysql->update(Mysite::$app->config['tablepre'].'admin',$data,"uid='".$userinfo['uid']."'");	
         	 ICookie::set('agentadminname',$userinfo['username'],86400);
           ICookie::set('agentadminpwd',$signup_password,86400);
           ICookie::set('agentadminuid',$userinfo['uid'],86400);  
  	   	   $this->success('success'); 
  	   }
	 }
	 
	 //代理商后台管理员退出
	 function agentadminloginout(){
	 	 ICookie::clear('agentadminname'); 
	    ICookie::clear('agentadminpwd');  
	    ICookie::clear('agentadminuid');  
	    ICookie::clear('agentadminshopid'); 
      $link = IUrl::creatUrl('member/agentlogin');
      $this->refunction('',$link); 
	 }
  
    
  
  
}



?>