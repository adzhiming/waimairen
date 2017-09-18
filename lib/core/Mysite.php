<?php

define('MYSITE_PATH',dirname(__file__).DIRECTORY_SEPARATOR);
 
class Mysite
{ 
	public static $app; 
	public static $_classes = array('controllers'=>'controllers.*');
	public static $_otherclass = array('class'=>'class.*');
    
    public static function createApp($className, $config)
    {
        $app = new $className($config); 
		    return $app;
    }
   
    
    public static function createWebApp($config = null)
    {
        self::$app = self::createApp('myapp',$config);
        return self::$app;
    } 
	 
	public static function autoload($className)
	{ 
		if(isset(self::$_coreClasses[$className]))
		{
			include(MYSITE_PATH.self::$_coreClasses[$className]); 
		}else if(isset(self::$_classes))
		{
		 /*
			foreach(self::$_classes as $classPath)
       { 
             $filePath = hopedir.strtr(strtolower(trim($classPath,'*')),'.','/').strtolower( $className ) .'.php';
                    if(is_file($filePath))
                    {
                    	 
                        include($filePath);
                        return true;
                    }
      }*/
			 foreach(self::$_otherclass as $classPath)
       { 
       	 
             $filePath = hopedir.strtr(strtolower(trim($classPath,'*')),'.','/').strtolower( $className ) .'.php';
           
             
                    if(is_file($filePath))
                    {
                    	 
                        include($filePath);
                        return true;
                    }
      } 
		} 
		return true;
	}
	 
   
    public static function setClasses($classes)
    {
        if(is_string($classes)) self::$_classes += array($classes);
        if(is_array($classes))  self::$_classes += $classes;
    }
    //系统内核所有类文件注册信息
	 public static $_coreClasses = array( 
		'myapp' => 'application_class.php',
		'IUrl'=> 'urlmanager_class.php',
		'IReq'=>'req_class.php', 
		'mysql_class'=>'extend/mysql_class.php',
		'page'=>'extend/page.php', 
		'phpmailer'=>'extend/class.phpmailer.php',
		'SMTP'=>'extend/class.smtp.php',
		'config'=>'extend/Config.php',
		'ICookie'=>'extend/cookie_class.php',
		'ICrypt'=>'extend/crypt_class.php',
		'IFile'=>'extend/IFile.php',
		'IValidate'=>'extend/IValidate.php',
		'IReq'=>'extend/req_class.php',
		'Services_JSON'=>'extend/Services_JSON.php',
		'ISmtp'=>'extend/smtp_class.php',
		'upload'=>'extend/upload.php',
		'IString'=>'extend/string_class.php',
		'ISession'=>'extend/session_class.php',
		'JSON'=>'extend/json_class.php',
		'IFilter'=>'extend/filter_class.php',
		'IClient'=>'extend/client_class.php',
		'Captcha'=>'extend/captcha_class.php' 
	 );
}
 
function __autoload($className)
{
	Mysite::autoload($className); 
}
