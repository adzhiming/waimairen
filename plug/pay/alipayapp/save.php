<?php   
if(!defined('IN_WaiMai')) {
	exit('Access Denied');
}
  echo "<meta charset='utf-8' />";
         echo "<script>parent.uploadsucess('已配置成功');</script>";   
         exit; 
$incFile = fopen($logindir."/".$idtype."/alipay.config.php","w+") or die("请设置plug\pay\alipayapp\alipay.config.php的权限为777"); 
$setting .= "<?php  \r\n";
$setting .=" \$alipay_config['partner']		= '".IReq::get('partner')."';\r\n";
$setting .=" \$alipay_config['private_key_path']			= 'key/rsa_private_key.pem';\r\n";
$setting .=" \$alipay_config['ali_public_key_path']    = 'key/alipay_public_key.pem';\r\n";
$setting .=" \$alipay_config['sign_type']= strtoupper('RSA');\r\n"; 
$setting .= " \$alipay_config['input_charset'] = strtolower('utf-8');\r\n";
$setting .= " \$alipay_config['cacert']    = getcwd().'\\\\cacert.pem';\r\n";
$setting .= " \$alipay_config['transport']    = 'http';\r\n";
$setting .= "?>";
$savedata['partner']=IReq::get('partner'); 
$savedata['seller_email'] = IReq::get('seller_email'); 
 if(fwrite($incFile, $setting)){
       
        $taskinfo =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."paylist where loginname='".$idtype."'  "); 
        
        	  include_once($logindir.'/'.$idtype.'/set.php');
        	  $ssdata['loginname'] = $idtype;
        	  $ssdata['logindesc'] = $setinfo['name']; 
        	  $ssdata['logourl'] = Mysite::$app->config['siteurl'].'/plug/pay/'.$idtype.'/images/'.$setinfo['logourl']; 
        	  $ssdata['temp'] = json_encode($savedata);
        	  $ssdata['type'] = 4;
        	  if(empty($taskinfo))
        {
        	 
  	      	$this->mysql->insert(Mysite::$app->config['tablepre'].'paylist',$ssdata);  //写消息表	 
        }	else{ 
        	   unset($ssdata['loginname']);
        	 	 $this->mysql->update(Mysite::$app->config['tablepre'].'paylist',$ssdata,"loginname='".$idtype."'"); 
        }
        echo "<meta charset='utf-8' />";
         echo "<script>parent.uploadsucess('配置成功');</script>";   
         exit; 
        	
  }else{
        echo "Error";
  } 
 
?>