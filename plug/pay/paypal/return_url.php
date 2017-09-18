<!DOCTYPE HTML>
<html>
    <head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php 
$hopedir = '../../../';
$config = $hopedir."config/hopeconfig.php";   
$cfg = include($config); 
?>

<div style="max-width:400px;margin:0px;margin:0px auto;min-height:300px;"> 
				<div style="margin-top:50px;background-color:#fff;">
					<div style="height:30px;width:80%;padding-left:10%;padding-right:10%;padding-top:10%;">
						<span style="background:url('<?php echo $cfg['siteurl'];?>/upload/images/order_ok.png') left no-repeat;height:30px;width:30px;background-size:100% 100%;display: inline-block;"></span>
						<div style="position:absolute;margin-left:50px;  margin-top: -30px; font-size: 20px;  font-weight: bold;  line-height: 20px;">恭喜您，支付成功</div>
				
			    
				   </div>
					      <div style="width:80%;margin:0px auto;padding-top:30px;text-align:right;"><a href="<?php echo $cfg['siteurl'];?>"><span style="font-size:20px;color:#fff;padding:5px;background-color:red;cursor: pointer;">返回首页</span></a></div>
		
				</div> 
			</div> 
			
					 
			
  </body>
</html>