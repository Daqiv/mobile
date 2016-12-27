<?php

/**
 * apimobile
 */
namespace Api\Controller;
use Think\Controller;
class MobileController extends Controller {
    
	//显示之前就修改状态
    public function index() {
        M()->startTrans();        
    	$data=M('applemobile')->field('mid,mobile,username')->where('isshow=0')->lock(true)->find();

        $t=M('applemobile')->where("mid=%d",$data['mid'])->setField('isshow',2);
        if(strlen($data['username'])>1){
           $data['username']= substr( $data['username'], 0,3);
        }

        M()->commit();
        if(!$t){
            M()->rollback();
            echo 0;
            exit();
        }
        $this->ajaxreturn($data,"xml");
        exit();
    }

    public function phonemobile(){
        $row=I("REQUEST.row");
        if(!$row){
            $row=1;
        }
        M()->startTrans();        
        $data=M('applemobile')->field('mid,mobile,username')->where('isshow=0')->limit($row)->lock(true)->select();
        foreach ($data as $k => $vl) {
            $t=M('applemobile')->where("mid=%d",$vl['mid'])->setField('isshow',3);
            if(!$t){
                M()->rollback();
                echo 0;
                exit();
            }
        }
        M()->commit();
        $this->ajaxreturn($data);
        exit();

    }
    //显示一个手机号码
    public function mboile(){
    	$data=M('mobile')->field('id,mobile')->where('status=0 and type=2')->find();
    	if($data){
    		echo $data['mobile'];
    	}else{
    		echo 0;
    	}
        exit();
    }
    
    //修改 ajax手机状态
    public function setmobiletype(){
        $mobile=I('get.mobile');
        $type=I('get.st');
        if($mobile){
            if($type==1){
                $info['type']=1;
                $info['status']=1;
            }else{
                if($type){
                    $info['type']=$type;
                }else{
                    $info['type']=0;    
                }
                
            }
            $info['showtime']=time();
            $result=M('mobile')->where("mobile='%s'",$mobile)->save($info);               
            if($result){
                echo 1;
            }else{
                echo 0;
            }   
            
        }else{
            echo 0;
        }
        exit();
    }
    
   
    
    //显示一个手机号码检查是否存在
   
    public function getmboiletype(){       
        //M()->startTrans();        
        //$data=M('mobile')->field('id,mobile')->where('status=0 and type=0')->lock(true)->find();
       // $prama['type']=7;
       // $sul=M('mobile')->where('id=%d',$data['id'])->setfield($prama);
       // if($sul){
       //     M()->commit();
       // }else{
       //     M()->rollback();
       // }
       // if(!$data){
       //     echo 0;  
       // }
       // echo $data['mobile'];      
       // exit();

        $count=M()->query('select count(*) as count from mobiledata.mobilefind where status=0 and type=0');   
        $nmb=rand(1,$count[0]['count']);         
        if($count>0){
            $data=M()->query('select * from mobiledata.mobilefind where status=0 and type=0 limit '.$nmb.',1'); 
            // if($data){
            //     $sul=M('mobile')->where("id=%d",$data[0]['id'])->setField('update',time());
            // }else{
            //     echo 0;
            //     exit();
            // } 

         }
        if($data){
            echo $data[0]['mobile'];         
        }else{
            echo 0;
        }
        exit();
    }

/*
    public function getmboiletype(){ 
        M()->startTrans();        
        $data=M('mobile')->field('id,mobile')->where('status=0 and type=2 and twotime=0')->lock(true)->find();

        //$data=M()->query('select id,mobile from mobiledata.mobilefind where status=0 and type=2 and twotime=0 limit 1');
        $sul=M('mobile')->where('id=%d',$data['id'])->setfield('twotime',time());
        if($sul){
            M()->commit();
        }else{
            M()->rollback();
        }
        if($data){
            echo $data['mobile'];         
        }else{
            echo 0;
        }
        exit();
    }
*/
    
/*
    public function getmboiletype(){ 
        M()->startTrans();        
        $data=M('mobile')->field('id,mobile')->where('status=0 and type=2 and twotime=0')->limit(1)->lock(true)->select();
        $sul=M('mobile')->where('id=%d',$data[0]['id'])->setfield('twotime',time());
        if($sul){
            M()->commit();
        }else{
            M()->rollback();
        }
        if($data){
            echo $data[0]['mobile'];         
        }else{
            echo 0;
        }
        exit();
    }
    */
    

    public function wxname(){
        $data=D('weixiname')->field('id,weixiname')->where('status=%d',0)->find();
        if($data){
            $info['status']=1;
            $result=M('weixiname')->where('id=%d',$data['id'])->save($info);
            if($result){
                echo $data['weixiname'];
            }else{
                echo 0;
            }
            exit();
        }       
        echo 0;
        exit();
    }
    //修改 ajax手机状态
    public function ajaxmobile(){
    	$mobile=I('get.mobile');
    	if($mobile){
    		$info['status']=1;
    		$result=M('mobile')->where('id=%d',$data['id'])->save($info);	
    		echo 1;
    	}else{
    		echo 0;
    	}
        exit();
    }

    public function saveweixi(){
        $mobile=I('pn');        
        if($mobile){
            $wexiexists=D('weixi')->where('mobile=%s',array($mobile))->find();
            if(I('cdkey')){
                $data['cdkey']=I('cdkey');
            }
            if(I('wx')){
                $data['weixi']=I('wx');
            }
            if(I('pn')){
                $data['mobile']=I('pn');
            }
            if(I('pwd')){
                $data['pwd']=I('pwd');
            }
            if(I('email')){
                $data['email']=I('email');
            }
            if(I('emailpwd')){
                $data['emailpwd']=I('emailpwd');
            }
            if(I('wn')){
                $data['weixiname']=I('wn');
            }
       
            if($wexiexists){
               $data['updatetime']=time();
                $data=D('weixi')->create($data);
                $result=D('weixi')->where("mobile='%s'",array($mobile))->save($data);
                if($result){
                    echo 1;
                    exit();
                }else{
                    echo 0;
                    exit();
                }
            }else{
                $data['status']=1;
                $data['createtime']=time();
                $data=D('weixi')->create($data);
                $result=D('weixi')->add($data);
                if($result){
                    echo 1;
                    exit();
                }else{
                    echo 0;
                    exit();
                }
            }            
            
        }else{
            echo 0;
            exit();
        }
    }
}

