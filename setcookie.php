<?php
header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');   
    setcookie("sso_mid", $_GET['mid'], time()+3600, "/", ".eat.banklay.cn");
	setcookie("sso_token", $_GET['token'], time()+3600, "/", ".eat.banklay.cn");
?>

