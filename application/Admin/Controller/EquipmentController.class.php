<?php

/**
 * 设备管理
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class EquipmentController extends AdminbaseController {
	
	function _initialize() {
	    empty($_GET['upw'])?"":session("__SP_UPW__",$_GET['upw']);//设置后台登录加密码	    
		parent::_initialize();
		$this->initMenu();
	}
	
    /**
     * 后台框架首页
     */
    public function index() {
    	if (C('LANG_SWITCH_ON',null,false)){
    		$this->load_menu_lang();
    	}
        $this->assign("SUBMENU_CONFIG", D("Common/Menu")->menu_json());
       	$this->display();
        
    }
    public function mobile(){
        $list=D('equictive')->select();
        $this->assign('list',$list);
        $this->display();
    }

    public function act(){        
        $this->display();
    }

    public function info(){
        $eqid=I('id');        
        if(IS_POST&&$eqid){
            $runcode=I('post.runcode');
            $result=D('equictive')->where('id=%d',array($eqid))->setfield('runcodeid',$runcode);

            if($result){
                $this->success('修改成功');
            }else{
                $this->error('修改失败');
            }
        }
        $list=D('runcode')->getfield('id,taskname',true);
        $this->assign('list',$list);
        $this->display();
    }
    
    private function load_menu_lang(){
    	$apps=sp_scan_dir(SPAPP."*",GLOB_ONLYDIR);
    	$error_menus=array();
    	foreach ($apps as $app){
    		if(is_dir(SPAPP.$app)){
    			$admin_menu_lang_file=SPAPP.$app."/Lang/".LANG_SET."/admin_menu.php";
    			if(is_file($admin_menu_lang_file)){
    				$lang=include $admin_menu_lang_file;
    				L($lang);
    			}
    		}
    	}
    }

}

