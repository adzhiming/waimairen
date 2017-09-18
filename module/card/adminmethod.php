<?php
class method   extends adminbaseclass
{  
	 
   function delcard(){ 
   	 $id = IReq::get('id'); 
		 if(empty($id))  $this->message('card_empty'); 
		 $ids = is_array($id)? join(',',$id):$id;    
	   $this->mysql->delete(Mysite::$app->config['tablepre'].'card',"id in($ids)");  
	   $this->success('success'); 
   } 
   function saveprensentjuan(){
   		$siteinfo['regester_juan'] = intval(IReq::get('regester_juan'));
   		$siteinfo['regester_juanlimit'] = intval(IReq::get('regester_juanlimit'));
   		$siteinfo['regester_juancost'] = intval(IReq::get('regester_juancost'));
   		$siteinfo['regester_juanday'] = intval(IReq::get('regester_juanday')); 
   		
		  $siteinfo['wx_juan'] = intval(IReq::get('wx_juan'));
		  $siteinfo['wx_juancost'] = intval(IReq::get('wx_juancost'));
		  $siteinfo['wx_juanlimit'] = intval(IReq::get('wx_juanlimit'));
		  $siteinfo['wx_juanday'] = intval(IReq::get('wx_juanday'));
 
		$siteinfo['login_juan'] = intval(IReq::get('login_juan'));
		$siteinfo['login_data'] = strtotime(IReq::get('login_data'));
    
    
    $siteinfo['login_juanlimit'] = intval(IReq::get('login_juanlimit'));
    $siteinfo['login_juancost'] = intval(IReq::get('login_juancost'));
    $siteinfo['login_juanday'] = intval(IReq::get('login_juanday'));
    
    
		$siteinfo['tui_juan'] = intval(IReq::get('tui_juan'));
		$siteinfo['tui_juanlimit'] = intval(IReq::get('tui_juanlimit'));
		$siteinfo['tui_juancost'] = intval(IReq::get('tui_juancost'));
		$siteinfo['tui_juanday'] = intval(IReq::get('tui_juanday'));
		 $config = new config('hopeconfig.php',hopedir,$this->mid,$type=1,Mysite::$app->config['conf_table']);  
	  $config->write($siteinfo);
		 
		$this->success('success');
		
   }
   function cardlist(){ 
       	$searchvalue = intval(IReq::get('searchvalue'));
      	$orderstatus = intval(IReq::get('orderstatus'));
      	$starttime = trim(IReq::get('starttime'));
      	$endtime = trim(IReq::get('endtime'));
      	$newlink = '';
      	$where= '';
	#	print_r($orderstatus);
      	$data['searchvalue'] = '';
      	if($searchvalue > 0)//限制值
      	{ 
      			 $data['searchvalue'] = $searchvalue;
         	   $where .= ' and  cost = \''.$searchvalue.'\' ';
         	   $newlink .= '/searchvalue/'.$searchvalue; 
      	}
      	$data['orderstatus'] = '';
		 
      	if($orderstatus > 0)
      	{
      		   $chastatus = $orderstatus-1;
      		   $data['orderstatus'] = $orderstatus;
         	   $where .= ' and  status = \''.$chastatus.'\' ';
         	   $newlink .= '/orderstatus/'.$orderstatus; 
      	}
      	$data['starttime'] = '';
      	if(!empty($starttime))
      	{
      		 $data['starttime'] = $starttime;
      		 $where .= ' and  creattime > '.strtotime($starttime.' 00:00:01').' ';
         	 $newlink .= '/starttime/'.$starttime; 
      	}
      	$data['endtime'] = '';
      	if(!empty($endtime))
      	{
      		 $data['endtime'] = $endtime;
      		 $where .= ' and  creattime < '.strtotime($endtime.' 23:59:59').' ';
         	 $newlink .= '/endtime/'.$endtime; 
      	} 
      	$data['where'] = " id > 0 ".$where;
 	#	print_r($data['where']);
		
		$pageinfo = new page();
 	   $pageinfo->setpage(intval(IReq::get('page')),10); 
		
		$cardlist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."card where   ".$data['where']."   order by id desc    limit ".$pageinfo->startnum().", ".$pageinfo->getsize()."  "); 

		$shuliang  = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."card where   ".$data['where']."   order by id desc");
		$pageinfo->setnum($shuliang);
		$pagelink = IUrl::creatUrl('adminpage/card/module/cardlist'.$newlink);
		$data['pagecontent'] = $pageinfo->getpagebar($pagelink); 		
		
		$data['cardlist'] = $cardlist;
		
      	$link = IUrl::creatUrl('adminpage/card/module/cardlist'.$newlink);
      	$data['outlink'] =IUrl::creatUrl('adminpage/card/module/outcard/outtype/query'.$newlink);           
        Mysite::$app->setdata($data);    
   }
   function savecard()
	{ 
		$card_temp = trim(IReq::get('card_temp')); 
		$card_acount = intval(IReq::get('card_acount')); 
		$card_cost = intval(IReq::get('card_cost')); 
		if(empty($card_temp))$this->message('card_emptypre');
		if($card_acount < 1)$this->message('card_emptycout');
		if(!in_array($card_cost,array(10,50,100,200)))$this->message('card_costerr');
		$timenow = time();
		for($i=0;$i< $card_acount;$i++)
		{
			$data['card'] = $card_temp.$timenow.$i.rand(1000,9999);
			$data['card_password'] = substr(md5($data['card']),0,11);
			$data['status'] = 0;
			$data['cost'] = $card_cost;
			$data['creattime'] = $timenow;
			$this->mysql->insert(Mysite::$app->config['tablepre'].'card',$data); 
		}
		$this->success('success');
	}
	function outcard(){ 
		$outtype = IReq::get('outtype'); 
		if(!in_array($outtype,array('query','ids')))
		{
		  	header("Content-Type: text/html; charset=UTF-8");
			 echo '查询条件错误';
			 exit;
		}	
		$where = '';
		if($outtype == 'ids')
		{
			  $id = trim(IReq::get('id'));
			  if(empty($id))
			  {
			  	 header("Content-Type: text/html; charset=UTF-8");
			  	 echo '查询条件不能为空';
			  	 exit;
			  }	 
			   $doid = explode('-',$id);
			  $id = join(',',$doid);
			  $where .= ' and id in('.$id.') ';
		}else{
		   $searchvalue = intval(IReq::get('searchvalue'));
		   $where .= $searchvalue > 0? ' and  cost = \''.$searchvalue.'\' ':'';
		   
		   $orderstatus = intval(IReq::get('orderstatus')); 
		   $where .= $orderstatus > 0?' and  status = \''.($orderstatus-1).'\' ':'';
		   
		   $starttime = trim(IReq::get('starttime')); 
		   $where .= !empty($starttime)? ' and  creattime > '.strtotime($starttime.' 00:00:01').' ':'';
		   
		   $endtime = trim(IReq::get('endtime')); 
		   $where .= !empty($endtime)? ' and  creattime < '.strtotime($endtime.' 23:59:59').' ':'';
		}		 
		
		 $outexcel = new phptoexcel();
		 $titledata = array('卡号','密码','充值金额');
		 $titlelabel = array('card','card_password','cost');  
		 $datalist = $this->mysql->getarr("select card,card_password,cost from ".Mysite::$app->config['tablepre']."card where id > 0 ".$where."   order by id desc  limit 0,2000 "); 
		 $outexcel->out($titledata,$titlelabel,$datalist,'','充指卡导出结果'); 
	}
	function juanlist(){ 
	$searchvalue = intval(IReq::get('searchvalue'));

		$orderstatus = intval(IReq::get('orderstatus'));
		#	print_r($orderstatus);
		
		$starttime = trim(IReq::get('starttime'));
		$endtime = trim(IReq::get('endtime'));
		
		$newlink = '';
		$where= '';
		$data['searchvalue'] = '';
		if($searchvalue > 0)//限制值
		{ 
				 $data['searchvalue'] = $searchvalue;
	   	   $where .= ' and  limitcost = \''.$searchvalue.'\' ';
	   	   $newlink .= '/searchvalue/'.$searchvalue; 
		}
		$data['orderstatus'] = '';
		if($orderstatus > 0)
		{
			   $chastatus = $orderstatus-1 ;
			   $data['orderstatus'] = $orderstatus;
	   	   $where .= ' and  status = \''.$chastatus.'\' ';
	   	   $newlink .= '/orderstatus/'.$orderstatus; 
		}
		$data['starttime'] = '';
		if(!empty($starttime))
		{
			 $data['starttime'] = $starttime;
			 $where .= ' and  creattime > '.strtotime($starttime.' 00:00:01').' ';
	   	 $newlink .= '/starttime/'.$starttime; 
		}
		$data['endtime'] = '';
		if(!empty($endtime))
		{
			 $data['endtime'] = $endtime;
			 $where .= ' and  creattime < '.strtotime($endtime.' 23:59:59').' ';
	   	 $newlink .= '/endtime/'.$endtime; 
		}
		  
		$data['where'] = ' id > 0 '.$where;
#		print_r($data['where']);
		$link = IUrl::creatUrl('adminpage/card/module/juanlist'.$newlink); 
		$data['outlink'] =IUrl::creatUrl('adminpage/card/outjuan/module/outtype/query'.$newlink);
		$data['nowtime'] = time();
		$data['statustype'] = array(
		          '1'=>'已绑定',
		          '2'=>'已使用',
		          '3'=>'无效'
		          );
		  Mysite::$app->setdata($data); 
	}
	function addregjuan(){
		
		$id = intval(IReq::get('id'));
		$regjuan = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."regsendjuan where id  =  ".$id."    "); 
		$data['regjuan'] = $regjuan;
		 Mysite::$app->setdata($data); 
		
	}
	function delregsendcard(){ 
   	 $id = IReq::get('id'); 
		 if(empty($id))  $this->message('card_emptyjuan'); 
		 $ids = is_array($id)? join(',',$id):$id;    
	   $this->mysql->delete(Mysite::$app->config['tablepre'].'regsendjuan',"id in($ids)");  
	   $this->success('success'); 
  }
	function saveregsendjuan(){ 
		$id = intval(IReq::get('id'));
		if(!empty($id)){
			$checkregjuan = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."regsendjuan where id  =  ".$id."    "); 
			if(empty($checkregjuan)){
				$this->message('获取注册优惠券失败');
			}
		}
		$name = trim(IReq::get('name')); 
		$limitcost = trim(IReq::get('limitcost')); 
		$jiancost = trim(IReq::get('jiancost')); 
		$starttime = strtotime(IReq::get('starttime')); 
		$endtime =   strtotime(IReq::get('endtime')); 
		$is_open = intval(IReq::get('is_open')); 
		
		if(empty($name)) $this->message('请填写优惠券名称');
		if(empty($limitcost)) $this->message('请填写最低消费使用金额');
		if(empty($jiancost)) $this->message('请填写优惠金额');
		if(empty($starttime)) $this->message('请填写开始时间');
		if(empty($endtime)) $this->message('请填写结束时间');
		if( $starttime > $endtime ) $this->message('时间填写不规范，请重新选择');
		
		$data['name'] = $name;
		$data['limitcost'] = $limitcost;
		$data['jiancost'] = $jiancost;
		$data['starttime'] = $starttime;
		$data['endtime'] = $endtime;
		$data['is_open'] = $is_open;
		
		if(empty($id)){
			$this->mysql->insert(Mysite::$app->config['tablepre'].'regsendjuan',$data); 
		}else{
			 
			$this->mysql->update(Mysite::$app->config['tablepre'].'regsendjuan',$data,"id='".$id."'");  
		}
		$this->success('success');
		
	}
	
  function savejuan()
	{ 
		$card_temp = trim(IReq::get('card_temp')); //卡前缀
		$card_acount = intval(IReq::get('card_acount')); //卡数量
		$card_cost = intval(IReq::get('card_cost')); //优惠金额
		$limit_cost = intval(IReq::get('limit_cost')); //限制金额
		$card_time = intval(IReq::get('card_time'));//有效时间
		$name = trim(IReq::get('name'));
		if(empty($name)) $this->message('card_emptyjuanname');
		if(empty($card_temp))$this->message('card_emptyjuanpre');
		if($card_acount < 1)$this->message('card_emptyjuancount'); 
		if($card_cost < 1) $this->message('card_emptyjuancost');
		if($limit_cost < 1) $this->message('card_emptyjuanlimitcost');
		if($card_time < 1) $this->message('card_emptyjuanactimetime'); 
		if($card_acount > 100) $this->message('card_emptyjuanlimitcount'); 
		$timenow = time();
		for($i=0;$i< $card_acount;$i++)
		{
			$data['card'] = $card_temp.$timenow.$i.rand(10,99);
			$data['card_password'] = substr(md5($data['card']),0,5);
			$data['status'] = 0;
			$data['creattime'] = $timenow;
			$data['cost'] = $card_cost;
			$data['limitcost'] = $limit_cost;
			$data['endtime'] = $timenow+$card_time*24*60*60;
			$data['name'] = $name;
			//uid ,  usetime username 
			$this->mysql->insert(Mysite::$app->config['tablepre'].'juan',$data); 
		}
		$this->success('success');
	}
	function deljuan(){ 
   	 $id = IReq::get('id'); 
		 if(empty($id))  $this->message('card_emptyjuan'); 
		 $ids = is_array($id)? join(',',$id):$id;    
	   $this->mysql->delete(Mysite::$app->config['tablepre'].'juan',"id in($ids)");  
	   $this->success('success'); 
  }
  function outjuan()
	{ 
		 $this->checkadminlogin();
		$outtype = IReq::get('outtype'); 
	 
		if(!in_array($outtype,array('query','ids')))
		{
		  	header("Content-Type: text/html; charset=UTF-8");
			 echo '查询条件错误';
			 exit;
		}	
		$where = '';
		if($outtype == 'ids')
		{
			  $id = trim(IReq::get('id'));
			  if(empty($id))
			  {
			  	 header("Content-Type: text/html; charset=UTF-8");
			  	 echo '查询条件不能为空';
			  	 exit;
			  }	 
			  $doid = explode('-',$id);
			  $id = join(',',$doid);
			  $where .= ' and id in('.$id.') ';
		}else{
		   $searchvalue = intval(IReq::get('searchvalue'));
		   $where .= $searchvalue > 0? ' and  limitcost = \''.$searchvalue.'\' ':'';
		   
		   $orderstatus = intval(IReq::get('orderstatus')); 
		   $where .= $orderstatus > 0?' and  status = \''.($orderstatus-1).'\' ':'';
		   
		   $starttime = trim(IReq::get('starttime')); 
		   $where .= !empty($starttime)? ' and  creattime > '.strtotime($starttime.' 00:00:01').' ':'';
		   
		   $endtime = trim(IReq::get('endtime')); 
		   $where .= !empty($endtime)? ' and  creattime > '.strtotime($endtime.' 23:59:59').' ':'';
		}		 
		
		 $outexcel = new phptoexcel();
		 $titledata = array('卡号','密码','购物车限制金额','优惠金');
		 $titlelabel = array('card','card_password','limitcost','cost');  
		 $datalist = $this->mysql->getarr("select card,card_password,limitcost,cost from ".Mysite::$app->config['tablepre']."juan where id > 0 ".$where."   order by id desc  limit 0,2000 "); 
		 $outexcel->out($titledata,$titlelabel,$datalist,'','消费卷导出结果'); 
		            	  
	}	  
	function savescore(){ 
   	 $siteinfo['commentscore'] = intval(IReq::get('commentscore'));
		$siteinfo['loginscore'] = intval(IReq::get('loginscore'));
		$siteinfo['regesterscore'] = intval(IReq::get('regesterscore')); 
		$siteinfo['commenttype'] =intval(IReq::get('commenttype'));
		$siteinfo['scoretocost'] =intval(IReq::get('scoretocost'));
		$siteinfo['maxdayscore'] =intval(IReq::get('maxdayscore')); 
		$siteinfo['commentday'] = intval(IReq::get('commentday'));
        $siteinfo['consumption']=intval(IReq::get('consumption'));
        $siteinfo['con_extend']=intval(IReq::get('con_extend'));
		$config = new config('hopeconfig.php',hopedir,$this->mid,$type=1,Mysite::$app->config['conf_table']);  
	  $config->write($siteinfo);
		$this->success('success'); 
  }
  function savesendtask(){ 
  	$data['taskname'] = IReq::get('taskname'); 
		$data['tasktype'] = IReq::get('tasktype');
		$data['tasktype'] = empty($data['tasktype'])?1:$data['tasktype'];
		$data['taskusertype'] = IReq::get('taskusertype');
		$data['taskusertype'] = empty($data['taskusertype'])?1:$data['taskusertype'];
		$data['usertype'] = IReq::get('usertype');
		$data['userscore'] = IReq::get('userscore');
		$data['creattime_starttime'] = IReq::get('creattime_starttime');
		$data['creattime_endtime'] = IReq::get('creattime_endtime');
		$data['logintime_starttime'] = IReq::get('logintime_starttime');
		$data['logintime_endtime'] = IReq::get('logintime_endtime');
		$data['objcontent'] = IReq::get('objcontent');
		$data['content']  = IReq::get('content');
		  $link = IUrl::creatUrl('adminpage/card/module/sendtask'); 
           
	  if(empty($data['taskname']))  $this->message('task_emptytitle',$link);
	  if(empty($data['content'])) $this->message('task_emptycontent',$link);  
	  $miaoshu = $data['tasktype']==1?'群发邮件':'群发短信'; 
	  if($data['taskusertype'] ==1 )
	  { 
	  	$where = '';
	  	$miaoshu .= '根据条件：';
	  	if($data['usertype'] > 0) 
	  	{
	  		if($data['usertype'] == 1)
	  		{
	  			 $where .= " and usertype  = \'0\' ";
	  		}else{
	  			$where .= " and usertype  = \'1\' ";
	  		} 
	  		$miaoshu .= $data['usertype'] == 1?'普通会员':'商家会员';
	  	}
	  	if($data['userscore'] > 0)
	  	{
	  		$where .= " and score   > ".$data['userscore']." ";
	  		$miaoshu .=  '积分大于'.$data['userscore'];
	  	}
	  	if(!empty($data['creattime_starttime']))
	  	{
	  		 $limittime = strtotime($data['creattime_starttime'].' 00:00:00');
	  		 $where .= " and creattime   > ".$limittime." ";
	  		 $miaoshu .=  '注册时间大于'.$data['creattime_starttime'];
	  	}
	  	if(!empty($data['logintime_starttime']))
	  	{
	  		 $limittime = strtotime($data['creattime_endtime'].' 00:00:00');
	  		 $where .= " and creattime   < ".$limittime." ";
	  		 $miaoshu .=  '注册时间小于'.$data['creattime_endtime'];
	  	}
	  	if(!empty($data['logintime_starttime']))
	  	{
	  		 $limittime = strtotime($data['logintime_starttime'].' 00:00:00');
	  		 $where .= " and logintime   > ".$limittime." ";
	  		 $miaoshu .=  '最近登陆时间大于'.$data['logintime_starttime'];
	  	}
	  	if(!empty($data['logintime_endtime']))
	  	{
	  		 $limittime = strtotime($data['logintime_endtime'].' 00:00:00');
	  		 $where .= " and logintime   < ".$limittime." ";
	  		 $miaoshu .=  '最近登陆时间小于'.$data['logintime_endtime'];
	  	}
	  	$data['tasklimit'] = $where;
	  	$data['othercontent'] = $miaoshu; 
	  }else{ 
	  	if(empty($data['objcontent'])) $this->message('task_emptyobj',$link);  
	  	$data['tasklimit'] = $data['objcontent'];
	  	$data['othercontent'] = $miaoshu.'指定对象:'.$data['objcontent'];
	  } 
	  unset($data['usertype']);
		unset($data['userscore']);
		unset($data['creattime_starttime']);
		unset($data['creattime_endtime']);
		unset($data['logintime_starttime']);
		unset($data['logintime_endtime']);
		unset($data['objcontent']);
	  $this->mysql->insert(Mysite::$app->config['tablepre'].'task',$data);
	  $link = IUrl::creatUrl('adminpage/card/module/sendtasklist'); 
		 $this->message('',$link);  
  }
  function starttask(){ 
		$taskid = IReq::get('taskid'); 
		$taskinfo =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."task where id='".$taskid."'  "); 
		if(empty($taskinfo))
		{
			echo '任务不存在';
			exit;
		} 
		if($taskinfo['status'] > 1)
		{
			  echo '任务执行完毕,请关闭窗口';
			  exit;
		}
		$data = array('taskmiaoshu'=>'');
		//执行任务
	  if($taskinfo['tasktype'] == 1)
	  {
	  	$emailids = '';//邮箱ID集
	  	$newdata = array();//任务处理数据
	    $data['taskmiaoshu'] .= '邮件群发任务';
	  	if($taskinfo['taskusertype'] == 1)
	  	{
	  	//	echo '根据用户表筛选查询'.$taskinfo['tasklimit'];//tasklimit 
	  		//构造默认查询
	  		$where = ' where uid > '.$taskinfo['start_id'].'  '.$taskinfo['tasklimit']; //start_id;//起点UID
	  		
	  		$memberlist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."member ".$where." order by uid asc  limit 0, 10");  
	  		$startid =  $taskinfo['start_id'];
	  		if(count($memberlist) > 9)
	  		{
          	foreach($memberlist as $key=>$value)//循环取出邮件集 
          	{
          	  if(IValidate::email($value['email'])) {
          		 $emailids .= empty($emailids)?	$value['email']:','.$value['email'];
              }  
             $startid = $value['uid'];
             
          	}
	  		}	 
	  		if(count($memberlist) < 10)
	  		{
	  			//更新任务执行完毕
	  			$newdata['status'] = 2;
	  			 $data['taskmiaoshu'] .= ',执行完毕';
	  		}else{
	  			//
	  			$newdata['status'] = 1;
	  			$newdata['start_id'] = $startid;//更新下一页
	  		  $data['taskmiaoshu'] .= ',从用户表uid为'.$taskinfo['start_id'].'执行到uid为'.$startid;
	  		}		 
	  	  //更新任务
	  	}else{
	  		$tasklimit = $taskinfo['tasklimit'];
	  		$checklist = explode(',',$tasklimit);
	  		foreach($checklist as $key=>$value)
	  		{
	  			 if(IValidate::email($value))
	  			  {
	  			  	 $emailids .= empty($emailids)? $value:','.$value;
	  			  }  
	  		} 
	  		$newdata['status'] = 2;
	  		//更新任务
	  		$data['taskmiaoshu'] .= ',根据指定邮箱地址发送邮件完成';
	  	}
	  	//更新任务
	  	 $this->mysql->update(Mysite::$app->config['tablepre'].'task',$newdata,"id='".$taskid."'");  
	  	if(!empty($emailids))
	  	{
	       $smtp = new ISmtp(Mysite::$app->config['smpt'],25,Mysite::$app->config['emailname'],Mysite::$app->config['emailpwd'],false); 
       //$content = iconv('utf-8','gb2312',$content);
        $info = $smtp->send($emailids, Mysite::$app->config['emailname'],$taskinfo['taskname'],$taskinfo['content'] , "" , "HTML" , "" , "");
      } 
      $data['taskdata'] = $newdata;
      $data['showcontent'] = $emailids; 
     
	  }else{ 
	  	
	  	$emailids = '';//邮箱ID集
	  	$newdata = array();//任务处理数据
	    $data['taskmiaoshu'] .= '短信群发任务';
	  	if($taskinfo['taskusertype'] == 1)
	  	{
	  	//	echo '根据用户表筛选查询'.$taskinfo['tasklimit'];//tasklimit 
	  		//构造默认查询
	  		$where = ' where uid > '.$taskinfo['start_id'].'  '.$taskinfo['tasklimit']; //start_id;//起点UID
	  		
	  		$memberlist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."member ".$where." order by uid asc  limit 0, 10");  
	  		$startid =  $taskinfo['start_id'];
	  		if(count($memberlist) > 9)
	  		{
          	foreach($memberlist as $key=>$value)//循环取出邮件集 
          	{
          	  if(IValidate::suremobi($value['phone'])) {
          		 $emailids .= empty($emailids)?	$value['phone']:','.$value['phone'];
              }  
             $startid = $value['uid'];
             
          	}
	  		}	 
	  		if(count($memberlist) < 10)
	  		{
	  			//更新任务执行完毕
	  			$newdata['status'] = 2;
	  			 $data['taskmiaoshu'] .= ',执行完毕';
	  		}else{
	  			//
	  			$newdata['status'] = 1;
	  			$newdata['start_id'] = $startid;//更新下一页
	  		  $data['taskmiaoshu'] .= ',从用户表uid为'.$taskinfo['start_id'].'执行到uid为'.$startid;
	  		}		 
	  	  //更新任务
	  	}else{
	  		$tasklimit = $taskinfo['tasklimit'];
	  		$checklist = explode(',',$tasklimit);
	  		foreach($checklist as $key=>$value)
	  		{
	  			 if(IValidate::suremobi($value))
	  			  {
	  			  	 $emailids .= empty($emailids)? $value:','.$value;  
	  			  }  
	  		} 
	  		$newdata['status'] = 2;
	  		//更新任务
	  		$data['taskmiaoshu'] .= ',根据指定手机号发送短信完成';
	  	}
	  	//更新任务
	  	$data['showcontent'] = $emailids; 
	   if(!empty($emailids))
	   {
		   
	      	  	
	      	  	$data['taskmiaoshu'] .= ',不支持群发,错误代码:'.$chekcinfo;
	      	  
	    } 
		  
      $data['taskdata'] = $newdata;
      
	  } 
	 Mysite::$app->setdata($data);
	}
	function deltask()
	{ 
		 $id = IReq::get('id'); 
		 if(empty($id))  $this->message('task_empty'); 
		 $ids = is_array($id)? join(',',$id):$id; 
	   $this->mysql->delete(Mysite::$app->config['tablepre'].'task'," id in($ids) ");   
	   $this->success('success');//(array('error'=>false)); 
	 }
	 
	 
 /* 8.3新增  2016-05-29  zem	 */
	 
	 function juanmarketing(){
		 
		 	$name = IReq::get('name');
       	$type = intval(IReq::get('type'));
      	$starttime = trim(IReq::get('starttime'));
      	$endtime = trim(IReq::get('endtime'));
      	$newlink = '';
      	$where= '';
       	$data['name'] = '';
      	if(!empty($name))//限制值
      	{ 
      			 $data['name'] = $name;
				 $where .= " and name like '%".$name."%'";
          	    $newlink .= '/name/'.$name; 
      	}
      	
		$data['type'] = '';
      	if(!empty($type))//限制值
      	{ 
      			 $data['type'] = $type;
				 $where .= " and type =  ".$type." ";
          	    $newlink .= '/type/'.$type; 
      	}
      	 
		 
		 
      	$data['starttime'] = '';
      	if(!empty($starttime))
      	{
      		 $data['starttime'] = $starttime;
      		 $where .= ' and  addtime > '.strtotime($starttime.' 00:00:01').' ';
         	 $newlink .= '/addtime/'.$starttime; 
      	}
      	$data['endtime'] = '';
      	if(!empty($endtime))
      	{
      		 $data['endtime'] = $endtime;
      		 $where .= ' and  endtime < '.strtotime($endtime.' 23:59:59').' ';
         	 $newlink .= '/endtime/'.$endtime; 
      	} 
      	$data['where'] = " id > 0 ".$where;
 	  
		 
		
		$pageinfo = new page();
 	   $pageinfo->setpage(intval(IReq::get('page')),10); 
		
		$marketinglist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."juanrule where   ".$data['where']."   order by orderid asc    limit ".$pageinfo->startnum().", ".$pageinfo->getsize()."  "); 

		$shuliang  = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."juanrule where   ".$data['where']."   order by orderid asc");
		$pageinfo->setnum($shuliang);
		$pagelink = IUrl::creatUrl('adminpage/card/module/juanmarketing'.$newlink);
		$data['pagecontent'] = $pageinfo->getpagebar($pagelink); 		
		
		$data['marketinglist'] = $marketinglist;
		
		
		
		$data['juantypename'] = array(
			'1'=>'充值',
			'2'=>'下单成功分享',
			'3'=>'推广',
			'4'=>'首次关注微信领取',
		);
		
		
           Mysite::$app->setdata($data);
		
		
	 }
	 
	 
	 function savemarketing()
	{ 
		$id = intval(IReq::get('id'));  
		$data['id'] = $id;
		$name = trim(IReq::get('name')); 
		$type = intval(IReq::get('type')); 
		$juantotalcost = intval(IReq::get('juantotalcost')); 
		$juannum = intval(IReq::get('juannum')); 
		$jiancostmin = intval(IReq::get('jiancostmin')); 
		$jiancostmax = intval(IReq::get('jiancostmax')); 
		$jiacostmin = intval(IReq::get('jiacostmin')); 
		$jiacostmax = intval(IReq::get('jiacostmax')); 
		$paytype = IReq::get('paytype'); 
		$daynum = intval(IReq::get('daynum')); 
		$is_open = intval(IReq::get('is_open')); 
 		$orderid = intval(IReq::get('orderid')); 
		  
		 
		if(empty($name))$this->message('请填写名称！');
		if($type <= 0)$this->message('请选择优惠券类型');
		if(!in_array($type,array(1,2,3,4)))$this->message('获取优惠券类型失败');
		if($type != 1){
			if(empty($juannum))$this->message('优惠券数量为空');
		}
		if( $jiancostmax > 0 ){
			if( $jiancostmin > $jiancostmax ) {   
				$this->message('请正确填写优惠券限制金额范围');  
			}
		} 
		if( $jiacostmin > $jiacostmax )$this->message('请正确填写优惠券优惠金额范围');
		
		 $tempvalue = '';
		 if(is_array($paytype)){
		 	$tempvalue = join(',',$paytype);
		 }

		if(empty($tempvalue))$this->message('请选择优惠券支持的支付方式');
		
		
		if(empty($daynum))$this->message('请填写优惠券失效天数');
		
		
		
		
		$data['name'] = $name;
		$data['type'] = $type;
		$data['juantotalcost'] = $juantotalcost;
		$data['juannum'] = $juannum;
		$data['jiancostmin'] = $jiancostmin;
		$data['jiancostmax'] = $jiancostmax;
		$data['jiacostmin'] = $jiacostmin;
		$data['jiacostmax'] = $jiacostmax;
		$data['paytype'] = $tempvalue;
 		$data['endtime'] = time()+$daynum*24*60*60;
		$data['is_open'] = $is_open;
		
		$data['orderid'] = $orderid;
		
		
 		if(empty($id)){
			 $data['addtime'] = time();
			$this->mysql->insert(Mysite::$app->config['tablepre'].'juanrule',$data); 
		}else{
			
			$this->mysql->update(Mysite::$app->config['tablepre'].'juanrule',$data,"id='".$id."'");  
		}
		$this->success('success');
	} 
	 
	 function delmarketing(){ 
   	 $id = IReq::get('id'); 
		 if(empty($id))  $this->message('获取失败'); 
		 $ids = is_array($id)? join(',',$id):$id;    
	   $this->mysql->delete(Mysite::$app->config['tablepre'].'juanrule',"id in($ids)");  
	   $this->success('success'); 
   } 
   
   
    
	 function saverechargecost()
	{ 
		$id = intval(IReq::get('id'));  
		$data['id'] = $id;
		
		$cost = intval(IReq::get('cost')); 
		$is_sendcost = intval(IReq::get('is_sendcost')); 
		$sendcost = trim(IReq::get('sendcost')); 
		$is_sendjuan = intval(IReq::get('is_sendjuan')); 
 		$sendjuancost = number_format( trim(IReq::get('sendjuancost'))  ,2); 
		$orderid = intval(IReq::get('orderid')); 

	 
		if(empty($cost))$this->message('请填写充值金额！');
 		if( $is_sendcost > 0 ){
			if(empty($sendcost))$this->message('请填写赠送金额！');
		}
 		if( $is_sendjuan == 1 ){
			$checkjuan =  $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."juanrule where type  = 1 and juantotalcost = ".$sendjuancost."   "); 
			if(empty($checkjuan)) $this->message('填写优惠券总金额之前，请先在营销分享优惠券中添加充值类型的优惠券总金额规则');
		} 
 		
		$data['cost'] = $cost;
		$data['is_sendcost'] = $is_sendcost;
		$data['sendcost'] = $sendcost;
		$data['is_sendjuan'] = $is_sendjuan; 
		$data['sendjuancost'] = $sendjuancost; 
		$data['orderid'] = $orderid;
		
		
 		if(empty($id)){
			$this->mysql->insert(Mysite::$app->config['tablepre'].'rechargecost',$data); 
		}else{
			 
			$this->mysql->update(Mysite::$app->config['tablepre'].'rechargecost',$data,"id='".$id."'");  
		}
		$this->success('success');
	} 
	 
	 function rechargezend(){
		 
		 	$cost = IReq::get('cost');
       	$newlink = '';
      	$where= '';
       	 
      	
		$data['cost'] = '';
      	if(!empty($cost))//限制值
      	{ 
      			 $data['cost'] = $cost;
				 $where .= " and cost =  ".$cost." ";
          	    $newlink .= '/cost/'.$cost; 
      	}
      	 
		 
		 
      	$data['where'] = " id > 0 ".$where;
 	  
		 
		
		$pageinfo = new page();
 	   $pageinfo->setpage(intval(IReq::get('page')),10); 
		
		$rechargelist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."rechargecost where   ".$data['where']."   order by orderid asc    limit ".$pageinfo->startnum().", ".$pageinfo->getsize()."  "); 

		$shuliang  = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."rechargecost where   ".$data['where']."   order by orderid asc");
		$pageinfo->setnum($shuliang);
		$pagelink = IUrl::creatUrl('adminpage/card/module/rechargecost'.$newlink);
		$data['pagecontent'] = $pageinfo->getpagebar($pagelink); 		
		
		$data['rechargelist'] = $rechargelist;
		 
		$data['juantypename'] = array(
			'1'=>'充值',
			'2'=>'下单成功分享',
			'3'=>'推广',
		);
		
		
           Mysite::$app->setdata($data);
		
		
	 }
	  
	 function delrechargecost(){ 
   	 $id = IReq::get('id'); 
		 if(empty($id))  $this->message('获取失败'); 
		 $ids = is_array($id)? join(',',$id):$id;    
	   $this->mysql->delete(Mysite::$app->config['tablepre'].'rechargecost',"id in($ids)");  
	   $this->success('success'); 
   } 
   
     public function juanupload()
	 {
 
	 	  $_FILES['imgFile'] = $_FILES['head'];
 			$json = new Services_JSON();
			$uploadpath = 'upload/juan/';
		  $filepath ='/upload/juan/';
      $upload = new upload($uploadpath,array('gif','jpg','jpge','png'));//upload
      $file = $upload->getfile();
	   
      if($upload->errno!=15&&$upload->errno!=0) {
		      $this->message($upload->errmsg());
		  }else{ 
		      $this->success($filepath.$file[0]['saveName']);
		  }
	 }
	 
	  
	 function savejuanshowinfo()
	{ 
		$id = intval(IReq::get('uid'));  
		$data['id'] = $id;
		
		$type = intval(IReq::get('type')); 
		$title = trim(IReq::get('title')); 
		$img = trim(IReq::get('img')); 
		$describe = trim(IReq::get('describe')); 
		$bigimg = trim(IReq::get('bigimg')); 
		$color = trim(IReq::get('color')); 
		$actcolor = trim(IReq::get('actcolor')); 
		$avtrule = trim(IReq::get('content')); 
		$orderid = trim(IReq::get('orderid')); 
	  
		  
		 
		if(empty($type)) $this->message('请选择类型！');
 		if(!in_array($type,array(2,3,4)))$this->message('获取选择类型失败');
		if(empty($title)) $this->message('请填写标题！');
		if(empty($img)) $this->message('请上传分享展示图标！');
		if(empty($describe)) $this->message('请填写描述！');
		if(empty($bigimg)) $this->message('请上传领取优惠券页面头部展示大图！');
		if(empty($color)) $this->message('请填写领取优惠券页面背景色！');
		if(empty($actcolor)) $this->message('请填写领取优惠券页面活动规则背景色！');
		if(empty($avtrule)) $this->message('请填写活动规则！');
		 
		
 		$data['type'] = $type;
 		$data['title'] = $title;
 		$data['img'] = $img;
 		$data['describe'] = $describe;
 		$data['bigimg'] = $bigimg;
 		$data['color'] = $color;
 		$data['actcolor'] = $actcolor;
 		$data['avtrule'] = $avtrule;
 		$data['orderid'] = $orderid;
		  
		
 		if(empty($id)){
			 $data['addtime'] = time();
			$this->mysql->insert(Mysite::$app->config['tablepre'].'juanshowinfo',$data); 
		}else{
			
			$this->mysql->update(Mysite::$app->config['tablepre'].'juanshowinfo',$data,"id='".$id."'");  
		}
		$this->success('success');
	} 
	 
    function sharejsinfo(){
		 
		$title = IReq::get('title');
       	$type = intval(IReq::get('type'));
       	$newlink = '';
      	$where= '';
       	$data['title'] = '';
      	if(!empty($title))//限制值
      	{ 
      			 $data['title'] = $title;
				 $where .= " and title like '%".$title."%'";
          	    $newlink .= '/title/'.$title; 
      	}
      	
		$data['type'] = '';
      	if(!empty($type))//限制值
      	{ 
      			 $data['type'] = $type;
				 $where .= " and type =  ".$type." ";
          	    $newlink .= '/type/'.$type; 
      	} 
		 
		 
      	$data['where'] = " id > 0 ".$where;
 	  
		 
		
		$pageinfo = new page();
 	   $pageinfo->setpage(intval(IReq::get('page')),10); 
		
		$shareshowinfo = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."juanshowinfo where   ".$data['where']."   order by orderid asc    limit ".$pageinfo->startnum().", ".$pageinfo->getsize()."  "); 

		$shuliang  = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."juanshowinfo where   ".$data['where']."   order by orderid asc");
		$pageinfo->setnum($shuliang);
		$pagelink = IUrl::creatUrl('adminpage/card/module/sharejsinfo'.$newlink);
		$data['pagecontent'] = $pageinfo->getpagebar($pagelink); 		
		
		$data['shareshowinfo'] = $shareshowinfo;
		
		
		
		$data['juantypename'] = array(
 			'2'=>'下单分享页面',
			'3'=>'推广页面',
			'4'=>'关注微信领取优惠券',
		);
		
		
           Mysite::$app->setdata($data);
		
		
	 }
	 
	  function delsjsinfo(){ 
   	 $id = IReq::get('id'); 
		 if(empty($id))  $this->message('获取失败'); 
		 $ids = is_array($id)? join(',',$id):$id;    
	   $this->mysql->delete(Mysite::$app->config['tablepre'].'juanshowinfo',"id in($ids)");  
	   $this->success('success'); 
   } 
   
  function receivejuanlog(){  // 优惠券领取记录列表
		 
		$name = IReq::get('name');
		$username = IReq::get('username');
		$bangphone = IReq::get('bangphone');
       	$type = intval(IReq::get('type'));
       	$status = intval(IReq::get('status'));
      	$starttime = trim(IReq::get('starttime'));
      	$endtime = trim(IReq::get('endtime'));
      	$newlink = '';
      	$where= '';
       	$data['name'] = '';
      	if(!empty($name))//限制值
      	{ 
      			 $data['name'] = $name;
				 $where .= " and name like '%".$name."%'";
          	    $newlink .= '/name/'.$name; 
      	}
      	 	$data['username'] = '';
      	if(!empty($username))//限制值
      	{ 
      			 $data['username'] = $username;
				 $where .= " and username like '%".$username."%'";
          	    $newlink .= '/username/'.$username; 
      	}
      		 	$data['bangphone'] = '';
      	if(!empty($bangphone))//限制值
      	{ 
      			 $data['bangphone'] = $bangphone;
				 $where .= " and bangphone like '%".$bangphone."%'";
          	    $newlink .= '/bangphone/'.$bangphone; 
      	}
      	
		$data['type'] = '';
      	if(!empty($type))//限制值
      	{ 
      			 $data['type'] = $type;
				 $where .= " and type =  ".$type." ";
          	    $newlink .= '/type/'.$type; 
      	}
      	 $data['status'] = '';
      	if(!empty($status))//限制值
      	{ 			
				$newstatus = $status-1;
      			 $data['status'] = $status;
				 $where .= " and status =  ".$newstatus." ";
          	    $newlink .= '/status/'.$newstatus; 
      	}
      	  
      	$data['starttime'] = '';
      	if(!empty($starttime))
      	{
      		 $data['starttime'] = $starttime;
      		 $where .= ' and  creattime > '.strtotime($starttime.' 00:00:01').' ';
         	 $newlink .= '/addtime/'.$starttime; 
      	}
      	$data['endtime'] = '';
      	if(!empty($endtime))
      	{
      		 $data['endtime'] = $endtime;
      		 $where .= ' and  creattime < '.strtotime($endtime.' 23:59:59').' ';
         	 $newlink .= '/endtime/'.$endtime; 
      	} 
      	$data['where'] = " id > 0 ".$where; 
		$pageinfo = new page();
 	   $pageinfo->setpage(intval(IReq::get('page')),10); 
		
		$receivejuanlog = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."juan where  type in(1,2,3,4,5) and  ".$data['where']."   order by creattime desc    limit ".$pageinfo->startnum().", ".$pageinfo->getsize()."  "); 

		$shuliang  = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."juan where   type in(1,2,3,4,5) and   ".$data['where']."   order by creattime desc");
		$pageinfo->setnum($shuliang);
		$pagelink = IUrl::creatUrl('adminpage/card/module/receivejuanlog'.$newlink);
		$data['pagecontent'] = $pageinfo->getpagebar($pagelink); 	 
		$data['receivejuanlog'] = $receivejuanlog; 
		$data['juantypename'] = array(
			'1'=>'充值送',
			'2'=>'下单分享领取',
			'3'=>'推广领取',
			'4'=>'返赠',
			'5'=>'关注微信领取优惠券',
		); 
		$data['juanstatus'] = array(
			'0'=>'未使用',
			'1'=>'已绑定',
			'2'=>'已使用',
			'3'=>'3无效',
		); 
           Mysite::$app->setdata($data);
		
		
	 }
 function savesharejuanset(){   //保存分享优惠券设置
	    $siteinfo['userordersharejuan'] =  intval(IReq::get('userordersharejuan'));
	    $siteinfo['userextensionsharejuan'] =  intval(IReq::get('userextensionsharejuan'));
	    $config = new config('hopeconfig.php',hopedir,$this->mid,$type=1,Mysite::$app->config['conf_table']);  
	    $config->write($siteinfo);
	    $configs = new config('hopeconfig.php',hopedir,$this->mid,$type=1,Mysite::$app->config['conf_table']);   
	    $tests = $config->getInfo();
		$this->success('success');
 }  
   
   	 
	
/*
*	8.3新增功能 
*	2016-06-04------ 
*	zem   
*/
	 function setstatus(){
	    $data['shoptype'] = array('0'=>'外卖','1'=>'超市');
	   Mysite::$app->setdata($data);
	}
	function virtualinfo(){	//增加店铺虚拟信息
	    $this->setstatus();
	    $where = '';
	    $goodswhere = '';
	     
	    
	    $data['shopname'] =  trim(IReq::get('shopname'));
	    $data['name'] =  trim(IReq::get('name'));
	   $data['username'] =  trim(IReq::get('username'));
	   $data['shop_type'] =  intval(IReq::get('shop_type'));
	 	 $data['phone'] = trim(IReq::get('phone'));
	 	 if(!empty($data['shopname'])){
 		    $where .= " and shopname like '%".$data['shopname']."%'";
	 	 } 
 		 if(!empty($data['shop_type'])){
			 $newshoptype = $data['shop_type']-1;
 		    $where .= " and shoptype = '".$newshoptype."'  ";
	 	 }
	 	 if(!empty($data['username'])){
	 	   $where .= " and uid in(select uid from ".Mysite::$app->config['tablepre']."member where username='".$data['username']."')";
	 	 }
	 	 if(!empty($data['phone'])){
	 	    $where .=" and phone='".$data['phone']."'";
	 	 }
 	 	 //构造查询条件
	 	 $data['where'] = $where; 
	    
		
		 if(!empty($data['shopname'])){
 		    $goodswhere .= " and shopname like '%".$data['shopname']."%'";
	 	 }
		 if(!empty($data['name'])){
	 	    $goodswhere .= " and name like '%".$data['name']."%'";
	 	 }
	 
		
		$this->pageCls->setpage(intval(IReq::get('page')),60); 
	 
 			$selectlist = $this->mysql->getarr("select id,shopname,phone,shoptype,uid,virtualsellcounts from ".Mysite::$app->config['tablepre']."shop  where is_pass = 1 ".$where."  order by sort asc  limit ".$this->pageCls->startnum().", ".$this->pageCls->getsize()."  ");
 			$shuliang  = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."shop  where is_pass = 1 ".$where." ");
 	   
	   $this->pageCls->setnum($shuliang); 
	  $data['pagecontent'] = $this->pageCls->getpagebar();
 		$data['selectlist'] = $selectlist;
 
	    Mysite::$app->setdata($data);
	    
	}
 	function saveshopsellcount(){   //保存店铺虚拟总销量
		$shopid = intval(IReq::get('shopid'));
		$virtualsellcounts= intval(IReq::get('savesellcounts'));
		$data['virtualsellcounts'] = $virtualsellcounts;
 		$this->mysql->update(Mysite::$app->config['tablepre'].'shop',$data,"id='".$shopid."'");
		$this->success('success');
	}
	
	
	function virtualgoods(){	//增加商品虚拟信息
	    $this->setstatus();
	    $where = '';
	    $goodswhere = '';
	    $goodswhere2 = '';
	     $shopid =  intval(IReq::get('id'));
 		 $shopinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."shop   where id = '".$shopid."'  ");
 		if(empty($shopinfo)){
			echo "获取店铺失败";
			exit;
		}
		$data['shopinfo'] = $shopinfo;
 	    $data['name'] =  trim(IReq::get('name'));
 	 	 
 	 	 //构造查询条件
	 	 $data['where'] = $where; 
	     
		 if(!empty($data['name'])){
	 	    $goodswhere .= " and name like '%".$data['name']."%'";
	 	    $goodswhere2 .= " and goodsname like '%".$data['name']."%'";
	 	 }
	 
		
		$this->pageCls->setpage(intval(IReq::get('page')),60); 
	 
  $selectlist1 = $this->mysql->getarr("select id,name,sellcount,virtualsellcount from ".Mysite::$app->config['tablepre']."goods  where shopid = '".$shopinfo['id']."'  ".$goodswhere."  order by good_order asc  limit ".$this->pageCls->startnum().", ".$this->pageCls->getsize()."  ");
	$selectlist3 =array();
  $selectlist2 = $this->mysql->getarr("select id,goodsid,goodsname,attrname from ".Mysite::$app->config['tablepre']."product  where shopid = '".$shopinfo['id']."'  ".$goodswhere2."  order by id asc  limit ".$this->pageCls->startnum().", ".$this->pageCls->getsize()."  ");
 
	foreach($selectlist2 as $key=>$val){ 
		$val['name'] = $val['goodsname'].'【'.$val['attrname'].'】';
		$val['id'] = $val['goodsid'];
		$selectlist3[] = $val; 
	}
 	$selectlist = array_merge($selectlist1,$selectlist3);

 $shuliang  = $this->mysql->counts("select * from ".Mysite::$app->config['tablepre']."goods  where shopid = '".$shopinfo['id']."'   ".$goodswhere." ");
 	   
	   $this->pageCls->setnum($shuliang); 
	  $data['pagecontent'] = $this->pageCls->getpagebar();
 		$data['selectlist'] = $selectlist;
 
	    Mysite::$app->setdata($data);
	    
	}
	 
	function savevirtualgoodcom(){  //后台保存添加商品虚拟评价
		
		$goodid = intval(IReq::get('goodid'));
		$point = intval(IReq::get('point'));
		$content = trim(IReq::get('content'));
		$addtime = trim(IReq::get('addtime'));
		$virtualname = trim(IReq::get('virtualname'));   // 新增   虚拟人名称
		
		 $goodsinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goods   where id = '".$goodid."'  ");
		 if(empty($goodsinfo)) $this->message('获取商品信息失败');
		 if(empty($point)) $this->message('请对商品进行评分');
		 if(empty($virtualname)) $this->message('请填写评论人');
		
		
		$data['goodsid'] = $goodid;
		$data['shopid'] = $goodsinfo['shopid'];
		$data['content'] = $content;
		$data['addtime'] = strtotime($addtime);
		$data['point'] = $point;
		$data['is_show'] = 0;
		$data['virtualname'] = $virtualname; 
		$this->mysql->insert(Mysite::$app->config['tablepre'].'comment',$data);
		$this->success('success');
	}
	
		 
	 

	 
	 
	 
	 
}



?>