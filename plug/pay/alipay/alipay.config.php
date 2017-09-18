<?php  
 $alipay_config['partner']		= '2088901767028754';
 $alipay_config['key']			= 'qq4hcijgedgp1jh2eui9vim6gmjan8cg';
 $alipay_config['sign_type']    = strtoupper('MD5');
 $alipay_config['input_charset']= strtolower('utf-8');
 $alipay_config['transport'] = 'http';
 $alipay_config['cacert']    = getcwd().'\\cacert.pem';
 $notify_url= 'http://m6.waimairen.com/plug/pay/alipay/notify_url.php';
 $return_url= 'http://m6.waimairen.com/plug/pay/alipay/return_url.php';
 $seller_email= '375952873@qq.com';
?>