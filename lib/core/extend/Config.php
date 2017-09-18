<?php
 
class Config
{
	private $configFile;
    private $config;
    private $base;
    private $mysql;
    private $table;
    private $mid;
    public function __construct($config,$baseurl,$mid,$type = 0, $table="system_config")
    {
    	$this->base = $baseurl;
    	$this->table = $table;
    	$this->mid = $mid;
    	$this->mysql = new mysql_class();
        $this->initConfig($config,$type);
                
    }

  
    public function setConfig($config,$type = 0, $table)
    {
        $this->initConfig($config,$type, $table);
    }
 
    public function getInfo()
    {
    	return $this->config;
    }

  
    private function initConfig($config,$type)
    {   	
    	/*
    	 *@ modify by lzm
    	 *@ 将原来以文件形式保存配置的形式改成数据库形式，用于兼容多系统
    	 *@ 2017-02-24
    	 * */
    	if($type){   	   
           $info = $this->mysql->select_one("select *  from ".Mysite::$app->config['tablepre'].$this->table."  where mid= '".$this->mid."'  ");
    	   if(!empty($info))
    	   {
    	   	 $this->config = $info;
    	   }
    	   else 
    	   {
    	   	 $this->config = null;
    	   }	
    	   	
    	}
    	else
    	{
    		if(file_exists($this->base.'config/'.$config))
    		{
    			$this->configFile = $this->base.'config/'.$config;
    			$this->config     = include($this->configFile);
    		}
    		else
    		{
    			$this->config = null;
    		}    		
    	}   
	  
    }

   
    public function __get($name)
    {
        if(isset($this->config[$name]))
        {
            return $this->config[$name];
        }
        else
        {
            $value = null;
            switch($name)
            {
                case 'list_thumb_width' :$value=100;break;
                case 'list_thumb_height':$value=100;break;
                case 'show_thumb_width' :$value=100;break;
                case 'show_thumb_height':$value=100;break;
            }
            return $value;
        }
        return '';
    }

   
    public function write($inputArray)
    {
    	$Msql = $this->mysql;
    	$table = $this->table;
    	$mid = $this->mid; 
    	self::edit($this->configFile , $inputArray,$Msql,$table,$mid);
    }

 
	public static function edit($configFile,$inputArray,$Msql,$table,$mid)
	{
		
		//安全过滤要写入文件的内容 
		$configStr = "";

		//读取配置信息内容
		if(file_exists($configFile))
		{
			$configStr   = file_get_contents($configFile);
			$configArray = include($configFile);
		}

		if(trim($configStr)=="")
		{
			$configStr   = "<?php return array( \r\n);?>";
			$configArray = array();
		}
        /* 修改成读取数据库配置 
         * @author lzm 
         * @date 2017-02-28
         * @ start
         * */
		$info = $Msql->select_one("select *  from ".Mysite::$app->config['tablepre'].$table."  where mid= '".$mid."'  ");
		if(!empty($info))
		{
			$configArray = $info;
		}
		/* end */
		//表单中存在但是不进行录用的键值
		$except = array('form_index');

		foreach($except as $value)
		{
			unset($inputArray[$value]);
		}

		/* $inputArray = array_merge($configArray,$inputArray);
		$configData = var_export($inputArray,true);
		$configStr = "<?php return {$configData}?>"; */
		//更新数据库配置表
		$rs = $Msql->update(Mysite::$app->config['tablepre'].$table, $inputArray, "mid ='$mid'");
		//写入配置文件
		//$fileObj   = new IFile($configFile,'w+');
		 
		//$fileObj->write($configStr);
	}
}
?>
