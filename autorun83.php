<?php
 
header("Content-Type:text/html;charset=utf-8"); 
define('hopedir', dirname(__FILE__).DIRECTORY_SEPARATOR);  
$config = hopedir."config/hopeconfig.php";   
$cfg = include($config); 
 
$lnk = mysql_connect($cfg['dbhost'], $cfg['dbuser'], $cfg['dbpw']) or die ('Not connected : ' . mysql_error()); 
$version = mysql_get_server_info(); 
if($version > '4.1' && $cfg['dbcharset']) {
				mysql_query("SET NAMES '".$cfg['dbcharset']."'");
} 
if($version > '5.0') {
				mysql_query("SET sql_mode=''");
}
												
if(!@mysql_select_db($cfg['dbname'])){ 
				if(@mysql_error()) {
					echo '数据库连接失败';exit;
				} else {
					mysql_select_db($cfg['dbname']);
				}
} 
//mysql_query("ALTER TABLE `xiaozu_member` ADD `md_flag` INT( 1 ) NOT NULL DEFAULT '0' COMMENT '分钟数';");
//mysql_query("ALTER TABLE `xiaozu_onlinelog` ADD `paydotype` VARCHAR( 100 ) NOT NULL ");
// mysql_query("ALTER TABLE `xiaozu_orderps` ADD `dotype` INT( 1 ) NOT NULL DEFAULT '1'");
//mysql_query(" UPDATE   `xiaozu_member` SET  `username` =  'zem' WHERE  `xiaozu_member`.`username` ='zem123456789101112131415161718192021222324252627282930' LIMIT 1 ;  ");

// mysql_query("
//UPDATE  `xiaozu_shop` SET
//`point` =  '5',
//`pointcount` =  '1',
//`psservicepoint` =  '5',
//`psservicepointcount` =  '1' ;
//  ");


/*  mysql_query(" TRUNCATE TABLE  `xiaozu_juan` ;  "); */
//mysql_query("ALTER TABLE `xiaozu_shopmarket` ADD `interval_minit` INT( 2 ) NOT NULL DEFAULT '30' COMMENT '分钟数';");
// mysql_query("ALTER TABLE `xiaozu_shopfast` ADD `interval_minit` INT( 2 ) NOT NULL DEFAULT '30' COMMENT '分钟数';");
 // mysql_query(" ALTER TABLE  `xiaozu_goods` ADD  `virtualsellcount` INT( 11 ) NOT NULL COMMENT  '商品虚拟销量'; ");
// mysql_query(" ALTER TABLE  `xiaozu_shop` ADD  `virtualsellcounts` INT( 11 ) NULL COMMENT  '店铺虚拟总销量'; ");
// mysql_query(" ALTER TABLE  `xiaozu_comment` ADD  `virtualname` VARCHAR( 50 ) NULL COMMENT  ' 新增 虚拟评论人名称'; ");
/*
mysql_query("ALTER TABLE  `xiaozu_rule` ADD  `supporttype` VARCHAR( 150 ) NOT NULL COMMENT  '支持类型：1首单有效，2在线支付有效'; ");
mysql_query("ALTER TABLE  `xiaozu_rule` ADD  `supportplatform` VARCHAR( 150 ) NOT NULL COMMENT  '支持平台：1pc端，2微信端，3触屏端，4客户端（安卓苹果）'; ");

  


mysql_query(" ALTER TABLE  `xiaozu_juan` ADD  `shareuid` INT( 11 ) NOT NULL COMMENT  '分享者UID';  ");
mysql_query(" ALTER TABLE  `xiaozu_juan` ADD  `paytype` VARCHAR( 20 ) NULL COMMENT  '(1,2) 1支持货到付款2支持在线支付';  ");


mysql_query(" ALTER TABLE  `xiaozu_juanrule` CHANGE  `paytype`  `paytype` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT  '(1,2) 1支持货到付款2支持在线支付';  ");

mysql_query(" ALTER TABLE  `xiaozu_juanrule` ADD  `paytype` VARCHAR( 20 ) NULL COMMENT  '(0,1) 0支持货到付款1支持在线支付' AFTER  `endtime` ;  ");


mysql_query(" ALTER TABLE  `xiaozu_juan` ADD  `type` INT( 1 ) NULL COMMENT  '1充值2下单3推广';  ");



mysql_query("
CREATE TABLE IF NOT EXISTS `xiaozu_juanshowinfo` (
  `id` int(11) NOT NULL auto_increment,
  `type` int(1) NOT NULL COMMENT '类型 2为下单分享 3为推广分享',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `img` varchar(255) NOT NULL COMMENT '分享的图标',
  `describe` varchar(255) NOT NULL COMMENT '简述',
  `bigimg` varchar(255) NOT NULL COMMENT '展示大图',
  `color` varchar(10) NOT NULL COMMENT '背景色调',
  `actcolor` varchar(10) NOT NULL COMMENT '活动规则背景色调',
  `avtrule` text NOT NULL COMMENT '活动规则',
  `orderid` int(11) NOT NULL,
  `addtime` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='分享优惠券展示信息' AUTO_INCREMENT=2 ;
");

mysql_query("
INSERT INTO `xiaozu_juanshowinfo` (`id`, `type`, `title`, `img`, `describe`, `bigimg`, `color`, `actcolor`, `avtrule`, `orderid`, `addtime`) VALUES
(1, 2, '外卖人发红包啦，快去抢抢看~', '\/upload\/juan\/20160531192139979.png', '外卖人发红包啦，外卖神器~~抢抢抢！！！', '\/upload\/juan\/20160531192154111.png', '#FF3F26', '#FF8C7D', '11111ds', 1, 1464693859);
  
  ");
mysql_query(" 
ALTER TABLE  `xiaozu_juan` ADD  `orderid` INT( 11 )   NULL ;
");
mysql_query("    ALTER TABLE  `xiaozu_juan` ADD  `bangphone` VARCHAR( 20 ) NULL COMMENT  '绑定手机号';   ");

mysql_query("   
CREATE TABLE IF NOT EXISTS `xiaozu_juanrule` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL COMMENT '名称',
  `type` int(1) NOT NULL COMMENT '营销类型 1充值  2下单成功分享优惠券  3会员中心推广优惠券',
  `juantotalcost` decimal(12,2) default NULL COMMENT '充值赠送优惠券总金额',
  `juannum` int(11) NOT NULL COMMENT '送多少张优惠券(type3固定为1张)',
  `jiancostmin` decimal(8,2) NOT NULL COMMENT '满---最小',
  `jiancostmax` decimal(8,2) NOT NULL COMMENT '满---最大',
  `jiacostmin` decimal(8,2) NOT NULL COMMENT '减---最小',
  `jiacostmax` decimal(8,2) NOT NULL COMMENT '减---最大',
  `endtime` int(11) NOT NULL COMMENT '失效时间',
  `is_open` int(1) NOT NULL default '1' COMMENT '是否启用默认1开启',
  `addtime` int(11) NOT NULL COMMENT '添加时间',
  `orderid` int(11) NOT NULL COMMENT '排序',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='分享优惠券规则类型' AUTO_INCREMENT=4 ;
  ");
 

mysql_query("
INSERT INTO `xiaozu_juanrule` (`id`, `name`, `type`, `juantotalcost`, `juannum`, `jiancostmin`, `jiancostmax`, `jiacostmin`, `jiacostmax`, `endtime`, `is_open`, `addtime`, `orderid`) VALUES
(1, '充值优惠券类型', 1, 100.00, 0, 100.00, 100.00, 20.00, 20.00, 1464917410, 1, 1464571810, 1),
(2, '下单成功分享优惠券', 2, NULL, 6, 20.00, 50.00, 3.00, 5.00, 1464678663, 1, 1464505863, 2),
(3, '推广优惠券', 3, NULL, 1, 10.00, 30.00, 2.00, 4.00, 1464765097, 1, 1464505897, 3);
");


mysql_query("
CREATE TABLE IF NOT EXISTS `xiaozu_rechargecost` (
  `id` int(11) NOT NULL auto_increment,
  `cost` decimal(12,2) NOT NULL COMMENT '充值金额',
  `is_sendcost` int(1) NOT NULL default '1' COMMENT '是否赠送账户余额   默认1赠送0不',
  `sendcost` decimal(12,2) NOT NULL COMMENT '赠送金额',
  `is_sendjuan` int(1) NOT NULL COMMENT '是否赠送优惠券  默认0不赠送1赠送',
  `sendjuancost` decimal(12,2) default NULL COMMENT '赠送优惠券总额',
  `orderid` int(11) NOT NULL COMMENT '排序',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;
");


mysql_query("
INSERT INTO `xiaozu_rechargecost` (`id`, `cost`, `is_sendcost`, `sendcost`, `is_sendjuan`, `sendjuancost`, `orderid`) VALUES
(1, 1.00, 1, 1.00, 1, 100.00, 1),
(2, 2.00, 1, 2.00, 0, 0.00, 2),
(3, 3.00, 0, 0.00, 1, 10.00, 3);

");

  
mysql_query("ALTER TABLE `xiaozu_onlinelog` CHANGE `dno` `dno` VARCHAR( 100 ) NOT NULL ");

*/

//mysql_query("ALTER TABLE  `xiaozu_goods` ADD  `is_live` CHAR(1) NOT NULL COMMENT  '上、下架'; ");
//mysql_query("UPDATE  `xiaozu_goods` SET `is_live` =  '1';");


//mysql_query("
//CREATE TABLE `xiaozu_helpcenter` (
//  `id` int(20) NOT NULL AUTO_INCREMENT,
//  `content` text NOT NULL,
//  `addtime` int(11) NOT NULL,
//  `title` varchar(100) NOT NULL,
//  `orderid` int(5) NOT NULL,
//  `seo_key` varchar(255) DEFAULT NULL,
//  `seo_content` varchar(255) DEFAULT NULL,
//  PRIMARY KEY (`id`)
//) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
//");




echo mysql_error();
mysql_close($lnk);		 
echo 'ok';
exit;		
?>