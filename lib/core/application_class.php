<?php 
class myapp
{
	//应用的名称
    public $name = 'My Application';
    public $defaultController = 'wxsite';
    public $defaultAction = 'index';
    private $timezone = 'Asia/Shanghai';
    private $mid;
    private $controller;
    private $Taction ;
    public $mysql;
    public $othertemplate = '';
    //渲染时的数据
    private $renderData = array();
    /**
     * @brief 构造函数
     * @param array or string $config 配置数组或者配置文件名称
     */
    public function __construct($config)
    { 

        if(is_string($config)) $config = require($config);
        if(is_array($config)) $this->config = $config;
        else $this->config = array();
  
		//设为if true为了标注以后要再解决cli模式下的basePath
		if(!isset($_SERVER['DOCUMENT_ROOT']))
		{
			if(isset($_SERVER['SCRIPT_FILENAME']))
			{
				$_SERVER['DOCUMENT_ROOT'] = dirname( $_SERVER['SCRIPT_FILENAME'] );
			}
			elseif(isset($_SERVER['PATH_TRANSLATED']))
			{
				$_SERVER['DOCUMENT_ROOT'] =  dirname( rtrim($_SERVER['PATH_TRANSLATED'],"/\\"));
			}
		}

		if($web = true)
		{
			$script_dir = trim(dirname($_SERVER['SCRIPT_NAME']),'/\\');
			if( $script_dir != "" )
			{
				$script_dir .="/";
			}

			$basePath = rtrim($_SERVER['DOCUMENT_ROOT'],'/\\')."/" . $script_dir; 
			$this->config['basePath'] = $basePath;
			$this->setBasePath($basePath);
		}
	} 
	public function getkey(){
		return '';
	}
	
public function appError($errno, $errstr, $errfile, $errline) {
		switch ($errno) {
			case E_ERROR:
			case E_PARSE:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case E_USER_ERROR:
				$errorStr = "$errstr ".$errfile." 第 $errline 行.";
				logwrite($errorStr);
				break;
			default:
				$errorStr = "[$errno] $errstr ".$errfile." 第 $errline 行.";
				logwrite($errorStr);
				break;
		}
	}
	
  public function run($setindex='')
  {
  	     //add by rain
  		set_error_handler(array(&$this,"appError"));
  	     
  	    IUrl::beginUrl(); 
  	    $mid = IUrl::getInfo(IUrl::MidName);
  	    if (empty($mid)) {
  	    	$mid='0';
  	    }
//            if($mid =='10000'){        //构造MID
//                $sn = IReq::get('sn');  //手表SN
//                $mysql = new mysql_class;
//                $info = $mysql->select_one("select c.mid,c.sid,a.name,b.sn,c.is_longhitcard,c.hitcarddistance,c.id,a.applypost,a.shift_id from ".Mysite::$app->config['tablepre']."staff a, ".Mysite::$app->config['tablepre']."staff_qrcode_set b, ".Mysite::$app->config['tablepre']."shop c where a.id = b.staffid and c.sid = a.sid
//   and b. sn = '".$sn."'");
//                $mid = $info['mid'];
//            }
            
  	    
        $controller = IUrl::getInfo("ctrl"); 
  	    $action = IUrl::getInfo("action"); 
  	    $Taction = empty($action) ? $this->defaultAction: $action;
  	    $info =isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');  

  	    //$url=  IUrl::getUrl();//'http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];   获取当前网页
  	    //获取地址的第二个参数
            $REQUEST_URI_ARR = explode('/', $_SERVER["REQUEST_URI"]);
  	    
  	    /* modify by lzm 2017-02-27
  	     * 兼容多系统，从数据库读取数据
  	    * @ return array()
  	    * */

  	    $mysql = new mysql_class;
  	    $hasconfig = $mysql->select_one("select * from ".Mysite::$app->config['tablepre']."system_config where mid='".$mid."'  ");
  	    if($hasconfig !==false && !empty($hasconfig)){
  	      $this->config = array_merge($this->config,$hasconfig);
  	    }
            elseif ($REQUEST_URI_ARR[2]=="autotask") {   //自动任务
               
                   $this->siteset();    
                   $method = $REQUEST_URI_ARR[2];
                   $Taction = $REQUEST_URI_ARR[3];
                   include(hopedir."module/".$REQUEST_URI_ARR[2]."/method.php");   
                   $method = new method();
                   $method->init(); 

                   if(method_exists($method, $Taction)){
                       call_user_func( array($method,$Taction)); 
                   }
                   exit;
                 
              }
//              elseif ($REQUEST_URI_ARR[2]=="watchsite"){
//                  
//                   $this->siteset();    
//                   $method = $REQUEST_URI_ARR[2];
//                   $Taction = $REQUEST_URI_ARR[3];
//                   include(hopedir."module/".$REQUEST_URI_ARR[2]."/method.php");   
//                   $method = new method();
//                   $method->init(); 
//
//                   if(method_exists($method, $Taction)){
//                       call_user_func( array($method,$Taction)); 
//                   }
//                   exit;
//                  
//              } 
//              elseif ($REQUEST_URI_ARR[2]=="watchsite" || $REQUEST_URI_ARR[2]=="autotask"){
//                  
//              }
              else{
  	    	//echo "该商户还没配置外卖功能，请联系管理员！";
                //发生错误跳转到404
                $link = Mysite::$app->config['siteurl'].'/404.html';
                echo "<script language=\"javascript\">";
                echo "document.location=\"".$link."\"";
                echo "</script>";
  	    	exit;
  	    }
  	   $sitekey = isset($this->config['sitekey'])?$this->config['sitekey']:''; 
   
  	   if($controller === null) $controller = $this->defaultController; 
  	   $this->controller = $controller;
  	   $this->Taction = $Taction;  
  	   $this->mid = $mid;
  	   if($controller == 'site' && $Taction == 'index'){ 
  	     //if(is_mobile_request()){   
            $this->controller = 'wxsite'; 
         //} 
       } 
  	   spl_autoload_register('Mysite::autoload');  
  	   $filePath = hopedir."/lib/Smarty/libs/Smarty.class.php"; 
       if( !class_exists('smarty')){
          include_once($filePath);  
       }
 
       if($controller == 'adminpage'){
       	       $smarty = new Smarty();  //建立smarty实例对象$smarty     
               $smarty->assign("siteurl",Mysite::$app->config['siteurl']); 
               $smarty->cache_lifetime =   60 * 60 * 24;  //设置缓存时间
               $smarty->caching = false; 
               $smarty->template_dir = hopedir."/templates/"; //设置模板目录  
               $smarty->compile_dir = hopedir."/templates_c/adminpage"; //设置编译目录 
               $smarty->cache_dir = hopedir."/smarty_cache"; //缓存文件夹  
               $smarty->left_delimiter = "<{"; 
               $smarty->right_delimiter = "}>"; 
               $module =  IUrl::getInfo("module"); 
               $module = empty($module)?'index':$module;
               $doaction = Mysite::$app->getAction()=='index'?'system':Mysite::$app->getAction();
               $this->Taction = $doaction;  
               $this->siteset(); 
               if(!file_exists(hopedir."/module/".Mysite::$app->getAction()."/adminmethod.php"))//判断文件是否存在
               {   
               	    
               	
               }else{  
                   include(hopedir."/module/".Mysite::$app->getAction()."/adminmethod.php");   
                   $method = new method();
                   $method->init(); 
                   if(method_exists($method, $module)){
                   	   call_user_func( array($method,$module)); 
                   }  
	             } 
               $datas = $this->getdata(); 
               if(is_array($datas)){
		              foreach($datas as $key=>$value)
		              {
		            	   $smarty->assign($key, $value);	 
		              }
	             }
               $nowID = ICookie::get('myaddress');
	             $lng = ICookie::get('lng'); 
	             $lat = ICookie::get('lat');
	             $mapname = ICookie::get('mapname');
	             $adminshopid = ICookie::get('adminshopid');
	             $smarty->assign("myaddress",$nowID);
	             $smarty->assign("mapname",$mapname);
	             $smarty->assign("adminshopid",$adminshopid);
	             $smarty->assign("lng",$lng);
	             $smarty->assign("lat",$lat); 
               $smarty->assign("controlname",Mysite::$app->getController());
               $smarty->assign("Taction",Mysite::$app->getAction());
               $smarty->assign("urlshort", Mysite::$app->getController().'/'.Mysite::$app->getAction());   
               //假设我是用户查看新闻    前台 /fontpage  /admin  后台 
               $templtepach = hopedir.'/templates/adminpage/'.Mysite::$app->getAction()."/".$module.".html";  
               if(file_exists($templtepach))//判断文件是否存在
               {  
               }elseif(file_exists(hopedir."/module/".Mysite::$app->getAction()."/adminpage/".$module.".html")){  
                  $smarty->compile_dir = hopedir."/templates_c/adminpage/".Mysite::$app->getAction();
                  $templtepach = hopedir."/module/".Mysite::$app->getAction()."/adminpage/".$module.".html";    
               }else{ 
                  $smarty->assign("msg",'模板文件不存在');
                  $smarty->assign("sitetitle",'错误提示'); 
                  $errorlink = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
                  $smarty->assign("errorlink",$errorlink); 
                  $templtepach = hopedir.'/templates/adminpage/public/error.html';   
               } 
               
               $smarty->assign("tmodule",$module);
               $smarty->assign("tempdir",'adminpage');	
               $smarty->registerPlugin("function","ofunc", "FUNC_function");
               $smarty->registerPlugin("block","oblock", "FUNC_block");
               $smarty->display($templtepach);  
               exit;
       }elseif($controller == 'areaadminpage'){
       	       $smarty = new Smarty();  //建立smarty实例对象$smarty     
               $smarty->assign("siteurl",Mysite::$app->config['siteurl']); 
               $smarty->cache_lifetime =   60 * 60 * 24;  //设置缓存时间
               $smarty->caching = false; 
               $smarty->template_dir = hopedir."/templates/"; //设置模板目录  
               $smarty->compile_dir = hopedir."/templates_c/areaadminpage"; //设置编译目录 
               $smarty->cache_dir = hopedir."/smarty_cache"; //缓存文件夹  
               $smarty->left_delimiter = "<{"; 
               $smarty->right_delimiter = "}>"; 
               $module =  IUrl::getInfo("module"); 
               $module = empty($module)?'index':$module;
               $doaction = Mysite::$app->getAction()=='index'?'system':Mysite::$app->getAction();
               $this->Taction = $doaction;  
               $this->siteset(); 
               if(!file_exists(hopedir."/module/".Mysite::$app->getAction()."/areaadminmethod.php"))//判断文件是否存在
               {   
               	 
               	
               }else{  
                   include(hopedir."/module/".Mysite::$app->getAction()."/areaadminmethod.php");   
                   $method = new method();
                   $method->init(); 
                   if(method_exists($method, $module)){
                   	   call_user_func( array($method,$module)); 
                   }  
	           } 
               $datas = $this->getdata(); 
               if(is_array($datas)){
		              foreach($datas as $key=>$value)
		              {
		            	   $smarty->assign($key, $value);	 
		              }
	             }
               $nowID = ICookie::get('myaddress');
	             $lng = ICookie::get('lng'); 
	             $lat = ICookie::get('lat');
	             $mapname = ICookie::get('mapname');
	             $adminshopid = ICookie::get('adminshopid');
	             $smarty->assign("myaddress",$nowID);
	             $smarty->assign("mapname",$mapname);
	             $smarty->assign("adminshopid",$adminshopid);
	             $smarty->assign("lng",$lng);
	             $smarty->assign("lat",$lat); 
               $smarty->assign("controlname",Mysite::$app->getController());
               $smarty->assign("Taction",Mysite::$app->getAction());
               $smarty->assign("urlshort", Mysite::$app->getController().'/'.Mysite::$app->getAction());   
               //假设我是用户查看新闻    前台 /fontpage  /admin  后台 
               $templtepach = hopedir.'/templates/areaadminpage/'.Mysite::$app->getAction()."/".$module.".html";  
               if(file_exists($templtepach))//判断文件是否存在
               {  
               }elseif(file_exists(hopedir."/module/".Mysite::$app->getAction()."/areaadminpage/".$module.".html")){  
                  $smarty->compile_dir = hopedir."/templates_c/areaadminpage/".Mysite::$app->getAction();
                  $templtepach = hopedir."/module/".Mysite::$app->getAction()."/areaadminpage/".$module.".html";    
               }else{ 
                  $smarty->assign("msg",'模板文件不存在');
                  $smarty->assign("sitetitle",'错误提示'); 
                  $errorlink = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
                  $smarty->assign("errorlink",$errorlink); 
                  $templtepach = hopedir.'/templates/areaadminpage/public/error.html';   
               } 
               
               
               $smarty->assign("tmodule",$module);
               $smarty->assign("tempdir",'areaadminpage');	
               $smarty->registerPlugin("function","ofunc", "FUNC_function");
               $smarty->registerPlugin("block","oblock", "FUNC_block");
               $smarty->display($templtepach);  
               exit;
        }elseif($controller == 'smallsite'){//小程序入口

               $this->siteset();    
               if(!file_exists(hopedir."module/".Mysite::$app->getController()."/method.php"))//判断文件是否存在
               {    
                  $this->setController ='smallsite'; 
                  $this->setAction = 'error';
                
               }else{  
                   include(hopedir."module/".Mysite::$app->getController()."/method.php");   
                   $method = new method();
                   $method->init(); 

                   if(method_exists($method, $Taction)){
                       call_user_func( array($method,$Taction)); 
                   }  
               }   
       }
       elseif($controller == 'watchsite'){//手表控制器

               $this->siteset();    
               if(!file_exists(hopedir."module/".Mysite::$app->getController()."/method.php"))//判断文件是否存在
               {    
                  $this->setController ='watchsite'; 
                  $this->setAction = 'error';
                
               }else{  
                   include(hopedir."module/".Mysite::$app->getController()."/method.php");   
                   $method = new method();
                   $method->init(); 

                   if(method_exists($method, $Taction)){
                       call_user_func( array($method,$Taction)); 
                   }  
               }
               exit;
       }
       else{
               $smarty = new Smarty();  //建立smarty实例对象$smarty     
               $smarty->assign("siteurl",Mysite::$app->config['siteurl']); 
               $smarty->cache_lifetime =  60 * 60 * 24;  //设置缓存时间
               $smarty->caching = false; 
               $smarty->template_dir = hopedir."/templates"; //设置模板目录  
               $smarty->compile_dir = hopedir."/templates_c/".Mysite::$app->config['sitetemp']; //设置编译目录 
               $smarty->cache_dir = hopedir."/smarty_cache"; //缓存文件夹  
               $smarty->left_delimiter = "<{"; 
               $smarty->right_delimiter = "}>"; 
               $this->siteset(); 
               if(!file_exists(hopedir."module/".Mysite::$app->getController()."/method.php"))//判断文件是否存在
               {   
               	  $this->setController ='site'; 
               	  $this->setAction = 'error';
               	
               }else{  
                   include(hopedir."module/".Mysite::$app->getController()."/method.php");   
                   $method = new method();
                   $method->init(); 
                   if(method_exists($method, $Taction)){
                   	   call_user_func( array($method,$Taction)); 
                   }  
	             } 
               $datas = $this->getdata();
              
               if(is_array($datas)){
		              foreach($datas as $key=>$value)
		              {
		            	   $smarty->assign($key, $value);	 
		              }
	             }
               $nowID = ICookie::get('myaddress');
	             $lng = ICookie::get('lng'); 
	             $lat = ICookie::get('lat');
	             $mapname = ICookie::get('mapname');
	             $adminshopid = ICookie::get('adminshopid');
	             $smarty->assign("myaddress",$nowID);
	             $smarty->assign("mapname",$mapname);
	             $smarty->assign("adminshopid",$adminshopid);
	             $smarty->assign("lng",$lng);
	             $smarty->assign("lat",$lat); 
               $smarty->assign("controlname",Mysite::$app->getController());
               $smarty->assign("Taction",Mysite::$app->getAction());
               $smarty->assign("urlshort", Mysite::$app->getMid().'/'.Mysite::$app->getController().'/'.Mysite::$app->getAction());   
               //假设我是用户查看新闻    前台 /fontpage  /admin  后台 
               $templtepach = hopedir.'/templates/'.Mysite::$app->config['sitetemp']."/".Mysite::$app->getController()."/".Mysite::$app->getAction().".html";   
                 
               if(file_exists($templtepach))//判断文件是否存在
               {  
               }elseif(file_exists(hopedir."/module/".Mysite::$app->getController()."/template/".Mysite::$app->getAction().".html")){  
                  $smarty->compile_dir = hopedir."/templates_c/system";
                  $templtepach = hopedir."/module/".Mysite::$app->getController()."/template/".Mysite::$app->getAction().".html";    
               }else{ 
                  $smarty->assign("msg",'模板文件不存在');
                  $smarty->assign("sitetitle",'错误提示'); 
                  $errorlink = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
                  $smarty->assign("errorlink",$errorlink); 
                  $templtepach = hopedir.'/templates/'.Mysite::$app->config['sitetemp']."/public/error.html";   
               } 

               $smarty->assign("tempdir",Mysite::$app->config['sitetemp']);	
               $smarty->registerPlugin("function","ofunc", "FUNC_function");
               $smarty->registerPlugin("block","oblock", "FUNC_block");
               $smarty->display($templtepach);  
       }
  	   
  }
  static public function statichtml($htmlcontent,$datas){
	 
		$filePath = hopedir."/lib/Smarty/libs/Smarty.class.php"; 
		if( !class_exists('smarty')){
       include_once($filePath); 
    } 
  	$tpl = new Smarty();  //建立smarty实例对象$smarty       
    $tpl->cache_lifetime = 0;  //设置缓存时间
    $tpl->caching = false; 
    $tpl->template_dir = hopedir."/templates"; //设置模板目录  
    $tpl->compile_dir = hopedir."/templates_c"; //设置编译目录 
    
    $tpl->cache_dir = hopedir."/smarty_cache"; //缓存文件夹  
    $tpl->left_delimiter = "{"; 
    $tpl->right_delimiter = "}";   
    if(is_array($datas)){
		  foreach($datas as $key=>$value)
		  {
			   $tpl->assign($key, $value);	 
		  }
		}
	  $content = $tpl->fetch('string:'.$htmlcontent); // 
	  return $content;
	}  
  public function siteset(){
	      $config = new config('hopeconfig.php',hopedir,$this->mid,$type=1,Mysite::$app->config['conf_table']);   	  
	      $this->setdata($config->getInfo()); 
	 }
    /**
     * @brief 设置应用的基本路径
     * @param string  $basePath 路径地址
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }
    
    public function getBasePath()
    {
        return $this->basePath;
    }
    //add by rain
    public function getMid()
    {
    	return $this->mid;
    }
    public function getMidName()
    {
    	return IUrl::MidName;
    } 
    public function getController()
    {
    	return $this->controller;
    }
    public function setController($controller){
    	$this->controller= $controller;
    }
    public function setAction($action){
    	 $this->Taction = $action;
    }
    public function getAction(){
    	return  $this->Taction;
    }
    public function setdata($data)
  	{
	    	$tempdata = $this->getdata();
	    	$tempdata = array_merge($tempdata,$data);
	    	$this->renderData = $tempdata;//合并数组;
  	}
  	
  	public function getdata()
  	{
	 	   return $this->renderData;
  	}
    
}

?>
