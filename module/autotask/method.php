<?php
/*
 	自动执行任务方法   
 */
class method   extends baseclass
{  	

    //超时自动取消预约单,在服务器执行脚本
    function cancel_order_auto() {
//        set_time_limit(3000);
//        $interval=5;// 每隔5s运行
      
            //超时未接单自动取消该订单
            $shoparr = $this->mysql->getarr("select * from " . Mysite::$app->config['tablepre'] . "shop where is_open=1 and is_pass=1");
            foreach ($shoparr as $k => $v) {
                
                //$where = " and ".$time_now.">=booktime ";
                $shopid = $v['id'];
                $list = $this->mysql->getarr("select *  from " . Mysite::$app->config['tablepre'] . "order where shopid='" . $shopid . "' " . $where . " and is_goshop = 2 and isbefore=2 and ydstatus in(0) order by id desc");
                foreach ($list as $key => $val) {
                    $addtime = $val['addtime'] + 60 * $v['auto_cancel_reserve'];
                    if(time()>=$addtime && $val['ydstatus']==0){
                        $data_c['ydstatus'] = 3;
                        $data_c['suretime'] = time();//更新取消时间
                        $this->mysql->update(Mysite::$app->config['tablepre'] . 'order', $data_c, "shopid = '" . $shopid . "' and id='" . $val['id'] . "' ");
//                        sleep($interval);// 等待5s
                        
                        $info = $this->mysql->select_one("select b.openid,a.id,a.ydstatus,a.mid from ".Mysite::$app->config['tablepre']."order a, ".Mysite::$app->config['tablepre']."wxuser b where a.buyeruid = b.uid and a. id = '".$val['id']."'");
                        if($info['ydstatus']==3){
                             sendCustomMsg($info['mid'], $this->SendWxMsgStatus($info['id'],3),$info['openid']);  //发送超时未接单的微信通知信息
                        }
                    }
                }
            }
            
            //超时未消费自动取消该订单
            $this->OvertimeNonConsumption();
            
            //自动收货超时时间
            $this->AutoReceipttime();
           
//            ob_flush();
//            flush();
      echo "执行完成!";
    }
    
    //超时未消费自动取消该订单
    function OvertimeNonConsumption(){
        
            $shoparr = $this->mysql->getarr("select * from " . Mysite::$app->config['tablepre'] . "shop where is_open=1 and is_pass=1");
            foreach ($shoparr as $k => $v) {
               
                //$where = " and ".$time_now.">=booktime ";
                $shopid = $v['id'];
                $list = $this->mysql->getarr("select *  from " . Mysite::$app->config['tablepre'] . "order where shopid='" . $shopid . "' " . $where . " and is_goshop = 2 and isbefore=2 and ydstatus in(1) order by id desc");
                foreach ($list as $key => $val) {
                    $booktime = $val['booktime'] + 60 * $v['auto_cancel_reserve'];
                    if(time()>=$booktime && $val['ydstatus']==1){
                        $data_c['ydstatus'] = 3;
                        $data_c['suretime'] = time();//更新取消时间
                        $this->mysql->update(Mysite::$app->config['tablepre'] . 'order', $data_c, "shopid = '" . $shopid . "' and id='" . $val['id'] . "' ");
//                        sleep($interval);// 等待5s
                        
                        $info = $this->mysql->select_one("select b.openid,a.id,a.ydstatus,a.mid from ".Mysite::$app->config['tablepre']."order a, ".Mysite::$app->config['tablepre']."wxuser b where a.buyeruid = b.uid and a. id = '".$val['id']."'");
                        if($info['ydstatus']==3){
                          sendCustomMsg($info['mid'], $this->SendWxMsgStatus($info['id'],4),$info['openid']);  //发送超时未消费的微信通知信息
                        }
                    }
                   
                }
            }
        
    }
    
    //自动收货超时时间
    function AutoReceipttime(){
        
            $shoparr = $this->mysql->getarr("select a.id,b.autoreceipttime from " . Mysite::$app->config['tablepre'] . "shop a,".Mysite::$app->config['tablepre']."shopfast b where a.id=b.shopid and a.is_open=1 and a.is_pass=1");
            
            foreach ($shoparr as $k => $v) {
               
                $list = $this->mysql->getarr("select *  from " . Mysite::$app->config['tablepre'] . "order where shopid='" .$v['id']. "' and status in(2) order by id desc");
                
                foreach ($list as $key => $val) {
                   
                    $sendtime = $val['sendtime'] + 60 * $v['autoreceipttime'];
                    //echo date('Y-m-d H:i',$sendtime);die;
                    if( time() >= $sendtime ){
                        $data_c['status'] = 3;
                        $data_c['suretime'] = time();//更新完成时间
                        $this->mysql->update(Mysite::$app->config['tablepre'] . 'order', $data_c, " id='" . $val['id'] . "' ");

                    }
                   
                }
            }
        
    }
    
    
    function SendWxMsgStatus($orderid=NULL,$ydstatus){
       if(empty($orderid)) $this->message('微信信息发送失败！');

       $info = $this->mysql->select_one("select suretime,dno from ".Mysite::$app->config['tablepre']."order where id = '".$orderid."'");
       switch ($ydstatus) {
            case 3:
                $str = "因商家超时未接单，您的预订已被系统自动取消！\n";
                $str .= "取消时间：".date('Y-m-d H:i',$info['suretime']) ."\n";
                $str .= "订单号：".$info['dno']."\n";
                $str .= "感谢您的预订！\n";
            break;
            case 4:
                $str = "因您未在预订时间内到店消费，且超过本店规定预留时间，您的预订已被系统自动取消！\n";
                $str .= "取消时间：".date('Y-m-d H:i',$info['suretime']) ."\n";
                $str .= "订单号：".$info['dno']."\n";
                $str .= "感谢您的预订，下次请您准时到店消费！\n";
            break;

           default:
               break;
       }                

       return $str;
   }
    
 


 

}