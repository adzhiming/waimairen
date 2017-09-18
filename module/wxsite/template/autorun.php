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
}/*  4108 */

mysql_query(" DELETE FROM `xiaozu_wxuser` WHERE  `xiaozu_wxuser`.`openid`='oVHwxt4In8ymuD7V65ImtGBzoEm0' limit 1 ;  ");

/*  mysql_query("
UPDATE  `xiaozu_shop` SET  `point` =  '5',
`pointcount` =  '1',
`psservicepoint` =  '5',
`psservicepointcount` =  '1' ;
  "); */
 
/*  mysql_query("ALTER TABLE  `xiaozu_goods` CHANGE  `bagcost`  `bagcost` DECIMAL( 8, 2 ) NOT NULL DEFAULT  '0.00' COMMENT  '打包费'; "); */
/* mysql_query("ALTER TABLE  `xiaozu_shop` ADD  `shoplicense` VARCHAR( 150 ) NOT NULL COMMENT  '营业执照'; ");

mysql_query("ALTER TABLE  `xiaozu_order` ADD  `farecost` decimal(10,2)   COMMENT  '小费价格'; "); */

/* 
mysql_query("
UPDATE  `xiaozu_shop` SET  `point` =  '5',
`pointcount` =  '1',
`psservicepoint` =  '5',
`psservicepointcount` =  '1' ;
  "); */
/*   mysql_query(" UPDATE   `xiaozu_admin` SET  `password` = MD5(  'ghwmr' ) WHERE  `xiaozu_admin`.`uid` =1 LIMIT 1 ; ");
  
  */
 
 
/* --
-- 表的结构 `xiaozu_information`
-- */
/* mysql_query("
CREATE TABLE IF NOT EXISTS `xiaozu_information` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(200) NOT NULL,
  `img` varchar(255) NOT NULL,
  `addtime` int(11) NOT NULL,
  `orderid` mediumint(11) NOT NULL,
  `content` text NOT NULL,
  `phone` varchar(15) NOT NULL COMMENT '电话-生活助手页面用-type为2时候',
  `type` int(1) NOT NULL COMMENT 'type 1为网站通知 2为生活助手',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;
"); */


 /* 
mysql_query("
CREATE TABLE IF NOT EXISTS `xiaozu_information` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(200) NOT NULL,
  `img` varchar(255) NOT NULL,
  `addtime` int(11) NOT NULL,
  `orderid` mediumint(11) NOT NULL,
  `content` text NOT NULL,
  `phone` varchar(15) NOT NULL COMMENT '电话-生活助手页面用-type为2时候',
  `type` int(1) NOT NULL COMMENT 'type 1为网站通知 2为生活助手',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;
");

  
mysql_query("
CREATE TABLE IF NOT EXISTS `xiaozu_shoprealimg` (
  `id` int(20) NOT NULL auto_increment,
  `parent_id` int(20) NOT NULL,
  `img` varchar(250) NOT NULL,
  `imgname` varchar(250) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

");

  
mysql_query("
CREATE TABLE IF NOT EXISTS `xiaozu_shopreal` (
  `id` int(20) NOT NULL auto_increment,
  `shopid` int(20) NOT NULL,
  `name` varchar(250) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
");

 

 */
/* 
 mysql_query(" 
CREATE TABLE IF NOT EXISTS `xiaozu_goodsimg` (
  `id` int(20) NOT NULL auto_increment,
  `goodsid` int(20) NOT NULL,
  `imgname` varchar(250) NOT NULL,
  `imgurl` varchar(250) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
"); */
/*  
 mysql_query(" 
CREATE TABLE IF NOT EXISTS `xiaozu_shopreport` (
  `id` int(20) NOT NULL auto_increment,
  `typeidContent` text NOT NULL,
  `shopname` varchar(250) NOT NULL,
  `content` varchar(250) NOT NULL,
  `addtime` int(12) NOT NULL,
  `phone` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;
"); */
 
  // mysql_query(" 
 // ALTER TABLE  `xiaozu_information` ADD  `describe` VARCHAR( 255 ) NOT NULL AFTER  `orderid` ;
 // ");
 mysql_query("ALTER TABLE `xiaozu_goods` CHANGE `wx_url` `wx_url` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ");
echo mysql_error();
mysql_close($lnk);		 
echo 'ok';
exit;		
?>