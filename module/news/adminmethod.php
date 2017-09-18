<?php
class method   extends adminbaseclass
{
	   function index(){
	 	       $link = IUrl::creatUrl('/adminpage/news/module/newslist');
           $this->refunction('',$link);
	 }
	 function savenewstype(){
	 	$id = intval(IReq::get('uid'));
		$data['name'] = IReq::get('name');
		$data['orderid']  = intval(IReq::get('orderid'));
		$data['type'] = intval(IReq::get('type'));
		$data['parent_id'] = intval(IReq::get('parent_id'));
		$data['displaytype'] = intval(IReq::get('displaytype'));
		if(empty($data['name'])) $this->message('news_emptynewstypename');
		if(empty($id))
		{
			$this->mysql->insert(Mysite::$app->config['tablepre'].'newstype',$data);
		}else{
			$this->mysql->update(Mysite::$app->config['tablepre'].'newstype',$data,"id='".$id."'");
		}
		$this->success('success','');
   }
   function delnews(){
   	  $id = IFilter::act(IReq::get('id'));
		  if(empty($id))  $this->message('news_empty');
		  $ids = is_array($id)? join(',',$id):$id;
		 if(empty($ids))  $this->message('news_empty');
	    $this->mysql->delete(Mysite::$app->config['tablepre'].'news'," id in($ids)");
	    $this->success('success','');
  }
   function delnewstype(){
		 $id = IFilter::act(IReq::get('id'));
		  if(empty($id))  $this->message('news_emptytype');
		 $ids = is_array($id)? join(',',$id):$id;
		 if(empty($ids))  $this->message('news_emptytype');
	   $this->mysql->delete(Mysite::$app->config['tablepre'].'newstype'," id in($ids)");
	   $this->success('success','');
   }
   function addnews(){
    $id = intval(IReq::get('id'));
		$data['info'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."news where id=".$id."  ");

    $data['typlist']= $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."newstype  order by orderid desc ");
    $temptypeid = array();
    if(!empty($id)){
		$tempid = $data['info']['typeid'];
     while ($tempid > 0) {
        $getstr =     $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."newstype where id=".$tempid."  ");
        if(!empty($getstr)){
        	$tempid = $getstr['parent_id'];
        	$temptypeid[] = $getstr['id'];
        }else{
        	$temptypeid[] = $tempid;
        	$tempid = 0;
        }
      }
		 $data['allids'] = $temptypeid;
		}else{
			$data['allids'] = array();
		}

		Mysite::$app->setdata($data);
   }
   function savenews(){
      $id = IReq::get('uid');
	   	$data['addtime'] = strtotime(IReq::get('addtime').' 00:00:00');
	   	$data['title'] = IReq::get('title');
	   	$data['content'] = IReq::get('content');
	   	$data['orderid'] = IReq::get('orderid');
	   	$data['typeid'] = IReq::get('typeid');
	   	$data['seo_key'] = IFilter::act(IReq::get('seo_key'));
	   	$data['seo_content'] = IFilter::act(IReq::get('seo_content'));

	   	if(empty($id))
	   	{
	   		$link = IUrl::creatUrl('adminpage/news/module/addnews');
	   		if(empty($data['content'])) $this->message('news_emptycontent',$link);
	   		if(empty($data['title'])) $this->message('news_emptytitle',$link);
	   		$this->mysql->insert(Mysite::$app->config['tablepre'].'news',$data);
	   	}else{
	   		$link = IUrl::creatUrl('adminpage/news/module/addnews/id/'.$id);
	   		if(empty($data['content'])) $this->message('news_emptycontent',$link);
	   		if(empty($data['title'])) $this->message('news_emptytitle',$link);
	   		$this->mysql->update(Mysite::$app->config['tablepre'].'news',$data,"id='".$id."'");
	   	}
	   	$link = IUrl::creatUrl('adminpage/news/module/newslist');
	    $this->success('success',$link);
   }
   function addnewstype()
	{
	  $id = intval(IReq::get('id'));
		$data['info'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."newstype where id=".$id."  ");
		$mydatinfo = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."newstype ");
		$data['typeoption'] = $this->huannewtype($mydatinfo,0,0,$data['info']['parent_id']);

	 	Mysite::$app->setdata($data);
	}

	function huannewtype($mydatinfo,$parent_id,$grade,$nowid=0){
		$htmlcontent = '';
		$tempshow = '';
		for($i = 0;$i< $grade;$i++)
		 {
		 	 $tempshow .="&nbsp&nbsp&nbsp&nbsp";
		 }
		foreach($mydatinfo as $key=>$value){
			if($value['parent_id'] == $parent_id){
			     if($value['type'] == 2)
			     {
			     	 $onoption = $nowid == $value['id']?'selected':'';
				     $htmlcontent .='<option value="'.$value['id'].'" '.$onoption.'>'.$tempshow.$value['name'].'</option>';
				     $htmlcontent .= $this->huannewtype($mydatinfo,$value['id'],$grade+1,$nowid);
				   }
			}
		}
		return $htmlcontent;
	}
	
	

   
   	/* 生活服务 */
  function addlifeass(){
		$id = intval(IReq::get('id'));
		$data['info'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."information where id=".$id." and type = 2 ");
 
		Mysite::$app->setdata($data);
   }
   function savelifeass(){
      $id = IReq::get('uid');
	   	$data['addtime'] = strtotime(IReq::get('addtime').' 00:00:00');
	   	$data['title'] = IReq::get('title');
	   	$data['content'] = IReq::get('content');
	   	$data['orderid'] = IReq::get('orderid');
	   	$data['img'] = IReq::get('img');
	   	$data['phone'] = IReq::get('phone');
	   	//$data['describe'] = IReq::get('describe');
		$data['type'] = 2; // type 1为网站通知 2为生活服务
		 
	   	if(empty($id))
	   	{
	   		$link = IUrl::creatUrl('adminpage/news/module/addlifeass');
	   		if(empty($data['title'])) $this->message('生活服务标题不能为空！',$link);
	   		//if(empty($data['describe'])) $this->message('生活服务简介不能为空！',$link);
	   		
	   		if(empty($data['phone'])) $this->message('生活服务联系电话不能为空！',$link);
			 
			 if(empty($data['content'])) $this->message('生活服务内容不能为空！',$link);
	   		$this->mysql->insert(Mysite::$app->config['tablepre'].'information',$data);
	   	}else{
	   		$link = IUrl::creatUrl('adminpage/news/module/addlifeass/id/'.$id);
	   		if(empty($data['title'])) $this->message('生活服务标题不能为空！',$link);
	   		if(empty($data['describe'])) $this->message('生活服务简介不能为空！',$link);
	   		
			if(empty($data['phone'])) $this->message('生活服务联系电话不能为空！',$link);
			
			 
			 if(empty($data['content'])) $this->message('生活服务内容不能为空！',$link);
	   		$this->mysql->update(Mysite::$app->config['tablepre'].'information',$data,"id='".$id."'");
	   	}
	   	$link = IUrl::creatUrl('adminpage/news/module/lifeassistant');
	    $this->success('success',$link);
   }
 function dellifeass(){
   	  $id = IFilter::act(IReq::get('id'));
		  if(empty($id))  $this->message('获取数据失败');
		  $ids = is_array($id)? join(',',$id):$id;
		 if(empty($ids))  $this->message('未选中数据');
	    $this->mysql->delete(Mysite::$app->config['tablepre'].'information'," id in($ids)");
	    $this->success('success','');
  }


    //8.4新增编辑帮助中心
    function addhelpcenter(){
        $id = intval(IReq::get('id'));
        $data['info'] = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."helpcenter where id=".$id."  ");
        Mysite::$app->setdata($data);
    }

    //8.4新增删除帮助中心
    function delhelpcenter(){
        $id = IFilter::act(IReq::get('id'));
        if(empty($id))  $this->message('删除失败');
        $ids = is_array($id)? join(',',$id):$id;
        if(empty($ids))  $this->message('删除失败');
        $this->mysql->delete(Mysite::$app->config['tablepre'].'helpcenter'," id in($ids)");
        $this->success('success');
    }

    //8.4新增保存帮助中心
    function savehelpcenter(){
        $id = IReq::get('uid');
        $data['addtime'] = strtotime(IReq::get('addtime').' 00:00:00');
        $data['title'] = IReq::get('title');
        $data['content'] = IReq::get('content');
        $data['orderid'] = IReq::get('orderid');
        $data['seo_key'] = IFilter::act(IReq::get('seo_key'));
        $data['seo_content'] = IFilter::act(IReq::get('seo_content'));

        if(empty($id))
        {
            $link = IUrl::creatUrl('adminpage/news/module/addhelpcenter');
            if(empty($data['content'])) $this->message('内容不能为空',$link);
            if(empty($data['title'])) $this->message('标题不能为空',$link);
            $this->mysql->insert(Mysite::$app->config['tablepre'].'helpcenter',$data);
        }else{
            $link = IUrl::creatUrl('adminpage/news/module/addhelpcenter/id/'.$id);
            if(empty($data['content'])) $this->message('内容不能为空',$link);
            if(empty($data['title'])) $this->message('标题不能为空',$link);
            $this->mysql->update(Mysite::$app->config['tablepre'].'helpcenter',$data,"id='".$id."'");
        }
        $link = IUrl::creatUrl('adminpage/news/module/helpcenter');
        $this->success('success',$link);
    }
  
	 
	
}



