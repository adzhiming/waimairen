<?php 

/**
 * @class 手机验证码
 * @brief 促销规则 
 */
class  phonecode
{
	
	private $mysql;//数据库连接
	//短信主要类容
	private $maincontent = array(
								0=>'用户注册，验证码:',
								1=>'登录，验证码:',
								2=>'找回密码，验证码:',
								3=>'更换手机，号验证码:',
								4=>'手机快捷登录，验证码:',
								5=>'更换密码，验证码:',
								6=>'下单，验证码:',
								7=>'APP调用用户登录验证',
								8=>'新绑定手机号',
							);
 	public   $qianming ;//短信签名
	private $sendtype;//验证发送类型
	private $typearray = array(0,1,2,3,4,5,6,7,8);//用户检测代码
	private $phone;//手机号
	private   $limittime = 120;//短信有效时间
	private $code;//验证码;
	
	private $errId;
	
	
	
		//初始化函数  type 0.用户注册  1登录验证 2.找回密码   3更换手机号  4手机快捷登录   5更换密码
		//IFilter::act(IReq::get('phone'));
		//初始化  $mysql 数据库连接  $sendtype 验证码类型=  phone 接受手机号  
	function __construct($mysql,$type,$phone=''){ 	 
	  	 $this->mysql = $mysql;  
		 $this->sendtype =  $type;
		 $this->phone = empty($phone)?intval(IFilter::act(IReq::get('phone'))):$phone; 
		 $this->qianming =  Mysite::$app->config['sitename'];
		 //$this->tablepre = Mysite::$app->config['mobileapp']; 
	}
	public function sendother($msg){
		  $contents =  '【'.$this->qianming.'】'.$msg; 
	     $APIServer = 'http://www.waimairen.com/sendtophone.php?apiuid='.Mysite::$app->config['apiuid'];  
		 $weblink = $APIServer.'&key='.trim(Mysite::$app->config['sms86ac']).'&code='.trim(Mysite::$app->config['sms86pd']).'&hm='.$this->phone.'&msgcontent='.urlencode($contents).''; 
	     $contentcccc =  file_get_contents($weblink);   
	     logwrite('短信发送结果:'.$contentcccc);   
	}
	//
	public function sendcode(){
		 $checkcancode = Mysite::$app->config['allowedcode'];
		 if($checkcancode != 1){
				 $this->errId = '平台未开启验证码功能';
			    return false; 
		 }
		 if(!IValidate::suremobi($this->phone)){
			 $this->errId = '手机号格式错误';
			  return false;
		 }  
		 if(!in_array($this->sendtype,$this->typearray)){
			  $this->errId = '未定义的发送类型';
			  return false;
		 } 
         if($this->sendtype == 2){
			  $checkmember = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where phone ='".$this->phone."'   order by uid desc limit 0,1");
			  if(empty($checkmember)){
				    $this->errId = '手机对应用户不存在';
					return false; 
			  } 
		 }elseif($this->sendtype == 3){
			  $checkmember = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where phone ='".$this->phone."'   order by uid desc limit 0,1");
			  if(empty($checkmember)){
				    $this->errId = '手机对应用户不存在';
					return false; 
			  } 
		 }elseif($this->sendtype == 4){
			 
			  
			  
			  
		 }elseif($this->sendtype == 5){
			  $checkmember = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where phone ='".$this->phone."'   order by uid desc limit 0,1");
			  if(empty($checkmember)){
				    $this->errId = '手机对应用户不存在';
					return false; 
			  } 
		 }elseif($this->sendtype == 0){
			  $checkmember = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where phone ='".$this->phone."'   order by uid desc limit 0,1");
			  if(!empty($checkmember)){
				    $this->errId = '手机号对应用户已存在';
					return false; 
			  } 
		 }elseif($this->sendtype == 8){
			 $checkmember = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where phone ='".$this->phone."'   order by uid desc limit 0,1");
			  if(!empty($checkmember)){
				    $this->errId = '手机号对应用户已存在';
					return false; 
			  } 
		 }	 
		 $checkphone = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."mobileapp where phone ='".$this->phone."'  and type='".$this->sendtype."' order by addtime desc limit 0,1");
		 if(!empty($checkphone)){
			$checktime = time()-$this->limittime; 
			if($checkphone['addtime'] > $checktime){
				// $this->errId = '验证码还未失效';
				 $this->code = $checkphone['code'];
				 return true;
			}
		 }
		 
	     $this->code = rand(1000,9999);  
		 $data['phone'] = $this->phone;
		 $data['addtime'] = time()+90;
		 $data['code'] = $this->code;
		 $data['type'] = $this->sendtype;
		 $this->mysql->insert(Mysite::$app->config['tablepre'].'mobileapp',$data);  
	     logwrite('短信发送结果:3の3');   
	     $contents =  '【'.$this->qianming.'】'.$this->maincontent[$this->sendtype].$this->code; 
	     $APIServer = 'http://www.waimairen.com/sendtophone.php?apiuid='.Mysite::$app->config['apiuid'];  
		 $weblink = $APIServer.'&key='.trim(Mysite::$app->config['sms86ac']).'&code='.trim(Mysite::$app->config['sms86pd']).'&hm='.$this->phone.'&msgcontent='.urlencode($contents).''; 
	     $contentcccc =  file_get_contents($weblink);  
		 
		 
		 
	     logwrite('短信发送结果:'.$contentcccc);   
	     return true; 
	}
	//校验验证码是否有效   $Inputcode 校验code
	public function checkcode($Inputcode){
		 $checkcancode = Mysite::$app->config['allowedcode'];
		 if($checkcancode != 1){ 
			    return true; 
		 }
		 if(!IValidate::suremobi($this->phone)){
			 $this->errId = '手机号格式错误';
			  return false;
		 }  
		 if(!in_array($this->sendtype,$this->typearray)){
			  $this->errId = '未定义的发送类型';
			  return false;
		 } 
		 $checkphone = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."mobileapp where phone ='".$this->phone."' and type='".$this->sendtype."'  order by addtime desc limit 0,1");
		 if(!empty($checkphone)){
			$checktime = time()-$this->limittime; 
			if($checkphone['addtime'] > $checktime){ 
			     if($Inputcode != $checkphone['code']){
					 $this->errId = '验证码错误';
					return false;
				 }
				 $this->code = $checkphone['code'];
				 return true;
			}else{
				$this->errId = '验证码已失效';
				return false;
			}
		 }else{
			  $this->errId = '该手机号未发送验证码';
			  return false;
		 }
	}
	public function getCode(){
		return $this->code; 
	}
	public function getError()
	{
		return $this->errId;
	}
    

}
?>