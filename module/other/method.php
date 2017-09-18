<?php
class method   extends baseclass
{
	
	    public function adminupload()  // 会员中心申请开店
	 {
	 	 $func = IFilter::act(IReq::get('func'));
		 $obj = IReq::get('obj');
		 $uploaddir =IFilter::act(IReq::get('dir'));
	   if(is_array($_FILES)&& isset($_FILES['imgFile']))
	   {
	   	 $uploaddir = empty($uploaddir)?'goods':$uploaddir;
	  	 $json = new Services_JSON();
       $uploadpath = 'upload/'.$uploaddir.'/';
		   $filepath = '/upload/'.$uploaddir.'/';
       $upload = new upload($uploadpath,array('gif','jpg','jpge','doc','png'));//upload 自动生成压缩图片
       $file = $upload->getfile();
       if($upload->errno!=15&&$upload->errno!=0){
		     echo "<script>parent.".$func."(true,'".$obj."','".json_encode($upload->errmsg())."');</script>";
		   }else{
		      echo "<script>parent.".$func."(false,'".$obj."','".$filepath.$file[0]['saveName']."');</script>";

		   }
		   exit;
	   }
	   $data['obj'] = $obj;
	   $data['uploaddir'] = $uploaddir;
	   $data['func'] = $func;
	   Mysite::$app->setdata($data);
	 }
	
	 public function saveupload()
	 {
		  $json = new Services_JSON();
      $uploadpath = 'upload/goods/';
		  $filepath = '/upload/goods/';
      $upload = new upload($uploadpath,array('gif','jpg','jpge','png'));//upload
      $file = $upload->getfile();
     if($upload->errno!=15&&$upload->errno!=0) {
			$msg = $json->encode(array('error' => 1, 'message' => $upload->errmsg()));

		  }else{
			$msg = $json->encode(array('error' => 0, 'url' => $filepath.$file[0][saveName], 'trueurl' => $upload->returninfo['name']));
		 }
		 echo $msg;
		 exit;
	 }
	public function userupload() {
	 	$link = IUrl::creatUrlEx($this->mid,'member/login');
	 	if($this->member['uid'] == 0&&$this->admin['uid'] == 0)  $this->message('未登陆',$link);
	 	  
	 	$_FILES['imgFile'] = $_FILES['head'];  
	 	$type = IFilter::act(IReq::get('type')); 
	 	if(empty($type)) $this->message('未定义的操作');
		$json = new Services_JSON();
		$uploadpath = 'upload/user/';
		$filepath ='/upload/user/';
      	$upload = new upload($uploadpath,array('gif','jpg','jpge','png'));//upload
      	$file = $upload->getfile();
      	if($upload->errno!=15&&$upload->errno!=0) {
		    $this->message($upload->errmsg());
		}else{
		  	if($type == 'userlogo'){
		  	   $arr['logo'] = $filepath.$file[0]['saveName'];
		  	   $this->mysql->update(Mysite::$app->config['tablepre'].'member',$arr,'uid = '.$this->member['uid'].' ');
		  	}elseif($type == 'goods'){
		  		 $shopid = ICookie::get('adminshopid');
		  	  $gid = intval(IFilter::act(IReq::get('gid')));
		  	   $data['img'] = $filepath.$file[0]['saveName'];
		      $this->mysql->update(Mysite::$app->config['tablepre'].'goods',$data,"id='".$gid."' and shopid='".$shopid."'");
		  	}elseif($type == 'shoplogo'){
		  		$shopid = ICookie::get('adminshopid');
		  		if(!empty($shopid)){
		  			$data['shoplogo'] = $filepath.$file[0]['saveName'];
		  		    $this->mysql->update(Mysite::$app->config['tablepre'].'shop',$data,"id='".$shopid."'");
		  	  }
		  	}elseif($type == 'shophdimg'){
		  		$shopid = ICookie::get('adminshopid');
		  		if(!empty($shopid)){
		  			$data['shophdimg'] = $filepath.$file[0]['saveName'];
		  			$this->mysql->update(Mysite::$app->config['tablepre'].'shop',$data,"id='".$shopid."'");
		  		}
		  	}elseif($type == 'picurl'){//员工头像
		  		$shopid = ICookie::get('adminshopid');
		  		$id = intval(IFilter::act(IReq::get('id')));
		  		if(empty($id)){
		  			$this->message($filepath.$file[0]['saveName']);
		  		}
		  		if(!empty($shopid)){
		  			$data['picurl'] = $filepath.$file[0]['saveName'];
		  			$this->mysql->update(Mysite::$app->config['tablepre'].'staff',$data,"id='".$id."'");
		  		}
		  	}elseif($type == 'wechat_qrcode'){//微信二维码
		  		$shopid = ICookie::get('adminshopid');
		  		$id = intval(IFilter::act(IReq::get('id')));
		  		if(!empty($shopid)){
		  			$data['wechat_qrcode'] = $filepath.$file[0]['saveName'];
		  			$this->mysql->update(Mysite::$app->config['tablepre'].'staff',$data,"id='".$id."'");
		  		}
		  	}

		    $this->success($filepath.$file[0]['saveName']);
		}
	 }
	 function goodsupload(){
	 	 $link = IUrl::creatUrl('member/login');
	 	  if($this->member['uid'] == 0&&$this->admin['uid'] == 0)  $this->message('未登陆',$link);
	 	 $type = IReq::get('type');
		 $goodsid =  intval(IReq::get('goodsid'));
		 $shopid = ICookie::get('adminshopid');
		 if($shopid < 0){
		   echo '无权限操作';
		   exit;
		 }
	   if(is_array($_FILES)&& isset($_FILES['imgFile']))
	   {

	  	$json = new Services_JSON();
      $uploadpath = 'upload/shop/';
		  $filepath ='/upload/shop/';
      $upload = new upload($uploadpath,array('gif','jpg','jpge','doc','png'));//upload
      $file = $upload->getfile();
      if($upload->errno!=15&&$upload->errno!=0) {
		   echo "<script>parent.uploaderror('".json_encode($upload->errmsg())."');</script>";
		  }else{
		     	 if($goodsid > 0&& $shopid > 0){
		     	 	  $data['img'] = $filepath.$file[0]['saveName'];
		          $this->mysql->update(Mysite::$app->config['tablepre'].'goods',$data,"id='".$goodsid."' and shopid='".$shopid."'");
		     	 }
		       echo "<script>parent.uploadsucess('".$filepath.$file[0]['saveName']."');</script>";
		  }
		  exit;
	   }
	   $imgurl ='';
	   if($goodsid > 0 && $type == 'goods'){
	  	  $temp = $this->mysql->select_one("select img from ".Mysite::$app->config['tablepre']."goods where id='".$goodsid."' and shopid='".$shopid."'");
	  	  $imgurl = $temp['img'];
	   }
	    Mysite::$app->setdata(array('type'=>$type,'goodsid'=>$goodsid,'imgurl'=>$imgurl));
	 }

	//小二广告，活动图上传
	public function adsupload(){
	 	$link = IUrl::creatUrlEx($this->mid,'member/login');
	 	if($this->member['uid'] == 0&&$this->admin['uid'] == 0)  $this->message('未登陆',$link);
	 	
	 	$type = IFilter::act(IReq::get('type'));
	 	if(empty($type)) $this->message('未定义的操作'); 
	 	 
	 	$_FILES['imgFile'] = $_FILES[$type];  
 
	 	$urlad = IReq::get('urlad');
	 	$shopid = ICookie::get('adminshopid');
	 	
		$json = new Services_JSON();
		$uploadpath = 'upload/user/';
		$filepath ='/upload/user/';
      	$upload = new upload($uploadpath,array('gif','jpg','jpge','png'));//upload
      	$file = $upload->getfile();
  	 	
  	 	if($upload->errno!=15&&$upload->errno!=0) {
  	 	  	$link = IUrl::creatUrlEx($this->mid,'shopcenter/noopsychePetty');
		    $this->message($upload->errmsg(),$link);
		}else{
		  	$data['mid']     = $this->mid;
		  	$data['shopid']  = $shopid;
		 	$data['advtype'] = $type;
		  	$data['img']     = $filepath.$file[0]['saveName'];
		  	$data['linkurl'] = $urlad;

		  	$count = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."adv where advtype='".$type."' and mid=".$this->mid." and shopid=".$shopid." ");

		  	if(!empty($count)){
		  		$this->mysql->update(Mysite::$app->config['tablepre'].'adv',$data,"advtype='".$type."' and shopid='".$shopid."'");
		  	}else{
		  		$this->mysql->insert(Mysite::$app->config['tablepre'].'adv',$data);    
		  	}
		  	 
		    $link = IUrl::creatUrlEx($this->mid,'shopcenter/noopsychePetty');
			$this->message('上传成功！',$link);
   		
   		}
	
	}


}



?>