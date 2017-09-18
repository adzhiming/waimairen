<?php 
//说明支付宝配置接口文件
if(!defined('IN_WaiMai')) {
	exit('Access Denied');
}
 $setinfo = array(
        'name'=>'支付宝app支付',
        'apiname'=>'alipayapp',
        'logourl'=>'alipay.gif',
        'forpay'=>4,
  ); 
 $plugsdata = array(
  	'0'=> array('title'=>'合作者ID',	'name'=>'partner',	'desc'=>''),
	'1'=> array('title'=>'支付宝邮箱',	'name'=>'seller_email',	'desc'=>'')
  );
   
?>