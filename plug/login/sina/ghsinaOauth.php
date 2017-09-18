<?php 
Class ghsinaOauth  {
	//模板展示
	public $app_id;//登录APPID
	public $app_secret;//登录密钥
	public $redirect;//跳转页面
	public $login_type;//登录类型 
	public $back_url;//回调地址
	public $wdb;
	public $config;
	public $error;
	public $openid;//用户开放ID
	public $token;
	public $osina;
	public $apiinfo;
	public $mysql;
	public function init(){   
		 spl_autoload_register('Mysite::autoload');   		 
		 $this->mysql = new mysql_class();  
		  require_once(plugdir."/login/sina/saetv2.ex.class.php");  
		 $this->app_id = '';
		 $this->app_secret = ''; 
		 $this->back_url = Mysite::$app->config['siteurl'].'/plug/login/sina/login.php'; 
		 $this->initapi(); 
	} 
	//初始化登录接口
	public function initapi(){  
		$info = $this->mysql->select_one(" select * from `".Mysite::$app->config['tablepre']."otherlogin`   where `loginname`='sina' "); 
        
		 if(empty($info)){
			 echo '新浪登录接口未安装';
			 exit;
		 }
		 if(empty($info['temp'])){
			 echo '新浪登录接口未设置';
			 exit;
		 } 
		 $tempall = json_decode($info['temp'],true); 
		 $this->app_id = $tempall['appid'];
	     $this->app_secret =  $tempall['appkey'];
		 $this->apiinfo = $tempall;  
		 $this->osina = new SaeTOAuthV2( $this->app_id , $this->app_secret );  
	} 
	public function gettable(){
		return 'oauthqq';
	}
	 public function login(){  
		$this->init();
			if (isset($_REQUEST['code'])) {
				$keys = array();
				$keys['code'] = $_REQUEST['code'];
				$keys['redirect_uri'] = $this->back_url;
				try {
					$token = $this->osina->getAccessToken( 'code', $keys ) ; 
					if(!empty($token)){
						$_SESSION['token'] = $token;
						setcookie( 'weibojs_'.$this->osina->client_id, http_build_query($token) );
						$c = new SaeTClientV2( $this->app_id , $this->app_secret , $token['access_token'] );
						$ms  = $c->home_timeline(); // done
						$uid_get = $c->get_uid();
						$openid = $uid_get['uid'];
						$user_message = $c->show_user_by_id($openid);//根据ID获取用户等基本信息
						//$opid = $openid;
						$acs = $token['access_token'];  
						$uname = $user_message['screen_name'];
						$uemail = $uid.'@sina.com';
						$ulogo = $user_message['profile_image_url'];
						
						 

						 
						 
						 
						 
						 $oauthqqinfo =$this->mysql->select_one(" select * from ".scfg::init()->tablepre."oauth  where openid ='".$openid."' and type='sina' "); 
						 $sinainfo = array();
						 if(empty($oauthqqinfo)){
							 
							  $tempuid = intval(ICookie::get('uid')); 
							  $data['uid'] = $tempuid;
								$data['token'] = $this->token['access_token'];
								$data['openid'] = $openid;
								$data['type'] = 'sina';
								$data['addtime'] = time();
								$this->mysql->insert(Mysite::$app->config['tablepre'].'oauth',$data);  
								if(!empty($this->memberinfo)){
									$link = IUrl::creatUrl('member/base');
									$this->refunction('',$link);
								}else{  
								  ICookie::set('adlogintype','sina',86400); 
								  ICookie::set('adtoken',$this->token['access_token'],86400); 
								  ICookie::set('adopenid',$openid,86400); 
								  ICookie::set('nickname',$openid,86400); 
								  
								  ICookie::set('apiuname',$uname,86400); 
								  ICookie::set('apiemail',$uemail,86400); 
								  ICookie::set('apilogo',$ulogo,86400);  
								  	$link = IUrl::creatUrl('member/bandaout');
									$this->refunction('',$link);
							  } 
						 }else{ 
						 
							if($oauthqqinfo['uid'] == 0){ 
								  /***设置session**/
								   $tempuid = intval(ICookie::get('uid')); 
								  if(!empty($tempuid)){
									$this->mysql->update(Mysite::$app->config['tablepre'].'oauth',array('uid'=>$tempuid),"openid='".$openid."' and type = 'sina'");
									$link = IUrl::creatUrl('member/base');
									 $this->refunction('',$link);
								  }else{
									ICookie::set('adlogintype','sina',86400); 
									  ICookie::set('adtoken',$this->token['access_token'],86400); 
									  ICookie::set('adopenid',$openid,86400); 
									  ICookie::set('nickname',$openid,86400); 
									  
									  ICookie::set('apiuname',$uname,86400); 
									  ICookie::set('apiemail',$uemail,86400); 
									  ICookie::set('apilogo',$ulogo,86400);  
									  $link = IUrl::creatUrl('member/bandaout');
										$this->refunction('',$link);
								  }
							}else{   
								  if($uid > 0){
									   $link = IUrl::creatUrl('member/base');/*跳转到用户中心*/ 
									   $this->refunction('',$link);
								  }else{  
									$userinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where  uid  = '".$oauthqqinfo['uid']."'"); 
									if(empty($userinfo)){
										 $link = IUrl::creatUrl('member/login');
										$this->refunction('账号未查找到,关联账号是否被删除',$link);
									}
									$data['loginip'] = IClient::getIp();
									$data['logintime'] = time();
									$checktime = date('Y-m-d',time());
									$checktime = strtotime($checktime); 
									$this->mysql->update(Mysite::$app->config['tablepre'].'member',$data,"uid='".$userinfo['uid']."'");	 
										ICookie::set('logintype','sina',86400);
										ICookie::set('uid',$userinfo['uid'],86400); 
										$link = IUrl::creatUrl('member/base');/*跳转到用户中心*/
										 
										$this->refunction('',$link);
								  } 
							}
						 
						 
						 
						 } 
						return true; 
					}else{
						$this->dologinlink();
					}
				} catch (OAuthException $e) {
				     $this->dologinlink();
				}
			}else{
				$this->dologinlink();
			}
			  
	}
	public static function refunction($msg,$info=''){
   	  $newrul = empty($info)?Mysite::$app->config['siteurl']:$info;
	    header("Content-Type:text/html;charset=utf-8"); 
	    if(!empty($msg))
	    {
	    	 $lngcls = new languagecls();
	 			 $msg = $lngcls->show($msg);
			   echo '<script>alert(\''.$msg.'\');location.href=\''.$newrul.'\';</script>';
		  }else{
		     echo '<script>location.href=\''.$newrul.'\';</script>';
	  	}
      exit;
   }
   public static function success($msg,$link=''){
   	   $datatype = IFilter::act(IReq::get('datatype')); 
	 		if($datatype == 'json'){
	 			 echo json_encode(array('error'=>false,'msg'=>$msg)); 
	       exit; 
	 		}else{
	 			 self::refunction($msg,$link); 
	 		}
    	
   }
	private function dologinlink(){  
		$code_url = $this->osina->getAuthorizeURL( $this->back_url ); 
		header("Location:$code_url");
	}
	   
	public function __call($method,$arg){  
      print_r('请求函数不存在');
	}
 
} 
?>